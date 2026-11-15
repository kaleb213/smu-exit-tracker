<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * exam_sessions + session_answers — every attempt is recorded
 * permanently (functional requirement: permanent attempt records).
 */
final class ExamSession extends Model
{
    public static function create(int $userId, string $mode, ?int $subjectId, ?int $topicId, int $total, ?int $timeLimit): int
    {
        Database::execute(
            'INSERT INTO exam_sessions (user_id, mode, subject_id, topic_id, total_questions, time_limit_minutes)
             VALUES (?,?,?,?,?,?)',
            [$userId, $mode, $subjectId, $topicId, $total, $timeLimit]
        );
        return Database::lastId();
    }

    public static function find(int $id): ?array
    {
        return Database::first(
            'SELECT es.*, s.name AS subject_name, t.name AS topic_name
             FROM exam_sessions es
             LEFT JOIN subjects s ON es.subject_id = s.id
             LEFT JOIN topics t   ON es.topic_id = t.id
             WHERE es.id = ?',
            [$id]
        );
    }

    public static function belongsToUser(int $sessionId, int $userId): bool
    {
        return Database::first(
            'SELECT id FROM exam_sessions WHERE id = ? AND user_id = ?',
            [$sessionId, $userId]
        ) !== null;
    }

    public static function addAnswer(int $sessionId, int $questionId, ?string $selected, bool $correct): void
    {
        if (DB_DRIVER === 'sqlite') {
            // portable UPSERT for the offline test profile
            Database::execute(
                'INSERT INTO session_answers (session_id, question_id, selected_option, is_correct)
                 VALUES (?,?,?,?)
                 ON CONFLICT(session_id, question_id) DO UPDATE SET
                     selected_option = excluded.selected_option,
                     is_correct = excluded.is_correct',
                [$sessionId, $questionId, $selected, $correct ? 1 : 0]
            );
            return;
        }
        Database::execute(
            'INSERT INTO session_answers (session_id, question_id, selected_option, is_correct)
             VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE selected_option = VALUES(selected_option), is_correct = VALUES(is_correct)',
            [$sessionId, $questionId, $selected, $correct ? 1 : 0]
        );
    }

    public static function answeredCount(int $sessionId): int
    {
        return (int) (Database::first(
            'SELECT COUNT(*) AS c FROM session_answers WHERE session_id = ?',
            [$sessionId]
        )['c'] ?? 0);
    }

    /** Evaluate unanswered questions as skipped (wrong) and finalise the session. */
    public static function complete(int $sessionId): array
    {
        $session = self::find($sessionId);

        // Practice sessions: remaining questions of the topic are recorded as skipped.
        // Mock sessions (topic_id NULL) record their skipped set in the controller.
        Database::execute(
            'INSERT INTO session_answers (session_id, question_id, selected_option, is_correct)
             SELECT ?, q.id, NULL, 0
             FROM questions q
             WHERE q.topic_id = ?
               AND q.id NOT IN (SELECT question_id FROM session_answers WHERE session_id = ?)',
            [$sessionId, $session['topic_id'], $sessionId]
        );

        $counts = Database::first(
            'SELECT COUNT(*) AS total,
                    SUM(is_correct = 1) AS correct
             FROM session_answers WHERE session_id = ?',
            [$sessionId]
        );

        $total   = (int) ($counts['total'] ?? 0);
        $correct = (int) ($counts['correct'] ?? 0);
        $score   = $total > 0 ? round($correct / $total * 100, 2) : 0.0; // division-by-zero guard

        Database::execute(
            "UPDATE exam_sessions
             SET status = 'completed', correct_count = ?, score_percent = ?, submitted_at = " . Database::nowSql() . "
             WHERE id = ?",
            [$correct, $score, $sessionId]
        );

        return ['total' => $total, 'correct' => $correct, 'score' => $score];
    }

    /** Force-finalise a timed-out mock exam (auto-submit on deadline). */
    public static function expire(int $sessionId): void
    {
        Database::execute("UPDATE exam_sessions SET status = 'expired', submitted_at = " . Database::nowSql() . " WHERE id = ?", [$sessionId]);
    }

    public static function hasQuestionsLeft(int $sessionId, int $topicId): bool
    {
        return Database::first(
            'SELECT q.id FROM questions q
             WHERE q.topic_id = ? AND q.is_validated = 1
               AND q.id NOT IN (SELECT question_id FROM session_answers WHERE session_id = ?)
             LIMIT 1',
            [$topicId, $sessionId]
        ) !== null;
    }

    /** Paginated attempt history of a user (boundary: pagination for large history). */
    public static function paginateForUser(int $userId, int $page, int $perPage): array
    {
        $total = (int) (Database::first(
            'SELECT COUNT(*) AS c FROM exam_sessions WHERE user_id = ? AND status <> "in_progress"',
            [$userId]
        )['c'] ?? 0);
        $rows = Database::query(
            'SELECT es.id, es.mode, es.total_questions, es.correct_count, es.score_percent,
                    es.status, es.started_at, es.submitted_at,
                    s.name AS subject_name, t.name AS topic_name
             FROM exam_sessions es
             LEFT JOIN subjects s ON es.subject_id = s.id
             LEFT JOIN topics t   ON es.topic_id = t.id
             WHERE es.user_id = ? AND es.status <> "in_progress"
             ORDER BY es.submitted_at DESC, es.id DESC
             LIMIT ' . (($page - 1) * $perPage) . ", $perPage",
            [$userId]
        );
        return ['rows' => $rows, 'total' => $total, 'pages' => max(1, (int) ceil($total / $perPage))];
    }

    /** Last N completed attempts, oldest first (score-history chart). */
    public static function lastCompleted(int $userId, int $limit): array
    {
        $rows = Database::query(
            'SELECT id, mode, score_percent, submitted_at FROM exam_sessions
             WHERE user_id = ? AND status = "completed"
             ORDER BY submitted_at DESC, id DESC LIMIT ' . max(1, $limit),
            [$userId]
        );
        return array_reverse($rows);
    }

    /** Per-topic accuracy across all completed attempts of a user. */
    public static function topicStatsForUser(int $userId): array
    {
        return Database::query(
            'SELECT t.id, t.name AS topic, s.name AS subject,
                    COUNT(*) AS answered,
                    SUM(sa.is_correct = 1) AS correct,
                    ROUND(SUM(sa.is_correct = 1) / COUNT(*) * 100) AS accuracy
             FROM session_answers sa
             JOIN exam_sessions es ON sa.session_id = es.id
             JOIN questions q ON sa.question_id = q.id
             JOIN topics t ON q.topic_id = t.id
             JOIN subjects s ON t.subject_id = s.id
             WHERE es.user_id = ? AND es.status IN ("completed","expired")
             GROUP BY t.id
             ORDER BY accuracy ASC',
            [$userId]
        );
    }

    /** Per-subject average score across completed attempts of a user. */
    public static function subjectStatsForUser(int $userId): array
    {
        $rows = Database::query(
            'SELECT s.name AS subject, AVG(es.score_percent) AS avg_score, COUNT(*) AS attempts
             FROM exam_sessions es
             LEFT JOIN subjects s ON es.subject_id = s.id
             WHERE es.user_id = ? AND es.status = "completed"
             GROUP BY s.id
             ORDER BY avg_score ASC',
            [$userId]
        );
        foreach ($rows as &$r) {
            $r['subject'] = $r['subject'] ?? 'All subjects (mock)';
        }
        return $rows;
    }

    public static function countForUser(int $userId, ?string $mode = null): int
    {
        if ($mode === null) {
            return (int) (Database::first(
                'SELECT COUNT(*) AS c FROM exam_sessions WHERE user_id = ? AND status <> "in_progress"',
                [$userId]
            )['c'] ?? 0);
        }
        return (int) (Database::first(
            'SELECT COUNT(*) AS c FROM exam_sessions WHERE user_id = ? AND mode = ? AND status <> "in_progress"',
            [$userId, $mode]
        )['c'] ?? 0);
    }
}

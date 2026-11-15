<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Reporting & Analytics subsystem (instructor-facing).
 * Aggregates student performance data so tutorials can be
 * targeted at the topics the cohort struggles with.
 */
final class ReportService
{
    /** Class-level KPIs for the instructor dashboard. */
    public static function classKpis(): array
    {
        return [
            'active_students' => (int) (Database::first(
                "SELECT COUNT(*) AS c FROM users WHERE role = 'student' AND status = 'active'"
            )['c'] ?? 0),
            'attempts' => (int) (Database::first(
                'SELECT COUNT(*) AS c FROM exam_sessions WHERE status IN ("completed","expired")'
            )['c'] ?? 0),
            'class_avg' => (float) (Database::first(
                'SELECT COALESCE(AVG(score_percent), 0) AS a FROM exam_sessions WHERE status = "completed"'
            )['a'] ?? 0),
            'questions' => (int) (Database::first('SELECT COUNT(*) AS c FROM questions WHERE is_validated = 1')['c'] ?? 0),
        ];
    }

    /** Average score by subject across the whole cohort. */
    public static function classAvgBySubject(): array
    {
        $rows = Database::query(
            'SELECT COALESCE(s.name, "Full mock (all subjects)") AS subject,
                    AVG(es.score_percent) AS avg_score,
                    COUNT(*) AS attempts
             FROM exam_sessions es
             LEFT JOIN subjects s ON es.subject_id = s.id
             WHERE es.status = "completed" AND es.mode = "practice"
             GROUP BY s.id
             ORDER BY avg_score ASC'
        );
        return $rows;
    }

    /**
     * Weak topics across the cohort (needs tutorial intervention).
     * Boundary condition: a topic is only ranked once it has at least
     * $minAnswers recorded answers, so tiny samples don't dominate.
     */
    public static function weakestTopics(int $minAnswers = 3, int $limit = 10): array
    {
        return Database::query(
            'SELECT t.name AS topic, s.name AS subject,
                    COUNT(*) AS answers,
                    ROUND(SUM(sa.is_correct = 1) / COUNT(*) * 100) AS accuracy
             FROM session_answers sa
             JOIN questions q ON sa.question_id = q.id
             JOIN topics t ON q.topic_id = t.id
             JOIN subjects s ON t.subject_id = s.id
             JOIN exam_sessions es ON sa.session_id = es.id AND es.status IN ("completed","expired")
             GROUP BY t.id
             HAVING COUNT(*) >= ?
             ORDER BY accuracy ASC
             LIMIT ' . max(1, $limit),
            [$minAnswers]
        );
    }

    /** Weekly trend of the class average (last 6 weeks). */
    public static function weeklyTrend(): array
    {
        $weekExpr = DB_DRIVER === 'sqlite'
            ? "CAST(strftime('%Y%W', submitted_at) AS INTEGER)"   // SQLite
            : 'YEARWEEK(submitted_at, 1)';                        // MySQL

        $rows = Database::query(
            "SELECT $weekExpr AS yw,
                    ROUND(AVG(score_percent)) AS avg_score,
                    COUNT(*) AS attempts
             FROM exam_sessions
             WHERE status = 'completed' AND submitted_at IS NOT NULL
             GROUP BY yw
             ORDER BY yw DESC
             LIMIT 6"
        );
        return array_reverse($rows);
    }

    /** Per-student performance table (Student Performance page). */
    public static function studentPerformance(int $limit = 100): array
    {
        return Database::query(
            "SELECT u.id, u.name, u.id_number, u.status,
                    COUNT(es.id) AS attempts,
                    ROUND(COALESCE(AVG(es.score_percent), 0)) AS avg_score,
                    MAX(es.submitted_at) AS last_attempt
             FROM users u
             LEFT JOIN exam_sessions es
                    ON es.user_id = u.id AND es.status IN ('completed','expired')
             WHERE u.role = 'student'
             GROUP BY u.id
             ORDER BY avg_score ASC
             LIMIT " . max(1, $limit)
        );
    }

    /** Weakest subject per student (for the performance table). */
    public static function weakestSubjectPerStudent(): array
    {
        $rows = Database::query(
            'SELECT u.id AS user_id, COALESCE(s.name, "Full mock") AS subject,
                    ROUND(AVG(es.score_percent)) AS avg_score
             FROM users u
             JOIN exam_sessions es ON es.user_id = u.id AND es.status = "completed"
             LEFT JOIN subjects s ON es.subject_id = s.id
             WHERE u.role = "student"
             GROUP BY u.id, s.id
             ORDER BY u.id, avg_score ASC'
        );
        $map = [];
        foreach ($rows as $r) {
            if (!isset($map[(int) $r['user_id']])) {
                $map[(int) $r['user_id']] = $r['subject'] . ' (' . $r['avg_score'] . '%)';
            }
        }
        return $map;
    }

    /** Question review: how the cohort performs on each stored question. */
    public static function questionReview(?int $subjectId, int $limit = 50): array
    {
        $where  = $subjectId !== null ? 'WHERE t.subject_id = ?' : '';
        $params = $subjectId !== null ? [$subjectId] : [];

        return Database::query(
            "SELECT q.id, q.question_text, q.difficulty, q.is_validated,
                    t.name AS topic, s.name AS subject,
                    COUNT(sa.id) AS answered,
                    ROUND(COALESCE(SUM(sa.is_correct = 1) / COUNT(sa.id) * 100, 0)) AS success_rate
             FROM questions q
             JOIN topics t ON q.topic_id = t.id
             JOIN subjects s ON t.subject_id = s.id
             LEFT JOIN session_answers sa ON sa.question_id = q.id
             $where
             GROUP BY q.id
             ORDER BY success_rate ASC, q.id DESC
             LIMIT " . max(1, $limit),
            $params
        );
    }

    /** System-wide stats for the admin dashboard. */
    public static function systemStats(): array
    {
        return [
            'students'    => (int) (Database::first("SELECT COUNT(*) AS c FROM users WHERE role = 'student'")['c'] ?? 0),
            'instructors' => (int) (Database::first("SELECT COUNT(*) AS c FROM users WHERE role = 'instructor'")['c'] ?? 0),
            'subjects'    => (int) (Database::first('SELECT COUNT(*) AS c FROM subjects')['c'] ?? 0),
            'topics'      => (int) (Database::first('SELECT COUNT(*) AS c FROM topics')['c'] ?? 0),
            'questions'   => (int) (Database::first('SELECT COUNT(*) AS c FROM questions')['c'] ?? 0),
            'attempts'    => (int) (Database::first('SELECT COUNT(*) AS c FROM exam_sessions WHERE status <> "in_progress"')['c'] ?? 0),
        ];
    }
}

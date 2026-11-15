<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * questions table — the validated question bank organised by
 * subject and topic (Question Bank Management subsystem).
 */
final class Question extends Model
{
    /** Randomised validated questions for a topic (Practice Mode). */
    public static function randomByTopic(int $topicId, int $limit): array
    {
        return Database::query(
            'SELECT id, topic_id, question_text, option_a, option_b, option_c, option_d,
                    correct_option, explanation, difficulty
             FROM questions
             WHERE topic_id = ? AND is_validated = 1
             ORDER BY ' . Database::randomSql() . '
             LIMIT ' . max(1, $limit),
            [$topicId]
        );
    }

    /** Randomised validated questions across ALL subjects (Mock Exam). */
    public static function randomAcrossSubjects(int $limit): array
    {
        return Database::query(
            'SELECT id, topic_id, question_text, option_a, option_b, option_c, option_d,
                    correct_option, explanation, difficulty
             FROM questions
             WHERE is_validated = 1
             ORDER BY ' . Database::randomSql() . '
             LIMIT ' . max(1, $limit)
        );
    }

    /** How many validated questions exist for a topic. */
    public static function countValidatedByTopic(int $topicId): int
    {
        return (int) (Database::first(
            'SELECT COUNT(*) AS c FROM questions WHERE topic_id = ? AND is_validated = 1',
            [$topicId]
        )['c'] ?? 0);
    }

    public static function find(int $id): ?array
    {
        return Database::first(
            'SELECT q.*, t.name AS topic_name, s.name AS subject_name
             FROM questions q
             JOIN topics t ON q.topic_id = t.id
             JOIN subjects s ON t.subject_id = s.id
             WHERE q.id = ?',
            [$id]
        );
    }

    /** Paginated admin list with filters (subject, topic, text search). */
    public static function paginate(?int $subjectId, ?int $topicId, ?string $search, int $page, int $perPage): array
    {
        $where  = ['1=1'];
        $params = [];
        if ($topicId !== null) {
            $where[] = 'q.topic_id = ?';
            $params[] = $topicId;
        } elseif ($subjectId !== null) {
            $where[] = 't.subject_id = ?';
            $params[] = $subjectId;
        }
        if ($search !== null && $search !== '') {
            $where[] = 'q.question_text LIKE ?';
            $params[] = "%$search%";
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) (Database::first(
            "SELECT COUNT(*) AS c FROM questions q JOIN topics t ON q.topic_id = t.id WHERE $whereSql",
            $params
        )['c'] ?? 0);

        $rows = Database::query(
            "SELECT q.id, q.question_text, q.difficulty, q.is_validated, q.correct_option,
                    t.name AS topic_name, s.name AS subject_name
             FROM questions q
             JOIN topics t ON q.topic_id = t.id
             JOIN subjects s ON t.subject_id = s.id
             WHERE $whereSql
             ORDER BY q.id DESC
             LIMIT " . (($page - 1) * $perPage) . ", $perPage",
            $params
        );
        return ['rows' => $rows, 'total' => $total, 'pages' => max(1, (int) ceil($total / $perPage))];
    }

    public static function create(array $q): int
    {
        Database::execute(
            'INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d,
                                    correct_option, explanation, difficulty, is_validated, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [
                $q['topic_id'], $q['question_text'], $q['option_a'], $q['option_b'],
                $q['option_c'], $q['option_d'], $q['correct_option'], $q['explanation'],
                $q['difficulty'], $q['is_validated'], $q['created_by'],
            ]
        );
        return Database::lastId();
    }

    public static function update(int $id, array $q): void
    {
        Database::execute(
            'UPDATE questions SET topic_id=?, question_text=?, option_a=?, option_b=?, option_c=?, option_d=?,
                                  correct_option=?, explanation=?, difficulty=?, is_validated=?
             WHERE id=?',
            [
                $q['topic_id'], $q['question_text'], $q['option_a'], $q['option_b'],
                $q['option_c'], $q['option_d'], $q['correct_option'], $q['explanation'],
                $q['difficulty'], $q['is_validated'], $id,
            ]
        );
    }

    /** Delete is blocked when answers exist (FK RESTRICT — history integrity). */
    public static function hasAnswers(int $id): bool
    {
        return Database::first('SELECT id FROM session_answers WHERE question_id = ? LIMIT 1', [$id]) !== null;
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM questions WHERE id = ?', [$id]);
    }

    public static function setValidated(int $id, bool $validated): void
    {
        Database::execute('UPDATE questions SET is_validated = ? WHERE id = ?', [$validated ? 1 : 0, $id]);
    }

    public static function countAll(): int
    {
        return (int) (Database::first('SELECT COUNT(*) AS c FROM questions')['c'] ?? 0);
    }
}

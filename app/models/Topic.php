<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * topics table — every topic belongs to one subject.
 */
final class Topic extends Model
{
    public static function bySubject(int $subjectId): array
    {
        return Database::query(
            'SELECT t.*, COUNT(q.id) AS question_count
             FROM topics t
             LEFT JOIN questions q ON q.topic_id = t.id AND q.is_validated = 1
             WHERE t.subject_id = ?
             GROUP BY t.id
             ORDER BY t.name',
            [$subjectId]
        );
    }

    public static function allWithSubject(): array
    {
        return Database::query(
            'SELECT t.*, s.name AS subject_name, COUNT(q.id) AS question_count
             FROM topics t
             JOIN subjects s ON t.subject_id = s.id
             LEFT JOIN questions q ON q.topic_id = t.id AND q.is_validated = 1
             GROUP BY t.id
             ORDER BY s.name, t.name'
        );
    }

    public static function find(int $id): ?array
    {
        return Database::first(
            'SELECT t.*, s.name AS subject_name, s.id AS subject_id
             FROM topics t JOIN subjects s ON t.subject_id = s.id
             WHERE t.id = ?',
            [$id]
        );
    }

    public static function create(int $subjectId, string $name): int
    {
        Database::execute('INSERT INTO topics (subject_id, name) VALUES (?,?)', [$subjectId, $name]);
        return Database::lastId();
    }

    public static function update(int $id, string $name): void
    {
        Database::execute('UPDATE topics SET name = ? WHERE id = ?', [$name, $id]);
    }

    public static function hasQuestions(int $id): bool
    {
        return Database::first('SELECT id FROM questions WHERE topic_id = ? LIMIT 1', [$id]) !== null;
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM topics WHERE id = ?', [$id]);
    }
}

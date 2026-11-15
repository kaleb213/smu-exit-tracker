<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * subjects table + topics table access (content organisation).
 */
final class Subject extends Model
{
    /** All subjects with topic & question counts (dashboard cards). */
    public static function allWithCounts(): array
    {
        return Database::query(
            'SELECT s.id, s.name, s.description,
                    COUNT(DISTINCT t.id) AS topic_count,
                    COUNT(q.id) AS question_count
             FROM subjects s
             LEFT JOIN topics t ON t.subject_id = s.id
             LEFT JOIN questions q ON q.topic_id = t.id AND q.is_validated = 1
             GROUP BY s.id
             ORDER BY s.name'
        );
    }

    public static function find(int $id): ?array
    {
        return Database::first('SELECT * FROM subjects WHERE id = ?', [$id]);
    }

    public static function all(): array
    {
        return Database::query('SELECT * FROM subjects ORDER BY name');
    }

    public static function create(string $name, string $description): int
    {
        Database::execute('INSERT INTO subjects (name, description) VALUES (?,?)', [$name, $description]);
        return Database::lastId();
    }

    public static function update(int $id, string $name, string $description): void
    {
        Database::execute('UPDATE subjects SET name = ?, description = ? WHERE id = ?', [$name, $description, $id]);
    }

    /** Delete is refused when validated questions exist (data integrity). */
    public static function hasQuestions(int $id): bool
    {
        return Database::first(
            'SELECT q.id FROM questions q JOIN topics t ON q.topic_id = t.id WHERE t.subject_id = ? LIMIT 1',
            [$id]
        ) !== null;
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM subjects WHERE id = ?', [$id]);
    }

    public static function search(string $term): array
    {
        return Database::query(
            'SELECT * FROM subjects WHERE name LIKE ? OR description LIKE ? ORDER BY name',
            ["%$term%", "%$term%"]
        );
    }
}

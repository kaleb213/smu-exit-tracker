<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * users table — one table for students, instructors and admins.
 */
final class User extends Model
{
    /** Find a user by login credential (id_number). */
    public static function findByLogin(string $idNumber): ?array
    {
        return Database::first(
            'SELECT id, name, id_number, password_hash, role, status, profile_photo FROM users WHERE id_number = ?',
            [$idNumber]
        );
    }

    public static function find(int $id): ?array
    {
        return Database::first('SELECT * FROM users WHERE id = ?', [$id]);
    }

    /** Student self-registration (business rule: only students self-register). */
    public static function createStudent(string $name, string $idNumber, string $passwordHash): int
    {
        Database::execute(
            'INSERT INTO users (name, id_number, password_hash, role) VALUES (?,?,?,?)',
            [$name, $idNumber, $passwordHash, 'student']
        );
        return Database::lastId();
    }

    /** Admin-only: create instructor accounts (no public registration). */
    public static function createInstructor(string $name, string $idNumber, string $passwordHash): int
    {
        Database::execute(
            'INSERT INTO users (name, id_number, password_hash, role) VALUES (?,?,?,?)',
            [$name, $idNumber, $passwordHash, 'instructor']
        );
        return Database::lastId();
    }

    public static function idNumberExists(string $idNumber, ?int $exceptId = null): bool
    {
        if ($exceptId === null) {
            return Database::first('SELECT id FROM users WHERE id_number = ?', [$idNumber]) !== null;
        }
        return Database::first(
            'SELECT id FROM users WHERE id_number = ? AND id <> ?',
            [$idNumber, $exceptId]
        ) !== null;
    }

    /** Paginated user list with optional role filter and search. */
    public static function paginate(?string $role, ?string $search, int $page, int $perPage): array
    {
        $where  = [];
        $params = [];
        if ($role !== null && in_array($role, ['student', 'instructor', 'admin'], true)) {
            $where[] = 'role = ?';
            $params[] = $role;
        }
        if ($search !== null && $search !== '') {
            $where[] = '(name LIKE ? OR id_number LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) (Database::first("SELECT COUNT(*) AS c FROM users $whereSql", $params)['c'] ?? 0);
        $orderSql = DB_DRIVER === 'sqlite'
            ? "ORDER BY CASE role WHEN 'admin' THEN 1 WHEN 'instructor' THEN 2 ELSE 3 END, name"
            : "ORDER BY FIELD(role,'admin','instructor','student'), name";

        $rows  = Database::query(
            "SELECT id, name, id_number, role, status, created_at, last_login
             FROM users $whereSql
             $orderSql
             LIMIT " . (($page - 1) * $perPage) . ", $perPage",
            $params
        );
        return ['rows' => $rows, 'total' => $total, 'pages' => max(1, (int) ceil($total / $perPage))];
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::execute('UPDATE users SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        Database::execute('UPDATE users SET password_hash = ? WHERE id = ?', [$passwordHash, $id]);
    }

    public static function updateName(int $id, string $name): void
    {
        Database::execute('UPDATE users SET name = ? WHERE id = ?', [$name, $id]);
    }

    public static function updateProfilePhoto(int $id, ?string $path): void
    {
        Database::execute('UPDATE users SET profile_photo = ? WHERE id = ?', [$path, $id]);
    }

    public static function updatePresence(int $id, string $status): void
    {
        if (!in_array($status, ['online', 'away', 'offline'], true)) {
            $status = 'offline';
        }
        Database::execute(
            'UPDATE users SET presence_status = ?, last_seen_at = ' . Database::nowSql() . ' WHERE id = ?',
            [$status, $id]
        );
    }

    public static function touchLogin(int $id): void
    {
        Database::execute('UPDATE users SET last_login = ' . Database::nowSql() . ' WHERE id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM users WHERE id = ?', [$id]);
    }


    /** Count active administrators to prevent locking the system out. */
    public static function countActiveAdmins(): int
    {
        return (int) (Database::first("SELECT COUNT(*) AS c FROM users WHERE role = 'admin' AND status = 'active'")['c'] ?? 0);
    }

    public static function countByRole(string $role): int
    {
        return (int) (Database::first('SELECT COUNT(*) AS c FROM users WHERE role = ?', [$role])['c'] ?? 0);
    }
}

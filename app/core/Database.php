<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Data layer — single PDO connection used by all models/services.
 * All queries throughout the system use prepared statements
 * (documented security requirement: prevents SQL injection).
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        try {
            if (DB_DRIVER === 'sqlite') {
                // Offline test profile (tests/local_test) — documented in tests/README.md
                $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
            } else {
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
                $pdo = new PDO($dsn, DB_USER, DB_PASS);
            }
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Database connection failed. Start MySQL in XAMPP and import database/schema.sql. (' . $e->getMessage() . ')',
                0,
                $e
            );
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Use native prepares (no emulation) — real parameterized queries.
        if (DB_DRIVER === 'mysql') {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$connection = $pdo;
    }

    /** Run a prepared statement and return all rows. */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Run a prepared statement and return the first row (or null). */
    public static function first(string $sql, array $params = []): ?array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Run an INSERT/UPDATE/DELETE and return affected row count. */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Last inserted id. */
    public static function lastId(): int
    {
        return (int) self::connection()->lastInsertId();
    }

    /**
     * Driver-aware SQL "current timestamp" fragment:
     * MySQL NOW() / SQLite CURRENT_TIMESTAMP.
     */
    public static function nowSql(): string
    {
        return DB_DRIVER === 'sqlite' ? 'CURRENT_TIMESTAMP' : 'NOW()';
    }

    /**
     * Driver-aware SQL "random order" fragment:
     * MySQL RAND() / SQLite RANDOM().
     */
    public static function randomSql(): string
    {
        return DB_DRIVER === 'sqlite' ? 'RANDOM()' : 'RAND()';
    }
}

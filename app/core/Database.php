<?php
/**
 * ============================================================
 *  Database.php — PDO wrapper (single shared connection)
 *  PDF security requirement: prepared statements everywhere
 *  (protects against SQL injection)
 * ============================================================
 */

class Database
{
    private static $instance = null;   // the one shared connection
    private $pdo;

    // Private constructor = nobody can create a second connection
    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw errors, don't fail silently
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // rows come back as arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                   // REAL prepared statements (SQLi protection)
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (ENVIRONMENT === 'development') {
                die('Database connection failed: ' . $e->getMessage());
            }
            die('Database connection failed. Please try again later.');
        }
    }

    // Get the single shared connection (singleton pattern)
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Run a prepared query. ALWAYS pass user input as $params — never inside $sql.
    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Fetch one row (or false if none)
    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    // Fetch many rows (or empty array)
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    // ID of the last inserted row (used after INSERT)
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
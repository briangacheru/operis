<?php

/**
 * Database — singleton connection manager.
 *
 * Provides a single shared MySQLi connection ($con) for the writer app and an
 * optional PDO connection ($pdo) for the admin app, both lazily initialised.
 * All query helpers use prepared statements automatically.
 *
 * Usage:
 *   $db  = Database::getInstance();
 *   $con = $db->getMysqli();   // MySQLi — writer app
 *   $pdo = $db->getPdo();      // PDO    — admin app
 *
 *   $row  = $db->fetchOne("SELECT * FROM tblwriters WHERE email = ?", "s", $email);
 *   $rows = $db->fetchAll("SELECT * FROM tblwriters WHERE is_online = ?", "i", 1);
 *   $db->query("UPDATE tblwriters SET is_online = ? WHERE email = ?", "is", 0, $email);
 */
class Database
{
    private static ?Database $instance = null;

    private mysqli $mysqli;
    private ?PDO   $pdo = null;

    private string $host;
    private string $user;
    private string $pass;
    private string $name;
    private string $charset;

    private function __construct()
    {
        $this->host    = defined('DB_HOST')    ? DB_HOST    : 'localhost';
        $this->user    = defined('DB_USER')    ? DB_USER    : 'root';
        $this->pass    = defined('DB_PASS')    ? DB_PASS    : '';
        $this->name    = defined('DB_NAME')    ? DB_NAME    : 'tasker';
        $this->charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        $this->mysqli = new mysqli($this->host, $this->user, $this->pass, $this->name);

        if ($this->mysqli->connect_error) {
            throw new RuntimeException('MySQLi connection failed: ' . $this->mysqli->connect_error);
        }

        $this->mysqli->set_charset($this->charset);
    }

    // Prevent cloning and unserialization.
    private function __clone() {}
    public function __wakeup(): never
    {
        throw new RuntimeException('Database instances cannot be unserialized.');
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    // -------------------------------------------------------------------------
    // Connection accessors
    // -------------------------------------------------------------------------

    public function getMysqli(): mysqli
    {
        return $this->mysqli;
    }

    /** Lazily creates a PDO connection on first call. */
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return $this->pdo;
    }

    // -------------------------------------------------------------------------
    // MySQLi query helpers
    // -------------------------------------------------------------------------

    /**
     * Execute a prepared statement. Returns the statement on success so the
     * caller can inspect affected_rows or get_result() if needed.
     *
     * @param  string $sql    Query with ? placeholders.
     * @param  string $types  bind_param type string, e.g. "ssi".
     * @param  mixed  ...$params  Values matching $types.
     */
    public function query(string $sql, string $types = '', mixed ...$params): mysqli_stmt
    {
        $stmt = $this->mysqli->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . $this->mysqli->error);
        }

        if ($types !== '' && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Execute failed: ' . $stmt->error);
        }

        return $stmt;
    }

    /**
     * Fetch a single row as an associative array, or null if not found.
     */
    public function fetchOne(string $sql, string $types = '', mixed ...$params): ?array
    {
        $stmt   = $this->query($sql, $types, ...$params);
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Fetch all matching rows as an array of associative arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, string $types = '', mixed ...$params): array
    {
        $stmt   = $this->query($sql, $types, ...$params);
        $result = $stmt->get_result();
        $rows   = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Returns the auto-generated ID from the last INSERT.
     */
    public function lastInsertId(): int
    {
        return (int) $this->mysqli->insert_id;
    }

    // -------------------------------------------------------------------------
    // Transaction helpers
    // -------------------------------------------------------------------------

    public function beginTransaction(): void
    {
        $this->mysqli->begin_transaction();
    }

    public function commit(): void
    {
        $this->mysqli->commit();
    }

    public function rollback(): void
    {
        $this->mysqli->rollback();
    }

    /**
     * Execute a callable inside a transaction. Automatically commits on success
     * and rolls back + re-throws on any exception.
     *
     * @param  callable $callback  Receives $this (Database) as its only argument.
     * @return mixed               The return value of $callback.
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}

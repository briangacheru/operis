<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/Database.php';

/**
 * BaseRepository — shared MySQLi helpers for all concrete repositories.
 *
 * Subclasses declare $table and optionally $primaryKey, then get free
 * implementations of findById(), findAll(), count(), and delete().
 * Complex queries live as named methods in the concrete class.
 */
abstract class BaseRepository
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected mysqli $db;

    public function __construct()
    {
        $this->db = Database::getMySQLi();
    }

    // -----------------------------------------------------------------------
    // Generic CRUD helpers
    // -----------------------------------------------------------------------

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /** @return array<int, array> */
    public function findAll(string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        if ($limit > 0) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function count(string $where = '', array $params = [], string $types = ''): int
    {
        $sql  = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0];
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?"
        );
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function softDelete(int $id, string $column = 'is_deleted'): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE `{$this->table}` SET `$column` = 1 WHERE `{$this->primaryKey}` = ?"
        );
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // -----------------------------------------------------------------------
    // Protected utilities for subclasses
    // -----------------------------------------------------------------------

    /**
     * Run a SELECT and return all rows.
     *
     * @param  array<mixed> $params
     */
    protected function query(string $sql, string $types = '', array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Query prepare failed: ' . $this->db->error . ' | SQL: ' . $sql);
        }
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Run a SELECT and return the first row or null.
     *
     * @param  array<mixed> $params
     */
    protected function queryOne(string $sql, string $types = '', array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Query prepare failed: ' . $this->db->error . ' | SQL: ' . $sql);
        }
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Run an INSERT/UPDATE/DELETE and return affected rows.
     *
     * @param  array<mixed> $params
     */
    protected function execute(string $sql, string $types = '', array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Query prepare failed: ' . $this->db->error . ' | SQL: ' . $sql);
        }
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->affected_rows;
    }

    /** Last auto-increment ID after an INSERT. */
    protected function lastInsertId(): int
    {
        return (int) $this->db->insert_id;
    }
}

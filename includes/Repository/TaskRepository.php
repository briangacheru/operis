<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';

/**
 * TaskRepository — data access for the tbltasks table.
 */
class TaskRepository extends BaseRepository
{
    protected string $table = 'tbltasks';

    // -----------------------------------------------------------------------
    // Fetch
    // -----------------------------------------------------------------------

    public function findByWriter(string $email, string $status = '', bool $includeDraft = false): array
    {
        $where  = 'email = ? AND is_deleted = 0';
        $params = [$email];
        $types  = 's';

        if ($status) {
            $where   .= ' AND status = ?';
            $params[] = $status;
            $types   .= 's';
        }
        if (!$includeDraft) {
            $where .= " AND status != 'Draft'";
        }

        return $this->query(
            "SELECT * FROM tbltasks WHERE $where ORDER BY create_date DESC",
            $types, $params
        );
    }

    public function findById(int $id): ?array
    {
        return $this->queryOne(
            "SELECT * FROM tbltasks WHERE id = ? LIMIT 1",
            'i', [$id]
        );
    }

    public function findByWriterAndId(string $email, int $id): ?array
    {
        return $this->queryOne(
            "SELECT * FROM tbltasks WHERE id = ? AND email = ? LIMIT 1",
            'is', [$id, $email]
        );
    }

    /** Paginated task list for a writer. */
    public function paginateByWriter(
        string $email,
        string $status,
        int    $limit,
        int    $offset,
        bool   $isDeleted = false
    ): array {
        $params = [$email];
        $types  = 's';
        $where  = 'email = ? AND is_deleted = ' . ($isDeleted ? 1 : 0);

        if ($status) {
            $where   .= ' AND status = ?';
            $params[] = $status;
            $types   .= 's';
        }

        $params[] = $limit;
        $params[] = $offset;
        $types   .= 'ii';

        return $this->query(
            "SELECT * FROM tbltasks WHERE $where ORDER BY create_date DESC LIMIT ? OFFSET ?",
            $types, $params
        );
    }

    public function countByWriter(string $email, string $status = '', bool $isDeleted = false): int
    {
        $where  = 'email = ? AND is_deleted = ' . ($isDeleted ? 1 : 0);
        $params = [$email];
        $types  = 's';

        if ($status) {
            $where   .= ' AND status = ?';
            $params[] = $status;
            $types   .= 's';
        }

        return $this->count($where, $params, $types);
    }

    public function sumEarnings(string $email, bool $paid): float
    {
        $row = $this->queryOne(
            "SELECT SUM(CPP * pages) AS total FROM tbltasks
             WHERE is_deleted = 0 AND is_paid = ? AND status = 'Completed' AND email = ?",
            'is', [(int) $paid, $email]
        );
        return (float) ($row['total'] ?? 0);
    }

    public function getTodayDue(string $email): array
    {
        return $this->query(
            "SELECT * FROM tbltasks
             WHERE is_deleted = 0 AND DATE(due_date) = CURDATE() AND email = ?
             ORDER BY due_date ASC",
            's', [$email]
        );
    }

    public function getOverdue(string $email): array
    {
        return $this->query(
            "SELECT * FROM tbltasks
             WHERE is_deleted = 0 AND status = 'In Progress' AND due_date < NOW() AND email = ?
             ORDER BY due_date ASC",
            's', [$email]
        );
    }

    public function getUnacknowledged(string $email): array
    {
        return $this->query(
            "SELECT * FROM tbltasks
             WHERE is_deleted = 0
               AND (status = 'In Progress' OR is_confirmed = 1)
               AND email = ?
               AND acknowledged = 0
             ORDER BY create_date DESC",
            's', [$email]
        );
    }

    // -----------------------------------------------------------------------
    // Mutate
    // -----------------------------------------------------------------------

    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO tbltasks
             (topic, description, pages, CPP, due_date, status, email, create_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            'ssiidss',
            [
                $data['topic'],
                $data['description'] ?? '',
                (int)   $data['pages'],
                (float) $data['CPP'],
                $data['due_date'],
                $data['status'] ?? 'In Progress',
                $data['email'],
            ]
        );
        return $this->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->execute(
            "UPDATE tbltasks SET status = ? WHERE id = ?",
            'si', [$status, $id]
        ) > 0;
    }

    public function markAcknowledged(int $id): bool
    {
        return $this->execute(
            "UPDATE tbltasks SET acknowledged = 1 WHERE id = ?",
            'i', [$id]
        ) > 0;
    }

    public function markPaid(int $id, bool $paid = true): bool
    {
        return $this->execute(
            "UPDATE tbltasks SET is_paid = ? WHERE id = ?",
            'ii', [(int) $paid, $id]
        ) > 0;
    }

    // -----------------------------------------------------------------------
    // Admin queries (all writers)
    // -----------------------------------------------------------------------

    public function getAllByStatus(string $status, int $limit = 0, int $offset = 0): array
    {
        $sql  = "SELECT t.*, w.username AS writer_name
                 FROM tbltasks t
                 LEFT JOIN tblwriters w ON t.email = w.email
                 WHERE t.is_deleted = 0 AND t.status = ?
                 ORDER BY t.create_date DESC";
        $params = [$status];
        $types  = 's';

        if ($limit > 0) {
            $sql     .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types   .= 'ii';
        }

        return $this->query($sql, $types, $params);
    }

    public function countAll(string $status = '', bool $isDeleted = false): int
    {
        $where  = 'is_deleted = ' . ($isDeleted ? 1 : 0);
        $params = [];
        $types  = '';

        if ($status) {
            $where   .= ' AND status = ?';
            $params[] = $status;
            $types   .= 's';
        }

        return $this->count($where, $params, $types);
    }
}

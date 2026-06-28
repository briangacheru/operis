<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';

/**
 * OverdraftRepository — data access for the tbloverdrafts table.
 */
class OverdraftRepository extends BaseRepository
{
    protected string $table = 'tbloverdrafts';

    public function findByWriter(string $email, bool $settled = false): array
    {
        return $this->query(
            "SELECT * FROM tbloverdrafts
             WHERE email = ? AND is_settled = ? AND is_deleted = 0
             ORDER BY od_date DESC",
            'si', [$email, (int) $settled]
        );
    }

    public function countByWriter(string $email, bool $settled = false): int
    {
        return $this->count(
            'email = ? AND is_settled = ? AND is_deleted = 0',
            [$email, (int) $settled], 'si'
        );
    }

    public function sumByWriter(string $email, bool $settled = false): float
    {
        $row = $this->queryOne(
            "SELECT SUM(amount) AS total FROM tbloverdrafts
             WHERE email = ? AND is_settled = ? AND is_deleted = 0",
            'si', [$email, (int) $settled]
        );
        return (float) ($row['total'] ?? 0);
    }

    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO tbloverdrafts (email, amount, reason, od_date)
             VALUES (?, ?, ?, NOW())",
            'sds',
            [$data['email'], (float) $data['amount'], $data['reason'] ?? '']
        );
        return $this->lastInsertId();
    }

    public function settle(int $id): bool
    {
        return $this->execute(
            "UPDATE tbloverdrafts SET is_settled = 1 WHERE id = ?",
            'i', [$id]
        ) > 0;
    }

    public function getAll(bool $settled = false): array
    {
        return $this->query(
            "SELECT o.*, w.username FROM tbloverdrafts o
             LEFT JOIN tblwriters w ON o.email = w.email
             WHERE o.is_settled = ? AND o.is_deleted = 0
             ORDER BY o.od_date DESC",
            'i', [(int) $settled]
        );
    }
}

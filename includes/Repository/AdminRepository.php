<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';

/**
 * AdminRepository — data access for the tbladmin table.
 */
class AdminRepository extends BaseRepository
{
    protected string $table = 'tbladmin';

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            "SELECT * FROM tbladmin WHERE email = ? LIMIT 1",
            's', [$email]
        );
    }

    public function emailExists(string $email): bool
    {
        return $this->count('email = ?', [$email], 's') > 0;
    }

    public function usernameExists(string $username): bool
    {
        return $this->count('username = ?', [$username], 's') > 0;
    }

    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO tbladmin (username, email, password, Photo, contact)
             VALUES (?, ?, ?, ?, ?)",
            'sssss',
            [
                $data['username'],
                $data['email'],
                $data['password'],
                $data['photo']   ?? null,
                $data['contact'] ?? null,
            ]
        );
        return $this->lastInsertId();
    }

    public function updatePassword(string $email, string $hashedPassword): bool
    {
        return $this->execute(
            "UPDATE tbladmin SET password = ? WHERE email = ?",
            'ss', [$hashedPassword, $email]
        ) > 0;
    }

    public function setOnlineStatus(string $email, bool $isOnline): void
    {
        $this->execute(
            "UPDATE tbladmin SET is_online = ?, last_seen = NOW() WHERE email = ?",
            'is', [(int) $isOnline, $email]
        );
    }

    public function getAll(): array
    {
        return $this->query(
            "SELECT id, username, email, Photo, is_online, last_seen
             FROM tbladmin ORDER BY username ASC"
        );
    }

    public function getProfile(string $email): ?array
    {
        return $this->queryOne(
            "SELECT id, username, email, Photo, contact, is_online, last_seen
             FROM tbladmin WHERE email = ? LIMIT 1",
            's', [$email]
        );
    }
}

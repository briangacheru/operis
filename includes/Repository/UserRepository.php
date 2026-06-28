<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';

/**
 * UserRepository — data access for the tblwriters table.
 */
class UserRepository extends BaseRepository
{
    protected string $table = 'tblwriters';

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            "SELECT * FROM tblwriters WHERE email = ? LIMIT 1",
            's', [$email]
        );
    }

    public function findByUsername(string $username): ?array
    {
        return $this->queryOne(
            "SELECT * FROM tblwriters WHERE username = ? LIMIT 1",
            's', [$username]
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
            "INSERT INTO tblwriters (username, email, password, Photo, contact)
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

    public function updateProfile(string $email, array $fields): bool
    {
        $allowed = ['username', 'contact', 'Photo', 'bio'];
        $set = [];
        $params = [];
        $types = '';
        foreach ($allowed as $col) {
            if (array_key_exists($col, $fields)) {
                $set[]    = "`$col` = ?";
                $params[] = $fields[$col];
                $types   .= 's';
            }
        }
        if (!$set) return false;
        $params[] = $email;
        $types   .= 's';
        return $this->execute(
            "UPDATE tblwriters SET " . implode(', ', $set) . " WHERE email = ?",
            $types, $params
        ) >= 0;
    }

    public function updatePassword(string $email, string $hashedPassword): bool
    {
        return $this->execute(
            "UPDATE tblwriters SET password = ? WHERE email = ?",
            'ss', [$hashedPassword, $email]
        ) > 0;
    }

    public function setOnlineStatus(string $email, bool $isOnline): void
    {
        $this->execute(
            "UPDATE tblwriters SET is_online = ?, last_seen = NOW() WHERE email = ?",
            'is', [(int) $isOnline, $email]
        );
    }

    public function getProfile(string $email): ?array
    {
        return $this->queryOne(
            "SELECT id, username, email, Photo, contact, bio, is_online, last_seen
             FROM tblwriters WHERE email = ? LIMIT 1",
            's', [$email]
        );
    }

    public function getAll(): array
    {
        return $this->query(
            "SELECT id, username, email, Photo, contact, is_online, last_seen
             FROM tblwriters ORDER BY username ASC"
        );
    }
}

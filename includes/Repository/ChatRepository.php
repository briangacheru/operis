<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';

/**
 * ChatRepository — data access for the chat_messages table.
 */
class ChatRepository extends BaseRepository
{
    protected string $table = 'chat_messages';

    public function getConversation(
        int    $userId,
        int    $targetId,
        string $since = '0000-00-00 00:00:00',
        int    $limit = 50
    ): array {
        return $this->query(
            "SELECT * FROM chat_messages
             WHERE (
                 (sender_id = ? AND receiver_id = ?) OR
                 (sender_id = ? AND receiver_id = ?)
             )
             AND timestamp > ?
             ORDER BY timestamp ASC
             LIMIT ?",
            'iiiisi',
            [$userId, $targetId, $targetId, $userId, $since, $limit]
        );
    }

    public function getUnread(int $receiverId, string $receiverType): array
    {
        return $this->query(
            "SELECT * FROM chat_messages
             WHERE is_read = 0 AND receiver_id = ? AND receiver_type = ?
             ORDER BY timestamp ASC",
            'is', [$receiverId, $receiverType]
        );
    }

    public function countUnread(int $receiverId): int
    {
        return $this->count(
            'is_read = 0 AND receiver_id = ?',
            [$receiverId], 'i'
        );
    }

    public function getNewSince(int $receiverId, string $receiverType, string $since): array
    {
        return $this->query(
            "SELECT sender_id, sender_type, receiver_id, receiver_type,
                    message, timestamp, file_url
             FROM chat_messages
             WHERE receiver_id = ? AND receiver_type = ? AND timestamp > ?
             ORDER BY timestamp ASC",
            'iss', [$receiverId, $receiverType, $since]
        );
    }

    public function send(array $data): int
    {
        $this->execute(
            "INSERT INTO chat_messages
             (sender_id, sender_type, receiver_id, receiver_type, message, file_url, timestamp)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            'isisss',
            [
                (int) $data['sender_id'],
                $data['sender_type'],
                (int) $data['receiver_id'],
                $data['receiver_type'],
                $data['message']  ?? '',
                $data['file_url'] ?? null,
            ]
        );
        return $this->lastInsertId();
    }

    public function markRead(int $senderId, int $receiverId): void
    {
        $this->execute(
            "UPDATE chat_messages SET is_read = 1
             WHERE sender_id = ? AND receiver_id = ? AND is_read = 0",
            'ii', [$senderId, $receiverId]
        );
    }

    public function getLatestBetween(int $userA, int $userB): ?array
    {
        return $this->queryOne(
            "SELECT message, timestamp, is_read FROM chat_messages
             WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
             ORDER BY timestamp DESC LIMIT 1",
            'iiii', [$userA, $userB, $userB, $userA]
        );
    }
}

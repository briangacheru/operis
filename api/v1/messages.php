<?php
declare(strict_types=1);

/**
 * /api/v1/messages
 *
 * GET  /messages?since={timestamp}   — new messages since timestamp
 * POST /messages                     — send a message
 * POST /messages/read                — mark messages as read
 */

require_once dirname(__DIR__, 2) . '/includes/Repository/ChatRepository.php';
require_once dirname(__DIR__, 2) . '/includes/Repository/UserRepository.php';
require_once dirname(__DIR__, 2) . '/includes/Repository/AdminRepository.php';

$email = requireAuth();
$chat  = new ChatRepository();

// Resolve current user from both tables
$users  = new UserRepository();
$admins = new AdminRepository();
$me     = $users->findByEmail($email) ?? $admins->findByEmail($email);
if (!$me) apiResponse(['error' => 'User not found'], 404);
$meId   = (int) $me['id'];
$meType = isset($me['userSession']) ? 'admin' : 'writer';

$subRoute = $parts[1] ?? '';

switch ("$method:$subRoute") {

    // -----------------------------------------------------------------------
    case 'GET:':
    // -----------------------------------------------------------------------
        $since = $_GET['since'] ?? '0000-00-00 00:00:00';
        $msgs  = $chat->getNewSince($meId, $meType, $since);
        apiResponse(['data' => $msgs]);

    // -----------------------------------------------------------------------
    case 'POST:':
    // -----------------------------------------------------------------------
        $receiverId   = Validator::sanitizeInt($body['receiver_id'] ?? 0);
        $receiverType = $body['receiver_type'] ?? 'admin';
        $message      = trim($body['message'] ?? '');

        if (!$receiverId || !$message) {
            apiResponse(['error' => 'receiver_id and message are required'], 422);
        }
        if (!in_array($receiverType, ['admin', 'writer'], true)) {
            apiResponse(['error' => 'Invalid receiver_type'], 422);
        }

        $id = $chat->send([
            'sender_id'     => $meId,
            'sender_type'   => $meType,
            'receiver_id'   => $receiverId,
            'receiver_type' => $receiverType,
            'message'       => $message,
            'file_url'      => $body['file_url'] ?? null,
        ]);
        apiResponse(['id' => $id, 'message' => 'Sent.'], 201);

    // -----------------------------------------------------------------------
    case 'POST:read':
    // -----------------------------------------------------------------------
        $senderId = Validator::sanitizeInt($body['sender_id'] ?? 0);
        if (!$senderId) apiResponse(['error' => 'sender_id required'], 422);
        $chat->markRead($senderId, $meId);
        apiResponse(['message' => 'Marked as read.']);

    // -----------------------------------------------------------------------
    default:
        apiResponse(['error' => 'Not found'], 404);
}

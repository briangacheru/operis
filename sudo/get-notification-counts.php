<?php
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/json');

if (!AdminAuth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$newTasksCount = $db->fetchOne(
    "SELECT COUNT(*) AS c FROM tbltasks WHERE is_deleted = 0 AND status = 'Submitted' AND admin_acknowledged = 0"
)['c'] ?? 0;

$lateTasksCount = $db->fetchOne(
    "SELECT COUNT(*) AS c FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress' AND due_date < NOW()"
)['c'] ?? 0;

$unreadMessages = $db->fetchOne(
    "SELECT COUNT(*) AS c FROM chat_messages WHERE receiver_id = 1 AND sender_id != 1 AND is_read = 0"
)['c'] ?? 0;

$unreadComments = $db->fetchOne(
    "SELECT COUNT(*) AS c FROM tbl_task_comments WHERE user_type = 'writer' AND is_read = 0"
)['c'] ?? 0;

echo json_encode([
    'success'   => true,
    'tasks'     => $newTasksCount + $lateTasksCount,
    'messages'  => $unreadMessages,
    'comments'  => $unreadComments,
    'timestamp' => time(),
]);
?>

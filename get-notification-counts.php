<?php
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/json');

if (!Auth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$email = Auth::currentUser();

try {
    $newTasksRow  = $db->fetchOne("SELECT COUNT(*) AS c FROM tbltasks WHERE is_deleted = 0 AND status IN ('In Progress', 'Unconfirmed', 'In Revision') AND acknowledged = 0");
    $lateTasksRow = $db->fetchOne("SELECT COUNT(*) AS c FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress' AND due_date < NOW()");
    $totalTasks   = ($newTasksRow['c'] ?? 0) + ($lateTasksRow['c'] ?? 0);

    $userRow      = $db->fetchOne("SELECT id FROM tblwriters WHERE email = ?", "s", $email);
    $userId       = $userRow['id'] ?? 0;
    $msgsRow      = $db->fetchOne("SELECT COUNT(*) AS c FROM chat_messages WHERE is_read = 0 AND receiver_id = ?", "i", $userId);
    $commentsRow  = $db->fetchOne(
        "SELECT COUNT(*) AS c FROM tbl_task_comments tc
         JOIN tbltasks t ON tc.task_id = t.id
         WHERE t.email = ? AND tc.user_type = 'admin' AND tc.is_read = 0",
        "s", $email
    );

    echo json_encode([
        'success'   => true,
        'tasks'     => $totalTasks,
        'messages'  => $msgsRow['c'] ?? 0,
        'comments'  => $commentsRow['c'] ?? 0,
        'timestamp' => time(),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
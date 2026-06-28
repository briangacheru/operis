<?php
session_start();
include('db.php');
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['sessionWriter'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$aid = $_SESSION['sessionWriter'];

try {
    // Get task notifications count (new submitted tasks + late tasks)
    $r1 = $con->query("SELECT COUNT(*) AS new_task_count FROM tbltasks WHERE is_deleted = 0 AND status IN ('In Progress', 'Unconfirmed', 'In Revision') AND acknowledged = 0");
    $newTasksCount = $r1->fetch_assoc()['new_task_count'];

    $r2 = $con->query("SELECT COUNT(*) AS late_task_count FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress' AND due_date < NOW()");
    $lateTasksCount = $r2->fetch_assoc()['late_task_count'];

    $totalTaskNotifications = $newTasksCount + $lateTasksCount;

    // Get unread messages count
    $s1 = $con->prepare("SELECT id FROM tblwriters WHERE email = ?");
    $s1->bind_param('s', $aid);
    $s1->execute();
    $userID = $s1->get_result()->fetch_assoc()['id'];

    $s2 = $con->prepare("SELECT COUNT(*) AS unread_count FROM chat_messages WHERE is_read = 0 AND receiver_id = ?");
    $s2->bind_param('i', $userID);
    $s2->execute();
    $unreadMessagesCount = $s2->get_result()->fetch_assoc()['unread_count'] ?? 0;

    // Get unread comments count
    $s3 = $con->prepare("SELECT COUNT(*) AS unread_comments_count FROM tbl_task_comments tc JOIN tbltasks t ON tc.task_id = t.id WHERE t.email = ? AND tc.user_type = 'admin' AND tc.is_read = 0");
    $s3->bind_param('s', $aid);
    $s3->execute();
    $unreadCommentsCount = $s3->get_result()->fetch_assoc()['unread_comments_count'];

    // Return counts as JSON
    echo json_encode([
        'success' => true,
        'tasks' => $totalTaskNotifications,
        'messages' => $unreadMessagesCount,
        'comments' => $unreadCommentsCount,
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
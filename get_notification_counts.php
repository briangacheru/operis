<?php
include('check-login.php');
$aid = $_SESSION['sessionWriter'];

// Get new tasks count
$s1 = $con->prepare("SELECT COUNT(*) AS new_task_count FROM tbltasks WHERE is_deleted = 0 AND (status = 'In Progress' OR is_confirmed = 1) AND email = ? AND acknowledged = 0");
$s1->bind_param('s', $aid);
$s1->execute();
$newTasksCount = $s1->get_result()->fetch_assoc()['new_task_count'];

// Get late tasks count
$s2 = $con->prepare("SELECT COUNT(*) AS late_task_count FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress' AND due_date < NOW() AND email = ?");
$s2->bind_param('s', $aid);
$s2->execute();
$lateTasksCount = $s2->get_result()->fetch_assoc()['late_task_count'];

echo json_encode([
    'newTasksCount' => $newTasksCount,
    'lateTasksCount' => $lateTasksCount
]);
?>
<?php
include "check-login.php"; // Database connection

if (!isset($_SESSION['sessionWriter'])) {
    echo json_encode([]);
    exit();
}

$aid = $_SESSION['sessionWriter'];

// Fetch current user information
$userStmt = $con->prepare("
    SELECT id, 'admin' as type FROM tbladmin WHERE email = ?
    UNION
    SELECT id, 'writer' as type FROM tblwriters WHERE email = ?
");
$userStmt->bind_param('ss', $aid, $aid);
$userStmt->execute();
$currentUser = $userStmt->get_result()->fetch_assoc();
$currentUserId = $currentUser['id'];
$currentUserType = $currentUser['type'];

// Get the last message timestamp from the request
$lastTimestamp = isset($_GET['last_timestamp']) ? $_GET['last_timestamp'] : '0000-00-00 00:00:00';

// Fetch new messages
$msgStmt = $con->prepare("
    SELECT sender_id, sender_type, receiver_id, receiver_type, message, timestamp, file_url
    FROM chat_messages
    WHERE (receiver_id = ? AND receiver_type = ?)
      AND timestamp > ?
    ORDER BY timestamp ASC
");
$msgStmt->bind_param('iss', $currentUserId, $currentUserType, $lastTimestamp);
$msgStmt->execute();
$newMessagesQuery = $msgStmt->get_result();

$newMessages = [];
while ($message = $newMessagesQuery->fetch_assoc()) {
    $newMessages[] = $message;
}

echo json_encode($newMessages);

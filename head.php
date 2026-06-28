<?php
include('check-login.php');

// Check if 'session' exists before using it
if (isset($_SESSION['sessionWriter'])) {
    $aid = $_SESSION['sessionWriter'];
} else {
    header('Location: login.php');
    exit();
}

// Fetch userID from the database using the email stored in the session
$userStmt = $con->prepare("SELECT id FROM tblwriters WHERE email = ?");
$userStmt->bind_param('s', $aid);
$userStmt->execute();
$userID = $userStmt->get_result()->fetch_assoc()['id'];

// Query to fetch unread messages details by userID
$msgStmt = $con->prepare("SELECT * FROM chat_messages WHERE is_read = 0 AND receiver_id = ? ORDER BY timestamp ASC");
$msgStmt->bind_param('i', $userID);
$msgStmt->execute();
$unreadMessagesQuery = $msgStmt->get_result();
$unreadMessages = []; // Initialize array to hold unread messages data
while ($message = $unreadMessagesQuery->fetch_assoc()) {
    $unreadMessages[] = $message; // Add each unread message to the array
}

$unreadMessagesCount = count($unreadMessages); // Count the number of unread messages
?>

<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token()) ?>">


    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
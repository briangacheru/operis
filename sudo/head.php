<?php
include('check-login.php');

// Check if 'session' exists before using it
if (isset($_SESSION['odmsaid'])) {
    $aid = $_SESSION['odmsaid'];
} else {
    header('Location: login.php');
    exit();
}

// Query to fetch late tasks details
$lateTasksQuery = mysqli_query($con, "SELECT * FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress' AND due_date < NOW()  ORDER BY due_date ASC");

$lateTasks = []; // Initialize array to hold late tasks data
while ($task = mysqli_fetch_assoc($lateTasksQuery)) {
    $lateTasks[] = $task; // Add each late task to the array
}

$lateTasksCount = count($lateTasks); // Count the number of late tasks for notifications

// Fetch userID from the database using the email stored in the session
$userQuery = mysqli_query($con, "SELECT id FROM tbladmin WHERE email = '$aid'");
$userResult = mysqli_fetch_assoc($userQuery);
$userID = $userResult['id']; // Get the userID

// Query to fetch unread messages details by userID
$unreadMessagesQuery = mysqli_query($con, "SELECT * FROM chat_messages WHERE is_read = 0 AND receiver_id = '$userID' ORDER BY timestamp ASC");

$unreadMessages = []; // Initialize array to hold unread messages data
while ($message = mysqli_fetch_assoc($unreadMessagesQuery)) {
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

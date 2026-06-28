<?php
include "check-login.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit();

validate_csrf();

$topic        = trim($_POST['topic']          ?? '');
$subject      = trim($_POST['subject']        ?? '');
$account      = trim($_POST['account']        ?? '');
$pages        = (int)   ($_POST['pages']      ?? 0);
$cpp          = (float) ($_POST['cpp']        ?? 0);
$due_date     = trim($_POST['due_date']       ?? '');
$is_confirmed = (int)   ($_POST['is_confirmed'] ?? 0);
$writer       = trim(explode('|', $_POST['writer'] ?? '')[0]);
$email        = trim($_POST['email']          ?? '');
$description  = trim($_POST['description']    ?? '');

if (!$topic || !$account || !$email || !$due_date) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
    exit();
}

$stmt = $con->prepare(
    "INSERT INTO tbltasks (topic, subject, account, pages, cpp, due_date, is_confirmed, writer, email, description)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('sssiidssss', $topic, $subject, $account, $pages, $cpp, $due_date, $is_confirmed, $writer, $email, $description);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'task_id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create task.']);
}
?>

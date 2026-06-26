<?php
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/json');

if (!AdminAuth::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (($_POST['extend'] ?? $_GET['extend'] ?? null) === 'true') {
    $_SESSION['last_activity'] = time();
    AdminAuth::updateOnlineStatus($_SESSION[AdminAuth::SESSION_KEY], true);
    echo json_encode(['success' => true, 'message' => 'Session extended']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
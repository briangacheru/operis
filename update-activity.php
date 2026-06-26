<?php
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/json');

if (Auth::isLoggedIn()) {
    $_SESSION['last_activity'] = time();
    $response = ['success' => true, 'timestamp' => $_SESSION['last_activity']];
} else {
    $response = ['success' => false, 'error' => 'No active session'];
}

ob_clean();
echo json_encode($response);
exit();
?>

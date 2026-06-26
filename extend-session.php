<?php
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/json');

$response = ['success' => false];

try {
    if (!empty($_SESSION['sessionWriter'])) {
        $_SESSION['last_activity'] = time();
        $email = $_SESSION['sessionWriter'];
        Auth::updateOnlineStatus($email, true);
        $response = ['success' => true, 'message' => 'Session extended successfully', 'new_activity_time' => $_SESSION['last_activity']];
    } else {
        $response['error'] = 'No active writer session found';
    }
} catch (Exception $e) {
    $response['error'] = 'Server error occurred';
}

ob_clean();
echo json_encode($response);
exit();
?>
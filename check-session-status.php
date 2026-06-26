<?php
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/json');

$response = ['timeRemaining' => 0, 'loggedIn' => false];

if (isset($_SESSION['sessionWriter']) && isset($_SESSION['last_activity'])) {
    $sessionTimeout = 86400;
    $timeElapsed    = time() - $_SESSION['last_activity'];
    $response['loggedIn']       = true;
    $response['timeRemaining']  = max(0, $sessionTimeout - $timeElapsed);
} else {
    $response['loggedIn'] = false;
}

ob_clean();
echo json_encode($response);
exit();
?>

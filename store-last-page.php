<?php
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/json');

$input    = json_decode(file_get_contents('php://input'), true);
$lastPage = $input['lastPage'] ?? null;

if ($lastPage !== null) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    setcookie('last_page_before_timeout', $lastPage, [
        'expires'  => time() + 600,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $response = ['success' => true, 'lastPage' => $lastPage];
} else {
    $response = ['success' => false, 'error' => 'No lastPage provided'];
}

ob_clean();
echo json_encode($response);
exit();
?>

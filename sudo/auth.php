<?php
// Legacy shim — authentication is now handled by sudo/includes/bootstrap.php + sudo/check-login.php.
if (!class_exists('AdminAuth')) {
    require_once __DIR__ . '/includes/bootstrap.php';
}
?>

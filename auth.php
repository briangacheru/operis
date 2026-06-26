<?php
// Legacy shim — authentication is now handled by includes/bootstrap.php + check-login.php.
if (!class_exists('Auth')) {
    require_once __DIR__ . '/includes/bootstrap.php';
}
?>

<?php
// Legacy shim — new code should use sudo/includes/bootstrap.php directly.
if (!class_exists('Database')) {
    require_once __DIR__ . '/includes/bootstrap.php';
}
?>
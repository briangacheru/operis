<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Track current page for post-login redirect (skip AJAX endpoints and guest pages).
$_guestPages = ['login.php', 'reset-password.php', 'forgot-password.php', 'public-task-view.php'];
$_ajaxPages  = [
    'check_new_tasks.php', 'logout_device.php', 'get_notification_counts.php',
    'extend-session.php', 'mark_task_read.php', 'mark_all_tasks_read.php',
    'get-task-details.php', 'update-task-acknowledgment.php',
    'mark-writer-comments-read.php', 'get_amount_due.php', 'notification-update.php',
];
$_self = basename($_SERVER['PHP_SELF']);

if (!in_array($_self, $_guestPages) && !in_array($_self, $_ajaxPages)) {
    $_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
}

if (in_array($_self, $_guestPages)) {
    AdminAuth::redirectIfLoggedIn('index');
} else {
    AdminAuth::requireLogin('login');
}
?>
<?php
require_once __DIR__ . '/includes/bootstrap.php';

$_guestPages = ['login.php', 'register.php', 'reset-password.php', 'forgot-password.php'];
$_self        = basename($_SERVER['PHP_SELF']);

if (in_array($_self, $_guestPages)) {
    // Guest-only pages: redirect already-authenticated users away.
    Auth::redirectIfLoggedIn();
} else {
    // Protected pages: enforce login, session timeout, and remember-me.
    Auth::requireLogin();
}
?>

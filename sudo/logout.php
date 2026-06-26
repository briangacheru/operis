<?php
require_once __DIR__ . '/includes/bootstrap.php';

$redirectAfter = $_SESSION['last_page'] ?? $_SERVER['HTTP_REFERER'] ?? 'index';
$sid           = session_id();

// Remove admin device session row before destroying the session.
$dbh->prepare("DELETE FROM tblsessions WHERE session_id = ?")->execute([$sid]);

AdminAuth::logout();

header("Location: login?redirect=" . urlencode($redirectAfter));
exit();
?>
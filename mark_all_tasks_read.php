<?php
include('check-login.php');

$aid = $_SESSION['sessionWriter'];
$result = mysqli_query($con, "UPDATE tbltasks SET acknowledged = 1 WHERE email = '$aid' AND acknowledged = 0");

if ($result) {
    $affected = mysqli_affected_rows($con);
    echo json_encode(['success' => true, 'affected_rows' => $affected]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
}
?>
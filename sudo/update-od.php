<?php
include "check-login.php";

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $od_date = mysqli_real_escape_string($con, $_POST['od_date']);
    $writer = mysqli_real_escape_string($con, $_POST['writer']);
    $description = mysqli_real_escape_string($con, $_POST['description']);

    $stmt = mysqli_prepare($con, "UPDATE tbloverdrafts SET amount = ?, od_date = ?, writer = ?, description = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssssi', $amount, $od_date, $writer, $description, $id);

    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = 'Overdraft record updated successfully.';
    } else {
        $response['message'] = 'Something went wrong. Please try again!';
    }

    mysqli_stmt_close($stmt);
} else {
    $response['message'] = 'Invalid request method.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>

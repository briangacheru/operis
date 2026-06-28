<?php
include "check-login.php"; // Make sure you include your database connection file here

// Check if the user is logged in and session variable is set
if (isset($_SESSION['sessionWriter'])) {
    $aid = $_SESSION['sessionWriter'];
} else {
    header('Location: login.php');
    exit();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=paid_tasks.csv');

$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('Task #', 'Topic', 'Pages', 'CPP', 'Amount', 'Status', 'Payment Date'));

$stmt = $con->prepare("SELECT * FROM tbltasks WHERE is_deleted = 0 AND status = 'Completed' AND is_paid = 1 AND email = ? ORDER BY id DESC");
$stmt->bind_param('s', $aid);
$stmt->execute();
$query = $stmt->get_result();
while ($row = $query->fetch_assoc()) {
    $totalprice = $row["cpp"] * $row["pages"];
    $status = $row["status"]; // Simplified for CSV
    $is_paid = ($row['is_paid'] == 1) ? 'Paid' : 'Unpaid';

    // Populate data rows
    fputcsv($output, array($row["id"], $row["topic"], $row["pages"], $row["cpp"], number_format($totalprice, 2), $is_paid, $row["paid_on"]));
}

fclose($output);
exit();
?>

<?php
include "check-login.php"; // Make sure you include your database connection file here

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=overdraft.csv');

$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('OD Id', 'Writer', 'Amount', 'Date'));

$stmt = $con->prepare("SELECT * FROM tbloverdrafts WHERE is_deleted = 0 AND is_settled = 0 AND email = ? ORDER BY id ASC");
$stmt->bind_param('s', $aid);
$stmt->execute();
$query = $stmt->get_result();
while ($row = $query->fetch_assoc()) {

    // Populate data rows
    fputcsv($output, array($row["id"], $row["writer"], $row["amount"], $row["od_date"]));
}

fclose($output);
exit();
?>
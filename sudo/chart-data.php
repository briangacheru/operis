<?php
require 'check-login.php';

$filter = $_GET['filter'] ?? 'monthly'; // Default to monthly if no filter is provided
$query = '';

// Prepare the query based on filter
if ($filter === 'daily') {
    $query = "
        SELECT DATE(expenseDate) AS period, 
            SUM(CASE WHEN category = 'Income' THEN amount + IFNULL(transactionCost, 0) ELSE 0 END) AS income,
            SUM(CASE WHEN category = 'Expense' THEN amount + IFNULL(transactionCost, 0) ELSE 0 END) AS expenses,
            SUM(CASE WHEN category = 'Savings' THEN amount ELSE 0 END) AS savings,
            (SELECT SUM(amount + IFNULL(transactionCost, 0)) FROM tbloverdrafts 
             WHERE is_deleted = 0 AND DATE(od_date) = DATE(expenseDate)) AS writer_payment
        FROM (
            SELECT category, amount, transactionCost, expenseDate
            FROM tblbudget
            WHERE is_deleted = 0 AND DATE(expenseDate) >= CURDATE() - INTERVAL 7 DAY
            UNION ALL
            SELECT 'Expense' AS category, amount, transactionCost, od_date AS expenseDate
            FROM tbloverdrafts
            WHERE is_deleted = 0 AND DATE(od_date) >= CURDATE() - INTERVAL 7 DAY
        ) AS combined
        GROUP BY DATE(expenseDate)
    ";
} elseif ($filter === 'weekly') {
    $query = "
        SELECT CONCAT(YEAR(expenseDate), ' W', WEEK(expenseDate)) AS period, 
            SUM(CASE WHEN category = 'Income' THEN amount + IFNULL(transactionCost, 0) ELSE 0 END) AS income,
            SUM(CASE WHEN category = 'Expense' THEN amount + IFNULL(transactionCost, 0) ELSE 0 END) AS expenses,
            SUM(CASE WHEN category = 'Savings' THEN amount ELSE 0 END) AS savings,
            (SELECT SUM(amount + IFNULL(transactionCost, 0)) FROM tbloverdrafts 
             WHERE is_deleted = 0 AND WEEK(od_date) = WEEK(expenseDate) AND YEAR(od_date) = YEAR(expenseDate)) AS writer_payment
        FROM (
            SELECT category, amount, transactionCost, expenseDate
            FROM tblbudget
            WHERE is_deleted = 0 AND expenseDate >= CURDATE() - INTERVAL 30 DAY
            UNION ALL
            SELECT 'Expense' AS category, amount, transactionCost, od_date AS expenseDate
            FROM tbloverdrafts
            WHERE is_deleted = 0 AND od_date >= CURDATE() - INTERVAL 30 DAY
        ) AS combined
        GROUP BY YEAR(expenseDate), WEEK(expenseDate)
    ";
} elseif ($filter === 'yearly') {
    $query = "
        SELECT YEAR(expenseDate) AS period, 
            SUM(CASE WHEN category = 'Income' THEN amount + IFNULL(transactionCost, 0) ELSE 0 END) AS income,
            SUM(CASE WHEN category = 'Expense' THEN amount + IFNULL(transactionCost, 0) ELSE 0 END) AS expenses,
            SUM(CASE WHEN category = 'Savings' THEN amount ELSE 0 END) AS savings,
            (SELECT SUM(amount + IFNULL(transactionCost, 0)) FROM tbloverdrafts 
             WHERE is_deleted = 0 AND YEAR(od_date) = YEAR(expenseDate)) AS writer_payment
        FROM (
            SELECT category, amount, transactionCost, expenseDate
            FROM tblbudget
            WHERE is_deleted = 0
            UNION ALL
            SELECT 'Expense' AS category, amount, transactionCost, od_date AS expenseDate
            FROM tbloverdrafts WHERE is_deleted = 0
        ) AS combined
        GROUP BY YEAR(expenseDate)
    ";
} else { // Monthly
    $query = "
        SELECT DATE_FORMAT(expenseDate, '%Y-%m') AS period, 
            SUM(CASE WHEN category = 'Income' THEN amount + IFNULL(transactionCost, 0) ELSE 0 END) AS income,
            SUM(CASE WHEN category = 'Expense' THEN amount + IFNULL(transactionCost, 0) ELSE 0 END) AS expenses,
            SUM(CASE WHEN category = 'Savings' THEN amount ELSE 0 END) AS savings,
            (SELECT SUM(amount + IFNULL(transactionCost, 0)) FROM tbloverdrafts 
             WHERE is_deleted = 0 AND DATE_FORMAT(od_date, '%Y-%m') = DATE_FORMAT(expenseDate, '%Y-%m')) AS writer_payment
        FROM (
            SELECT category, amount, transactionCost, expenseDate
            FROM tblbudget
            WHERE is_deleted = 0
            UNION ALL
            SELECT 'Expense' AS category, amount, transactionCost, od_date AS expenseDate
            FROM tbloverdrafts WHERE is_deleted = 0
        ) AS combined
        GROUP BY DATE_FORMAT(expenseDate, '%Y-%m')
    ";
}

$result = $con->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>

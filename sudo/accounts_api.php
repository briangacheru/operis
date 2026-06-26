<?php
require_once __DIR__ . '/includes/bootstrap.php';
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1); // Log errors to file
ini_set('error_log', __DIR__ . '/php-errors.log');
date_default_timezone_set('Africa/Nairobi');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$db = $dbh;

$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['action']) ? $_GET['action'] : '';

switch($method) {
    case 'GET':
        if ($request == 'summary') {
            getSummary($db);
        } elseif ($request == 'distribution') {
            getDistribution($db);
        } elseif ($request == 'distribution_by_month') {
            getDistributionByMonth($db);
        } elseif ($request == 'search') {
            searchAccounts($db);
        } elseif ($request == 'growth') {
            getGrowthData($db);
        } elseif ($request == 'account_types') {
            getAccountTypes($db);
        } elseif ($request == 'balance_history') {
            getAccountBalanceHistory($db);
        } elseif ($request == 'get_balance_history') {
            getBalanceHistory($db);
        } elseif ($request == 'accounts_for_update') {
            getAccountsForBalanceUpdate($db);
        } elseif ($request == 'balances_by_month') {
            getBalancesByMonth($db);
        } elseif ($request == 'available_months') {
            getAvailableMonths($db);
        } elseif ($request == 'monthly_comparison') {
            getMonthlyComparison($db);
        } elseif ($request == 'latest_month') {
            getLatestMonthData($db);
        } elseif ($request == 'savings_breakdown') {
            getSavingsBreakdown($db);
        } elseif ($request == 'total_balance_breakdown') {
            getTotalBalanceBreakdown($db);
        } elseif ($request == 'latest_month_details') {
            getLatestMonthDetails($db);
        } else {
            getAllAccounts($db);
        }
        break;
    case 'POST':
        if ($request == 'update_balance') {
            updateMonthlyBalance($db);
        } elseif ($request == 'manage_type') {
            manageAccountType($db);
        } elseif ($request == 'create') {
            createAccount($db);
        } else {
            createAccount($db); // Default to create if no specific action
        }
        break;
    case 'PUT':
        if ($request == 'update') {
            updateAccount($db);
        } elseif ($request == 'manage_type') {
            manageAccountType($db);
        } else {
            updateAccount($db);
        }
        break;
    case 'DELETE':
        deleteAccount($db);
        break;
}

function getAllAccounts($db)
{
    $query = 'SELECT a.id, a.account_name, a.account_type_id, a.bank_name, a.account_number, 
                     a.status, a.interest_rate, a.minimum_balance, a.notes, a.created_at, a.last_updated,
                     at.type_name as account_type, at.color_code, at.icon_class,
                     COALESCE(bh.balance, 0) as balance,
                     bh.month_year as last_balance_update,
                     COALESCE(bh.growth_amount, 0) as growth_amount,
                     COALESCE(bh.growth_percentage, 0) as growth_percentage
              FROM accounts a 
              LEFT JOIN account_types at ON a.account_type_id = at.id 
              LEFT JOIN balance_history bh ON a.id = bh.account_id 
                  AND bh.month_year = (
                      SELECT MAX(month_year) 
                      FROM balance_history bh2 
                      WHERE bh2.account_id = a.id
                  )
              ORDER BY a.account_name';

    $stmt = $db->prepare($query);
    $stmt->execute();

    $accounts = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ensure numeric values
        $row['balance'] = floatval($row['balance']);
        $row['growth_amount'] = floatval($row['growth_amount']);
        $row['growth_percentage'] = floatval($row['growth_percentage']);
        $accounts[] = $row;
    }

    echo json_encode($accounts);
}

function getSummary($db)
{
    try {
        // Get basic account statistics
        $query = "SELECT 
            COUNT(*) as total_accounts,
            COUNT(CASE WHEN a.status = 'Active' THEN 1 END) as active_accounts,
            COUNT(CASE WHEN a.status != 'Active' THEN 1 END) as inactive_accounts,
            COALESCE(SUM(CASE WHEN a.status = 'Active' THEN COALESCE(bh.balance, 0) ELSE 0 END), 0) as total_balance,
            COALESCE(AVG(CASE WHEN a.status = 'Active' THEN COALESCE(bh.balance, 0) ELSE NULL END), 0) as average_balance
            FROM accounts a 
            LEFT JOIN balance_history bh ON a.id = bh.account_id 
            AND bh.month_year = (
                SELECT MAX(month_year) 
                FROM balance_history bh2 
                WHERE bh2.account_id = a.id
            )";

        $stmt = $db->prepare($query);
        $stmt->execute();
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate dynamic monthly growth (current month vs previous month)
        $currentMonth = date('Y-m-01'); // First day of current month
        $previousMonth = date('Y-m-01', strtotime('-1 month')); // First day of previous month

        // Get current month total balance
        $currentQuery = "SELECT COALESCE(SUM(bh.balance), 0) as current_total
            FROM balance_history bh
            INNER JOIN accounts a ON bh.account_id = a.id
            WHERE bh.month_year = ? AND a.status = 'Active'";

        $currentStmt = $db->prepare($currentQuery);
        $currentStmt->execute([$currentMonth]);
        $currentResult = $currentStmt->fetch(PDO::FETCH_ASSOC);
        $currentTotal = floatval($currentResult['current_total']);

        // Get previous month total balance
        $previousQuery = "SELECT COALESCE(SUM(bh.balance), 0) as previous_total
            FROM balance_history bh
            INNER JOIN accounts a ON bh.account_id = a.id
            WHERE bh.month_year = ? AND a.status = 'Active'";

        $previousStmt = $db->prepare($previousQuery);
        $previousStmt->execute([$previousMonth]);
        $previousResult = $previousStmt->fetch(PDO::FETCH_ASSOC);
        $previousTotal = floatval($previousResult['previous_total']);

        // Calculate monthly growth percentage
        if ($previousTotal > 0) {
            $monthlyGrowth = (($currentTotal - $previousTotal) / $previousTotal) * 100;
        } else {
            $monthlyGrowth = $currentTotal > 0 ? 100 : 0; // 100% if we have current balance but no previous
        }

        // Calculate monthly savings using latest available data - SIMPLIFIED
        $savingsTypes = ['Savings', 'MMF', 'Sacco'];

        // Get the latest month that has any savings data
        $latestSavingsQuery = "SELECT MAX(bh.month_year) as latest_month
            FROM balance_history bh
            INNER JOIN accounts a ON bh.account_id = a.id
            INNER JOIN account_types at ON a.account_type_id = at.id
            WHERE a.status = 'Active' AND at.type_name IN ('Savings', 'MMF', 'Sacco')";

        $latestSavingsStmt = $db->prepare($latestSavingsQuery);
        $latestSavingsStmt->execute();
        $latestSavingsResult = $latestSavingsStmt->fetch(PDO::FETCH_ASSOC);
        $latestSavingsMonth = $latestSavingsResult['latest_month'];

        if ($latestSavingsMonth) {
            // Get the previous month that has savings data
            $previousSavingsMonthQuery = "SELECT MAX(bh.month_year) as previous_month
                FROM balance_history bh
                INNER JOIN accounts a ON bh.account_id = a.id
                INNER JOIN account_types at ON a.account_type_id = at.id
                WHERE a.status = 'Active' AND at.type_name IN ('Savings', 'MMF', 'Sacco') 
                AND bh.month_year < ?";

            $previousSavingsMonthStmt = $db->prepare($previousSavingsMonthQuery);
            $previousSavingsMonthStmt->execute([$latestSavingsMonth]);
            $previousSavingsMonthResult = $previousSavingsMonthStmt->fetch(PDO::FETCH_ASSOC);
            $previousSavingsMonth = $previousSavingsMonthResult['previous_month'];

            // Get latest month savings total
            $currentSavingsQuery = "SELECT COALESCE(SUM(bh.balance), 0) as savings_total
                FROM balance_history bh
                INNER JOIN accounts a ON bh.account_id = a.id
                INNER JOIN account_types at ON a.account_type_id = at.id
                WHERE bh.month_year = ? AND a.status = 'Active' AND at.type_name IN ('Savings', 'MMF', 'Sacco')";

            $currentSavingsStmt = $db->prepare($currentSavingsQuery);
            $currentSavingsStmt->execute([$latestSavingsMonth]);
            $currentSavingsResult = $currentSavingsStmt->fetch(PDO::FETCH_ASSOC);
            $currentSavingsTotal = floatval($currentSavingsResult['savings_total']);

            $previousSavingsTotal = 0;
            if ($previousSavingsMonth) {
                $previousSavingsStmt = $db->prepare($currentSavingsQuery);
                $previousSavingsStmt->execute([$previousSavingsMonth]);
                $previousSavingsResult = $previousSavingsStmt->fetch(PDO::FETCH_ASSOC);
                $previousSavingsTotal = floatval($previousSavingsResult['savings_total']);
            }

            $monthlySavings = $currentSavingsTotal - $previousSavingsTotal;
            $savingsMonthDisplay = date('F Y', strtotime($latestSavingsMonth));

        } else {
            $monthlySavings = 0;
            $savingsMonthDisplay = 'No Data';
            $latestSavingsMonth = null;
        }

        // Add calculated values to summary
        $summary['monthly_growth'] = round($monthlyGrowth, 2);
        $summary['current_month_total'] = $currentTotal;
        $summary['previous_month_total'] = $previousTotal;
        $summary['growth_amount'] = $currentTotal - $previousTotal;
        $summary['monthly_savings'] = $monthlySavings;
        $summary['savings_month_display'] = $savingsMonthDisplay;
        $summary['latest_savings_month'] = $latestSavingsMonth;

        // Ensure all numeric values are properly formatted
        $summary['total_balance'] = floatval($summary['total_balance']);
        $summary['average_balance'] = floatval($summary['average_balance']);
        $summary['active_accounts'] = intval($summary['active_accounts']);
        $summary['inactive_accounts'] = intval($summary['inactive_accounts']);
        $summary['total_accounts'] = intval($summary['total_accounts']);

        echo json_encode($summary);

    } catch (PDOException $e) {
        echo json_encode(array(
            'error' => 'Database error: ' . $e->getMessage(),
            'total_accounts' => 0,
            'active_accounts' => 0,
            'inactive_accounts' => 0,
            'total_balance' => 0,
            'monthly_growth' => 0
        ));
    }
}

function getDistribution($db)
{
    $query = 'SELECT at.type_name as account_type, at.color_code, 
                     SUM(COALESCE(bh.balance, 0)) as total_balance, 
                     COUNT(a.id) as account_count
              FROM accounts a 
              LEFT JOIN account_types at ON a.account_type_id = at.id 
              LEFT JOIN balance_history bh ON a.id = bh.account_id 
                  AND bh.month_year = (
                      SELECT MAX(month_year) 
                      FROM balance_history bh2 
                      WHERE bh2.account_id = a.id
                  )
              GROUP BY at.id, at.type_name, at.color_code 
              ORDER BY total_balance DESC';

    $stmt = $db->prepare($query);
    $stmt->execute();

    $distribution = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['total_balance'] = floatval($row['total_balance']);
        $distribution[] = $row;
    }

    echo json_encode($distribution);
}

function searchAccounts($db)
{
    $searchTerm = isset($_GET['q']) ? $_GET['q'] : '';

    $query = 'SELECT a.id, a.account_name, a.account_type_id, a.bank_name, a.account_number, 
                     a.status, a.interest_rate, a.minimum_balance, a.notes, a.created_at, a.last_updated,
                     at.type_name as account_type, at.color_code, at.icon_class,
                     COALESCE(bh.balance, 0) as balance,
                     bh.month_year as last_balance_update
              FROM accounts a 
              LEFT JOIN account_types at ON a.account_type_id = at.id 
              LEFT JOIN balance_history bh ON a.id = bh.account_id 
                  AND bh.month_year = (
                      SELECT MAX(month_year) 
                      FROM balance_history bh2 
                      WHERE bh2.account_id = a.id
                  )
              WHERE a.account_name LIKE :search 
                 OR at.type_name LIKE :search 
                 OR a.bank_name LIKE :search 
                 OR a.status LIKE :search
              ORDER BY a.account_name';

    $stmt = $db->prepare($query);
    $searchParam = "%{$searchTerm}%";
    $stmt->bindParam(':search', $searchParam);
    $stmt->execute();

    $accounts = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['balance'] = floatval($row['balance']);
        $accounts[] = $row;
    }

    echo json_encode($accounts);
}

function createAccount($db)
{
    try {
        // Get input data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            echo json_encode(['message' => 'Invalid JSON data', 'success' => false]);
            return;
        }

        // Validate required fields
        if (empty($data['account_name'])) {
            echo json_encode(['message' => 'Account name is required', 'success' => false]);
            return;
        }

        if (empty($data['account_type'])) {
            echo json_encode(['message' => 'Account type is required', 'success' => false]);
            return;
        }

        // Validate status
        $validStatuses = ['Active', 'Inactive', 'Locked', 'Low Balance', 'Debt'];
        $status = isset($data['status']) ? $data['status'] : 'Active';
        if (!in_array($status, $validStatuses)) {
            echo json_encode(array('message' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses), 'error' => true));
            return;
        }

        // Get account type ID
        $stmt = $db->prepare('SELECT id FROM account_types WHERE type_name = ? AND is_active = 1');
        $stmt->execute([$data['account_type']]);
        $accountType = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$accountType) {
            echo json_encode(['message' => 'Invalid account type', 'success' => false]);
            return;
        }

        // Start transaction
        $db->beginTransaction();

        // Insert account
        $stmt = $db->prepare('
            INSERT INTO accounts (account_name, account_type_id, bank_name, account_number, status, interest_rate, minimum_balance, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['account_name'],
            $accountType['id'],
            $data['bank_name'] ?? '',
            $data['account_number'] ?? '',
            $data['status'] ?? 'Active',
            $data['interest_rate'] ?? 0,
            $data['minimum_balance'] ?? 0,
            $data['notes'] ?? ''
        ]);

        $accountId = $db->lastInsertId();

        // Insert initial balance (using positional parameters - cleaner!)
        if (isset($data['balance']) && $data['balance'] >= 0) {
            $stmt = $db->prepare('
                INSERT INTO balance_history (account_id, balance, month_year, notes) 
                VALUES (?, ?, ?, ?)
            ');

            $stmt->execute([
                $accountId,
                $data['balance'],
                date('Y-m-01'),
                'Initial balance'
            ]);
        }

        $db->commit();

        echo json_encode([
            'message' => 'Account created successfully',
            'success' => true,
            'account_id' => $accountId
        ]);

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['message' => 'Error: ' . $e->getMessage(), 'success' => false]);
    }
}


function deleteAccount($db)
{
    $id = isset($_GET['id']) ? $_GET['id'] : '';

    $query = 'DELETE FROM accounts WHERE id = :id';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo json_encode(array('message' => 'Account deleted successfully.'));
    } else {
        echo json_encode(array('message' => 'Unable to delete account.'));
    }
}

// Add after existing functions in accounts_api.php

function getGrowthData($db)
{
    try {
        // Build growth data directly from balance_history grouped by month
        $query = "SELECT 
                    bh.month_year AS month,
                    COALESCE(SUM(bh.balance), 0)         AS total_balance,
                    COALESCE(SUM(bh.growth_amount), 0)   AS total_growth_amount,
                    CASE 
                        WHEN SUM(bh.balance) - SUM(bh.growth_amount) > 0
                        THEN (SUM(bh.growth_amount) / (SUM(bh.balance) - SUM(bh.growth_amount))) * 100
                        ELSE 0
                    END                                   AS growth_percentage,
                    COUNT(DISTINCT bh.account_id)         AS account_count
                FROM balance_history bh
                INNER JOIN accounts a ON bh.account_id = a.id
                WHERE a.status = 'Active'
                GROUP BY bh.month_year
                ORDER BY bh.month_year DESC
                LIMIT 12";

        $stmt = $db->prepare($query);
        $stmt->execute();

        $growth = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['total_balance']       = floatval($row['total_balance']);
            $row['total_growth_amount'] = floatval($row['total_growth_amount']);
            $row['growth_percentage']   = round(floatval($row['growth_percentage']), 2);
            $row['account_count']       = intval($row['account_count']);
            $growth[] = $row;
        }

        echo json_encode($growth);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function getAccountBalanceHistory($db)
{
    $accountId = isset($_GET['account_id']) ? $_GET['account_id'] : '';

    if (!$accountId) {
        echo json_encode(array('error' => 'Account ID required'));
        return;
    }

    $query = 'SELECT * FROM balance_history 
              WHERE account_id = :account_id 
              ORDER BY month_year DESC';

    $stmt = $db->prepare($query);
    $stmt->bindParam(':account_id', $accountId);
    $stmt->execute();

    $history = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $history[] = $row;
    }

    echo json_encode($history);
}

function getAccountsForBalanceUpdate($db)
{
    $query = 'SELECT 
                acb.id, 
                acb.account_name, 
                acb.bank_name, 
                acb.current_balance, 
                acb.last_balance_update,
                acb.account_type_id,
                at.type_name as account_type,
                at.color_code,
                at.icon_class
              FROM account_current_balances acb
              LEFT JOIN account_types at ON acb.account_type_id = at.id
              WHERE acb.status = "Active"
              ORDER BY acb.account_name';

    $stmt = $db->prepare($query);
    $stmt->execute();

    $accounts = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $accounts[] = $row;
    }

    echo json_encode($accounts);
}

function getAccountTypes($db)
{
    // Get parameter to determine if we want all types or just active ones
    $includeInactive = isset($_GET['include_inactive']) ? $_GET['include_inactive'] : false;

    if ($includeInactive) {
        $query = 'SELECT * FROM account_types ORDER BY is_active DESC, type_name';
    } else {
        $query = 'SELECT * FROM account_types WHERE is_active = 1 ORDER BY type_name';
    }

    $stmt = $db->prepare($query);
    $stmt->execute();

    $types = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $types[] = $row;
    }

    echo json_encode($types);
}

function updateMonthlyBalance($db)
{
    $data = json_decode(file_get_contents('php://input'));

    try {
        // Get previous month's balance for growth calculation
        $prevQuery = 'SELECT balance FROM balance_history 
                      WHERE account_id = ? 
                      AND month_year < ? 
                      ORDER BY month_year DESC 
                      LIMIT 1';

        $prevStmt = $db->prepare($prevQuery);
        $prevStmt->execute([$data->account_id, $data->month_year]);
        $prevResult = $prevStmt->fetch(PDO::FETCH_ASSOC);

        $previousBalance = $prevResult ? floatval($prevResult['balance']) : 0;
        $currentBalance = floatval($data->balance);

        // Calculate growth automatically
        $calculatedGrowthAmount = $currentBalance - $previousBalance;
        $calculatedGrowthPercentage = $previousBalance > 0 ?
            (($calculatedGrowthAmount / $previousBalance) * 100) : 0;

        $query = 'INSERT INTO balance_history (account_id, balance, month_year, growth_amount, growth_percentage, notes)
                  VALUES (?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE 
                      balance = VALUES(balance),
                      growth_amount = VALUES(growth_amount),
                      growth_percentage = VALUES(growth_percentage),
                      notes = VALUES(notes)';

        $stmt = $db->prepare($query);
        $success = $stmt->execute([
            $data->account_id,
            $currentBalance,
            $data->month_year,
            $calculatedGrowthAmount,
            $calculatedGrowthPercentage,
            $data->notes ?? ''
        ]);

        if ($success) {
            echo json_encode(array(
                'message' => 'Balance updated successfully.',
                'success' => true,
                'growth_amount' => $calculatedGrowthAmount,
                'growth_percentage' => round($calculatedGrowthPercentage, 2),
                'previous_balance' => $previousBalance,
                'current_balance' => $currentBalance
            ));
        } else {
            echo json_encode(array('message' => 'Unable to update balance.', 'success' => false));
        }

    } catch (Exception $e) {
        echo json_encode(array('message' => 'Unable to update balance: ' . $e->getMessage(), 'success' => false));
    }
}

function getLatestMonthData($db)
{
    $query = 'SELECT 
                DATE_FORMAT(bh.month_year, "%Y-%m") as month_year,
                DATE_FORMAT(bh.month_year, "%M %Y") as formatted_month,
                COUNT(DISTINCT bh.account_id) as accounts_with_data,
                SUM(bh.balance) as total_balance,
                SUM(bh.growth_amount) as total_growth
              FROM balance_history bh
              WHERE bh.month_year = (
                  SELECT MAX(month_year) 
                  FROM balance_history
              )
              GROUP BY bh.month_year
              ORDER BY bh.month_year DESC
              LIMIT 1';

    $stmt = $db->prepare($query);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        // If no balance history data, return current month
        $result = array(
            'month_year' => date('Y-m'),
            'formatted_month' => date('F Y'),
            'accounts_with_data' => 0,
            'total_balance' => 0,
            'total_growth' => 0
        );
    }

    echo json_encode($result);
}

function manageAccountType($db)
{
    $data = json_decode(file_get_contents('php://input'));
    $action = $data->action;

    try {
        if ($action === 'create') {
            // Check if type already exists (including inactive ones)
            $checkQuery = 'SELECT id, is_active FROM account_types WHERE type_name = :type_name';
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':type_name', $data->type_name);
            $checkStmt->execute();
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['is_active'] == 0) {
                    echo json_encode(array('message' => 'Account type exists but is inactive. Please reactivate it instead.', 'error' => true));
                } else {
                    echo json_encode(array('message' => 'Account type already exists.', 'error' => true));
                }
                return;
            }

            $query = 'INSERT INTO account_types (type_name, color_code, icon_class) 
                      VALUES (:type_name, :color_code, :icon_class)';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':type_name', $data->type_name);
            $stmt->bindParam(':color_code', $data->color_code);
            $stmt->bindParam(':icon_class', $data->icon_class);

        } elseif ($action === 'update') {
            // Check if another type with same name exists (excluding current one)
            $checkQuery = 'SELECT id FROM account_types WHERE type_name = :type_name AND id != :id';
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':type_name', $data->type_name);
            $checkStmt->bindParam(':id', $data->id);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                echo json_encode(array('message' => 'Account type name already exists.', 'error' => true));
                return;
            }

            $query = 'UPDATE account_types 
                      SET type_name = :type_name, color_code = :color_code, icon_class = :icon_class, is_active = :is_active
                      WHERE id = :id';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':type_name', $data->type_name);
            $stmt->bindParam(':color_code', $data->color_code);
            $stmt->bindParam(':icon_class', $data->icon_class);
            $stmt->bindParam(':is_active', $data->is_active);
            $stmt->bindParam(':id', $data->id);

        } elseif ($action === 'delete') {
            // Check if any accounts are using this type
            $checkQuery = 'SELECT COUNT(*) as count FROM accounts WHERE account_type_id = :id';
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $data->id);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                echo json_encode(array('message' => 'Cannot delete account type. It is being used by ' . $result['count'] . ' account(s).', 'error' => true));
                return;
            }

            $query = 'UPDATE account_types SET is_active = 0 WHERE id = :id';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $data->id);

        } elseif ($action === 'reactivate') {
            // New action to reactivate inactive types
            $query = 'UPDATE account_types SET is_active = 1 WHERE id = :id';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $data->id);
        }

        if ($stmt->execute()) {
            echo json_encode(array('message' => 'Account type ' . $action . 'd successfully.', 'success' => true));
        } else {
            echo json_encode(array('message' => 'Unable to ' . $action . ' account type.', 'error' => true));
        }

    } catch (PDOException $e) {
        // Handle database errors gracefully
        if ($e->getCode() == 23000) { // Integrity constraint violation
            echo json_encode(array('message' => 'Account type name must be unique.', 'error' => true));
        } else {
            echo json_encode(array('message' => 'Database error: ' . $e->getMessage(), 'error' => true));
        }
    }
}

function getBalancesByMonth($db)
{
    $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m-01');
    $accountId = isset($_GET['account_id']) ? $_GET['account_id'] : null;

    if ($accountId) {
        // Get balance for specific account and month
        $query = 'SELECT a.id, a.account_name, at.type_name as account_type, at.color_code,
                         bh.balance, 
                         bh.month_year, 
                         bh.growth_amount, 
                         bh.growth_percentage, 
                         COALESCE(bh.notes, "") as notes
                  FROM accounts a
                  LEFT JOIN account_types at ON a.account_type_id = at.id
                  LEFT JOIN balance_history bh ON a.id = bh.account_id AND bh.month_year = :month
                  WHERE a.id = :account_id AND a.status = "Active"';

        $stmt = $db->prepare($query);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':account_id', $accountId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Only return actual recorded values, no defaults for missing data
            $result['balance'] = $result['balance'] ? floatval($result['balance']) : null;
            $result['growth_amount'] = $result['growth_amount'] ? floatval($result['growth_amount']) : null;
            $result['growth_percentage'] = $result['growth_percentage'] ? floatval($result['growth_percentage']) : null;
        }

        echo json_encode($result ?: array('error' => 'Account not found or inactive'));

    } else {
        // Get balances for all active accounts for specific month - only actual recorded data
        $query = 'SELECT a.id, a.account_name, at.type_name as account_type, at.color_code, at.icon_class,
                         bh.balance, 
                         bh.month_year, 
                         bh.growth_amount, 
                         bh.growth_percentage,
                         COALESCE(bh.notes, "") as notes,
                         CASE WHEN bh.balance IS NOT NULL THEN "Has data" ELSE "No data" END as data_status
                  FROM accounts a
                  LEFT JOIN account_types at ON a.account_type_id = at.id
                  LEFT JOIN balance_history bh ON a.id = bh.account_id AND bh.month_year = :month
                  WHERE a.status = "Active"
                  ORDER BY a.account_name';

        $stmt = $db->prepare($query);
        $stmt->bindParam(':month', $month);
        $stmt->execute();

        $accounts = array();
        $totalBalance = 0;
        $totalGrowth = 0;
        $accountsWithData = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Only process accounts that have actual data for this month
            if ($row['balance'] !== null) {
                $row['balance'] = floatval($row['balance']);
                $row['growth_amount'] = floatval($row['growth_amount']);
                $row['growth_percentage'] = floatval($row['growth_percentage']);

                $totalBalance += $row['balance'];
                $totalGrowth += floatval($row['growth_amount']);
                $accountsWithData++;
            } else {
                // Set null values for accounts without data for this month
                $row['balance'] = null;
                $row['growth_amount'] = null;
                $row['growth_percentage'] = null;
            }

            $accounts[] = $row;
        }

        echo json_encode(array(
            'month' => $month,
            'accounts' => $accounts,
            'summary' => array(
                'total_balance' => floatval($totalBalance),
                'total_growth' => floatval($totalGrowth),
                'accounts_with_data' => intval($accountsWithData),
                'total_accounts' => count($accounts)
            )
        ));
    }
}

function getAvailableMonths($db)
{
    $query = 'SELECT DISTINCT month_year 
              FROM balance_history 
              ORDER BY month_year DESC';

    $stmt = $db->prepare($query);
    $stmt->execute();

    $months = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $months[] = $row['month_year'];
    }

    echo json_encode($months);
}

function getMonthlyComparison($db)
{
    $startMonth = isset($_GET['start_month']) ? $_GET['start_month'] : '';
    $endMonth = isset($_GET['end_month']) ? $_GET['end_month'] : '';

    if (!$startMonth || !$endMonth) {
        echo json_encode(array('error' => 'Start and end months required'));
        return;
    }

    $query = 'SELECT a.id, a.account_name, at.type_name as account_type,
                     COALESCE(bh1.balance, 0) as start_balance, 
                     bh1.month_year as start_month,
                     COALESCE(bh2.balance, 0) as end_balance, 
                     bh2.month_year as end_month,
                     (COALESCE(bh2.balance, 0) - COALESCE(bh1.balance, 0)) as balance_change,
                     CASE 
                         WHEN COALESCE(bh1.balance, 0) > 0 THEN 
                             ((COALESCE(bh2.balance, 0) - COALESCE(bh1.balance, 0)) / bh1.balance * 100)
                         ELSE 0 
                     END as percentage_change
              FROM accounts a
              LEFT JOIN account_types at ON a.account_type_id = at.id
              LEFT JOIN balance_history bh1 ON a.id = bh1.account_id AND bh1.month_year = :start_month
              LEFT JOIN balance_history bh2 ON a.id = bh2.account_id AND bh2.month_year = :end_month
              WHERE a.status = "Active" AND (bh1.balance IS NOT NULL OR bh2.balance IS NOT NULL)
              ORDER BY balance_change DESC';

    $stmt = $db->prepare($query);
    $stmt->bindParam(':start_month', $startMonth);
    $stmt->bindParam(':end_month', $endMonth);
    $stmt->execute();

    $comparison = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ensure all numeric fields are properly typed
        $row['start_balance'] = floatval($row['start_balance']);
        $row['end_balance'] = floatval($row['end_balance']);
        $row['balance_change'] = floatval($row['balance_change']);
        $row['percentage_change'] = floatval($row['percentage_change']);

        $comparison[] = $row;
    }

    echo json_encode($comparison);
}

function getBalanceHistory($db)
{
    try {
        $accountId = isset($_GET['account_id']) ? $_GET['account_id'] : '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;

        if (empty($accountId)) {
            echo json_encode(['message' => 'Account ID is required', 'success' => false]);
            return;
        }

        $query = 'SELECT * FROM balance_history WHERE account_id = ? ORDER BY month_year DESC';
        if ($limit) {
            $query .= ' LIMIT ' . $limit;
        }

        $stmt = $db->prepare($query);
        $stmt->execute([$accountId]);

        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ensure numeric values
        foreach ($history as &$record) {
            $record['balance'] = floatval($record['balance']);
            $record['growth_amount'] = floatval($record['growth_amount']);
            $record['growth_percentage'] = floatval($record['growth_percentage']);
        }

        echo json_encode($history);

    } catch (Exception $e) {
        echo json_encode(['message' => 'Error: ' . $e->getMessage(), 'success' => false]);
    }
}

function updateAccount($db)
{
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            echo json_encode(['message' => 'Invalid JSON data', 'success' => false]);
            return;
        }

        // Validate required fields
        if (empty($data['id']) || empty($data['account_name']) || empty($data['account_type'])) {
            echo json_encode(['message' => 'Missing required fields', 'success' => false]);
            return;
        }

        // Get account type ID
        $stmt = $db->prepare('SELECT id FROM account_types WHERE type_name = ? AND is_active = 1');
        $stmt->execute([$data['account_type']]);
        $accountType = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$accountType) {
            echo json_encode(['message' => 'Invalid account type', 'success' => false]);
            return;
        }

        // Update account
        $stmt = $db->prepare('
            UPDATE accounts 
            SET account_name = ?, account_type_id = ?, bank_name = ?, account_number = ?, 
                status = ?, interest_rate = ?, minimum_balance = ?, notes = ?, 
                last_updated = CURRENT_TIMESTAMP
            WHERE id = ?
        ');

        $success = $stmt->execute([
            $data['account_name'],
            $accountType['id'],
            $data['bank_name'] ?? '',
            $data['account_number'] ?? '',
            $data['status'] ?? 'Active',
            $data['interest_rate'] ?? 0,
            $data['minimum_balance'] ?? 0,
            $data['notes'] ?? '',
            $data['id']
        ]);

        if ($success) {
            echo json_encode([
                'message' => 'Account updated successfully',
                'success' => true
            ]);
        } else {
            echo json_encode(['message' => 'Failed to update account', 'success' => false]);
        }

    } catch (Exception $e) {
        echo json_encode(['message' => 'Error: ' . $e->getMessage(), 'success' => false]);
    }
}

function getDistributionByMonth($db)
{
    $month = isset($_GET['month']) ? $_GET['month'] : 'latest';

    try {
        // Determine which month to use
        if ($month === 'latest') {
            // Get the latest month with data
            $latestQuery = 'SELECT MAX(month_year) as latest_month FROM balance_history';
            $latestStmt = $db->prepare($latestQuery);
            $latestStmt->execute();
            $latestResult = $latestStmt->fetch(PDO::FETCH_ASSOC);
            $month = $latestResult['latest_month'] ?: date('Y-m-01');
        } elseif ($month === 'current') {
            $month = date('Y-m-01');
        }

        $query = 'SELECT at.type_name as account_type, at.color_code, at.icon_class,
            COALESCE(SUM(bh.balance), 0) as total_balance, 
            COUNT(a.id) as account_count
            FROM accounts a 
            LEFT JOIN account_types at ON a.account_type_id = at.id 
            LEFT JOIN balance_history bh ON a.id = bh.account_id AND bh.month_year = :month
            WHERE a.status = "Active"
            GROUP BY at.id, at.type_name, at.color_code, at.icon_class
            HAVING total_balance > 0
            ORDER BY total_balance DESC';

        $stmt = $db->prepare($query);
        $stmt->bindParam(':month', $month);
        $stmt->execute();

        $distribution = array();
        $totalBalance = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['total_balance'] = floatval($row['total_balance']);
            $totalBalance += $row['total_balance'];
            $distribution[] = $row;
        }

        // Format month for display
        $monthDisplay = date('F Y', strtotime($month));

        echo json_encode(array(
            'distribution' => $distribution,
            'month' => $month,
            'month_display' => $monthDisplay,
            'total_balance' => $totalBalance,
            'account_types_count' => count($distribution)
        ));

    } catch (PDOException $e) {
        echo json_encode(array(
            'error' => 'Database error: ' . $e->getMessage(),
            'distribution' => [],
            'month' => $month,
            'month_display' => 'Error loading data'
        ));
    }
}

function getSavingsBreakdown($db)
{
    try {
        $savingsTypes = ['Savings', 'MMF', 'Sacco'];

        // Get the latest month that has any savings data
        $latestSavingsQuery = "SELECT MAX(bh.month_year) as latest_month
            FROM balance_history bh
            INNER JOIN accounts a ON bh.account_id = a.id
            INNER JOIN account_types at ON a.account_type_id = at.id
            WHERE a.status = 'Active' AND at.type_name IN ('Savings', 'MMF', 'Sacco')";

        $latestSavingsStmt = $db->prepare($latestSavingsQuery);
        $latestSavingsStmt->execute();
        $latestSavingsResult = $latestSavingsStmt->fetch(PDO::FETCH_ASSOC);
        $latestSavingsMonth = $latestSavingsResult['latest_month'];

        if (!$latestSavingsMonth) {
            echo json_encode([
                'success' => false,
                'message' => 'No savings data available',
                'accounts' => [],
                'summary' => []
            ]);
            return;
        }

        // Get the previous month that has savings data
        $previousSavingsMonthQuery = "SELECT MAX(bh.month_year) as previous_month
            FROM balance_history bh
            INNER JOIN accounts a ON bh.account_id = a.id
            INNER JOIN account_types at ON a.account_type_id = at.id
            WHERE a.status = 'Active' AND at.type_name IN ('Savings', 'MMF', 'Sacco') 
            AND bh.month_year < ?";

        $previousSavingsMonthStmt = $db->prepare($previousSavingsMonthQuery);
        $previousSavingsMonthStmt->execute([$latestSavingsMonth]);
        $previousSavingsMonthResult = $previousSavingsMonthStmt->fetch(PDO::FETCH_ASSOC);
        $previousSavingsMonth = $previousSavingsMonthResult['previous_month'];

        // Get detailed breakdown for each savings account
        $breakdownQuery = "SELECT 
                a.id,
                a.account_name,
                at.type_name as account_type,
                at.color_code,
                at.icon_class,
                COALESCE(bh_current.balance, 0) as current_balance,
                COALESCE(bh_previous.balance, 0) as previous_balance,
                (COALESCE(bh_current.balance, 0) - COALESCE(bh_previous.balance, 0)) as growth_amount,
                CASE 
                    WHEN COALESCE(bh_previous.balance, 0) > 0 THEN 
                        ((COALESCE(bh_current.balance, 0) - COALESCE(bh_previous.balance, 0)) / bh_previous.balance * 100)
                    WHEN COALESCE(bh_current.balance, 0) > 0 THEN 100
                    ELSE 0 
                END as growth_percentage
            FROM accounts a
            INNER JOIN account_types at ON a.account_type_id = at.id
            LEFT JOIN balance_history bh_current ON a.id = bh_current.account_id AND bh_current.month_year = :current_month
            LEFT JOIN balance_history bh_previous ON a.id = bh_previous.account_id AND bh_previous.month_year = :previous_month
            WHERE a.status = 'Active' 
            AND at.type_name IN ('Savings', 'MMF', 'Sacco')
            AND (bh_current.balance IS NOT NULL OR bh_previous.balance IS NOT NULL)
            ORDER BY growth_amount DESC";

        $breakdownStmt = $db->prepare($breakdownQuery);
        $breakdownStmt->bindParam(':current_month', $latestSavingsMonth);
        $breakdownStmt->bindParam(':previous_month', $previousSavingsMonth);
        $breakdownStmt->execute();

        $accounts = [];
        $totalCurrentBalance = 0;
        $totalPreviousBalance = 0;
        $totalGrowth = 0;

        while ($row = $breakdownStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['current_balance'] = floatval($row['current_balance']);
            $row['previous_balance'] = floatval($row['previous_balance']);
            $row['growth_amount'] = floatval($row['growth_amount']);
            $row['growth_percentage'] = floatval($row['growth_percentage']);

            $totalCurrentBalance += $row['current_balance'];
            $totalPreviousBalance += $row['previous_balance'];
            $totalGrowth += $row['growth_amount'];

            $accounts[] = $row;
        }

        // Calculate summary by account type
        $typeSummaryQuery = "SELECT 
                at.type_name as account_type,
                at.color_code,
                at.icon_class,
                COALESCE(SUM(bh_current.balance), 0) as total_current,
                COALESCE(SUM(bh_previous.balance), 0) as total_previous,
                (COALESCE(SUM(bh_current.balance), 0) - COALESCE(SUM(bh_previous.balance), 0)) as total_growth,
                COUNT(a.id) as account_count
            FROM accounts a
            INNER JOIN account_types at ON a.account_type_id = at.id
            LEFT JOIN balance_history bh_current ON a.id = bh_current.account_id AND bh_current.month_year = :current_month
            LEFT JOIN balance_history bh_previous ON a.id = bh_previous.account_id AND bh_previous.month_year = :previous_month
            WHERE a.status = 'Active' 
            AND at.type_name IN ('Savings', 'MMF', 'Sacco')
            GROUP BY at.id, at.type_name, at.color_code, at.icon_class
            ORDER BY total_growth DESC";

        $typeSummaryStmt = $db->prepare($typeSummaryQuery);
        $typeSummaryStmt->bindParam(':current_month', $latestSavingsMonth);
        $typeSummaryStmt->bindParam(':previous_month', $previousSavingsMonth);
        $typeSummaryStmt->execute();

        $typeSummary = [];
        while ($row = $typeSummaryStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['total_current'] = floatval($row['total_current']);
            $row['total_previous'] = floatval($row['total_previous']);
            $row['total_growth'] = floatval($row['total_growth']);
            $row['account_count'] = intval($row['account_count']);
            $typeSummary[] = $row;
        }

        echo json_encode([
            'success' => true,
            'current_month' => $latestSavingsMonth,
            'current_month_display' => date('F Y', strtotime($latestSavingsMonth)),
            'previous_month' => $previousSavingsMonth,
            'previous_month_display' => $previousSavingsMonth ? date('F Y', strtotime($previousSavingsMonth)) : 'N/A',
            'accounts' => $accounts,
            'type_summary' => $typeSummary,
            'totals' => [
                'current_balance' => $totalCurrentBalance,
                'previous_balance' => $totalPreviousBalance,
                'total_growth' => $totalGrowth,
                'growth_percentage' => $totalPreviousBalance > 0 ? (($totalGrowth / $totalPreviousBalance) * 100) : ($totalGrowth > 0 ? 100 : 0)
            ]
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'accounts' => [],
            'summary' => []
        ]);
    }
}

function getTotalBalanceBreakdown($db)
{
    try {
        // Get the two most recent months with data
        $monthsQuery = "SELECT DISTINCT month_year FROM balance_history ORDER BY month_year DESC LIMIT 2";
        $monthsStmt = $db->prepare($monthsQuery);
        $monthsStmt->execute();
        $months = $monthsStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($months)) {
            echo json_encode([
                'success' => false,
                'message' => 'No balance data available',
                'current_month' => null,
                'previous_month' => null
            ]);
            return;
        }

        $currentMonth = $months[0];
        $previousMonth = isset($months[1]) ? $months[1] : null;

        // Get ALL accounts (including inactive) with their balances for current month
        $currentMonthQuery = "SELECT 
                a.id,
                a.account_name,
                a.bank_name,
                a.status,
                at.type_name as account_type,
                at.color_code,
                at.icon_class,
                COALESCE(bh.balance, 0) as balance,
                COALESCE(bh.growth_amount, 0) as growth_amount,
                COALESCE(bh.growth_percentage, 0) as growth_percentage
            FROM accounts a
            LEFT JOIN account_types at ON a.account_type_id = at.id
            LEFT JOIN balance_history bh ON a.id = bh.account_id AND bh.month_year = :month
            ORDER BY bh.balance DESC, a.account_name ASC";

        $currentStmt = $db->prepare($currentMonthQuery);
        $currentStmt->bindParam(':month', $currentMonth);
        $currentStmt->execute();

        $currentMonthAccounts = [];
        $currentTotal = 0;
        $currentActiveTotal = 0;
        $currentInactiveTotal = 0;

        while ($row = $currentStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['balance'] = floatval($row['balance']);
            $row['growth_amount'] = floatval($row['growth_amount']);
            $row['growth_percentage'] = floatval($row['growth_percentage']);

            $currentTotal += $row['balance'];
            if ($row['status'] === 'Active') {
                $currentActiveTotal += $row['balance'];
            } else {
                $currentInactiveTotal += $row['balance'];
            }

            $currentMonthAccounts[] = $row;
        }

        // Calculate percentage of total for each account
        foreach ($currentMonthAccounts as &$account) {
            $account['percentage'] = $currentTotal > 0 ? ($account['balance'] / $currentTotal) * 100 : 0;
        }

        // Get ALL accounts with their balances for previous month
        $previousMonthAccounts = [];
        $previousTotal = 0;
        $previousActiveTotal = 0;
        $previousInactiveTotal = 0;

        if ($previousMonth) {
            $previousStmt = $db->prepare($currentMonthQuery);
            $previousStmt->bindParam(':month', $previousMonth);
            $previousStmt->execute();

            while ($row = $previousStmt->fetch(PDO::FETCH_ASSOC)) {
                $row['balance'] = floatval($row['balance']);
                $row['growth_amount'] = floatval($row['growth_amount']);
                $row['growth_percentage'] = floatval($row['growth_percentage']);

                $previousTotal += $row['balance'];
                if ($row['status'] === 'Active') {
                    $previousActiveTotal += $row['balance'];
                } else {
                    $previousInactiveTotal += $row['balance'];
                }

                $previousMonthAccounts[] = $row;
            }

            // Calculate percentage of total for each account
            foreach ($previousMonthAccounts as &$account) {
                $account['percentage'] = $previousTotal > 0 ? ($account['balance'] / $previousTotal) * 100 : 0;
            }
        }

        // Build comparison data
        $comparisonData = [];
        $accountMap = [];

        // Index previous month accounts by ID
        foreach ($previousMonthAccounts as $acc) {
            $accountMap[$acc['id']] = $acc;
        }

        // Build comparison
        foreach ($currentMonthAccounts as $currentAcc) {
            $prevBalance = isset($accountMap[$currentAcc['id']]) ? $accountMap[$currentAcc['id']]['balance'] : 0;
            $change = $currentAcc['balance'] - $prevBalance;
            $changePercent = $prevBalance > 0 ? (($change / $prevBalance) * 100) : ($currentAcc['balance'] > 0 ? 100 : 0);

            $comparisonData[] = [
                'id' => $currentAcc['id'],
                'account_name' => $currentAcc['account_name'],
                'account_type' => $currentAcc['account_type'],
                'color_code' => $currentAcc['color_code'],
                'status' => $currentAcc['status'],
                'previous_balance' => $prevBalance,
                'current_balance' => $currentAcc['balance'],
                'change' => $change,
                'change_percentage' => $changePercent
            ];
        }

        // Sort comparison by change amount descending
        usort($comparisonData, function($a, $b) {
            return $b['change'] - $a['change'];
        });

        // Calculate totals for comparison
        $netChange = $currentTotal - $previousTotal;
        $growthRate = $previousTotal > 0 ? (($netChange / $previousTotal) * 100) : ($currentTotal > 0 ? 100 : 0);

        echo json_encode([
            'success' => true,
            'current_month' => [
                'month' => $currentMonth,
                'month_display' => date('F Y', strtotime($currentMonth)),
                'accounts' => $currentMonthAccounts,
                'total' => $currentTotal,
                'active_total' => $currentActiveTotal,
                'inactive_total' => $currentInactiveTotal,
                'account_count' => count($currentMonthAccounts)
            ],
            'previous_month' => $previousMonth ? [
                'month' => $previousMonth,
                'month_display' => date('F Y', strtotime($previousMonth)),
                'accounts' => $previousMonthAccounts,
                'total' => $previousTotal,
                'active_total' => $previousActiveTotal,
                'inactive_total' => $previousInactiveTotal,
                'account_count' => count($previousMonthAccounts)
            ] : null,
            'comparison' => [
                'data' => $comparisonData,
                'previous_total' => $previousTotal,
                'current_total' => $currentTotal,
                'net_change' => $netChange,
                'growth_rate' => $growthRate
            ]
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

function getLatestMonthDetails($db)
{
    try {
        // Get the latest month with data
        $latestMonthQuery = "SELECT MAX(month_year) as latest_month FROM balance_history";
        $latestStmt = $db->prepare($latestMonthQuery);
        $latestStmt->execute();
        $latestResult = $latestStmt->fetch(PDO::FETCH_ASSOC);
        $latestMonth = $latestResult['latest_month'];

        if (!$latestMonth) {
            echo json_encode([
                'success' => false,
                'message' => 'No balance data available'
            ]);
            return;
        }

        // Get total active accounts count
        $totalAccountsQuery = "SELECT COUNT(*) as total FROM accounts WHERE status = 'Active'";
        $totalAccountsStmt = $db->prepare($totalAccountsQuery);
        $totalAccountsStmt->execute();
        $totalAccountsResult = $totalAccountsStmt->fetch(PDO::FETCH_ASSOC);
        $totalActiveAccounts = intval($totalAccountsResult['total']);

        // Get accounts with data for this month
        $accountsWithDataQuery = "SELECT COUNT(DISTINCT account_id) as count FROM balance_history WHERE month_year = ?";
        $accountsWithDataStmt = $db->prepare($accountsWithDataQuery);
        $accountsWithDataStmt->execute([$latestMonth]);
        $accountsWithDataResult = $accountsWithDataStmt->fetch(PDO::FETCH_ASSOC);
        $accountsWithData = intval($accountsWithDataResult['count']);

        // Get total balance and growth for the month
        $totalsQuery = "SELECT 
                COALESCE(SUM(bh.balance), 0) as total_balance,
                COALESCE(SUM(bh.growth_amount), 0) as total_growth
            FROM balance_history bh
            INNER JOIN accounts a ON bh.account_id = a.id
            WHERE bh.month_year = ? AND a.status = 'Active'";
        $totalsStmt = $db->prepare($totalsQuery);
        $totalsStmt->execute([$latestMonth]);
        $totalsResult = $totalsStmt->fetch(PDO::FETCH_ASSOC);

        // Get breakdown by account type
        $typeBreakdownQuery = "SELECT 
                at.type_name as account_type,
                at.color_code,
                at.icon_class,
                COALESCE(SUM(bh.balance), 0) as total_balance,
                COALESCE(SUM(bh.growth_amount), 0) as total_growth,
                COUNT(DISTINCT a.id) as account_count
            FROM accounts a
            INNER JOIN account_types at ON a.account_type_id = at.id
            LEFT JOIN balance_history bh ON a.id = bh.account_id AND bh.month_year = :month
            WHERE a.status = 'Active' AND bh.balance IS NOT NULL
            GROUP BY at.id, at.type_name, at.color_code, at.icon_class
            ORDER BY total_balance DESC";
        $typeStmt = $db->prepare($typeBreakdownQuery);
        $typeStmt->bindParam(':month', $latestMonth);
        $typeStmt->execute();

        $typeBreakdown = [];
        $totalBalance = floatval($totalsResult['total_balance']);
        while ($row = $typeStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['total_balance'] = floatval($row['total_balance']);
            $row['total_growth'] = floatval($row['total_growth']);
            $row['percentage'] = $totalBalance > 0 ? ($row['total_balance'] / $totalBalance) * 100 : 0;
            $typeBreakdown[] = $row;
        }

        // Get top performers (highest growth)
        $topPerformersQuery = "SELECT 
                a.account_name,
                at.type_name as account_type,
                at.color_code,
                bh.balance,
                bh.growth_amount,
                bh.growth_percentage
            FROM balance_history bh
            INNER JOIN accounts a ON bh.account_id = a.id
            INNER JOIN account_types at ON a.account_type_id = at.id
            WHERE bh.month_year = ? AND a.status = 'Active' AND bh.growth_amount > 0
            ORDER BY bh.growth_amount DESC
            LIMIT 5";
        $topStmt = $db->prepare($topPerformersQuery);
        $topStmt->execute([$latestMonth]);

        $topPerformers = [];
        while ($row = $topStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['balance'] = floatval($row['balance']);
            $row['growth_amount'] = floatval($row['growth_amount']);
            $row['growth_percentage'] = floatval($row['growth_percentage']);
            $topPerformers[] = $row;
        }

        // Get accounts needing attention (no data for current month, negative growth, or below minimum)
        $needsAttentionQuery = "SELECT 
                a.id,
                a.account_name,
                at.type_name as account_type,
                at.color_code,
                a.status,
                a.minimum_balance,
                bh.balance,
                bh.growth_amount,
                CASE 
                    WHEN bh.balance IS NULL THEN 'No data for this month'
                    WHEN bh.growth_amount < 0 THEN 'Negative growth'
                    WHEN a.minimum_balance > 0 AND bh.balance < a.minimum_balance THEN 'Below minimum balance'
                    ELSE 'OK'
                END as issue
            FROM accounts a
            INNER JOIN account_types at ON a.account_type_id = at.id
            LEFT JOIN balance_history bh ON a.id = bh.account_id AND bh.month_year = :month
            WHERE a.status = 'Active' 
            AND (bh.balance IS NULL OR bh.growth_amount < 0 OR (a.minimum_balance > 0 AND bh.balance < a.minimum_balance))
            ORDER BY 
                CASE WHEN bh.balance IS NULL THEN 0 ELSE 1 END,
                bh.growth_amount ASC";
        $attentionStmt = $db->prepare($needsAttentionQuery);
        $attentionStmt->bindParam(':month', $latestMonth);
        $attentionStmt->execute();

        $needsAttention = [];
        while ($row = $attentionStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['balance'] = $row['balance'] !== null ? floatval($row['balance']) : null;
            $row['growth_amount'] = $row['growth_amount'] !== null ? floatval($row['growth_amount']) : null;
            $row['minimum_balance'] = floatval($row['minimum_balance']);
            $needsAttention[] = $row;
        }

        // Calculate data freshness
        $currentMonth = date('Y-m-01');
        $dataMonth = date('Y-m-01', strtotime($latestMonth));
        $isCurrentMonth = ($currentMonth === $dataMonth);
        $monthsDiff = (strtotime($currentMonth) - strtotime($dataMonth)) / (30 * 24 * 60 * 60);

        $freshness = 'current';
        $freshnessMessage = 'Data is current';
        if (!$isCurrentMonth) {
            if ($monthsDiff <= 1) {
                $freshness = 'recent';
                $freshnessMessage = 'Data is from last month';
            } else {
                $freshness = 'stale';
                $freshnessMessage = 'Data is ' . floor($monthsDiff) . ' months old';
            }
        }

        // Calculate completion percentage
        $completionPercentage = $totalActiveAccounts > 0 ? ($accountsWithData / $totalActiveAccounts) * 100 : 0;

        echo json_encode([
            'success' => true,
            'month' => $latestMonth,
            'month_display' => date('F Y', strtotime($latestMonth)),
            'totals' => [
                'total_balance' => floatval($totalsResult['total_balance']),
                'total_growth' => floatval($totalsResult['total_growth'])
            ],
            'accounts' => [
                'total_active' => $totalActiveAccounts,
                'with_data' => $accountsWithData,
                'pending' => $totalActiveAccounts - $accountsWithData,
                'completion_percentage' => round($completionPercentage, 1)
            ],
            'freshness' => [
                'status' => $freshness,
                'message' => $freshnessMessage,
                'is_current_month' => $isCurrentMonth
            ],
            'type_breakdown' => $typeBreakdown,
            'top_performers' => $topPerformers,
            'needs_attention' => $needsAttention
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}



?>
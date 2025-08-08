<?php include "head.php";?>
<?php
if (isset($_POST['export_csv'])) {
    // Define the CSV file headers
    $filename = "transactions_" . date("Ymd_His") . ".csv";
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $output = fopen("php://output", "w");
    fputcsv($output, ['Category', 'Subcategory', 'Description', 'Amount (Ksh)', 'Cost (Ksh)', 'Tag', 'Date']);
    // Query the database for transaction data
    $query = mysqli_query($con, "
        SELECT category, subcategory, description, amount, transactionCost, tag, expenseDate AS date FROM tblbudget WHERE is_deleted = 0
        UNION ALL 
        SELECT category, subcategory, description, amount, transactionCost, tag, od_date AS date FROM tbloverdrafts WHERE is_deleted = 0 ORDER BY date DESC");

    // Loop through each row and write it to the CSV file
    while ($row = mysqli_fetch_assoc($query)) {
        // Format the date
        $row['date'] = date("M j, Y H:i", strtotime($row['date']));
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Handle CSV Import
if (isset($_POST['import_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $csvFile = fopen($_FILES['csv_file']['tmp_name'], 'r');

        // Read and validate header row
        $header = fgetcsv($csvFile);

        // Normalize headers by trimming whitespace and converting to lowercase
        $normalizedHeader = array_map('trim', $header);
        $normalizedHeader = array_map('strtolower', $normalizedHeader);

        // Define expected headers (also normalized)
        $expectedHeaders = [
            'category',
            'subcategory',
            'description',
            'amount (ksh)',
            'cost (ksh)',
            'tag',
            'date'
        ];

        // Compare normalized headers with expected headers
        if ($normalizedHeader !== $expectedHeaders) {
            $_SESSION['alert'] = '
            <div class="alert alert-danger border-0 d-flex align-items-center">
                <p class="mb-0 flex-1">Invalid CSV headers. Please ensure the file has the correct column names: ' . implode(', ', $expectedHeaders) . '.</p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
            fclose($csvFile);
            header("Location: transactions");
            exit;
        }

        $successCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        while (($row = fgetcsv($csvFile)) !== FALSE) {
            list($category, $subcategory, $description, $amount, $transactionCost, $tag, $date) = $row;

            // Convert date to MySQL format
            $dateFormatted = date("Y-m-d H:i:s", strtotime($date));
            if ($dateFormatted === false) {
                $dateFormatted = date("Y-m-d H:i:s"); // fallback
                $errorCount++;
                continue;
            }

            // Sanitize input
            $category = mysqli_real_escape_string($con, htmlspecialchars($category, ENT_QUOTES, 'UTF-8'));
            $subcategory = mysqli_real_escape_string($con, htmlspecialchars($subcategory, ENT_QUOTES, 'UTF-8'));
            $description = mysqli_real_escape_string($con, htmlspecialchars($description, ENT_QUOTES, 'UTF-8'));
            $amount = floatval($amount);
            $transactionCost = floatval($transactionCost);
            $tag = mysqli_real_escape_string($con, htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'));

            // Check for duplicate based on expenseDate
            $checkSql = "
                SELECT * FROM tblbudget 
                WHERE expenseDate = '$dateFormatted'
                  AND is_deleted = 0
            ";
            $result = mysqli_query($con, $checkSql);

            if (mysqli_num_rows($result) > 0) {
                $duplicateCount++;
                continue; // Skip inserting this record
            }

            // Insert into tblbudget
            $insertSql = "
                INSERT INTO tblbudget (
                    category, subcategory, description, amount, transactionCost, tag, expenseDate
                ) VALUES (
                    '$category', '$subcategory', '$description', '$amount', '$transactionCost', '$tag', '$dateFormatted'
                )
            ";

            if (mysqli_query($con, $insertSql)) {
                $successCount++;
            } else {
                $errorCount++;
                error_log("DB Error: " . mysqli_error($con));
            }
        }

        fclose($csvFile);

        // Set session alerts
        $alerts = [];

        if ($successCount > 0) {
            $alerts[] = '
            <div class="alert alert-success border-0 d-flex align-items-center">
                <p class="mb-0 flex-1">' . $successCount . ' transaction(s) added successfully!</p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }

        if ($duplicateCount > 0) {
            $alerts[] = '
            <div class="alert alert-warning border-0 d-flex align-items-center">
                <p class="mb-0 flex-1">Skipped ' . $duplicateCount . ' transaction(s) with duplicate dates.</p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }

        if ($errorCount > 0) {
            $alerts[] = '
            <div class="alert alert-danger border-0 d-flex align-items-center">
                <p class="mb-0 flex-1">Failed to import ' . $errorCount . ' transaction(s). Please check the file.</p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }

        if (empty($alerts)) {
            $alerts[] = '
            <div class="alert alert-info border-0 d-flex align-items-center">
                <p class="mb-0 flex-1">No new transactions were imported. All records had duplicate dates or invalid data.</p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }

        $_SESSION['alert'] = implode('', $alerts);
        header("Location: transactions");
        exit;

    } else {
        $_SESSION['alert'] = '
        <div class="alert alert-danger border-0 d-flex align-items-center">
            <p class="mb-0 flex-1">Error uploading file. Please try again.</p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        header("Location: transactions");
        exit;
    }
}
?>
<title>Transactions Management</title>
<?php include "navi.php";
$status = "OK";
$msg = "";
?>
    <div class="card shadow-none border mb-3">
        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/illustrations/corner-6.png);">
        </div>
        <!--/.bg-holder-->
        <div class="card-header z-1">
            <div class="row flex-between-center gx-0">
                <div class="col-lg-auto d-flex align-items-center">
                    <h4 class="mb-0 text-primary fw-bold">Transactions<span class="text-info fw-medium"> Management</span></h4>
                </div>
                <div class="col-lg-auto pt-3 pt-lg-0">
                    <form class="row flex-lg-column flex-xxl-row gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
                        <div class="col-auto">
                        </div>
                        <div class="col-md-auto position-relative">
                            <h6 class="mb-1 badge rounded-pill badge-subtle-info"><?php echo date("jS F Y"); ?> | <span id="timeDisplay"></span></h6>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
    //echo '<meta http-equiv="refresh" content="10;url=' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
}
?>

    <div class="row  g-3 mb-3">
        <div class="col">
            <div class="card mb-3">
                <div class="card-body p-0">
                    <div class="tab-content">
                        <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-41cf422d-2a1d-40e2-b92a-ceac8cdfaca0" id="dom-41cf422d-2a1d-40e2-b92a-ceac8cdfaca0">
                            <div class="card shadow-none">
                                <form id="tasksForm" method="post">
                                    <div class="card-header">
                                        <div class="row flex-between-center">
                                            <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                                                <h4 class="mb-0">
                                                    <span class="text-primary">Transaction History</span>
                                                    <span class="text-warning">
                                                </span>
                                                </h4>
                                            </div>
                                            <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                                                <div class="d-none" id="table-simple-pagination-actions">
                                                    <div class="d-flex">
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center" id="table-simple-pagination-replace-element">
                                                    <form method="post" action="">
                                                        <button type="submit" name="export_csv" data-bs-toggle="tooltip" data-bs-placement="top" title="Export all transactions as CSV" class="btn btn-falcon-default btn-sm"><span class="fas fa-external-link-alt" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Export</span></button>
                                                        <a class="btn btn-falcon-info btn-sm mx-2" data-bs-toggle="modal" data-bs-target="#addTransactionModal" data-bs-toggle="tooltip" data-bs-placement="top" title="Add a new transaction" type="button"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Add Transaction</span></a>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Loader -->
                                    <div id="loading-spinner" class="text-center py-5">
                                        <div class="spinner-grow text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <div class="card-body px-0 pt-0" id="transaction-table">
                                        <table class="table table-sm mb-0 overflow-hidden data-table fs-10"  data-datatables="data-datatables">
                                            <thead class="bg-200">
                                            <tr>
                                                <th class="text-900 no-sort white-space-nowrap">
                                                    <div class="form-check mb-0 d-flex align-items-center">
                                                        <input class="form-check-input" id="checkbox-select-all" type="checkbox" onclick="selectAllTasks(this)" data-bulk-select='{"body":"table-simple-pagination-body","actions":"table-simple-pagination-actions","replacedElement":"table-simple-pagination-replace-element"}' />
                                                    </div>
                                                </th>
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap">Category</th>
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap">Description</th>
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap">Amount (Ksh)</th>
                                                <th class="text-900 sort pe-1 text-center white-space-nowrap">Platform</th>
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap">Date</th>
                                                <th class="text-900 no-sort pe-1 align-middle data-table-row-action"></th>
                                            </tr>
                                            </thead>
                                            <tbody class="list" id="table-simple-pagination-body">
                                            <?php
                                            $query=mysqli_query($con,"SELECT 
        budgetID AS id, category, subcategory, description, tag, amount, transactionCost, expenseDate AS date, 'tblbudget' AS table_source FROM tblbudget WHERE is_deleted = 0
    UNION ALL SELECT id, category, subcategory, description, tag, amount, transactionCost, od_date AS date, 'tbloverdrafts' AS table_source FROM tbloverdrafts WHERE is_deleted = 0 ORDER BY date DESC");
                                            $cnt=1;
                                            while($row=mysqli_fetch_array($query))
                                            {

                                                ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger hover-bg-100">
                                                    <td class="align-middle" style="width: 28px;">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="simple-pagination-item-<?php echo $cnt; ?>" data-bulk-select-row="data-bulk-select-row" value="<?php echo $row['id']; ?>" name="taskIds[]"/>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-start product">
                                                        <div class="d-flex align-items-center position-relative">
                                                            <div class="flex-1">
                                                                <h6 class="mb-0 fw-semi-bold text-nowrap">
                                                                    <?php
                                                                    if ($row["category"] === "Expense") {
                                                                        echo '<span class="badge fs-10 rounded-pill badge-subtle-danger">Expense</span>';
                                                                    } elseif ($row["category"] === "Savings") {
                                                                        echo '<span class="badge fs-10 rounded-pill badge-subtle-success">Savings</span>';
                                                                    } elseif ($row["category"] === "Income") {
                                                                        echo '<span class="badge fs-10 rounded-pill badge-subtle-primary">Income</span>';
                                                                    } else {
                                                                        echo htmlspecialchars($row["category"]);
                                                                    }
                                                                    ?>
                                                                </h6>
                                                                <p class="fw-semi-bold mb-0 text-300">
                                                                    <?php
                                                                    if (isset($row['table_source']) && $row['table_source'] === 'tblbudget') {
                                                                        echo 'TNSN';
                                                                    } elseif (isset($row['table_source']) && $row['table_source'] === 'tbloverdrafts') {
                                                                        echo 'OVDT';}
                                                                    ?> | #<?php echo $row["id"]; ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-start product">
                                                        <div class="d-flex align-items-center position-relative">
                                                            <div class="flex-1">
                                                                <h6 class="mb-0 fw-semi-bold text-nowrap"> <?php echo $row["subcategory"];?></h6>
                                                                <p class="fw-semi-bold mb-0 text-500"><?php echo $row["description"];?></p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-start amount">
                                                        <div class="flex-1">
                                                            <h6 class="mb-0 fw-semi-bold text-nowrap"> <?php echo $row["amount"]; ?></h6>
                                                            <p class="fw-semi-bold mb-0 text-500">
                                                                <span class="badge  badge-subtle-secondary"><?php echo $row["transactionCost"];?></span>
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-start product">
                                                        <div class="d-flex align-items-center position-relative">
                                                            <div class="flex-1">
                                                                <h6 class="mb-1 fw-semi-bold text-nowrap">
                                                                    <?php
                                                                    if ($row["tag"] === "Mpesa") {
                                                                        echo '<span class="badge fs-10 w-100 rounded-pill badge-subtle-success">Mpesa</span>';
                                                                    } elseif ($row["tag"] === "Cash") {
                                                                        echo '<span class="badge fs-10 w-100 rounded-pill badge-subtle-warning">Cash</span>';
                                                                    } elseif (strtolower($row["tag"]) === "paypal") {
                                                                        echo '<span class="badge fs-10 w-100 rounded-pill badge-subtle-primary">PayPal</span>';
                                                                    } elseif ($row["tag"] === "Card") {
                                                                        echo '<span class="badge fs-10 w-100 rounded-pill badge-subtle-info">Card</span>';
                                                                    } else {
                                                                        echo htmlspecialchars($row["tag"]);
                                                                    }
                                                                    ?>
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-start amount">
                                                        <h6 class="mb-0 fw-semi-bold mb-0 text-500"><?php $originalDate = $row["date"];
                                                            $formattedDate = date("M j, Y", strtotime($originalDate));
                                                            echo $formattedDate;?>
                                                        </h6>
                                                    </td>
                                                    <td class="align-middle white-space-nowrap text-end position-relative">
                                                        <div class="hover-actions bg-100">
                                                            <a class="btn bg-primary-subtle icon-item rounded-3 me-2 fs-11 icon-item-sm" data-bs-toggle="modal"  data-bs-target="#viewTransactionModal" data-bs-toggle="tooltip" data-bs-placement="top" title="View Transaction"
                                                               onclick="loadViewModal('<?php echo $row['id']; ?>', '<?php echo addslashes($row['category']); ?>', '<?php echo addslashes($row['subcategory']); ?>', '<?php echo addslashes($row['tag']); ?>', '<?php echo addslashes($row['description']); ?>', '<?php echo $row['amount']; ?>', '<?php echo date('M j, Y \a\\t H:i', strtotime($row['date'])); ?>')"><span class="far fa-eye"></span></a>
                                                            <a class="btn bg-success-subtle icon-item rounded-3 me-2 fs-11 icon-item-sm" data-bs-toggle="modal"  data-bs-target="#editTransactionModal" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Transaction"
                                                               onclick="loadEditModal('<?php echo $row['id']; ?>', '<?php echo addslashes($row['category']); ?>', '<?php echo addslashes($row['subcategory']); ?>', '<?php echo addslashes($row['tag']); ?>', '<?php echo addslashes($row['description']); ?>', '<?php echo $row['amount']; ?>', '<?php echo date('Y-m-d\TH:i', strtotime($row['date'])); ?>')"><span class="far fa-edit"></span></a>
                                                            <a class="btn bg-danger-subtle icon-item rounded-3 me-2 fs-11 icon-item-sm" data-bs-toggle="modal"   data-bs-target="#deleteTransactionModal" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Transaction"
                                                               onclick="loadDeleteModal('<?php echo $row['id']; ?>')"><span class="fas fa-trash"></span></a>
                                                        </div>
                                                        <div class="dropdown font-sans-serif btn-reveal-trigger">
                                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none" type="button" id="crm-recent-leads-4" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span class="fas fa-chevron-left fs-11"></span></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                                $cnt=$cnt+1;
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col">
            <div class="card shadow-none border ps-3">
                <div class="row gx-0 flex-between-center">
                    <div class="col-sm-auto d-flex align-items-center"><img class="ms-n2" src="../assets/img/illustrations/crm-bar-chart.png" alt="" width="90" />
                        <div>
                            <h4 class="text-primary fw-bold mb-0">Import <span class="text-info fw-medium">Transactions</span></h4>
                        </div><img class="ms-n4 d-md-none d-lg-block" src="../assets/img/illustrations/crm-line-chart.png" alt="" width="150" />
                    </div>
                    <div class="col-sm-auto pt-lg-0">
                        <form method="post" enctype="multipart/form-data" class="row flex-lg-column flex-xxl-row align-items-center align-items-lg-start align-items-xxl-center">
                            <div class="card-body">
                                <div class="mb-1">
                                    <label for="transaction_file" class="form-label">Select CSV File</label>
                                    <input type="file" class="form-control" id="transaction_file" name="csv_file" accept=".csv" required>
                                    <div class="form-text">File must match the export format with columns: Category, Subcategory, Description, Amount, Cost, Tag, Date</div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="import_csv" class="btn btn-primary">Import CSV</button>
                                <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importHelpModal">
                                    <i class="fas fa-question-circle me-1"></i> Import Help
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="add-transaction" method="post">
                    <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                        <div class="position-relative z-1">
                            <h4 class="mb-0 text-white" id="addTransactionModalLabel">Add Transaction</h4>
                        </div>
                        <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" name="category" id="category" aria-label="Default select example" required>
                                <option selected=""></option>
                                <option value="Expense">Expense</option>
                                <option value="Income">Income</option>
                                <option value="Savings">Savings</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subcategory" class="form-label">Sub Category</label>
                            <input type="text" name="subcategory" id="subcategory" class="form-control" placeholder="Enter or select subcategory" list="subcategoryList" required>
                            <datalist id="subcategoryList">
                                <!-- Options dynamically populated -->
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" id="description" class="form-control" placeholder="Enter or select description" list="descriptionList" required>
                            <datalist id="descriptionList">
                                <!-- Options dynamically populated -->
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label for="tag" class="form-label">Tag</label>
                            <select class="form-select" name="tag" id="tag" aria-label="Default select example" required>
                                <option selected=""></option>
                                <option value="Card">Card</option>
                                <option value="Cash">Cash</option>
                                <option value="Mpesa">MPesa</option>
                                <option value="PayPal">PayPal</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="expenseDate" class="form-label">Transaction Date</label>
                            <input type="datetime-local" name="expenseDate" id="expenseDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><span class="fas fa-plus"></span> Add Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- View Transaction Modal -->
    <div class="modal fade" id="viewTransactionModal" tabindex="-1" aria-labelledby="viewTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header position-relative modal-shape-header bg-shape">
                    <div class="position-relative z-1">
                        <h4 class="mb-0 text-white text-center" id="viewTransactionModalLabel">Transaction Details</h4>
                    </div>
                    <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col pe-2">
                        <div class="fs-8 mt-1">
                            <div class="d-flex flex-between-center mb-1">
                                <div class="d-flex align-items-center"><span class="dot bg-primary"></span><span class="fw-semi-bold">Category</span></div>
                                <div class="d-xxl-none"><span class="text-warning" id="viewCategory"></span></div>
                            </div>
                            <div class="d-flex flex-between-center mb-1">
                                <div class="d-flex align-items-center"><span class="dot bg-primary"></span><span class="fw-semi-bold">Sub Category</span></div>
                                <div class="d-xxl-none"><span class="text-warning" id="viewSubcategory"></span></div>
                            </div>
                            <div class="d-flex flex-between-center mb-1">
                                <div class="d-flex align-items-center"><span class="dot bg-primary"></span><span class="fw-semi-bold">Description</span></div>
                                <div class="d-xxl-none"><span class="text-warning" id="viewDescription"></span></div>
                            </div>
                            <div class="d-flex flex-between-center mb-1">
                                <div class="d-flex align-items-center"><span class="dot bg-primary"></span><span class="fw-semi-bold">Amount</span></div>
                                <div class="d-xxl-none"><span class="text-warning" id="viewAmount"></span></div>
                            </div>
                            <div class="d-flex flex-between-center mb-1">
                                <div class="d-flex align-items-center"><span class="dot bg-primary"></span><span class="fw-semi-bold">Transaction cost</span></div>
                                <div class="d-xxl-none"><span class="text-warning" id="viewTransactionCost"></span></div>
                            </div>
                            <div class="d-flex flex-between-center mb-1">
                                <div class="d-flex align-items-center"><span class="dot bg-primary"></span><span class="fw-semi-bold">Tag</span></div>
                                <div class="d-xxl-none"><span class="text-warning" id="viewTag"></span></div>
                            </div>
                            <div class="d-flex flex-between-center mb-1">
                                <div class="d-flex align-items-center"><span class="dot bg-primary"></span><span class="fw-semi-bold">Date</span></div>
                                <div class="d-xxl-none"><span class="text-warning" id="viewDate"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editTransactionForm" method="POST" action="edit-transaction">
                    <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                        <div class="position-relative z-1">
                            <h4 class="mb-0 text-white" id="editTransactionModalLabel">Edit Transaction</h4>
                        </div>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="budgetID" id="editBudgetID">
                        <div class="mb-3">
                            <label for="editCategory" class="form-label">Category</label>
                            <select class="form-select" id="editCategory" name="category" required>
                                <option value="Expense">Expense</option>
                                <option value="Income">Income</option>
                                <option value="Savings">Savings</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editSubCategory" class="form-label">Sub Category</label>
                            <input type="text" class="form-control" id="editSubcategory" name="subcategory" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <input type="text" class="form-control" id="editDescription" name="description" rows="3" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAmount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="editAmount" name="amount" step="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTag" class="form-label">Tag</label>
                            <select class="form-select" id="editTag" name="tag" required>
                                <option value="Card">Card</option>
                                <option value="Cash">Cash</option>
                                <option value="Mpesa">Mpesa</option>
                                <option value="PayPal">PayPal</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editDate" class="form-label">Date</label>
                            <input type="datetime-local" class="form-control" id="editDate" name="date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><span class="fas fa-save"></span> Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Transaction Modal -->
    <div class="modal fade" id="deleteTransactionModal" tabindex="-1" aria-labelledby="deleteTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="deleteTransactionForm" method="POST" action="delete-transaction">
                <div class="modal-content">
                    <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                        <div class="position-relative z-1">
                            <h4 class="mb-0 text-white" id="deleteTransactionModalLabel">Delete Transaction</h4>
                        </div>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="budgetID" id="deleteBudgetID">
                        <p>Are you sure you want to delete this transaction?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger"><span class="fas fa-trash"></span> Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="importHelpModal" tabindex="-1" aria-labelledby="importHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importHelpModalLabel">Import File Format</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>The import file should be in CSV format with the following columns:</p>
                    <ol>
                        <li><strong>Category</strong> - Transaction category (Expense, Income, Savings)</li>
                        <li><strong>Subcategory</strong> - Transaction subcategory</li>
                        <li><strong>Description</strong> - Transaction description</li>
                        <li><strong>Amount (Ksh)</strong> - Transaction amount</li>
                        <li><strong>Cost (Ksh)</strong> - Transaction cost</li>
                        <li><strong>Tag</strong> - Payment method (Mpesa, Cash, PayPal, Card)</li>
                        <li><strong>Date</strong> - Transaction date in format "MMM D, YYYY H:MM" (e.g., "May 10, 2025 15:30")</li>
                    </ol>
                    <p>You can download a template by exporting your current transactions.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Load data into modals dynamically
            const tableRows = document.querySelectorAll('.hover-actions-trigger');
            tableRows.forEach(row => {
                const viewBtn = row.querySelector('[data-bs-target="#viewTransactionModal"]');
                const editBtn = row.querySelector('[data-bs-target="#editTransactionModal"]');
                const deleteBtn = row.querySelector('[data-bs-target="#deleteTransactionModal"]');
                const rowData = {
                    id: row.querySelector('input[type="checkbox"]').value, // Use 'id' consistently
                    category: row.querySelector('td:nth-child(2) h6').innerText,
                    subcategory: row.querySelector('td:nth-child(3) h6').innerText,
                    description: row.querySelector('td:nth-child(3) p').innerText,
                    amount: row.querySelector('td:nth-child(4) h6').innerText,
                    transactionCost: row.querySelector('td:nth-child(4) p').innerText,
                    tag: row.querySelector('td:nth-child(5) h6').innerText,
                    date: row.querySelector('td:nth-child(6) h6').innerText,
                };

                // Populate View Modal
                viewBtn.addEventListener('click', () => {
                    document.getElementById('viewCategory').innerText = rowData.category;
                    document.getElementById('viewSubcategory').innerText = rowData.subcategory;
                    document.getElementById('viewDescription').innerText = rowData.description;
                    document.getElementById('viewAmount').innerText = rowData.amount;
                    document.getElementById('viewTransactionCost').innerText = rowData.transactionCost;
                    document.getElementById('viewTag').innerText = rowData.tag;
                    document.getElementById('viewDate').innerText = rowData.date;
                });

                // Populate Edit Modal
                editBtn.addEventListener('click', () => {
                    document.getElementById('editBudgetID').value = rowData.id; // Ensure 'id' is passed here
                    document.getElementById('editCategory').value = rowData.category;
                    document.getElementById('editSubcategory').value = rowData.subcategory;
                    document.getElementById('editDescription').value = rowData.description;
                    document.getElementById('editAmount').value = rowData.amount;
                    document.getElementById('editTag').value = rowData.tag;
                    // Format date for datetime-local input with GMT+3 adjustment
                    const date = new Date(rowData.date);
                    date.setHours(date.getHours() + 3);
                    const formattedDate = date.toISOString().slice(0, 16);
                    document.getElementById('editDate').value = formattedDate;
                });

                // Populate Delete Modal
                deleteBtn.addEventListener('click', () => {
                    document.getElementById('deleteBudgetID').value = rowData.id; // Ensure 'id' is passed here
                });
            });
        });


        function toggleAmountVisibility(amountId, btn) {
            const amountElement = document.getElementById(amountId); // Target the amount element
            const icon = btn.querySelector('.fas'); // Target the icon inside the button

            if (amountElement.classList.contains('blurred-text')) {
                // Show the amount
                amountElement.classList.remove('blurred-text');
                amountElement.classList.add('visible-text');
                icon.classList.remove('fa-eye'); // Change to slashed-eye icon
                icon.classList.add('fa-eye-slash');
            } else {
                // Hide the amount
                amountElement.classList.remove('visible-text');
                amountElement.classList.add('blurred-text');
                icon.classList.remove('fa-eye-slash'); // Change back to regular eye icon
                icon.classList.add('fa-eye');
            }
        }

        function toggleFinancialVisibility() {
            const financialAmounts = document.querySelectorAll('#financialOverviewSection .blur-financial-data, #financialOverviewSection .visible-financial-data');
            const financialIcon = document.getElementById('financialToggleIcon');

            financialAmounts.forEach(amount => {
                if (amount.classList.contains('blur-financial-data')) {
                    // If blurred, make visible
                    amount.classList.remove('blur-financial-data');
                    amount.classList.add('visible-financial-data');
                } else if (amount.classList.contains('visible-financial-data')) {
                    // If visible, blur it again
                    amount.classList.remove('visible-financial-data');
                    amount.classList.add('blur-financial-data');
                }
            });

            // Toggle the eye icon
            if (financialIcon.classList.contains('fa-eye')) {
                financialIcon.classList.remove('fa-eye');
                financialIcon.classList.add('fa-eye-slash');
            } else {
                financialIcon.classList.remove('fa-eye-slash');
                financialIcon.classList.add('fa-eye');
            }
        }

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Show the loader initially
            const spinner = document.getElementById('loading-spinner');
            const table = document.getElementById('transaction-table');

            // Simulate a delay to mimic data fetching
            setTimeout(() => {
                // Hide the spinner and show the table
                spinner.classList.add('d-none');
                table.classList.remove('d-none');
            }, 1000); // Adjust the timeout as per actual data fetching duration
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const categoryField = document.getElementById("category");
            const subCategoryField = document.getElementById("subcategory");
            const subCategoryDatalist = document.getElementById("subcategoryList");
            const descriptionField = document.getElementById("description");
            const descriptionDatalist = document.getElementById("descriptionList");

            categoryField.addEventListener("change", async function () {
                const category = categoryField.value;

                // Reset subcategory and description datalists
                subCategoryDatalist.innerHTML = '';
                descriptionDatalist.innerHTML = '';
                subCategoryField.value = '';
                descriptionField.value = '';

                if (category === "Savings") {
                    // Populate subcategory datalist for Savings
                    const savingsOptions = ["MMF", "Sacco"];
                    savingsOptions.forEach(option => {
                        const opt = document.createElement("option");
                        opt.value = option;
                        subCategoryDatalist.appendChild(opt);
                    });

                    // Fetch goal names for the Description field
                    try {
                        const response = await fetch("fetch-savings-goals.php");
                        const goals = await response.json(); // Assuming JSON response from the server

                        goals.forEach(goal => {
                            const opt = document.createElement("option");
                            opt.value = goal.goalName;
                            descriptionDatalist.appendChild(opt);
                        });
                    } catch (error) {
                        console.error("Error fetching savings goals:", error);
                    }
                } else if (category === "Income") {
                    // Populate subcategory datalist for Income
                    const incomeOptions = ["Writing"];
                    incomeOptions.forEach(option => {
                        const opt = document.createElement("option");
                        opt.value = option;
                        subCategoryDatalist.appendChild(opt);
                    });

                    // Populate description datalist for Income
                    const incomeDescriptions = ["Freelance"];
                    incomeDescriptions.forEach(option => {
                        const opt = document.createElement("option");
                        opt.value = option;
                        descriptionDatalist.appendChild(opt);
                    });
                }
            });

            // Enable manual updates (allow users to type custom values)
            subCategoryField.addEventListener("input", function () {
                subCategoryField.setAttribute("value", subCategoryField.value);
            });

            descriptionField.addEventListener("input", function () {
                descriptionField.setAttribute("value", descriptionField.value);
            });
        });
    </script>

<?php
include "footer.php";
?>
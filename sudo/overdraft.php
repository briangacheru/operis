<?php include "head.php";?>
    <title>iTasker | Overdraft</title>
<?php include "navi.php";

$status = "OK";
$msg = "";

if (isset($_POST['save'])) {
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $od_date = mysqli_real_escape_string($con, $_POST['od_date']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $record_type = mysqli_real_escape_string($con, $_POST['record_type']); // New field for record type
    $tag = mysqli_real_escape_string($con, $_POST['tag']); // New field for tag
    $writerInfo = mysqli_real_escape_string($con, $_POST['writer']); // This contains "name | email"

    // Split the writer info into name and email
    list($writerName, $writerEmail) = explode(' | ', $writerInfo, 2);

    // Check if a writer is selected
    if (empty($writerName) || empty($writerEmail)) {
        $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert"> 
                                <i class="bi bi-exclamation-circle me-1"></i> You must select a writer.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
        header('Location: overdraft');
        exit;
    }

    // Check if tag is selected
    if (empty($tag)) {
        $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert"> 
                                <i class="bi bi-exclamation-circle me-1"></i> You must select a tag.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
        header('Location: overdraft');
        exit;
    }

    // Check for duplicate record - keep backward compatibility
    $checkQuery = "SELECT COUNT(*) AS record_count FROM tbloverdrafts WHERE amount = ? AND od_date = ? AND tag = ? AND (record_type = ? OR (record_type IS NULL AND ? = 'overdraft'))";
    $checkStmt = mysqli_prepare($con, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, 'sssss', $amount, $od_date, $tag, $record_type, $record_type);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_bind_result($checkStmt, $recordCount);
    mysqli_stmt_fetch($checkStmt);
    mysqli_stmt_close($checkStmt);

    if ($recordCount > 0) {
        $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert"> 
                                <i class="bi bi-exclamation-circle me-1"></i> A record with the same amount, date, tag, and type already exists.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
        header('Location: overdraft');
        exit;
    }

    // Function to calculate transaction cost (only for Mpesa tag, applies to both overdrafts and bonuses)
    function calculateTransactionCost($amount, $recordType, $tag) {
        if ($tag !== 'Mpesa') return 0; // No transaction cost for Airtel Money

        if ($amount >= 1 && $amount <= 49) return 0;
        if ($amount >= 50 && $amount <= 100) return 0;
        if ($amount >= 101 && $amount <= 500) return 7;
        if ($amount >= 501 && $amount <= 1000) return 13;
        if ($amount >= 1001 && $amount <= 1500) return 23;
        if ($amount >= 1501 && $amount <= 2500) return 33;
        if ($amount >= 2501 && $amount <= 3500) return 53;
        if ($amount >= 3501 && $amount <= 5000) return 57;
        if ($amount >= 5001 && $amount <= 7500) return 78;
        if ($amount >= 7501 && $amount <= 10000) return 90;
        if ($amount >= 10001 && $amount <= 15000) return 100;
        if ($amount >= 15001 && $amount <= 20000) return 105;
        if ($amount >= 20001 && $amount <= 35000) return 108;
        if ($amount >= 35001 && $amount <= 50000) return 108;
        if ($amount >= 50001 && $amount <= 250000) return 108;
        return 0;
    }

    $transactionCost = calculateTransactionCost($amount, $record_type, $tag);

    // Insert with all required fields including tag
    $stmt = mysqli_prepare($con, "INSERT INTO tbloverdrafts (amount, od_date, writer, email, transactionCost, description, record_type, tag) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssdsss', $amount, $od_date, $writerName, $writerEmail, $transactionCost, $description, $record_type, $tag);

    if (mysqli_stmt_execute($stmt)) {
        $recordTypeDisplay = ($record_type === 'bonus') ? 'Bonus' : 'Overdraft';
        $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert"> 
                                <i class="bi bi-check-circle me-1"></i> ' . $recordTypeDisplay . ' record added.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
        header('Location: overdraft');
        exit;
    } else {
        $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert"> 
                                <i class="bi bi-exclamation-triangle me-1"></i> Something went wrong. Please try again!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
        header('Location: overdraft');
        exit;
    }
    mysqli_stmt_close($stmt);
}

if (isset($_GET['delete'])) {
    $encodedId = $_GET['delete'];
    $cmpid = base64_decode($encodedId);

    // Validate $cmpid to ensure it's numeric and not empty
    if (is_numeric($cmpid) && !empty($cmpid)) {

        // First, retrieve the current status, is_paid value, and created_at of the overdraft
        $checkQuery = mysqli_query($con, "SELECT created_at FROM tbloverdrafts WHERE id='$cmpid'");
        $rowData = mysqli_fetch_assoc($checkQuery);

        if ($rowData) {
            // Calculate the difference in minutes between the current time and the created_at time
            $created_at = new DateTime($rowData['created_at']);
            $now = new DateTime();
            $interval = $now->diff($created_at);
            $minutesDifference = $interval->i + ($interval->h * 60) + ($interval->d * 1440);

            if ($minutesDifference > 30) {
                $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                      <i class="bi bi-exclamation-triangle"></i> Record cannot be cancelled as it was created more than 30 minutes ago.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                      </div>';
            } else {
                // Perform the delete operation
                $query = mysqli_query($con, "UPDATE tbloverdrafts SET is_deleted = 1 WHERE id='$cmpid'");

                if ($query) {
                    $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                          <i class="bi bi-check-circle"></i> Record cancelled successfully.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                          </div>';
                } else {
                    $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                          <i class="bi bi-exclamation-octagon"></i> Error cancelling record.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                          </div>';
                }
            }
        } else {
            $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                  <i class="bi bi-exclamation-octagon"></i> Invalid or missing ID.
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                  </div>';
        }
    }

    header('Location: overdraft');
    exit;
}

?>

    <div class="card shadow-none border mb-3">
        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/illustrations/corner-6.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-header z-1">
            <div class="row flex-between-center gx-0">
                <div class="col-lg-auto d-flex align-items-center">
                    <h4 class="mb-0 text-primary fw-bold">Overdraft & Bonus <span class="text-info fw-medium"> Records</span></h4>
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
    unset($_SESSION['alert']); // Clear the alert message
}
?>
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor" id="readonly-plain-text">Add/View Records<a class="anchorjs-link " aria-label="Anchor" data-anchorjs-icon="#" href="#readonly-plain-text" style="margin-left: 0.1875rem; padding-right: 0.1875rem; padding-left: 0.1875rem;"></a></h5>
                </div>
                <div class="col-auto ms-auto">
                    <div class="nav nav-pills nav-pills-falcon flex-grow-1 mt-2" role="tablist">
                        <button class="btn btn-sm active" data-bs-toggle="pill" data-bs-target="#add-tab" type="button" role="tab" aria-controls="add-tab" aria-selected="true" id="tab-add">Add</button>
                        <button class="btn btn-sm" data-bs-toggle="pill" data-bs-target="#view-tab" type="button" role="tab" aria-controls="view-tab" aria-selected="false" id="tab-view" tabindex="-1">View</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <div class="tab-content">
                <div class="tab-pane preview-tab-pane active show" role="tabpanel" aria-labelledby="tab-add" id="add-tab">
                    <form method="post" id="overdraftForm">
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label" for="record_type">Record Type</label>
                            <div class="col-sm-10">
                                <select name="record_type" id="record_type" class="form-select" onchange="handleRecordTypeChange();" required>
                                    <option value="" selected disabled>Select Record Type</option>
                                    <option value="overdraft">Overdraft</option>
                                    <option value="bonus">Bonus</option>
                                </select>
                                <div class="mb-3 row"></div>
                            </div>
                            <label class="col-sm-2 col-form-label" for="staticEmail">Select Writer</label>
                            <div class="col-sm-10">
                                <?php
                                $query = "SELECT username, email FROM tblwriters WHERE is_deleted = 0 AND is_verified = 1 ORDER BY username DESC";
                                $result = mysqli_query($con, $query);

                                if(mysqli_num_rows($result) > 0) {
                                    echo "<select name='writer' id='writer' class='form-select' onchange='handleWriterChange();'>";
                                    echo "<option value='' selected disabled>Select Writer</option>";
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $displayText = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . " | " . htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                                        echo "<option value='" . $displayText . "'>" . $displayText . "</option>";
                                    }
                                    echo "</select>";
                                } else {
                                    echo "<p>No writers found.</p>";
                                }
                                ?>
                                <div class="mb-3 row"></div>
                            </div>
                            <label class="col-sm-2 col-form-label" for="tag">tag</label>
                            <div class="col-sm-10">
                                <select name="tag" id="tag" class="form-select" onchange="checkFormCompletion();" required>
                                    <option value="" selected disabled>Select tag</option>
                                    <option value="Mpesa">Mpesa</option>
                                    <option value="Airtel Money">Airtel Money</option>
                                </select>
                                <small class="form-text text-muted">Transaction costs apply to both overdrafts and bonuses when using Mpesa</small>
                                <div class="mb-3 row"></div>
                            </div>
                            <label class="col-sm-2 col-form-label" for="inputPassword">Amount </label>
                            <div class="col-sm-10">
                                <input type="number" name="amount" value="" min="0" placeholder="Enter amount" class="form-control" id="amount" onchange="checkFormCompletion();" required>
                                <small class="form-text text-muted" id="amount-help">Enter the overdraft or bonus amount</small>
                                <div class="mb-3 row"></div>
                            </div>
                            <label class="col-sm-2 col-form-label" for="inputPassword">Date </label>
                            <div class="col-sm-10">
                                <input class="form-control" name="od_date" type="datetime-local" required="required" id="od_date" onchange="checkFormCompletion();" />
                                <div class="mb-3 row"></div>
                            </div>
                            <label class="col-sm-2 col-form-label" for="inputPassword">Description </label>
                            <div class="col-sm-10">
                                <select class="form-select" name="description" id="description" aria-label="Default select" required>
                                    <option selected=""></option>
                                    <option value="iTasker">iTasker</option>
                                    <option value="Writers admin">Writers admin</option>
                                    <option value="Performance Bonus">Performance Bonus</option>
                                    <option value="Completion Bonus">Completion Bonus</option>
                                </select>
                                <div class="mb-3 row"></div>
                            </div>
                        </div>
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="row justify-content-between align-items-center">
                                    <div class="col-md">
                                        <h5 class="mb-2 mb-md-0" style="display: none;" id="completion-message">You're almost done!</h5>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-link text-secondary p-0 me-3 fw-medium" type="button" id="discardButton" role="button" onclick="clearForm()" style="display: none;">Discard</button>
                                        <button class="btn btn-primary" name="save" type="submit" role="button" style="display: none;" id="submit-button">Add Record</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane code-tab-pane" role="tabpanel" aria-labelledby="tab-view" id="view-tab">
                    <form method="post" id="overdraftFormView">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label" for="staticEmail">Select Writer</label>
                            <div class="col-sm-9">
                                <select name='writer' id='writer_view' class='form-select' onchange='updateFormFields(this.value);'>
                                    <option value='' selected disabled>Select Writer</option>
                                    <?php
                                    $query = "SELECT username, email FROM tblwriters WHERE is_deleted = 0 AND is_verified = 1 ORDER BY created_at ASC";
                                    $result = mysqli_query($con, $query);
                                    if(mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $displayText = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . " | " . htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                                            echo "<option value='" . $row['username'] . "'>" . $displayText . "</option>";
                                        }
                                    } else {
                                        echo "<p>No writers found.</p>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label" for="tasks_total">Unpaid Total</label>
                            <div class="col-sm-9">
                                <input type="number" name="tasks_total" class="form-control" id="tasks_total" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label" for="bonus_total">Total Bonuses</label>
                            <div class="col-sm-9">
                                <input type="number" name="bonus_total" class="form-control" id="bonus_total" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label" for="overdraft_total">Total Overdraft</label>
                            <div class="col-sm-9">
                                <input type="number" name="overdraft_total" class="form-control" id="overdraft_total" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label" for="amount_due">Amount Due</label>
                            <div class="col-sm-9">
                                <input type="number" name="amount_due" class="form-control" id="amount_due" readonly>
                                <small class="form-text text-success">Calculation: Unpaid Total + Total Bonuses - Total Overdraft</small>                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
    <div class="col">
        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="tab-content">
                    <div class="tab-pane preview-tab-pane active" role="tabpanel">
                        <div class="card shadow-none">
                            <form id="tasksForm" method="post">
                                <div class="card-header">
                                    <div class="row flex-between-center">
                                        <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="filter-all" onclick="filterRecords('all')">All</button>
                                                <button type="button" class="btn btn-outline-warning btn-sm" id="filter-overdraft" onclick="filterRecords('overdraft')">Overdrafts</button>
                                                <button type="button" class="btn btn-outline-info btn-sm" id="filter-bonus" onclick="filterRecords('bonus')">Bonuses</button>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                                            <div class="d-none" id="table-simple-pagination-actions">
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-falcon-info btn-sm ms-2" onclick="submitForm('settle-overdrafts')">Mark as Settled</button>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center" id="table-simple-pagination-replace-element">
                                                <button class="btn btn-falcon-primary btn-sm" onclick="exportOverdraft()" data-bs-toggle="tooltip" data-bs-placement="top" title="Export as CSV" type="button"><span class="fas fa-external-link-alt" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Export as CSV</span></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-0 pt-0">
                                    <table class="table table-sm mb-0 overflow-hidden data-table fs-10" data-datatables="data-datatables">
                                        <thead class="bg-200">
                                        <tr>
                                            <th class="text-900 no-sort white-space-nowrap">
                                                <div class="form-check mb-0 d-flex align-items-center">
                                                    <input class="form-check-input" id="checkbox-select-all" type="checkbox" onclick="selectAllTasks(this)" data-bulk-select='{"body":"table-simple-pagination-body","actions":"table-simple-pagination-actions","replacedElement":"table-simple-pagination-replace-element"}' />
                                                </div>
                                            </th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">#</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Type</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Writer</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">tag</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Amount</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Cost</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Description</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Transaction Date</th>
                                            <th class="text-900 no-sort pe-1 align-middle data-table-row-action"></th>
                                        </tr>
                                        </thead>
                                        <tbody class="list" id="table-simple-pagination-body">
                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM tbloverdrafts WHERE is_settled = 0 AND is_deleted = 0 ORDER BY od_date DESC");
                                        $cnt = 1;
                                        while ($row = mysqli_fetch_array($query)) {
                                            $encodedId = base64_encode($row["id"]); // Encode the id
                                            $recordType = isset($row["record_type"]) && !empty($row["record_type"]) ? $row["record_type"] : 'overdraft'; // Default to overdraft for backward compatibility
                                            $tag = isset($row["tag"]) && !empty($row["tag"]) ? $row["tag"] : 'Mpesa'; // Default to Mpesa for backward compatibility
                                            $badgeClass = ($recordType === 'bonus') ? 'badge-subtle-info' : 'badge-subtle-warning';
                                            $typeDisplay = ($recordType === 'bonus') ? 'Bonus' : 'Overdraft';
                                            $tagBadgeClass = ($tag === 'Mpesa') ? 'badge-subtle-success' : 'badge-subtle-danger';
                                            ?>
                                            <tr class="hover-actions-trigger btn-reveal-trigger hover-bg-100 record-row" data-record-type="<?php echo $recordType; ?>">
                                                <td class="align-middle" style="width: 28px;">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" id="simple-pagination-item-<?php echo $cnt; ?>" data-bulk-select-row="data-bulk-select-row" value="<?php echo $row['id']; ?>" name="taskIds[]" />
                                                    </div>
                                                </td>
                                                <td class="align-middle white-space-nowrap text-900"><?php echo $row["id"]; ?></td>
                                                <td class="align-middle white-space-nowrap">
                                                    <span class="badge <?php echo $badgeClass; ?> rounded-pill"><?php echo $typeDisplay; ?></span>
                                                </td>
                                                <td class="align-middle white-space-nowrap fw-semi-bold text-900"><?php echo $row["writer"]; ?></td>
                                                <td class="align-middle white-space-nowrap">
                                                    <span class="badge <?php echo $tagBadgeClass; ?> rounded-pill"><?php echo $tag; ?></span>
                                                </td>
                                                <td class="align-middle white-space-nowrap text-900">Ksh. <?php echo $row["amount"]; ?></td>
                                                <td class="align-middle white-space-nowrap text-900">
                                                    <?php if ($tag === 'Airtel Money'): ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php else: ?>
                                                        Ksh. <?php echo $row["transactionCost"]; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle text-center white-space-nowrap text-900"><?php echo $row["description"]; ?></td>
                                                <td class="align-middle text-center white-space-nowrap text-900"><?php echo date("jS M, Y h:i A", strtotime($row['od_date'])); ?></td>
                                                <td class="align-middle white-space-nowrap text-end position-relative">
                                                    <div class="hover-actions bg-100">
                                                        <a class="btn bg-success-subtle icon-item rounded-3 me-2 fs-11 icon-item-sm" data-bs-toggle="modal" href="#overdraft-view-modal" data-bs-placement="top" title="Edit record"
                                                           data-id="<?php echo $row['id']; ?>"
                                                           data-writer="<?php echo htmlspecialchars($row['writer']); ?>"
                                                           data-amount="<?php echo $row['amount']; ?>"
                                                           data-date="<?php echo $row['od_date']; ?>"
                                                           data-record-type="<?php echo $recordType; ?>"
                                                           data-tag="<?php echo htmlspecialchars($tag); ?>"
                                                           data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                                           onclick="openEditModal(this)">
                                                            <span class="far fa-edit"></span>
                                                        </a>
                                                        <a class="btn bg-danger-subtle icon-item rounded-3 me-2 fs-11 icon-item-sm" href="overdraft?delete=<?php echo $encodedId; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel Record" onclick="return confirm('Do you really want to cancel this record?');"><span class="fas fa-trash"></span></a>
                                                    </div>
                                                    <div class="dropdown font-sans-serif btn-reveal-trigger">
                                                        <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none" type="button" id="crm-recent-leads-4" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span class="fas fa-chevron-left fs-11"></span></button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            $cnt = $cnt + 1;
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

    <div class="modal fade" id="overdraft-view-modal" tabindex="-1" role="dialog" aria-labelledby="authentication-modal-label" aria-hidden="true">
        <div class="modal-dialog mt-6" role="document">
            <div class="modal-content border-0">
                <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                    <div class="position-relative z-1">
                        <h4 class="mb-0 text-white" id="authentication-modal-label">Edit Record</h4>
                    </div>
                    <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4 px-5">
                    <div id="modal-alert" class="alert d-none"></div>
                    <form id="overdraft-form">
                        <input type="hidden" id="overdraft-id" name="id">
                        <div class="mb-3">
                            <label class="form-label" for="modal-record-type">Record Type</label>
                            <select class="form-select" id="modal-record-type" name="record_type">
                                <option value="overdraft">Overdraft</option>
                                <option value="bonus">Bonus</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-name">Writer</label>
                            <input class="form-control" type="text" autocomplete="on" name="writer" id="modal-auth-name" readonly />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal-tag">tag</label>
                            <select class="form-select" id="modal-tag" name="tag">
                                <option value="Mpesa">Mpesa</option>
                                <option value="Airtel Money">Airtel Money</option>
                            </select>
                            <small class="form-text text-muted">Transaction costs apply to both overdrafts and bonuses when using Mpesa</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-amount">Amount</label>
                            <input class="form-control" type="number" autocomplete="on" name="amount" id="modal-auth-amount" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-date">Date</label>
                            <input class="form-control" type="datetime-local" name="od_date" id="modal-auth-date" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-description">Description</label>
                            <select class="form-select" name="description" id="modal-auth-description" aria-label="Default select">
                                <option value="iTasker">iTasker</option>
                                <option value="Writers admin">Writers admin</option>
                                <option value="Performance Bonus">Performance Bonus</option>
                                <option value="Holiday Bonus">Holiday Bonus</option>
                                <option value="Completion Bonus">Completion Bonus</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary d-block w-100 mt-3" type="submit">Update Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Set default filter to show all records
            filterRecords('all');

            // Handle auto-hiding alerts
            setTimeout(function() {
                let alertElement = document.querySelector('.alert:not(#modal-alert)');
                if (alertElement) {
                    alertElement.classList.remove('show');
                    setTimeout(function() {
                        alertElement.remove();
                    }, 150);
                }
            }, 10000);

            // Form completion check for the main form (not the modal)
            const mainForm = document.getElementById('overdraftForm');
            if (mainForm) {
                checkFormCompletion();

                const recordTypeSelect = document.getElementById('record_type');
                const writerSelect = document.getElementById('writer');
                const tagSelect = document.getElementById('tag');
                const amountInput = document.getElementById('amount');
                const dateInput = document.getElementById('od_date');
                const descriptionSelect = document.getElementById('description');

                if (recordTypeSelect) recordTypeSelect.addEventListener('change', handleRecordTypeChange);
                if (writerSelect) writerSelect.addEventListener('change', handleWriterChange);
                if (tagSelect) tagSelect.addEventListener('change', checkFormCompletion);
                if (amountInput) amountInput.addEventListener('input', checkFormCompletion);
                if (dateInput) dateInput.addEventListener('input', checkFormCompletion);
                if (descriptionSelect) descriptionSelect.addEventListener('change', checkFormCompletion);
            }

            // Handle form submission for the modal
            const overdraftForm = document.getElementById('overdraft-form');
            if (overdraftForm) {
                overdraftForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const modalAlert = document.getElementById('modal-alert');

                    fetch('update-od', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                modalAlert.className = 'alert alert-success';
                                modalAlert.textContent = data.message || 'Record updated successfully!';

                                // Reload the page after a delay
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                modalAlert.className = 'alert alert-danger';
                                modalAlert.textContent = data.message || 'Error updating record';
                            }
                            modalAlert.classList.remove('d-none');
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            modalAlert.className = 'alert alert-danger';
                            modalAlert.textContent = 'An error occurred while updating the record';
                            modalAlert.classList.remove('d-none');
                        });
                });
            }
        });

        // Function to open edit modal and populate it with data
        function openEditModal(button) {
            // Extract info from data-* attributes
            const id = button.getAttribute('data-id');
            const writer = button.getAttribute('data-writer');
            const amount = button.getAttribute('data-amount');
            const date = button.getAttribute('data-date');
            const recordType = button.getAttribute('data-record-type') || 'overdraft';
            const tag = button.getAttribute('data-tag') || 'Mpesa';
            const description = button.getAttribute('data-description');

            // Update the modal's content
            document.getElementById('overdraft-id').value = id;
            document.getElementById('modal-auth-name').value = writer;
            document.getElementById('modal-auth-amount').value = amount;
            document.getElementById('modal-auth-date').value = date;
            document.getElementById('modal-record-type').value = recordType;
            document.getElementById('modal-tag').value = tag;
            document.getElementById('modal-auth-description').value = description;

            // Clear any previous alert message
            const modalAlert = document.getElementById('modal-alert');
            modalAlert.className = 'alert d-none';
            modalAlert.textContent = '';
        }

        function checkFormCompletion() {
            const recordType = document.getElementById('record_type');
            const writer = document.getElementById('writer');
            const tag = document.getElementById('tag');
            const amount = document.getElementById('amount');
            const od_date = document.getElementById('od_date');
            const description = document.getElementById('description');
            const discardButton = document.getElementById('discardButton');
            const submitButton = document.getElementById('submit-button');
            const completionMessage = document.getElementById('completion-message');

            if (!recordType || !writer || !tag || !amount || !od_date || !description || !discardButton || !submitButton) {
                return; // Exit if any elements don't exist
            }

            if (recordType.value && writer.value && tag.value && amount.value && od_date.value && description.value) {
                discardButton.style.display = 'inline-block';
                submitButton.style.display = 'inline-block';
                completionMessage.style.display = 'block';
            } else {
                discardButton.style.display = 'none';
                submitButton.style.display = 'none';
                completionMessage.style.display = 'none';
            }
        }

        function clearForm() {
            const form = document.getElementById('overdraftForm');
            if (form) {
                form.reset();
                checkFormCompletion();
                updatePlaceholders();
            }
        }

        function handleRecordTypeChange() {
            const recordType = document.getElementById('record_type');
            const descriptionSelect = document.getElementById('description');

            if (recordType && descriptionSelect) {
                if (recordType.value === 'bonus') {
                    descriptionSelect.value = 'Performance Bonus';
                } else if (recordType.value === 'overdraft') {
                    // Keep the existing logic for overdrafts - check writer email
                    updateDescription();
                }
            }

            updatePlaceholders();
            checkFormCompletion();
        }

        function handleWriterChange() {
            updateDescription();
            checkFormCompletion();
        }

        function updatePlaceholders() {
            const recordType = document.getElementById('record_type');
            const amountInput = document.getElementById('amount');
            const amountHelp = document.getElementById('amount-help');
            const submitButton = document.getElementById('submit-button');

            if (!recordType || !amountInput || !amountHelp || !submitButton) return;

            if (recordType.value === 'bonus') {
                amountInput.placeholder = 'Enter bonus amount';
                amountHelp.textContent = 'Enter the bonus amount (transaction costs apply for Mpesa)';
                submitButton.textContent = 'Add Bonus';
            } else if (recordType.value === 'overdraft') {
                amountInput.placeholder = 'Enter overdraft amount';
                amountHelp.textContent = 'Enter the overdraft amount';
                submitButton.textContent = 'Add Overdraft';
            } else {
                amountInput.placeholder = 'Enter amount';
                amountHelp.textContent = 'Enter the overdraft or bonus amount';
                submitButton.textContent = 'Add Record';
            }
        }

        function updateDescription() {
            const recordType = document.getElementById('record_type');

            // Only auto-update description for overdrafts, not bonuses
            if (recordType && recordType.value === 'bonus') {
                return; // Don't change description for bonuses
            }

            const writerDropdown = document.getElementById("writer");
            const descriptionDropdown = document.getElementById("description");

            if (!writerDropdown || !descriptionDropdown) {
                return; // Exit if elements don't exist
            }

            const selectedValue = writerDropdown.value;
            const parts = selectedValue.split(" | ");
            const email = parts.length > 1 ? parts[1].trim() : "";

            if (email === "wa@mail.com") {
                descriptionDropdown.value = "Writers admin";
            } else {
                descriptionDropdown.value = "iTasker";
            }
        }

        function filterRecords(type) {
            const rows = document.querySelectorAll('.record-row');
            const filterButtons = document.querySelectorAll('[id^="filter-"]');

            // Update button states
            filterButtons.forEach(btn => {
                btn.classList.remove('btn-warning', 'btn-info', 'btn-outline-warning', 'btn-outline-info', 'btn-outline-secondary');
                if (btn.id === `filter-${type}`) {
                    if (type === 'all') btn.classList.add('btn-outline-secondary');
                    else if (type === 'overdraft') btn.classList.add('btn-warning');
                    else if (type === 'bonus') btn.classList.add('btn-info');
                } else {
                    btn.classList.add('btn-outline-secondary');
                }
            });

            // Show/hide rows based on filter
            rows.forEach(row => {
                const recordType = row.getAttribute('data-record-type');
                if (type === 'all' || recordType === type) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function updateFormFields(writerName) {
            if (!writerName) {
                clearViewFormFields();
                return;
            }

            $.ajax({
                type: 'GET',
                url: 'get_amount_due',
                data: { writer_name: writerName },
                dataType: 'json',
                success: function(response) {
                    console.log('Response received:', response);

                    const tasksTotal = parseFloat(response.totalCompletedTasks) || 0;
                    const overdraftTotal = parseFloat(response.totalOverdrafts) || 0;
                    const bonusTotal = parseFloat(response.totalBonuses) || 0;
                    const amountDue = tasksTotal + bonusTotal - overdraftTotal;

                    // Use setTimeout to ensure DOM is ready
                    setTimeout(function() {
                        setFieldValue('tasks_total', tasksTotal);
                        setFieldValue('overdraft_total', overdraftTotal);
                        setFieldValue('bonus_total', bonusTotal);
                        setFieldValue('amount_due', amountDue);
                    }, 100);
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', error);
                }
            });
        }

        // Helper function to set field values across browsers
        function setFieldValue(fieldId, value) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.removeAttribute('readonly');
                field.value = value;
                field.setAttribute('readonly', 'readonly');
                field.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        // Helper function to clear all view form fields
        function clearViewFormFields() {
            ['tasks_total', 'overdraft_total', 'bonus_total', 'amount_due'].forEach(fieldId => {
                setFieldValue(fieldId, '');
            });
        }
    </script>
<?php
include "footer.php";
?>
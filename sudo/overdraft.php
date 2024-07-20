<?php include "head.php";?>
    <title>iTasker | Overdraft</title>
<?php include "navi.php";

$status = "OK";
$msg = "";

if(isset($_POST['save']))
{
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $od_date = mysqli_real_escape_string($con, $_POST['od_date']);
    $writerInfo = mysqli_real_escape_string($con, $_POST['writer']); // This contains "name | email"
    // Split the writer info into name and email
    list($writerName, $writerEmail) = explode(' | ', $writerInfo, 2); // Limit to 2 parts to ensure only the first " | " is used

    // Check if a writer is selected (assuming the "Select Writer" option submits an empty value)
    if (empty($writerName) || empty($writerEmail)) {
        $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert"> <i class="bi bi-exclamation-circle me-1"></i> 
                                You must select a writer.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        header('Location: overdraft'); // Adjust the redirection to your form page
        exit;
    }

    if ($status == "OK") {
        // It's safer to use prepared statements to prevent SQL Injection
        $stmt = mysqli_prepare($con, "INSERT INTO tbloverdrafts (amount, od_date, writer, email) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $amount, $od_date, $writerName, $writerEmail);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert"> <i class="bi bi-check-circle me-1"></i> 
                                    Overdraft record added successfully.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
            header('Location: overdraft');
            exit;
        } else {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle me-1"></i>Something went wrong. Please try again!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        }
        mysqli_stmt_close($stmt); // Don't forget to close the statement
    }
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
                                      <i class="bi bi-exclamation-triangle"></i> Overdraft cannot be cancelled as it was created more than 30 minutes ago.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                      </div>';
            } else {
                // Perform the delete operation if the overdraft is not completed, submitted, or paid and was posted within 2 minutes
                $query = mysqli_query($con, "UPDATE tbloverdrafts SET is_deleted = 1 WHERE id='$cmpid'");

                if ($query) {
                    $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                          <i class="bi bi-check-circle"></i> Overdraft cancelled successfully.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                          </div>';
                } else {
                    $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                          <i class="bi bi-exclamation-octagon"></i> Error cancelling overdraft record.
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
                    <h4 class="mb-0 text-primary fw-bold">Overdraft <span class="text-info fw-medium"> Records</span></h4>
                </div>
                <div class="col-lg-auto pt-3 pt-lg-0">
                    <form class="row flex-lg-column flex-xxl-row gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
                        <div class="col-auto">
                        </div>
                        <div class="col-md-auto position-relative">
                            <h6 class="mb-1 text-info"><?php echo date("jS F Y / H:i"); ?></h6>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--<div class="alert alert-success border-0 d-flex align-items-center" role="alert">
        <div class="bg-success me-3 icon-item"><span class="fas fa-check-circle text-white fs-6"></span></div>
        <p class="mb-0 flex-1">A simple success alert—check it out!</p>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>-->

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
                    <h5 class="mb-0" data-anchor="data-anchor" id="readonly-plain-text">Add/View Overdraft<a class="anchorjs-link " aria-label="Anchor" data-anchorjs-icon="#" href="#readonly-plain-text" style="margin-left: 0.1875em; padding-right: 0.1875em; padding-left: 0.1875em;"></a></h5>
<!--                    <p class="mb-0 pt-1 mt-2 mb-0">If you want to have <code>input readonly</code> elements in your form styled as plain text, use the <code>.form-control-plaintext</code> class to remove the default form field styling and preserve the correct margin and padding.</p>-->
                </div>
                <div class="col-auto ms-auto">
                    <div class="nav nav-pills nav-pills-falcon flex-grow-1 mt-2" role="tablist">
                        <button class="btn btn-sm active" data-bs-toggle="pill" data-bs-target="#dom-d44a7604-3161-4788-b15b-1099097428d5" type="button" role="tab" aria-controls="dom-d44a7604-3161-4788-b15b-1099097428d5" aria-selected="true" id="tab-dom-d44a7604-3161-4788-b15b-1099097428d5">Add</button>
                        <button class="btn btn-sm" data-bs-toggle="pill" data-bs-target="#dom-912bd897-154c-475b-9bb9-735d5b6603ec" type="button" role="tab" aria-controls="dom-912bd897-154c-475b-9bb9-735d5b6603ec" aria-selected="false" id="tab-dom-912bd897-154c-475b-9bb9-735d5b6603ec" tabindex="-1">View</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <div class="tab-content">
                <div class="tab-pane preview-tab-pane active show" role="tabpanel" aria-labelledby="tab-dom-d44a7604-3161-4788-b15b-1099097428d5" id="dom-d44a7604-3161-4788-b15b-1099097428d5">
                    <form method="post" id="overdraftForm">
                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label" for="staticEmail">Select Writer</label>
                        <div class="col-sm-10">
                            <?php
                            $query = "SELECT username, email FROM tblwriters WHERE is_deleted = 0 ORDER BY username DESC"; // Fetching the ID as well might be useful for future needs

                            $result = mysqli_query($con, $query);

                            if(mysqli_num_rows($result) > 0) {
                                echo "<select name='writer' id='writer' class='form-select' onchange='updateAmountDue();'>";
                                echo "<option value='' selected disabled>Select Writer</option>"; // Added disabled and selected attributes
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $displayText = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . " | " . htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                                    echo "<option value='" . $displayText . "'>" . $displayText . "</option>";
                                }
                                echo "</select>";
                            } else {
                                echo "<p>No writers found.</p>"; // Changed to a paragraph for better HTML semantics
                            }
                            ?>
                            <div class="mb-3 row"></div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="inputPassword">Amount </label>
                        <div class="col-sm-10">
                            <input type="number" name="amount" value="" min="0" placeholder="Enter Overdraft amount" class="form-control" id="amount" required>
                            <div class="mb-3 row"></div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="inputPassword">Date </label>
                        <div class="col-sm-10">
                            <input class="form-control datetimepicker" name="od_date" type="text" required="required" placeholder="YYYY-mm.dd H:i" data-options='{"enableTime":true,"dateFormat":"Y-m-d H:i","disableMobile":true,"allowInput":true}' />
                            <div class="mb-3 row"></div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-md">
                                    <h5 class="mb-2 mb-md-0">You're almost done!</h5>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-link text-secondary p-0 me-3 fw-medium" type="button" id="discardButton" role="button" onclick="clearForm()">Discard</button>
                                    <button class="btn btn-primary" name="save" type="submit" role="button">Add Overdraft</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="tab-pane code-tab-pane" role="tabpanel" aria-labelledby="tab-dom-912bd897-154c-475b-9bb9-735d5b6603ec" id="dom-912bd897-154c-475b-9bb9-735d5b6603ec">
                    <form method="post" id="overdraftFormView">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label" for="staticEmail">Select Writer</label>
                            <div class="col-sm-9">
                                <select name='writer' id='writer' class='form-select' onchange='updateFormFields(this.value);'>
                                    <option value='' selected disabled>Select Writer</option>
                                    <?php
                                    $query = "SELECT username, email FROM tblwriters WHERE is_deleted = 0 ORDER BY username DESC";
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
                            <label class="col-sm-3 col-form-label" for="tasks_total">Unpaid total</label>
                            <div class="col-sm-9">
                                <input type="number" name="tasks_total" class="form-control" id="tasks_total" readonly>
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
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                                        </div>
                                        <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                                            <div class="d-none" id="table-simple-pagination-actions">
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-falcon-info btn-sm ms-2" onclick="submitForm('settle-overdrafts')">Mark as Settled</button>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center" id="table-simple-pagination-replace-element">
                                                <a class="btn btn-falcon-info btn-sm mx-2" href="overdraft" title="Create Task" type="button"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Overdraft</span></a>
<!--                                                <button class="btn btn-falcon-default btn-sm mx-2" type="button"><span class="fas fa-filter" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Filter</span></button>-->
                                                <button class="btn btn-falcon-primary btn-sm" onclick="exportOverdraft()" title="Export as CSV" type="button"><span class="fas fa-external-link-alt" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Export as CSV</span></button>
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
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap">OD Id</th>
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap">Writer</th>
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap">Amount</th>
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Date</th>
                                                <th class="text-900 no-sort pe-1 align-middle data-table-row-action"></th>
                                            </tr>
                                            </thead>
                                            <tbody class="list" id="table-simple-pagination-body">
                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM tbloverdrafts WHERE is_settled = 0 AND is_deleted = 0 ORDER BY created_at DESC");
                                            $cnt = 1;
                                            while ($row = mysqli_fetch_array($query)) {
                                                $encodedId = base64_encode($row["id"]); // Encode the id
                                                ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger hover-bg-100">
                                                    <td class="align-middle" style="width: 28px;">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="simple-pagination-item-<?php echo $cnt; ?>" data-bulk-select-row="data-bulk-select-row" value="<?php echo $row['id']; ?>" name="taskIds[]" />
                                                        </div>
                                                    </td>
                                                    <td class="align-middle white-space-nowrap fw-semi-bold name"><?php echo $row["id"]; ?></td>
                                                    <td class="align-middle white-space-nowrap email"><?php echo $row["writer"]; ?></td>
                                                    <td class="align-middle white-space-nowrap email">Ksh. <?php echo $row["amount"]; ?></td>
                                                    <td class="align-middle text-center white-space-nowrap payment"><?php echo date("jS M, Y h:i A", strtotime($row['od_date'])); ?></td>
                                                    <td class="align-middle white-space-nowrap text-end position-relative">
                                                        <div class="hover-actions bg-100">
                                                            <a class="btn bg-success-subtle icon-item rounded-3 me-2 fs-11 icon-item-sm" data-bs-toggle="modal" href="#overdraft-view-modal" title="Edit task" data-id="<?php echo $row['id']; ?>" data-writer="<?php echo $row['writer']; ?>" data-amount="<?php echo $row['amount']; ?>" data-date="<?php echo $row['od_date']; ?>"><span class="far fa-edit"></span></a>
                                                            <a class="btn bg-danger-subtle icon-item rounded-3 me-2 fs-11 icon-item-sm" href="overdraft?delete=<?php echo $encodedId; ?>" title="Cancel Overdraft" onclick="return confirm('Do you really want to cancel overdraft?');"><span class="fas fa-trash"></span></a>
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
                        <h4 class="mb-0 text-white" id="authentication-modal-label">Edit Overdraft</h4>
                    </div>
                    <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4 px-5">
                    <div id="modal-alert" class="alert d-none"></div>
                    <form id="overdraft-form">
                        <input type="hidden" id="overdraft-id" name="id">
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-name">Writer</label>
                            <input class="form-control" type="text" autocomplete="on" name="writer" id="modal-auth-name" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-amount">Amount</label>
                            <input class="form-control" type="number" autocomplete="on" name="amount" id="modal-auth-amount" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-date">Date</label>
                            <input class="form-control datetimepicker" type="text" autocomplete="on" name="od_date" id="modal-auth-date" placeholder="YYYY-mm.dd H:i" data-options='{"enableTime":true,"dateFormat":"Y-m-d H:i","disableMobile":true,"allowInput":true}' />
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary d-block w-100 mt-3" type="submit">Update Overdraft</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        function clearForm() {
            document.getElementById('overdraftForm').reset();
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                let alertElement = document.querySelector('.alert');
                if (alertElement) {
                    alertElement.classList.remove('show');
                    setTimeout(function() {
                        alertElement.remove();
                    }, 150); // Give time for the alert to fade out before removing
                }
            }, 10000); // 10 seconds
        });
    </script>
<?php
include "footer.php";
?>
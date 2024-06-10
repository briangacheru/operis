<?php
include "header.php";

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
        header('Location: overdraft.php'); // Adjust the redirection to your form page
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
            header('Location: overdraft.php');
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

    header('Location: overdraft.php');
    exit;
}

?>

    <div class="card shadow-none border mb-3">
        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/illustrations/corner-6.png);">
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
                    <h5 class="mb-0" data-anchor="data-anchor" id="readonly-plain-text">Overdraft Computation<a class="anchorjs-link " aria-label="Anchor" data-anchorjs-icon="#" href="#readonly-plain-text" style="margin-left: 0.1875em; padding-right: 0.1875em; padding-left: 0.1875em;"></a></h5>
<!--                    <p class="mb-0 pt-1 mt-2 mb-0">If you want to have <code>input readonly</code> elements in your form styled as plain text, use the <code>.form-control-plaintext</code> class to remove the default form field styling and preserve the correct margin and padding.</p>-->
                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <div class="tab-content">
                <div class="tab-pane preview-tab-pane active show" role="tabpanel" aria-labelledby="tab-dom-d44a7604-3161-4788-b15b-1099097428d5" id="dom-d44a7604-3161-4788-b15b-1099097428d5">
                    <form method="post" id="overdraftFormView">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label" for="staticEmail">Select Writer</label>
                            <div class="col-sm-9">
                                <select name='writer' id='writer' class='form-select' onchange='updateFormFields(this.value);'>
                                    <option value='' selected disabled>Select Writer</option>
                                    <?php
                                    $query = "SELECT id, username, email FROM tblwriters WHERE is_deleted = 0 AND email = '$aid' ORDER BY username DESC";
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
                            <label class="col-sm-3 col-form-label" for="tasks_total">Unpaid Tasks total</label>
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
                                            <div class="d-flex align-items-center" id="table-simple-pagination-replace-element">
<!--                                                <button class="btn btn-falcon-default btn-sm mx-2" type="button"><span class="fas fa-filter" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Filter</span></button>-->
                                                <button class="btn btn-falcon-primary btn-sm" onclick="exportTableToCSVWithConfirmation('overdrafts.csv')"  title="Export as CSV" type="button"><span class="fas fa-external-link-alt" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Export as CSV</span></button>
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
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap">Amount (Ksh)</th>
                                                <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Date</th>
                                                <th class="text-900 no-sort pe-1 align-middle data-table-row-action"></th>
                                            </tr>
                                            </thead>
                                            <tbody class="list" id="table-simple-pagination-body">
                                            <?php
                                            $query = mysqli_query($con, "SELECT * FROM tbloverdrafts WHERE is_settled = 0 AND is_deleted = 0 AND email = '$aid' ORDER BY created_at DESC");
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
                                                    <td class="align-middle white-space-nowrap email"><?php echo $row["amount"]; ?></td>
                                                    <td class="align-middle text-center white-space-nowrap payment"><?php echo date("jS M, Y h:i A", strtotime($row['od_date'])); ?></td>
                                                    <td class="align-middle white-space-nowrap text-end position-relative">
                                                        <div class="hover-actions bg-100">
                                                            <a class="btn bg-success-subtle icon-item rounded-3 me-2 fs-11 icon-item-sm" data-bs-toggle="modal" href="#overdraft-view-modal" title="View Overdraft" data-id="<?php echo $row['id']; ?>" data-writer="<?php echo $row['writer']; ?>" data-amount="<?php echo $row['amount']; ?>" data-date="<?php echo $row['od_date']; ?>"><span class="far fa-eye"></span></a>
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

    <script>
        function clearForm() {
            document.getElementById('overdraftForm').reset();
        }

        function exportTableToCSVWithConfirmation(filename) {
            if (confirm("Are you sure you want to export the table as a CSV file?")) {
                exportTableToCSV(filename);
            }
        }

        function exportTableToCSV(filename) {
            var csv = [];
            var rows = document.querySelectorAll("table tr");

            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll("td, th");

                for (var j = 0; j < cols.length; j++) {
                    row.push(cols[j].innerText);
                }

                csv.push(row.join(","));
            }

            // Download CSV
            downloadCSV(csv.join("\n"), filename);
        }

        function downloadCSV(csv, filename) {
            var csvFile;
            var downloadLink;

            csvFile = new Blob([csv], {type: "text/csv"});

            downloadLink = document.createElement("a");

            downloadLink.download = filename;

            downloadLink.href = window.URL.createObjectURL(csvFile);

            downloadLink.style.display = "none";

            document.body.appendChild(downloadLink);

            downloadLink.click();
        }
    </script>
<?php
include "footer.php";
?>
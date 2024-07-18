<?php include "head.php";?>
    <title>iTasker | Writer Management</title>
<?php include "navi.php";

$status = "OK";
$msg = "";
if (isset($_GET['delid'])) {
    $cmpid = $_GET['delid'];
    if (is_numeric($cmpid) && !empty($cmpid)) {
        // Perform the delete operation
        $query = mysqli_query($con, "UPDATE tblwriters SET is_deleted = 1 WHERE id='$cmpid'");
        if ($query) {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-check-circle"></i> User record deleted.
                                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        } else {
            $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-octagon"></i> Error deleting user record.
                                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
            echo "Error: " . mysqli_error($con);
        }
    } else {
        $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-octagon"></i> Invalid or missing ID.
                             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>';
    }

    header('Location: usermanagement');
    exit;
}

// Place this code near your deletion logic
if (isset($_GET['verifyid'])) {
    $userid = $_GET['verifyid'];
    if (is_numeric($userid) && !empty($userid)) {
        // Check current verification status
        $checkQuery = mysqli_query($con, "SELECT is_verified FROM tblwriters WHERE id='$userid'");
        $row = mysqli_fetch_assoc($checkQuery);
        $newStatus = $row['is_verified'] ? 0 : 1; // Toggle status

        // Perform the update operation
        $query = mysqli_query($con, "UPDATE tblwriters SET is_verified = '$newStatus' WHERE id='$userid'");
        if ($query) {
            $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">User verification status updated.
                                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        } else {
            $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">Error updating verification status.
                                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        }
    } else {
        $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">Invalid or missing ID for verification status update.
                             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>';
    }

    header('Location: usermanagement');
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
                    <h4 class="mb-0 text-primary fw-bold">Writer <span class="text-info fw-medium"> Records</span></h4>
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
                                            <!--<div class="d-none" id="table-simple-pagination-actions">
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-falcon-info btn-sm ms-2" onclick="submitForm('mark-tasks-completed.php')">Mark as Completed</button>
                                                    <button type="button" class="btn btn-falcon-success btn-sm ms-2" onclick="submitForm('mark-tasks-paid.php')">Mark as Paid</button>
                                                </div>
                                            </div>-->
                                            <div class="d-flex align-items-center" id="table-simple-pagination-replace-element">
                                                <button class="btn btn-falcon-primary btn-sm" onclick="exportWriter()" title="Export as CSV" type="button"><span class="fas fa-external-link-alt" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Export as CSV</span></button>
                                                <!--<div class="dropdown font-sans-serif ms-2">
                                                    <button class="btn btn-falcon-default text-600 btn-sm dropdown-toggle dropdown-caret-none" type="button" id="preview-dropdown" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-11"></span></button>
                                                    <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="preview-dropdown"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Export</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="#!">Remove</a>
                                                    </div>
                                                </div>-->
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
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Writer ID</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Name</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Email</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Date Registered</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Verification</th>
                                            <th class="text-900 no-sort pe-1 align-middle data-table-row-action"></th>
                                        </tr>
                                        </thead>
                                        <tbody class="list" id="table-simple-pagination-body">
                                        <?php
                                        $query=mysqli_query($con,"SELECT * FROM tblwriters WHERE is_deleted = 0 ORDER BY id DESC");
                                        $cnt=1;
                                        while($row=mysqli_fetch_array($query)) {
                                            $encodedId = base64_encode($row["id"]); // Encode the id
                                            ?>
                                            <tr class="hover-actions-trigger btn-reveal-trigger hover-bg-100">
                                                <td class="align-middle" style="width: 28px;">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" id="simple-pagination-item-<?php echo $cnt; ?>" data-bulk-select-row="data-bulk-select-row" value="<?php echo $row['id']; ?>" name="taskIds[]"/>
                                                    </div>
                                                </td>
                                                <td class="align-middle white-space-nowrap fw-semi-bold name"><?php echo $row["id"];?></td>
                                                <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="writer?writerID=<?php echo $encodedId;?>"><?php echo $row["username"];?></a></td>
                                                <td class="align-middle white-space-nowrap email"><?php echo $row["email"];?></td>
                                                <td class="align-middle white-space-nowrap email"><?php echo date("jS M, Y", strtotime($row['created_at'])); ?></td>
                                                <td class="align-middle white-space-nowrap email">
                                                    <?php
                                                    if ($row['is_verified'] == 1) {
                                                        echo '<span class="badge badge rounded-pill badge-subtle-success">Verified<span class="ms-1 fas fa-check" data-fa-transform="shrink-2"></span></span>';
                                                    } else {
                                                        echo '<span class="badge badge rounded-pill badge-subtle-danger">Unverified<span class="ms-1 fas fa-ban" data-fa-transform="shrink-2"></span></span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="align-middle white-space-nowrap text-end position-relative">
                                                    <div class="hover-actions bg-100">
                                                        <a class="btn btn-outline-info bg-info icon-item rounded-3 me-2 fs-11 icon-item-sm" href="writer?writerID=<?php echo $encodedId;?>" title="View Writer"><span class="fas fa-eye"></span></a>
                                                        <a class="btn btn-outline-primary bg-primary icon-item rounded-3 me-2 fs-11 icon-item-sm" data-bs-toggle="modal" href="#user-edit-modal" title="Edit Writer" data-writer-id="<?php echo $row['id']; ?>" data-writer="<?php echo $row['username']; ?>" data-email="<?php echo $row['email']; ?>" data-phone="<?php echo $row['phone']; ?>"><span class="far fa-edit"></span></a>
                                                        <a href="usermanagement?verifyid=<?php echo $row['id'];?>" class="btn bg-<?php echo $row['is_verified'] ? 'danger' : 'success'; ?> icon-item rounded-3 me-2 fs-11 icon-item-sm" onclick="return confirm('Do you want to <?php echo $row['is_verified'] ? 'unverify' : 'verify'; ?> this writer?');" title="<?php echo $row['is_verified'] ? 'Unverify' : 'Verify'; ?> Writer"><i class="bi bi-<?php echo $row['is_verified'] ? 'x-circle-fill' : 'check-circle-fill'; ?>"></i></a>
                                                    </div>
                                                    <div class="dropdown font-sans-serif btn-reveal-trigger">
                                                        <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none" type="button" id="user-view-edit" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span class="fas fa-chevron-left fs-11"></span></button>
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

    <div class="modal fade" id="user-edit-modal" tabindex="-1" role="dialog" aria-labelledby="user-edit-modal-label" aria-hidden="true">
        <div class="modal-dialog mt-6" role="document">
            <div class="modal-content border-0">
                <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                    <div class="position-relative z-1">
                        <h4 class="mb-0 text-white" id="user-edit-modal-label">View/Edit Writer</h4>
                    </div>
                    <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4 px-5">
                    <div id="user-modal-alert" class="alert d-none"></div>
                    <form id="writer-form">
                        <input type="hidden" id="writer-id" name="writer-id">
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-writer">Name</label>
                            <input class="form-control" type="text" autocomplete="on" name="name" id="modal-auth-writer" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-email">Email</label>
                            <input class="form-control" type="text" autocomplete="on" name="email" id="modal-auth-email" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal-auth-phone">Phone</label>
                            <input class="form-control" type="text" autocomplete="on" name="phone" id="modal-auth-phone" />
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary d-block w-100 mt-3" type="submit">Update Writer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-writer-id');
                    const name = this.getAttribute('data-writer');
                    const email = this.getAttribute('data-email');
                    const phone = this.getAttribute('data-phone');

                    // Now set the data in the modal fields
                    document.getElementById('writer-id').value = id;
                    document.getElementById('modal-auth-writer').value = name;
                    document.getElementById('modal-auth-email').value = email;
                    document.getElementById('modal-auth-phone').value = phone;

                    // Clear any previous alert message
                    let alertDiv = document.getElementById('user-modal-alert');
                    alertDiv.classList.add('d-none');
                    alertDiv.classList.remove('alert-success', 'alert-danger');
                    alertDiv.textContent = '';
                });
            });

            document.getElementById('writer-form').addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('update-writer', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        let alertDiv = document.getElementById('user-modal-alert');
                        if (data.success) {
                            alertDiv.classList.remove('d-none', 'alert-danger');
                            alertDiv.classList.add('alert-success');
                            alertDiv.textContent = data.message;
                            // Refresh the table data
                            $('#table-simple-pagination-body').load(' #table-simple-pagination-body > *');
                            // Hide the modal after 5 seconds
                            setTimeout(function() {
                                $('#user-edit-modal').modal('hide');
                            }, 5000);
                        } else {
                            alertDiv.classList.remove('d-none', 'alert-success');
                            alertDiv.classList.add('alert-danger');
                            alertDiv.textContent = data.message;
                        }
                    })
                    .catch(error => {
                        let alertDiv = document.getElementById('user-modal-alert');
                        alertDiv.classList.remove('d-none', 'alert-success');
                        alertDiv.classList.add('alert-danger');
                        alertDiv.textContent = 'An error occurred: ' + error.message;
                    });
            });
        });

        function exportWriter() {
            var exportWriter = confirm("Do you want to download the exported CSV file?");
            if (exportWriter) {
                window.location.href = 'export_writers';
            }
        }

    </script>
<?php
include "footer.php";
?>
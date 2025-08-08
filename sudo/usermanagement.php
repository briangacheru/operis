<?php include "head.php";?>
    <title>iTasker | Writer Management</title>
<?php include "navi.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function sendVerificationEmail($writerName, $writerEmail, $action, $writerId)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.monkbrian.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'support@monkbrian.com';
        $mail->Password = 'EDU+pass.';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('support@monkbrian.com', 'itasker');
        $mail->addReplyTo('bryo4419@gmail.com', 'itasker admin');
        $mail->addAddress($writerEmail);
        $mail->addAddress('bryo4419@gmail.com', 'itasker admin');

        // Content
        $status = $action == 'verified' ? 'VERIFIED' : 'UNVERIFIED';
        $statusColor = $action == 'verified' ? '#28a745' : '#dc3545';
        $mail->isHTML(true);
        $mail->Subject = 'Account ' . $status . ' - itasker Writer ID: ' . $writerId . ' ';

        // Email Body with Logo and Modern Formatting
        $companyLogo = 'https://web.monkbrian.com/assets/img/team/itasker-email-header.png';
        $dashboardUrl = "https://web.monkbrian.com/login";

        $verificationMessage = $action == 'verified'
            ? "Congratulations! Your writer account has been successfully verified. You can now access all platform features and start receiving tasks."
            : "Your writer account verification has been revoked. Please contact support if you believe this is an error.";

        $actionButton = $action == 'verified'
            ? "<a class='btn' href='$dashboardUrl' style='background: #28a745;'>Access itasker</a>"
            : "<a class='btn' href='mailto:bryo4419@gmail.com' style='background: #dc3545;'>Contact Support</a>";

        $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        padding: 20px;
                    }
                    .email-container {
                        max-width: 600px;
                        background: #ffffff;
                        margin: 0 auto;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
                    }
                    .email-header {
                        text-align: center;
                        border-bottom: 2px solid #0073e6;
                        padding-bottom: 15px;
                    }
                    .email-header img {
                        max-width: 100%;
                        height: auto;
                        max-height:100px;
                    }
                    .email-content {
                        padding: 20px;
                    }
                    .email-content h2 {
                        color: $statusColor;
                        text-align: center;
                    }
                    .email-content p {
                        font-size: 16px;
                        line-height: 1.5;
                        color: #333;
                    }
                    .highlight {
                        font-weight: bold;
                        color: #0073e6;
                    }
                    .status-badge {
                        display: inline-block;
                        background: $statusColor;
                        color: white;
                        padding: 8px 16px;
                        border-radius: 20px;
                        font-weight: bold;
                        font-size: 14px;
                    }
                    .btn {
                        display: block;
                        text-align: center;
                        color: #ffffff;
                        padding: 12px;
                        border-radius: 5px;
                        text-decoration: none;
                        font-size: 16px;
                        font-weight: bold;
                        margin-top: 20px;
                        transition: opacity 0.3s ease-in-out;
                    }
                    .btn:hover {
                        opacity: 0.8;
                        color: #ffffff !important;
                    }
                    .footer {
                        text-align: center;
                        padding-top: 15px;
                        font-size: 12px;
                        color: #777;
                    }
                    .verification-info {
                        background: #f8f9fa;
                        padding: 15px;
                        border-radius: 5px;
                        margin: 15px 0;
                        border-left: 4px solid $statusColor;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='email-header'>
                        <img src='{$companyLogo}' alt='Company Logo'>
                    </div>
                    <div class='email-content'>
                        <h2>Account Verification Update</h2>
                        <p>Hello <span class='highlight'>$writerName</span>,</p>
                        <div class='verification-info'>
                            <p style='margin: 0; text-align: center;'>
                                Your account status: <span class='status-badge'>$status</span>
                            </p>
                        </div>
                        <p>$verificationMessage</p>
                        <p><strong>Writer ID:</strong> <span class='highlight'>$writerId</span></p>
                        <p><strong>Email:</strong> <span class='highlight'>$writerEmail</span></p>
                        <p><strong>Status Changed:</strong> <span class='highlight'>" . date('F j, Y \a\t g:i A') . "</span></p>
                        $actionButton
                    </div>
                    <div class='footer'>
                        <p>For any questions, contact <a href='mailto:bryo4419@gmail.com'>bryo4419@gmail.com</a></p>
                        <p>&copy; " . date('Y') . " itasker. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>";
        $mail->AltBody = "Account Verification Update\n\n
                    Hello $writerName,\n\n
                    Your account status: $status\n
                    $verificationMessage\n\n
                    Writer ID: $writerId\n
                    Email: $writerEmail\n
                    Status Changed: " . date('F j, Y \a\t g:i A') . "\n\n
                    Dashboard: $dashboardUrl\n\n
                    For any questions, contact bryo4419@gmail.com";

        $mail->send();

    } catch (Exception $e) {
        error_log("Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

$status = "OK";
$msg = "";
if (isset($_GET['delid'])) {
    $cmpid = $_GET['delid'];
    if (is_numeric($cmpid) && !empty($cmpid)) {
        // Perform the delete operation
        $query = mysqli_query($con, "DELETE FROM tblwriters WHERE id='$cmpid'");
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

// Writer verification
if (isset($_GET['verifyid'])) {
    $userid = $_GET['verifyid'];
    if (is_numeric($userid) && !empty($userid)) {
        // Get writer details before updating
        $writerQuery = mysqli_query($con, "SELECT username, email, is_verified FROM tblwriters WHERE id='$userid'");
        $writerData = mysqli_fetch_assoc($writerQuery);

        if ($writerData) {
            $currentStatus = $writerData['is_verified'];
            $newStatus = $currentStatus ? 0 : 1; // Toggle status
            $writerName = $writerData['username'];
            $writerEmail = $writerData['email'];

            // Perform the update operation
            $query = mysqli_query($con, "UPDATE tblwriters SET is_verified = '$newStatus' WHERE id='$userid'");
            if ($query) {
                // Send email notification
                $action = $newStatus ? 'verified' : 'unverified';
                sendVerificationEmail($writerName, $writerEmail, $action, $userid);

                $statusText = $newStatus ? 'verified' : 'unverified';
                $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">User verification status updated to ' . $statusText . '. Email notification sent.
                                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
            } else {
                $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">Error updating verification status.
                                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
            }
        } else {
            $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">Writer not found.
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
                                                <button class="btn btn-falcon-primary btn-sm" onclick="exportWriter()" data-bs-toggle="tooltip" data-bs-placement="top" title="Export as CSV" type="button"><span class="fas fa-external-link-alt" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Export as CSV</span></button>
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
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Email</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Date Registered</th>
                                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Verification</th>
                                            <th class="text-900 no-sort pe-1 align-middle data-table-row-action"></th>
                                        </tr>
                                        </thead>
                                        <tbody class="list" id="table-simple-pagination-body">
                                        <?php
                                        $query=mysqli_query($con,"SELECT * FROM tblwriters ORDER BY id ASC");
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
                                                <td class="align-middle white-space-nowrap "><?php echo $row["id"];?></td>
                                                <td>
                                                    <div class="d-flex align-items-center position-relative">
                                                        <div class="flex-1">
                                                            <h6 class="mb-1 fw-semi-bold text-nowrap"><a class="text-900 stretched-link"  href="writer?writerID=<?php echo $encodedId;?>"><?php echo $row["username"];?></a></h6>
                                                            <p class="fw-semi-bold mb-0 text-500">
                                                                <?php
                                                                if (isset($row["last_seen"]) && !empty($row["last_seen"])) {
                                                                    $lastSeen = new DateTime($row["last_seen"]);
                                                                    $now = new DateTime();
                                                                    $diff = $now->diff($lastSeen);

                                                                    if ($diff->y > 0) {
                                                                        echo "Last seen " . $diff->y . "y ago";
                                                                    } elseif ($diff->m > 0) {
                                                                        echo "Last seen " . $diff->m . "mo ago";
                                                                    } elseif ($diff->days >= 7) {
                                                                        $weeks = floor($diff->days / 7);
                                                                        echo "Last seen " . $weeks . "w ago";
                                                                    } elseif ($diff->days > 0) {
                                                                        echo "Last seen " . $diff->days . "d ago";
                                                                    } elseif ($diff->h > 0) {
                                                                        echo "Last seen " . $diff->h . "h ago";
                                                                    } elseif ($diff->i > 0) {
                                                                        echo "Last seen " . $diff->i . "m ago";
                                                                    } else {
                                                                        echo "Online";
                                                                    }
                                                                } else {
                                                                    echo "Last seen: Unknown";
                                                                }
                                                                ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle white-space-nowrap text-900"><?php echo $row["email"];?></td>
                                                <td class="align-middle white-space-nowrap text-900"><?php echo date("jS M, Y", strtotime($row['created_at'])); ?></td>
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
                                                        <a class="btn btn-outline-info bg-info icon-item rounded-3 me-2 fs-11 icon-item-sm" href="writer?writerID=<?php echo $encodedId;?>" data-bs-toggle="tooltip" data-bs-placement="top" title="View Writer"><span class="fas fa-eye"></span></a>
                                                        <a class="btn btn-outline-primary bg-primary icon-item rounded-3 me-2 fs-11 icon-item-sm" data-bs-toggle="modal" href="#user-edit-modal" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Writer" data-writer-id="<?php echo $row['id']; ?>" data-writer="<?php echo $row['username']; ?>" data-email="<?php echo $row['email']; ?>" data-phone="<?php echo $row['phone']; ?>"><span class="far fa-edit"></span></a>
                                                        <a href="usermanagement?verifyid=<?php echo $row['id'];?>" class="btn btn-outline-danger bg-<?php echo $row['is_verified'] ? 'danger' : 'success'; ?> icon-item rounded-3 me-2 fs-11 icon-item-sm" onclick="return confirm('Do you want to <?php echo $row['is_verified'] ? 'unverify' : 'verify'; ?> this writer?');" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo $row['is_verified'] ? 'Unverify' : 'Verify'; ?> Writer"><i class="bi bi-<?php echo $row['is_verified'] ? 'x-circle-fill' : 'check-circle-fill'; ?>"></i></a>
                                                        <?php if ($row['is_verified'] == 0) { ?>
                                                            <button type="button" class="btn btn-outline-danger bg-danger icon-item rounded-3 me-2 fs-11 icon-item-sm delete-writer-btn"
                                                                    data-writer-id="<?php echo $row['id']; ?>"
                                                                    data-writer-username="<?php echo htmlspecialchars($row['username']); ?>"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Writer">
                                                                <span class="fas fa-trash"></span>
                                                            </button>
                                                        <?php } ?>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="delete-confirmation-modal" tabindex="-1" aria-labelledby="deleteConfirmationLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmationLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-warning me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone!
                    </div>
                    <p>Are you absolutely sure you want to delete this writer account?</p>
                    <p><strong>Writer:</strong> <span id="writer-to-delete"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="proceed-to-verification">Yes, Proceed to Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Username Verification Modal -->
    <div class="modal fade" id="username-verification-modal" tabindex="-1" aria-labelledby="usernameVerificationLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="usernameVerificationLabel">
                        <i class="fas fa-shield-alt me-2"></i>Verify Username to Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Final Step:</strong> Type the exact username to confirm deletion.
                    </div>
                    <p>To confirm deletion, click the Auto-fill button to populate the username: <strong><span id="username-to-verify"></span></strong></p>                    <div class="mb-3">
                        <label for="username-input" class="form-label">Enter Username:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="username-input" placeholder="Click copy button then paste here">
                            <button class="btn btn-outline-secondary" type="button" id="copy-username-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Auto-fill Username">
                                <i class="fas fa-magic"></i> Auto-fill
                            </button>
                        </div>
                        <div class="form-text text-danger d-none" id="username-error">Username does not match!</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Username is case-sensitive and must match exactly.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-btn" disabled>Delete Writer</button>
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
                            <label class="form-label" for="modal-auth-writer">Username</label>
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
            let currentWriterId = null;
            let currentWriterUsername = null;

            // Handle delete button clicks
            const deleteButtons = document.querySelectorAll('.delete-writer-btn');
            deleteButtons.forEach((button, index) => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    currentWriterId = this.getAttribute('data-writer-id');
                    currentWriterUsername = this.getAttribute('data-writer-username');

                    // Show writer info in confirmation modal
                    const writerToDeleteElement = document.getElementById('writer-to-delete');
                    if (writerToDeleteElement) {
                        writerToDeleteElement.textContent = currentWriterUsername;
                    }

                    // Show first confirmation modal
                    const modalElement = document.getElementById('delete-confirmation-modal');
                    if (modalElement) {
                        const confirmModal = new bootstrap.Modal(modalElement);
                        confirmModal.show();
                    }
                });
            });

            // Handle copy username button
            const copyUsernameBtn = document.getElementById('copy-username-btn');
            if (copyUsernameBtn) {
                copyUsernameBtn.addEventListener('click', function() {
                    const usernameInput = document.getElementById('username-input');

                    if (currentWriterUsername && usernameInput) {
                        // Directly paste into input field
                        usernameInput.value = currentWriterUsername;
                        usernameInput.focus();

                        // Trigger input event to validate
                        usernameInput.dispatchEvent(new Event('input'));

                        // Visual feedback
                        const originalText = copyUsernameBtn.innerHTML;
                        copyUsernameBtn.innerHTML = '<i class="fas fa-check"></i> Done!';
                        copyUsernameBtn.classList.remove('btn-outline-secondary');
                        copyUsernameBtn.classList.add('btn-success');

                        setTimeout(function() {
                            copyUsernameBtn.innerHTML = originalText;
                            copyUsernameBtn.classList.remove('btn-success');
                            copyUsernameBtn.classList.add('btn-outline-secondary');
                        }, 1500);
                    }
                });
            }

            // Handle proceed to verification
            const proceedBtn = document.getElementById('proceed-to-verification');
            if (proceedBtn) {
                proceedBtn.addEventListener('click', function() {
                    // Hide first modal
                    const confirmModal = bootstrap.Modal.getInstance(document.getElementById('delete-confirmation-modal'));
                    if (confirmModal) {
                        confirmModal.hide();
                    }

                    // Show username verification modal
                    const usernameToVerifyElement = document.getElementById('username-to-verify');
                    if (usernameToVerifyElement) {
                        usernameToVerifyElement.textContent = currentWriterUsername;
                    }

                    document.getElementById('username-input').value = '';
                    document.getElementById('username-error').classList.add('d-none');
                    document.getElementById('confirm-delete-btn').disabled = true;

                    const verificationModal = new bootstrap.Modal(document.getElementById('username-verification-modal'));
                    verificationModal.show();
                });
            }

            // Handle username input validation
            const usernameInput = document.getElementById('username-input');
            if (usernameInput) {
                usernameInput.addEventListener('input', function() {
                    const enteredUsername = this.value;
                    const errorDiv = document.getElementById('username-error');
                    const confirmBtn = document.getElementById('confirm-delete-btn');

                    if (enteredUsername === currentWriterUsername) {
                        errorDiv.classList.add('d-none');
                        confirmBtn.disabled = false;
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        if (enteredUsername.length > 0) {
                            errorDiv.classList.remove('d-none');
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        } else {
                            errorDiv.classList.add('d-none');
                            this.classList.remove('is-invalid', 'is-valid');
                        }
                        confirmBtn.disabled = true;
                    }
                });
            }

            // Handle final deletion
            const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    // Hide the verification modal
                    const verificationModal = bootstrap.Modal.getInstance(document.getElementById('username-verification-modal'));
                    if (verificationModal) {
                        verificationModal.hide();
                    }

                    // Redirect to delete URL
                    window.location.href = `usermanagement?delid=${currentWriterId}`;
                });
            }

            // Reset modals when closed
            const deleteConfirmModal = document.getElementById('delete-confirmation-modal');
            if (deleteConfirmModal) {
                deleteConfirmModal.addEventListener('hidden.bs.modal', function() {
                    // Keep variables for verification modal
                });
            }

            const usernameVerificationModal = document.getElementById('username-verification-modal');
            if (usernameVerificationModal) {
                usernameVerificationModal.addEventListener('hidden.bs.modal', function() {
                    document.getElementById('username-input').value = '';
                    document.getElementById('username-error').classList.add('d-none');
                    document.getElementById('username-input').classList.remove('is-valid', 'is-invalid');
                    document.getElementById('confirm-delete-btn').disabled = true;

                    // Reset variables only when verification modal closes
                    currentWriterId = null;
                    currentWriterUsername = null;
                });
            }

            // Existing edit modal functionality
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
<?php
include "header.php";

if (isset($_GET['task_id'])) {
    $encodedId = $_GET['task_id'];
    $taskId = base64_decode($encodedId);
} else {
    $_SESSION['alert'] ='<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                        <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                        <p class="mb-0 flex-1">Invalid task ID!</p>
                                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
}

// Define variables for task data
$taskTopic = $taskSubject = $taskAccount = $taskCreatedOn = $taskStatus = $taskIsPaid = $taskIsConfirmed = $taskDescription = $taskWriter = $taskWriterEmail = $taskDueDate = $taskCPP = $taskPages = $existingFiles = $taskSubmitTime = $submittedOn =  '';

// Retrieve the task data from the database
$sql2 = "SELECT * FROM tbltasks WHERE id='$taskId'";
$result = mysqli_query($con, $sql2);

if ($row = mysqli_fetch_array($result)) {
    $id = base64_encode($row["id"]);
    $taskTopic = $row["topic"];
    $taskSubject = $row["subject"];
    $taskAccount = $row["account"];
    $taskCreatedOn = $row["create_date"];
    $taskStatus = $row["status"];
    $taskIsPaid = $row["is_paid"];
    $taskIsConfirmed = $row["is_confirmed"];
    $taskDescription = $row["description"];
    $taskWriter = $row["writer"];
    $taskWriterEmail = $row["email"];
    $taskDueDate = $row["due_date"];
    $taskCPP = $row["cpp"];
    $taskPages = $row["pages"];
    $existingFiles = $row['task_files']; // Assuming this contains comma-separated file paths
    $submittedFiles = $row['submitted_files'];
    $taskSubmitTime = $row['submitted_on'];
    $submittedOn = $row['submitted_on'];
}

// Determine badge based on task status
$statusBadge = '';
switch ($row["status"]) {
    case 'In Progress':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-warning fs-11">In progress<span class="fas fa-stream ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'Cancelled':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-danger fs-11">Cancelled<span class="fas fa-ban ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'Draft':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-danger fs-11">Draft<span class="fas fa-edit ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'Unconfirmed':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-primary fs-11">Unconfirmed<span class="fas fa-question ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'Submitted':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-info fs-11">Submitted<span class="fas fa-file ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'Completed':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-success fs-11">Completed<span class="fas fa-check ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
}
// Correctly retrieve is_paid status from the row
$is_paid = $row['is_paid']; // Assuming 'is_paid' is the column name in your database

// Determine badge based on payment status
$statusBadgeClass = ($is_paid == 1) ? 'bg-success' : 'bg-warning';
$statusBadgeText = ($is_paid == 1) ? 'Paid' : 'Unpaid';
$statusBadgePay = "<span class='badge $statusBadgeClass'>$statusBadgeText</span>";

$is_confirmed = $row['is_confirmed']; // Assuming 'is_paid' is the column name in your database
$confirmationClass = ($is_confirmed == 0) ? 'bg-light' : 'bg-primary';
$confirmationText = ($is_confirmed == 0) ? 'Confirmed' : 'Unconfirmed';
$confirmation = "<span class='badge $confirmationClass'>$confirmationText</span>";

?>

    <div class="card shadow-none border mb-3">
        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/illustrations/corner-6.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-header z-1">
            <div class="row flex-between-center gx-0">
                <div class="col-lg-auto d-flex align-items-center">
                    <h4 class="mb-0 text-primary fw-bold">View <span class="text-info fw-medium">Task Details</span></h4>
                </div>
                <div class="col-lg-auto pt-3 pt-lg-0">
                    <form class="row flex-lg-column flex-xxl-row gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
                        <div class="col-auto">
                        </div>
                        <div class="col-md-auto position-relative">
                            <h6 class="mb-1 text-primary"></h6>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Display Bootstrap Alerts -->
        <?php
        if (isset($_GET['message'])) {
            // Sanitize the message to remove any HTML tags
            $message = htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8');
            echo "
                <div class='alert alert-success border-0 d-flex align-items-center' role='alert'>
                    <div class='bg-success me-3 icon-item'><span class='fas fa-check-circle text-white fs-6'></span></div>
                        <p class='mb-0 flex-1'>$message</p>
                    <button class='btn-close' type='button' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
                <script>
            // Use JavaScript to hide the alert after 5 seconds
            setTimeout(function() {
                var alertElement = document.querySelector('.alert');
                if (alertElement) {
                    alertElement.classList.add('fade'); // Add Bootstrap's fade class
                    alertElement.addEventListener('transitionend', function() {
                        alertElement.remove();
                    });
                }
            }, 5000); // 5000 milliseconds = 5 seconds
        </script>
                ";
        }
        ?>
<?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']); // Clear the alert message
}
?>

    <div class="card mb-3">
    <div class="bg-holder bg-card" style="background-image:url(assets/img/icons/spot-illustrations/corner-5.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-body position-relative">
            <div class="row g-2 align-items-sm-center">
                <div class="col-auto"><img src="assets/img/icons/connect-circle.png" alt="" height="55" /></div>
                    <div class="col">
                    <div class="row align-items-center">
                        <div class="col col-lg-8">
                            <h5 class="mb-sm-0 text-primary fs-7">Task ID: <span class="text-info fw-medium">#<?php  echo $taskId;?></span></h5>
                            <p class="fw-semi-bold fs-10"><span class="me-1">Posted</span><span class="text-info ms-2"><?php  echo date("d M Y, g:i A", strtotime($taskCreatedOn));?></span>
                            </p>
                            <div class="fs-9 mb-3 mb-sm-0 text-primary"><strong class="me-2">Status: </strong><?php  echo $statusBadge;?>
                                <?php if ($is_confirmed == 1): ?>
                                    <?php echo $confirmation;?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto ms-auto">
                            <?php if ($taskStatus == 'In Progress'): ?>
                                <a class="btn btn-outline-primary btn-lg fs-9" href="submission.php?task_id=<?php echo $encodedId; ?>#filesSubmission">Submit Task</a>
                            <?php elseif ($is_confirmed == 1): ?>
                                <a class="btn btn-outline-success btn-sm fs-10" href="#" onclick="confirmAction('<?php echo $encodedId; ?>', 'accept')">Accept Task</a>
                                <a class="btn btn-outline-danger btn-sm fs-10" href="#" onclick="confirmAction('<?php echo $encodedId; ?>', 'decline')">Decline Task</a>
                            <?php elseif ($taskStatus == 'Submitted'): ?>
                                <a class="btn btn-outline-primary btn-lg fs-9" href="submission.php?task_id=<?php echo $encodedId; ?>#filesSubmission">Resubmit Task</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden mb-3" data-bs-theme="light">
        <div class="card-body bg-black">
            <div class="bg-holder rounded-3" style="background-image:url(assets/img/illustrations/corner-3.png);">
            </div>
            <!--/.bg-holder-->

            <div class="row">
                <div class="card-body position-relative">
                    <div class="row g-3 align-items-center">
                        <div class="col">
                            <div class="row align-items-center">
                                <div class="col col-sm-12">
                                    <h6 class="fw-semi-bold text-400 fs-9"><span class="fas fa-book text-white me-1"> </span><?php  echo $taskSubject;?></h6>
                                    <h2 class="fw-bold text-white"><?php  echo $taskTopic;?> </h2>
                                    <p class="text-white fw-semi-bold fs-10"><span class="me-1 fs-9">Due</span><span class="text-info ms-2 fs-10"><?php  echo date("d M Y, g:i A", strtotime($taskDueDate));?></span>
                                     </p>
                                    <?php
                                    $due_date = new DateTime($row['due_date']);
                                    $currentDateTime = new DateTime(); // Assuming you've already got this
                                    $interval = $currentDateTime->diff($due_date);
                                    $isLate = ($due_date < $currentDateTime) ? true : false;

                                    // Calculate total hours and minutes
                                    $totalHours = ($interval->days * 24) + $interval->h;
                                    $totalMinutes = $interval->i;

                                    // Format the difference as a string, and choose color based on whether it's late
                                    if ($isLate) {
                                        $timeDiff = "<span style='color: red; font-weight: bold;'> Past Due by: $totalHours hrs $totalMinutes min </span>";
                                    } else {
                                        $timeDiff = "<span style='color: green; font-weight: bold;'>Time Remaining: $totalHours hrs $totalMinutes min </span>";
                                    }
                                    ?>
                                    <?php if ($taskStatus !='Completed'): ?>
                                        <p class="text-danger fs-9 fw-semi-bold"><span class="far fa-clock text-white me-1"></span><?php echo $timeDiff; ?></p>
                                    <?php elseif ($taskIsPaid = 1): ?>
                                        <?php echo $statusBadgePay; ?>
                                        <?php if ($is_paid == 1):
                                            $paidOn = $row['paid_on'];
                                            $paidDate = date("d M Y, g:i A", strtotime($paidOn));
                                            ?> <span class="text-info ms-2 fs-10"><?php echo $paidDate; ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php $totalCost = $taskPages * $taskCPP;  ?>
                                    <h5 class="fs-9 mt-3 text-white">Ksh. <?php  echo $totalCost;?> </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="text-secondary text-opacity-50" />
                    <ul class="list-unstyled d-flex flex-wrap gap-3 fs-9 fw-semi-bold text-300 mt-3 mb-0">
                        <li><span class="fas fa-user-graduate text-white me-1"> </span><?php  echo $taskWriter;?></li>
                        <li><span class="fas fa-user text-white me-1"> </span><?php  echo $taskAccount;?></li>
                        <li><span class="fas fa-file text-white me-1"> </span><?php  echo $taskPages;?> Pages</li>
                        <li><span class="fas fa-credit-card text-white me-1"> </span>Ksh. <?php  echo $taskCPP;?> Per page</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row ">
        <div class="col-lg-12 order-1 order-lg-0">
            <div class="card mb-3">
                <div class="card-header bg-body-tertiary">
                    <h5 class="mb-0">Description</h5>
                </div>
                <div class="card-body position-relative">
                    <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/icons/spot-illustrations/corner-1.png);">
                    </div>
                    <!--/.bg-holder-->

                    <ul class="list-unstyled position-relative fs-9 p-0 m-0">
                        <li class="mb-2">
<!--                            <div class="d-flex"><dd>--><?php //echo $taskDescription; ?><!--</dd></div>-->
                            <div class="d-flex">
                                <dd>
                                    <?php
                                    echo html_entity_decode($taskDescription); ?>
                                </dd>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

            <div class="col mb-3">
                <div class="row g-3">
                    <div class="col-xxl-12">
                        <div class="card h-100 h-xxl-auto mt-xxl-3">
                            <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
                                <h6 class="mb-0">Task Files</h6><!--<a class="py-1 fs-10 font-sans-serif" href="#!">View All</a>-->
                            </div>
                            <div class="card-body position-relative">
                                <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/icons/spot-illustrations/corner-2.png);">
                                </div>
                                <!--/.bg-holder-->
                                <?php
                                // Display Task Files section
                                if (!empty($existingFiles)) {
                                    // Assuming $submittedFiles contains comma-separated file paths
                                    $filePaths = explode(',', $existingFiles);
                                    foreach ($filePaths as $filePath) {
                                        $fileName = basename($filePath); // Extracts the filename from the path
                                        $fileUrl = "taskfiles/" . $filePath; // Constructs the full URL to the file
                                        $formattedDate = date("d M Y, g:i A", strtotime($taskCreatedOn)); // Format 'submitted_on' date
                                        // Adjust the image path as necessary
                                        $thumbnailPath = "assets/img/icons/docs.png"; // Placeholder path for the thumbnail
                                        ?>
                                        <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                            <div class="file-thumbnail"><img class="border h-100 w-100 object-fit-cover rounded-2" src="<?php echo $thumbnailPath; ?>" alt="" /></div>
                                            <div class="ms-3 flex-shrink-1 flex-grow-1">
                                                <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold" href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileName; ?></a></h6>
                                                <div class="fs-10"><span class="fw-semi-bold">Uploaded on</span><span class="fw-medium text-600 ms-2"><?php echo $formattedDate; ?></span></div>
                                                <!-- Add or adjust action buttons as necessary -->
                                                <div class="hover-actions end-0 top-50 translate-middle-y">
                                                    <a class="btn btn-tertiary border-300 btn-sm me-1 text-600" data-bs-toggle="tooltip" data-bs-placement="top" title="Download" href="<?php echo $fileUrl; ?>" download="<?php echo $fileName; ?>"><img src="assets/img/icons/cloud-download.svg" alt="" width="15" /></a>
                                                    <!-- Edit button or other actions -->
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="text-200" />
                                        <?php
                                    }
                                } else {
                                    echo '<div>No files attached.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    <div class="col mb-3">
        <div class="row g-3">
            <div class="col-xxl-12">
                <div class="card h-100 h-xxl-auto mt-xxl-3">
                    <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
                        <h6 class="mb-0">Submitted Files</h6><!--<a class="py-1 fs-10 font-sans-serif" href="#!">View All</a>-->
                    </div>
                    <div class="card-body position-relative">
                        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/icons/spot-illustrations/corner-7.png);">
                        </div>
                        <!--/.bg-holder-->
                        <?php
                        // Display Task Files section
                        if (!empty($submittedFiles)) {
                            // Assuming $submittedFiles contains comma-separated file paths
                            $filePaths = explode(',', $submittedFiles);
                            foreach ($filePaths as $filePath) {
                                $fileName = basename($filePath); // Extracts the filename from the path
                                $fileUrl = "taskfiles/" . $filePath; // Constructs the full URL to the file
                                $formattedDate = date("d M Y, g:i A", strtotime($submittedOn)); // Format 'submitted_on' date
                                // Adjust the image path as necessary
                                $thumbnailPath = "assets/img/icons/docs.png"; // Placeholder path for the thumbnail
                                ?>
                                <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                    <div class="file-thumbnail"><img class="border h-100 w-100 object-fit-cover rounded-2" src="<?php echo $thumbnailPath; ?>" alt="" /></div>
                                    <div class="ms-3 flex-shrink-1 flex-grow-1">
                                        <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold" href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileName; ?></a></h6>
                                        <div class="fs-10"><span class="fw-semi-bold">Submitted on</span><span class="fw-medium text-600 ms-2"><?php echo $formattedDate; ?></span></div>
                                        <!-- Add or adjust action buttons as necessary -->
                                        <div class="hover-actions end-0 top-50 translate-middle-y">
                                            <a class="btn btn-tertiary border-300 btn-sm me-1 text-600" data-bs-toggle="tooltip" data-bs-placement="top" title="Download" href="<?php echo $fileUrl; ?>" download="<?php echo $fileName; ?>"><img src="assets/img/icons/cloud-download.svg" alt="" width="15" /></a>
                                            <!-- Edit button or other actions -->
                                        </div>
                                    </div>
                                </div>
                                <hr class="text-200" />
                                <?php
                            }
                        } else {
                            echo '<div>No files attached.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmAction(taskId, action) {
            let actionText = action === 'accept' ? 'accept' : 'decline';
            if (confirm(`Are you sure you want to ${actionText} this task?`)) {
                window.location.href = `confirmation.php?task_id=${taskId}&action=${action}`;
            }
        }
    </script>
<?php
include "footer.php";
?>
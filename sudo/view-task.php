<?php include "head.php";

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
$taskTopic = $taskSubject = $taskAccount = $taskCreatedOn = $taskStatus = $taskIsPaid = $taskDescription = $taskWriter = $taskWriterEmail = $taskDueDate = $taskCPP = $taskPages = $existingFiles = $taskSubmitTime = $submittedOn = $completedOn = '';

// Retrieve the task data from the database
$sql2 = "SELECT * FROM tbltasks WHERE id='$taskId'";
$result = mysqli_query($con, $sql2);

if ($rowTask = mysqli_fetch_array($result)) {
    $id = base64_encode($rowTask["id"]);
    $taskTopic = $rowTask["topic"];
    $taskSubject = $rowTask["subject"];
    $taskAccount = $rowTask["account"];
    $taskCreatedOn = $rowTask["create_date"];
    $taskStatus = $rowTask["status"];
    $taskIsPaid = $rowTask["is_paid"];
    $taskDescription = $rowTask["description"];
    $taskWriter = $rowTask["writer"];
    $taskWriterEmail = $rowTask["email"];
    $taskDueDate = $rowTask["due_date"];
    $taskCPP = $rowTask["cpp"];
    $taskPages = $rowTask["pages"];
    $existingFiles = $rowTask['task_files']; // Assuming this contains comma-separated file paths
    $submittedFiles = $rowTask['submitted_files'];
    $taskSubmitTime = $rowTask['submitted_on'];
    $submittedOn = $rowTask['submitted_on'];
    $completedOn = $rowTask['completed_on'];
}
$due_date = new DateTime($rowTask['due_date']);
$currentDateTime = new DateTime(); // Assuming you've already got this
$interval = $currentDateTime->diff($due_date);
$isLate = ($due_date < $currentDateTime) ? true : false;
// Determine badge based on task status
$statusBadge = '';
switch ($rowTask["status"]) {
    case 'In Progress':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-warning fs-11">In progress<span class="fas fa-stream ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'In Revision':
        $statusBadge = '<span class="badge badge rounded-pill badge-subtle-warning">In Revision<span class="ms-1 fas fa-flag" data-fa-transform="shrink-2"></span></span>';
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
if ($isLate && $rowTask["status"] === 'In Progress') {
    $statusBadge .= ' <span class="badge badge rounded-pill badge-subtle-danger">Late<span class="ms-1 fa fa-exclamation-triangle" data-fa-transform="shrink-2"></span></span>';
}
    // Correctly retrieve is_paid status from the $rowTask
    $is_paid = $rowTask['is_paid']; // Assuming 'is_paid' is the column name in your database
    // Determine badge based on payment status
    $statusBadgeClass = ($is_paid == 1) ? 'badge-subtle-success' : 'badge-subtle-warning';
    $statusBadgeText = ($is_paid == 1) ? 'Paid' : 'Unpaid';
    $statusBadgePay = "<span class='badge badge rounded-pill $statusBadgeClass'>$statusBadgeText</span>";

    $is_confirmed = $rowTask['is_confirmed']; // Assuming 'is_confirmed' is the column name in your database
    if ($is_confirmed == 0) {
        $confirmationClass = 'bg-light';
        $confirmationText = 'Confirmed';
    } elseif ($is_confirmed == 1) {
        $confirmationClass = 'bg-primary';
        $confirmationText = 'Unconfirmed';
    } elseif ($is_confirmed == 2) {
        $confirmationClass = 'bg-danger';
        $confirmationText = 'Declined';
    }
    $confirmation = "<span class='badge badge rounded-pill $confirmationClass'>$confirmationText</span>";

?>

<title>iTasker | View Task #<?php  echo $taskId;?></title>
<?php include "navi.php";
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
?>

    <div class="card shadow-none border mb-3">
        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/illustrations/corner-6.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-header z-1">
            <div class="row flex-between-center gx-0">
                <div class="col-lg-auto d-flex align-items-center">
                    <h4 class="mb-0 text-primary fw-bold">View <span class="text-info fw-medium">Task Details</span></h4>
                </div>
                <div class="col-lg-auto pt-3 pt-lg-0">
                    <form class="$rowTask flex-lg-column flex-xxl-$rowTask gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
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
            }, 60000); // 5000 milliseconds = 5 seconds
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
        <div class="card-body">
            <div class="row justify-content-between align-items-center">
                <div class="col">
                    <div class="d-flex">
                        <div class="calendar me-2">
                            <span class="calendar-month">
                                <?php // Get the current month and date
                                $currentMonth = date('M'); // Current month abbreviation (e.g., 'Jul')
                                $currentDay = date('d'); // Current day of the month (e.g., '19')
                                echo $currentMonth;?>
                            </span>
                            <span class="calendar-day"><?php echo $currentDay; ?> </span></div>
                        <div class="flex-1 fs-10">
                            <h5 class="mb-sm-0 text-primary fs-7">Task ID: <span class="text-info fw-medium">#<?php  echo $taskId;?></span></h5>
                            <p class="mb-0">Posted <span class="text-info ms-2"><?php  echo date("d M Y, g:i A", strtotime($taskCreatedOn));?></span></p>
                            <div class="fs-9 mb-3 mb-sm-0 text-primary"><strong class="me-2">Status: </strong><?php  echo $statusBadge;?>
                                <?php if ($taskStatus == 'Submitted' && !empty($submittedOn)): ?>
                                    <span class="fs-10 text-info ms-2"><?php echo date("d M Y, g:i A", strtotime($submittedOn)); ?></span>
                                <?php elseif ($taskStatus == 'Completed' && !empty($completedOn)): ?>
                                    <span class="fs-10 text-success ms-2"><?php echo date("d M Y, g:i A", strtotime($completedOn)); ?></span>
                                <?php endif; ?>
                                <?php if ($is_confirmed != 0): ?>
                                    <?php echo $confirmation;?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-auto mt-4 mt-md-0">
                    <a class="btn btn-sm btn-outline-primary me-2" type="button" href="edit-task?task_id=<?php  echo $encodedId; ?>" title="Edit Task">
                        <i class="fas fa-edit" aria-hidden="true"></i>
                        <span class="ms-1 d-none d-sm-inline-block">Edit Task</span>
                    </a>
                    <a class="btn btn-outline-info btn-sm mx-2" type="button" target="_blank" href="duplicate-task?task_id=<?php echo $encodedId; ?>" title="Duplicate Task" onclick="return confirmDuplicate();">
                        <i class="fas fa-copy" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1">Duplicate</span>
                    </a>
                    <a class="btn btn-outline-danger btn-sm mx-2" type="button" id="favorite-btn" onclick="toggleFavorite(<?php echo $taskId; ?>)">
                        <i id="favorite-icon" class="fas <?php $is_favorite = $rowTask['is_favorite']; echo ($is_favorite == 1) ? 'fa-heart' : 'fa-heart-broken'; ?>" aria-hidden="true"></i>
                        <span id="favorite-text" class="d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1"><?php echo ($is_favorite == 1) ? 'Unfavorite' : 'Favorite'; ?></span>
                    </a>
                    <?php if ($taskStatus =='Submitted'): ?>
                        <a class="btn btn-outline-success btn-sm mx-2" type="button" id="complete-task-btn-<?php echo $taskId; ?>" title="Complete Task" onclick="completeTask('<?php echo $encodedId; ?>', <?php echo $taskId; ?>)">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            <span id="complete-task-text-<?php echo $taskId; ?>" class="d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1">Complete</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden mb-3" data-bs-theme="light">
        <div class="card-body bg-black">
            <div class="bg-holder rounded-3" style="background-image:url(../assets/img/illustrations/corner-3.png);">
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
                                    $due_date = new DateTime($rowTask['due_date']);
                                    $currentDateTime = new DateTime();
                                    $isLate = ($due_date < $currentDateTime);

                                    $remainingSeconds = $isLate ? 0 : $due_date->getTimestamp() - $currentDateTime->getTimestamp();

                                    // Format the difference as a string, and choose color based on whether it's late
                                    if ($rowTask['status'] == 'Completed') {
                                        $timeDiff = "<span style='font-weight: bold;'>Completed</span>";
                                    } elseif ($rowTask['status'] == 'Cancelled') {
                                        $timeDiff = "<span style='font-weight: bold;'>Cancelled</span>";
                                    } elseif ($rowTask['status'] == 'Submitted') {
                                        $timeDiff = "<span style='font-weight: bold;'>Submitted</span>";
                                    } elseif ($rowTask['is_confirmed'] == 2) {
                                        $timeDiff = "<span style='font-weight: bold;'>Declined</span>";
                                    } else {
                                        if ($isLate) {
                                            $timeDiff = "<span id='time-remaining' style='color: red; font-weight: bold;'>Past Due</span>";
                                        } else {
                                            $timeDiff = "<span id='time-remaining' class='fw-bold text-green fs-8'></span>";
                                        }
                                    }
                                    ?>
                                    <?php if ($taskStatus !='Completed'): ?>
                                        <p class="text-danger fs-9 fw-semi-bold"><span class="far fa-clock text-white me-2"></span><?php echo $timeDiff; ?></p>
                                    <?php elseif ($taskIsPaid = 1): ?>
                                        <?php if ($is_paid == 0): ?>
                                            <!-- Unpaid Badge as a Button -->
                                            <button class="badge rounded-pill badge-subtle-warning fs-10 fw-semi-bold" onclick="markAsPaidConfirm('<?php echo $encodedId; ?>', <?php echo $taskId; ?>)">
                                                Unpaid
                                            </button>
                                        <?php else: ?>
                                            <!-- Paid Badge Display -->
                                            <?php echo $statusBadgePay; ?>
                                            <?php if ($is_paid == 1):
                                                $paidOn = $rowTask['paid_on'];
                                                $paidDate = date("d M Y, g:i A", strtotime($paidOn));
                                                ?>
                                                <span class="text-success ms-2 fs-10"><?php echo $paidDate; ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php $totalCost = $taskPages * $taskCPP;  ?>
                                    <h5 class="fs-9 mt-3 text-white">Ksh. <?php  echo $totalCost;?> </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="text-secondary text-opacity-50" />
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                        <span class="badge rounded-pill badge-subtle-dark border border-300 text-info py-2 px-3">
                            <?php
                            if ($taskWriter) {
                                // Fetch the writerID from tblwriters based on the username (taskWriter)
                                $stmt = $con->prepare("SELECT id FROM tblwriters WHERE username = ?");
                                $stmt->bind_param("s", $taskWriter);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $writerId = $result->fetch_assoc()['id'];

                                if ($writerId) {
                                    // Encode writerID for use in the link
                                    $encodedWriterId = base64_encode($writerId);
                                    ?>
                                    <span class="fas fa-user-graduate text-white me-1"></span>
                                    <a class="text-info" href="writer.php?writerID=<?php echo $encodedWriterId; ?>"><?php echo htmlspecialchars($taskWriter); ?></a>
                                    <?php
                                } else {
                                    echo "Writer not found.";
                                    exit;
                                }
                            } else {
                                echo "Task writer not found.";
                                exit;
                            }
                            ?>
                        </span>
                        <span class="badge rounded-pill badge-subtle-dark border border-300 text-info py-2 px-3">
                            <span class="fas fa-user text-white me-1"> </span><?php  echo $taskAccount;?></span>
                        <span class="badge rounded-pill badge-subtle-dark border border-300 text-info py-2 px-3">
                            <span class="fas fa-file text-white me-1"> </span><?php  echo $taskPages;?> Pages</span>
                        <span class="badge rounded-pill badge-subtle-dark border border-300 text-info py-2 px-3">
                            <span class="fas fa-credit-card text-white me-1"> </span>Ksh. <?php  echo $taskCPP;?> Per page</span>
                    </div>
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
                    <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);">
                    </div>
                    <!--/.bg-holder-->

                    <ul class="list-unstyled position-relative fs-9 p-0 m-0">
                        <li class="mb-2">
                            <div class="d-flex">
                                <dd>
                                    <?php
                                    echo $taskDescription;
                                    ?>
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
                                <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);">
                                </div>
                                <?php
                                if (!empty($existingFiles)) {
                                    $filePaths = explode(',', $existingFiles);
                                    $fileUrls = !empty($rowTask['file_urls']) ? explode(',', $rowTask['file_urls']) : array_fill(0, count($filePaths), '');
                                    $fileSizes = !empty($rowTask['file_sizes']) ? explode(',', $rowTask['file_sizes']) : [];

                                    foreach ($filePaths as $index => $filePath) {
                                        $fileName = basename($filePath); // Extracts the filename from the path
                                        $fileUrl = isset($fileUrls[$index]) ? $fileUrls[$index] : ''; // Get the corresponding URL
                                        $fileSize = isset($fileSizes[$index]) ? $fileSizes[$index] : null;
                                        $formattedSize = formatFileSize($fileSize);

                                        $formattedDate = date("d M Y, g:i A", strtotime($taskCreatedOn));
                                        $thumbnailPath = "../assets/img/icons/docs.png";
                                        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                                        switch (strtolower($fileExtension)) {
                                            case 'pdf':
                                                $thumbnailPath = "../assets/img/icons/pdf.png";
                                                break;
                                            case 'doc':
                                            case 'docx':
                                            case 'rtf':
                                                $thumbnailPath = "../assets/img/icons/word.png";
                                                break;
                                            case 'xls':
                                            case 'xlsx':
                                            case 'csv':
                                                $thumbnailPath = "../assets/img/icons/excel.png";
                                                break;
                                            case 'ppt':
                                            case 'pptx':
                                                $thumbnailPath = "../assets/img/icons/powerpoint.png";
                                                break;
                                            case 'mp4':
                                            case 'avi':
                                            case 'mov':
                                            case 'mkv':
                                            case 'wmv':
                                            case 'flv':
                                            case 'mpeg':
                                            case 'mpg':
                                            case '3gp':
                                            case 'webm':
                                            case 'm4v':
                                                $thumbnailPath = "../assets/img/icons/mp4.png";
                                                break;
                                            case 'jpg':
                                            case 'jpeg':
                                            case 'png':
                                            case 'gif':
                                                $thumbnailPath = "../assets/img/icons/image.png";
                                                break;
                                            case 'zip':
                                            case 'rar':
                                                $thumbnailPath = "../assets/img/icons/zip.png";
                                                break;
                                            default:
                                                $thumbnailPath = "../assets/img/icons/docs.png";
                                                break;
                                        }
                                        ?>
                                        <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                            <div class="file-thumbnail"><img class="border h-100 w-100 object-fit-cover rounded-2" src="<?php echo $thumbnailPath; ?>" alt="" /></div>
                                            <div class="ms-3 flex-shrink-1 flex-grow-1">
                                                <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold" href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileName; ?></a></h6>
                                                <div class="fs-10"><span class="fw-medium text-600 file-size"><?php echo $formattedSize; ?></span> <span class="fw-medium text-600 ms-2"><?php echo $formattedDate; ?></span></div>
                                                <div class="hover-actions end-0 top-50 translate-middle-y">
                                                    <a class="btn btn-tertiary border-300 btn-sm me-1 text-600" data-bs-toggle="tooltip" data-bs-placement="top" title="Download" href="<?php echo $fileUrl; ?>" download="<?php echo $fileName; ?>"><img src="../assets/img/icons/cloud-download.svg" alt="" width="15" /></a>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="text-200" />
                                        <?php
                                    }
                                } else {
                                    echo '<div>No task files attached.</div>';
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
                                <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/icons/spot-illustrations/corner-7.png);">
                                </div>
                                <?php
                                if (!empty($submittedFiles)) {
                                    $filePaths = explode(',', $submittedFiles);
                                    $fileUrls = !empty($rowTask['submitted_file_urls']) ? explode(',', $rowTask['submitted_file_urls']) : array_fill(0, count($filePaths), '');
                                    $fileSizes = !empty($rowTask['submitted_file_sizes']) ? explode(',', $rowTask['submitted_file_sizes']) : array_fill(0, count($filePaths), '');

                                    foreach ($filePaths as $index => $filePath) {
                                        $fileName = basename($filePath); // Extracts the filename from the path
                                        $fileUrl = isset($fileUrls[$index]) ? $fileUrls[$index] : ''; // Get the corresponding URL
                                        $fileSize = isset($fileSizes[$index]) ? $fileSizes[$index] : '0';
                                        $formattedDate = date("d M Y, g:i A", strtotime($submittedOn)); // Format 'submitted_on' date
                                        $thumbnailPath = "../assets/img/icons/docs.png"; // Placeholder path for the thumbnail

                                        if (!function_exists('formatFileSize')) {
                                            function formatFileSize($bytes) {
                                                if ($bytes == 0 || $bytes == '0') return 'Unknown size';
                                                $bytes = (int)$bytes;
                                                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                                $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
                                                return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
                                            }
                                        }
                                        $formattedFileSize = formatFileSize($fileSize);

                                        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                                        switch (strtolower($fileExtension)) {
                                            case 'pdf':
                                                $thumbnailPath = "../assets/img/icons/pdf.png";
                                                break;
                                            case 'doc':
                                            case 'docx':
                                            case 'rtf':
                                                $thumbnailPath = "../assets/img/icons/word.png";
                                                break;
                                            case 'xls':
                                            case 'xlsx':
                                            case 'csv':
                                                $thumbnailPath = "../assets/img/icons/excel.png";
                                                break;
                                            case 'ppt':
                                            case 'pptx':
                                                $thumbnailPath = "../assets/img/icons/powerpoint.png";
                                                break;
                                            case 'mp4':
                                            case 'avi':
                                            case 'mov':
                                            case 'mkv':
                                            case 'wmv':
                                            case 'flv':
                                            case 'mpeg':
                                            case 'mpg':
                                            case '3gp':
                                            case 'webm':
                                            case 'm4v':
                                                $thumbnailPath = "../assets/img/icons/mp4.png";
                                                break;
                                            case 'jpg':
                                            case 'jpeg':
                                            case 'png':
                                            case 'gif':
                                                $thumbnailPath = "../assets/img/icons/image.png";
                                                break;
                                            case 'zip':
                                            case 'rar':
                                                $thumbnailPath = "../assets/img/icons/zip.png";
                                                break;
                                            default:
                                                $thumbnailPath = "../assets/img/icons/docs.png";
                                                break;
                                        }
                                        ?>
                                        <div class="d-flex mb-3 hover-actions-trigger align-items-center" data-file-url="<?php echo $fileUrl; ?>">
                                            <div class="file-thumbnail"><img class="border h-100 w-100 object-fit-cover rounded-2" src="<?php echo $thumbnailPath; ?>" alt="" /></div>
                                            <div class="ms-3 flex-shrink-1 flex-grow-1">
                                                <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold" href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileName; ?></a></h6>
                                                <div class="fs-10">
                                                    <span class="fw-medium text-600"><?php echo $formattedFileSize; ?></span>
                                                    <span class="fw-medium text-600 ms-2"><?php echo $formattedDate; ?></span>
                                                </div>
                                                <div class="hover-actions end-0 top-50 translate-middle-y">
                                                    <a class="btn btn-tertiary border-300 btn-sm me-1 text-600" data-bs-toggle="tooltip" data-bs-placement="top" title="Download" href="<?php echo $fileUrl; ?>" download="<?php echo $fileName; ?>"><img src="../assets/img/icons/cloud-download.svg" alt="" width="15" /></a>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="text-200" />
                                        <?php
                                    }
                                } else {
                                    echo '<div>No submitted files.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <script>
        function confirmDuplicate() {
            return confirm('Are you sure you want to duplicate this task?');
        }

        function toggleFavorite(taskId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'toggle_favorite', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        const favoriteBtn = document.getElementById('favorite-btn');
                        const favoriteIcon = document.getElementById('favorite-icon');
                        const favoriteText = document.getElementById('favorite-text');
                        if (response.is_favorite == 1) {
                            favoriteIcon.classList.remove('fa-heart-broken');
                            favoriteIcon.classList.add('fa-heart');
                            favoriteText.textContent = 'Unfavorite';
                        } else {
                            favoriteIcon.classList.remove('fa-heart');
                            favoriteIcon.classList.add('fa-heart-broken');
                            favoriteText.textContent = 'Favorite';
                        }
                    } else {
                        alert('Failed to update favorite status.');
                    }
                }
            };
            xhr.send('task_id=' + taskId);
        }

        function completeTask(encodedId, taskId) {
            if (confirm('Are you sure you want to complete task ID: #' + taskId + '?')) {
                $.ajax({
                    url: 'complete-task',
                    type: 'POST',
                    data: { task_id: encodedId },
                    success: function() {
                        // Redirect to the task details page after completing the task
                        window.location.href = 'view-task?task_id=' + encodedId;
                    },
                    error: function() {
                        alert('An error occurred while completing the task.');
                    }
                });
            }
        }

    </script>
    <script>
        function markAsPaidConfirm(encodedId, taskId) {
            if (confirm('Are you sure you have paid task ID: #' + taskId + '?')) {
                $.ajax({
                    url: 'confirm-paid',
                    type: 'POST',
                    data: { task_id: encodedId },
                    success: function() {
                        // Redirect to the task details page after completing the task
                        window.location.href = 'view-task?task_id=' + encodedId;
                    },
                    error: function() {
                        alert('An error occurred while marking the task as paid.');
                    }
                });
            }
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const timeElement = document.getElementById('time-remaining');
            let remainingSeconds = <?= $remainingSeconds ?>;

            function updateTime() {
                if (remainingSeconds <= 0) {
                    timeElement.style.color = 'red';
                    timeElement.innerHTML = "Past Due";
                    return;
                }

                const hours = Math.floor(remainingSeconds / 3600);
                const minutes = Math.floor((remainingSeconds % 3600) / 60);
                const seconds = remainingSeconds % 60;

                timeElement.innerHTML = `Time Remaining: ${hours} hrs ${minutes} min ${seconds} sec`;
                timeElement.style.color = 'green';

                remainingSeconds--;
            }

            // Update every second
            setInterval(updateTime, 1000);

            // Initialize immediately
            updateTime();
        });
    </script>

<?php
include "footer.php";
?>
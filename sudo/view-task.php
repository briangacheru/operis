<?php include "head.php";
if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes) {
        if ($bytes == 0 || $bytes == '0' || $bytes === null) return 'Unknown size';
        $bytes = (int)$bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
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
$taskTopic = $taskSubject = $taskAccount = $taskCreatedOn = $taskStatus = $taskIsPaid = $taskDescription = $taskWriter = $taskWriterEmail = $taskDueDate = $taskCPP = $taskPages = $taskSubmitTime = $submittedOn = $completedOn = '';

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
                            <p class="mb-0">Posted on <span class="text-info ms-2"><?php  echo date("d M Y, g:i A", strtotime($taskCreatedOn));?></span></p>
                            <?php if ($rowTask['acknowledged'] == 0): ?>
                                <p class='mb-0'>Viewed on <span class='badge badge rounded-pill badge-subtle-secondary'> Not Viewed<span class='ms-1 fas fa-eye-slash' data-fa-transform='shrink-2'></span></span></p>
                            <?php elseif ($rowTask['acknowledged'] == 1): ?>
                                <p class='mb-0'>Viewed on <span class='text-info ms-2'><?php echo date('d M Y, g:i A', strtotime($rowTask['acknowledged_at'])); ?></span></p>
                            <?php endif; ?>
                            <div class="fs-9 mt-2 mb-3 mb-sm-0 text-primary"><strong class="me-2">Status: </strong><?php  echo $statusBadge;?>
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
                    <a class="btn btn-sm btn-outline-primary me-2" type="button" href="edit-task?task_id=<?php  echo $encodedId; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Task">
                        <i class="fas fa-edit" aria-hidden="true"></i>
                        <span class="ms-1 d-none d-sm-inline-block">Edit Task</span>
                    </a>
                    <a class="btn btn-outline-info btn-sm mx-2" type="button"  href="duplicate-task?task_id=<?php echo $encodedId; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Duplicate Task" onclick="return confirmDuplicate();">
                        <i class="fas fa-copy" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1">Duplicate</span>
                    </a>
                    <a class="btn btn-outline-danger btn-sm mx-2" type="button" id="favorite-btn" onclick="toggleFavorite(<?php echo $taskId; ?>)">
                        <i id="favorite-icon" class="fas <?php $is_favorite = $rowTask['is_favorite']; echo ($is_favorite == 1) ? 'fa-heart' : 'fa-heart-broken'; ?>" aria-hidden="true"></i>
                        <span id="favorite-text" class="d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1"><?php echo ($is_favorite == 1) ? 'Unfavorite' : 'Favorite'; ?></span>
                    </a>
                    <?php if ($taskStatus =='Submitted'): ?>
                        <a class="btn btn-outline-success btn-sm mx-2" type="button" id="complete-task-btn-<?php echo $taskId; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Complete Task" onclick="completeTask('<?php echo $encodedId; ?>', <?php echo $taskId; ?>)">
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
                                    <h6 class="fw-semi-bold text-400 fs-9 text-uppercase"><span class="fas fa-book text-white me-1"> </span><?php  echo $taskSubject;?></h6>
                                    <h2 class="fw-bold text-white text-uppercase"><?php  echo $taskTopic;?> </h2>
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
                <div class='card-header d-flex bg-body-tertiary align-items-center'>
                    <i class='fas fa-sliders-h me-2 text-primary'></i>
                    <h6 class="mb-0">Description</h6>
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
                                    // Remove slashes and make links clickable
                                    $cleanText = stripslashes($taskDescription);

                                    // Convert URLs to clickable links with highlighting
                                    $pattern = '/(https?:\/\/[^\s]+)/';
                                    $replacement = '<a href="$1" class="highlighted-link" target="_blank">$1</a>';
                                    $formattedText = preg_replace($pattern, $replacement, $cleanText);

                                    echo $formattedText;
                                    ?>
                                </dd>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Files card section -->
    <div class='col mb-3'>
        <div class='row g-3'>
            <div class='col-xxl-12'>
                <div class='card h-100 h-xxl-auto mt-xxl-3'>
                    <div class='card-header d-flex bg-body-tertiary align-items-center'>
                        <i class='far fa-folder-open me-2 text-primary'></i>
                        <h6 class='mb-0'>Task Files</h6>
                    </div>
                    <div class='card-body position-relative'>
                        <div class='bg-holder bg-card d-none d-md-block'
                             style='background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);'></div>
                        <?php
                        // Get task files from new table
                        $taskFilesQuery = "SELECT * FROM tbl_task_files WHERE task_id = ? AND file_type = 'task' AND is_deleted = 0 ORDER BY upload_time ASC";
                        $stmt = mysqli_prepare($con, $taskFilesQuery);
                        mysqli_stmt_bind_param($stmt, 'i', $taskId);
                        mysqli_stmt_execute($stmt);
                        $taskFilesResult = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($taskFilesResult) > 0) {
                            $fileIndex = 0;
                            while ($fileRow = mysqli_fetch_assoc($taskFilesResult)) {
                                $fileName = $fileRow['original_file_name'];
                                $fileUrl = $fileRow['file_url'];
                                $fileSize = $fileRow['file_size'];
                                $uploadTime = $fileRow['upload_time'];
                                $formattedSize = formatFileSize($fileSize);
                                $formattedDate = date('d M Y, g:i A', strtotime($uploadTime));

                                // Determine thumbnail based on file extension
                                $thumbnailPath = '../assets/img/icons/docs.png';
                                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                                switch (strtolower($fileExtension)) {
                                    case 'pdf':
                                        $thumbnailPath = '../assets/img/icons/pdf.png';
                                        break;
                                    case 'doc':
                                    case 'docx':
                                    case 'rtf':
                                        $thumbnailPath = '../assets/img/icons/word.png';
                                        break;
                                    case 'xls':
                                    case 'xlsx':
                                    case 'csv':
                                        $thumbnailPath = '../assets/img/icons/excel.png';
                                        break;
                                    case 'ppt':
                                    case 'pptx':
                                        $thumbnailPath = '../assets/img/icons/powerpoint.png';
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
                                        $thumbnailPath = '../assets/img/icons/mp4.png';
                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif':
                                        $thumbnailPath = '../assets/img/icons/image.png';
                                        break;
                                    case 'zip':
                                    case 'rar':
                                        $thumbnailPath = '../assets/img/icons/zip.png';
                                        break;
                                    default:
                                        $thumbnailPath = '../assets/img/icons/docs.png';
                                        break;
                                }
                                ?>
                                <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                    <div class="file-thumbnail">
                                        <img class="border h-100 w-100 object-fit-cover rounded-2"
                                             src="<?php echo $thumbnailPath; ?>" alt=""/>
                                    </div>
                                    <div class="ms-3 flex-shrink-1 flex-grow-1">
                                        <h6 class="mb-1">
                                            <a class="stretched-link text-900 fw-semi-bold"
                                               href="<?php echo $fileUrl; ?>"><?php echo htmlspecialchars($fileName); ?></a>
                                        </h6>
                                        <div class="fs-10">
                                            <span class="fw-medium text-600 file-size"><?php echo $formattedSize; ?></span>
                                            <span class='fw-medium text-600 mx-1'>•</span>
                                            <span class="fw-medium text-600"><?php echo $formattedDate; ?></span>
                                        </div>
                                        <div class="hover-actions end-0 top-50 translate-middle-y">
                                            <button class="btn btn-tertiary border-300 btn-sm me-1 text-600"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="View File"
                                                    onclick="previewFile('<?php echo $encodedId; ?>', <?php echo $fileIndex; ?>, 'task')">
                                                <img src="../assets/img/icons/eye.svg" alt="" width="15"/>
                                            </button>
                                            <a class="btn btn-tertiary border-300 btn-sm me-1 text-600"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="Download"
                                               href="<?php echo $fileUrl; ?>"
                                               download="<?php echo htmlspecialchars($fileName); ?>">
                                                <img src="../assets/img/icons/cloud-download.svg" alt="" width="15"/>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <hr class="text-200"/>
                                <?php
                                $fileIndex++;
                            }
                            mysqli_stmt_close($stmt);
                        } else {
                            echo '<div>No task files attached.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submitted Files card section -->
    <div class='col mb-3'>
        <div class='row g-3'>
            <div class='col-xxl-12'>
                <div class='card h-100 h-xxl-auto mt-xxl-3'>
                    <div class='card-header d-flex bg-body-tertiary align-items-center'>
                        <i class='far fa-folder me-2 text-primary'></i>
                        <h6 class='mb-0'>Submitted Files</h6>
                    </div>
                    <div class='card-body position-relative'>
                        <div class='bg-holder bg-card d-none d-md-block'
                             style='background-image:url(../assets/img/icons/spot-illustrations/corner-7.png);'></div>
                        <?php
                        // Get submitted files from new table
                        $submittedFilesQuery = "SELECT * FROM tbl_task_files WHERE task_id = ? AND file_type = 'submitted' AND is_deleted = 0 ORDER BY upload_time ASC";
                        $stmt = mysqli_prepare($con, $submittedFilesQuery);
                        mysqli_stmt_bind_param($stmt, 'i', $taskId);
                        mysqli_stmt_execute($stmt);
                        $submittedFilesResult = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($submittedFilesResult) > 0) {
                            $fileIndex = 0;
                            while ($fileRow = mysqli_fetch_assoc($submittedFilesResult)) {
                                $fileName = $fileRow['original_file_name'];
                                $fileUrl = $fileRow['file_url'];
                                $fileSize = $fileRow['file_size'];
                                $uploadTime = $fileRow['upload_time'];
                                $formattedSize = formatFileSize($fileSize);
                                $formattedDate = date('d M Y, g:i A', strtotime($uploadTime));

                                // Determine thumbnail based on file extension
                                $thumbnailPath = '../assets/img/icons/docs.png';
                                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                                switch (strtolower($fileExtension)) {
                                    case 'pdf':
                                        $thumbnailPath = '../assets/img/icons/pdf.png';
                                        break;
                                    case 'doc':
                                    case 'docx':
                                    case 'rtf':
                                        $thumbnailPath = '../assets/img/icons/word.png';
                                        break;
                                    case 'xls':
                                    case 'xlsx':
                                    case 'csv':
                                        $thumbnailPath = '../assets/img/icons/excel.png';
                                        break;
                                    case 'ppt':
                                    case 'pptx':
                                        $thumbnailPath = '../assets/img/icons/powerpoint.png';
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
                                        $thumbnailPath = '../assets/img/icons/mp4.png';
                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif':
                                        $thumbnailPath = '../assets/img/icons/image.png';
                                        break;
                                    case 'zip':
                                    case 'rar':
                                        $thumbnailPath = '../assets/img/icons/zip.png';
                                        break;
                                    default:
                                        $thumbnailPath = '../assets/img/icons/docs.png';
                                        break;
                                }
                                ?>
                                <div class="d-flex mb-3 hover-actions-trigger align-items-center"
                                     data-file-url="<?php echo $fileUrl; ?>">
                                    <div class="file-thumbnail">
                                        <img class="border h-100 w-100 object-fit-cover rounded-2"
                                             src="<?php echo $thumbnailPath; ?>" alt=""/>
                                    </div>
                                    <div class="ms-3 flex-shrink-1 flex-grow-1">
                                        <h6 class="mb-1">
                                            <a class="stretched-link text-900 fw-semi-bold"
                                               href="<?php echo $fileUrl; ?>"><?php echo htmlspecialchars($fileName); ?></a>
                                        </h6>
                                        <div class="fs-10">
                                            <span class="fw-medium text-600"><?php echo $formattedSize; ?></span>
                                            <span class='fw-medium text-600 mx-1'>•</span>
                                            <span class="fw-medium text-600"><?php echo $formattedDate; ?></span>
                                        </div>
                                        <div class="hover-actions end-0 top-50 translate-middle-y">
                                            <button class="btn btn-tertiary border-300 btn-sm me-1 text-600"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="View File"
                                                    onclick="previewFile('<?php echo $encodedId; ?>', <?php echo $fileIndex; ?>, 'submitted')">
                                                <img src="../assets/img/icons/eye.svg" alt="" width="15"/>
                                            </button>
                                            <a class="btn btn-tertiary border-300 btn-sm me-1 text-600"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="Download"
                                               href="<?php echo $fileUrl; ?>"
                                               download="<?php echo htmlspecialchars($fileName); ?>">
                                                <img src="../assets/img/icons/cloud-download.svg" alt="" width="15"/>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <hr class="text-200"/>
                                <?php
                                $fileIndex++;
                            }
                            mysqli_stmt_close($stmt);
                        } else {
                            echo '<div>No submitted files.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Fetch all comments for this task
    $commentsQuery = 'SELECT * FROM tbl_task_comments WHERE task_id = ? ORDER BY created_at ASC';
    $stmt = $con->prepare($commentsQuery);
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $commentsResult = $stmt->get_result();
    $comments = $commentsResult->fetch_all(MYSQLI_ASSOC);
    ?>

    <div class='row'>
        <div class='col-lg-12 order-1 order-lg-0'>
            <div class='card mb-3'>
                <div class='card-header bg-body-tertiary d-flex align-items-center justify-content-between'>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-comments me-2 text-primary"></i>
                        <h6 class='mb-0'>Task Discussion</h6>
                        <span class="badge badge-subtle-info ms-2"><?php echo count($comments); ?> messages</span>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" onclick="toggleCommentForm()">
                        <i class="fas fa-plus me-1"></i>Add Comment
                    </button>
                </div>
                <div class='card-body position-relative' style="max-height: 500px; overflow-y: auto;">

                    <!-- Comment Form (Initially Hidden) -->
                    <div id="commentForm" class="mb-3" style="display: none;">
                        <form id="addCommentForm" onsubmit="addComment(event)">
                            <div class="mb-2">
                                <textarea class="form-control" id="commentText" rows="3"
                                          placeholder="Type your message here..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleCommentForm()">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Send
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Comments Thread -->
                    <div id="commentsContainer">
                        <?php if (empty($comments)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-comment-slash fa-2x mb-2"></i>
                                <p>No messages yet. Start the conversation!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item mb-3 <?php echo $comment['user_type'] === 'admin' ? 'admin-comment' : 'writer-comment'; ?>">
                                    <div class="d-flex align-items-start">
                                        <div class="notification-avatar">
                                            <div class='avatar avatar-2xl me-2'>
                                                <div class="avatar-name rounded-circle <?php echo $comment['user_type'] === 'admin' ? 'bg-primary' : 'bg-success'; ?>">
                                            <span class='fs-9 text-white'>
                                                <?php echo strtoupper(substr($comment['username'], 0, 2)); ?>
                                            </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="comment-bubble p-3 rounded-3 <?php echo $comment['user_type'] === 'admin' ? 'bg-100 border border-primary-subtle' : 'bg-200 border border-success-subtle'; ?>">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="comment-author fw-bold <?php echo $comment['user_type'] === 'admin' ? 'text-primary' : 'text-success'; ?>">
                                                        <?php echo htmlspecialchars($comment['username']); ?>
                                                        <?php if ($comment['user_type'] === 'writer' && $comment['is_read'] == 0): ?>
                                                            <span class="badge badge-subtle-danger ms-2 unread-badge" style="font-size: 0.7em;">NEW</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="fw-medium text-600 fs-10">
                                                        <?php echo date('M d, Y g:i A', strtotime($comment['created_at'])); ?>
                                                    </small>
                                                </div>
                                                <div class="comment-text fs-9">
                                                    <?php
                                                    $unescaped_comment = stripcslashes($comment['comment']);
                                                    echo nl2br(htmlspecialchars($unescaped_comment));
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- File Preview Modal -->
    <div class='modal fade' id='filePreviewModal' tabindex='-1' aria-labelledby='filePreviewModalLabel' aria-hidden='true'>
        <div class='modal-dialog modal-xl mt-6' style='max-width: 90vw;'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='filePreviewModalLabel'>itasker File Preview</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body' id='filePreviewContent'
                     style='min-height: 70vh; display: flex; justify-content: center; align-items: center;'>
                    <!-- Preview content will be injected here -->
                    <div id='previewLoading' style='text-align:center;'>
                        <div class='spinner-border text-primary' role='status'><span
                                    class='visually-hidden'>Loading...</span></div>
                        <p>Loading preview...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        function confirmDuplicate() {
            return confirm('Are you sure you want to duplicate this task?');
        }

        function viewFile(taskId, fileIndex, fileType) {
            const url = `file-viewer.php?task_id=${taskId}&file=${fileIndex}&type=${fileType}`;
            window.open(url, 'fileViewer', 'width=1000,height=700,scrollbars=yes,resizable=yes');
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

                        let toastMessage = '';
                        if (response.is_favorite == 1) {
                            favoriteIcon.classList.remove('fa-heart-broken');
                            favoriteIcon.classList.add('fa-heart');
                            favoriteText.textContent = 'Unfavorite';
                            toastMessage = 'Task added to favorites!';
                        } else {
                            favoriteIcon.classList.remove('fa-heart');
                            favoriteIcon.classList.add('fa-heart-broken');
                            favoriteText.textContent = 'Favorite';
                            toastMessage = 'Task removed from favorites!';
                        }

                        // Show success toast notification
                        showBootstrapToast(toastMessage, 'success');
                    } else {
                        showBootstrapToast('Failed to update favorite status.', 'danger');
                    }
                } else {
                    showBootstrapToast('An error occurred while updating favorite status.', 'danger');
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
                        // Show success toast before redirecting
                        showBootstrapToast('Task completed successfully!', 'success');

                        // Delay redirect to allow toast to be seen
                        setTimeout(function() {
                            window.location.href = 'view-task?task_id=' + encodedId;
                        }, 2000);
                    },
                    error: function() {
                        showBootstrapToast('An error occurred while completing the task.', 'danger');
                    }
                });
            }
        }

        // Simple Bootstrap toast function
        function showBootstrapToast(message, type = 'success') {
            // Remove any existing toast
            const existingToast = document.getElementById('dynamic-toast');
            if (existingToast) {
                existingToast.remove();
            }

            // Create the toast alert
            const toast = document.createElement('div');
            toast.id = 'dynamic-toast';
            toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    `;
            toast.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

            document.body.appendChild(toast);

            // Auto-dismiss after 4 seconds
            setTimeout(() => {
                const alert = toast.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 4000);

            // Remove toast element when alert is closed
            toast.addEventListener('closed.bs.alert', function() {
                toast.remove();
            });
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
            // Exit early if element doesn't exist
            if (!timeElement) {
                return;
            }
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
    <script>
        function previewFile(taskId, fileIndex, fileType) {
            const modalElement = document.getElementById('filePreviewModal');
            const contentContainer = document.getElementById('filePreviewContent');
            const loadingIndicator = document.getElementById('previewLoading');

            // Clean up any existing modal instance
            const existingModal = bootstrap.Modal.getInstance(modalElement);
            if (existingModal) {
                existingModal.dispose();
            }

            // Create a fresh modal instance
            const modal = new bootstrap.Modal(modalElement);

            // Reset modal content
            contentContainer.innerHTML = '';

            // Create and show loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'previewLoading';
            loadingDiv.style.textAlign = 'center';
            loadingDiv.innerHTML = `
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p>Loading preview...</p>
      `;
            contentContainer.appendChild(loadingDiv);

            // Build the file viewer URL
            const url = `file-viewer?task_id=${taskId}&file=${fileIndex}&type=${fileType}`;

            // Create iframe for preview
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.style.width = '100%';
            iframe.style.height = '70vh';
            iframe.style.border = 'none';

            // When iframe loads, hide loading spinner
            iframe.onload = () => {
                const currentLoading = document.getElementById('previewLoading');
                if (currentLoading) {
                    currentLoading.remove();
                }
            };

            // Add error handling
            iframe.onerror = () => {
                const currentLoading = document.getElementById('previewLoading');
                if (currentLoading) {
                    currentLoading.innerHTML = '<p class="text-danger">Failed to load file preview.</p>';
                }
            };

            // Add iframe to content container
            contentContainer.appendChild(iframe);

            // Clean up when modal is hidden
            modalElement.addEventListener('hidden.bs.modal', function() {
                contentContainer.innerHTML = '';
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.dispose();
                }
            }, { once: true }); // Use { once: true } to prevent multiple event listeners

            // Show modal
            modal.show();
        }
    </script>
    <script>
        function toggleCommentForm() {
            const form = document.getElementById('commentForm');
            const isVisible = form.style.display !== 'none';

            if (isVisible) {
                form.style.display = 'none';
                document.getElementById('commentText').value = '';
            } else {
                form.style.display = 'block';
                document.getElementById('commentText').focus();
            }
        }

        function addComment(event) {
            event.preventDefault();

            const commentText = document.getElementById('commentText').value.trim();
            if (!commentText) return;

            const formData = new FormData();
            formData.append('task_id', <?php echo $taskId; ?>);
            formData.append('comment', commentText);
            formData.append('action', 'add_comment');

            fetch('add-task-comment', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show the new comment
                        window.location.reload();
                    } else {
                        showBootstrapToast(data.message || 'Failed to add comment', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showBootstrapToast('An error occurred while adding the comment', 'danger');
                });
        }

        // Auto-scroll to bottom of comments on page load
        document.addEventListener('DOMContentLoaded', function() {
            const commentsContainer = document.getElementById('commentsContainer');
            if (commentsContainer && commentsContainer.children.length > 0) {
                commentsContainer.scrollTop = commentsContainer.scrollHeight;
            }
        });
    </script>
    <script>
        function markCommentsAsReadOnLoad() {
            const taskId = <?php echo $taskId; ?>;

            fetch('mark-writer-comments-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    task_id: taskId,
                    action: 'mark_writer_comments_read'
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.count > 0) {
                        setTimeout(() => {
                            updateCommentsUI();
                        }, 5000);
                    }
                })
                .catch(error => {
                    console.error('Error marking comments as read:', error);
                });
        }

        function observeCommentsSection() {
            const commentsSection = document.querySelector('#commentsContainer');

            if (commentsSection) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            markCommentsAsReadOnLoad();
                            observer.unobserve(entry.target); // Only mark once
                        }
                    });
                }, {
                    threshold: 0.9 // Trigger when 90% of comments section is visible
                });

                observer.observe(commentsSection);
            }
        }

        function updateCommentsUI() {
            // Remove "NEW" badges from admin comments with fade effect
            const unreadBadges = document.querySelectorAll('.writer-comment .unread-badge');
            unreadBadges.forEach(badge => {
                // Add fade out animation
                badge.style.transition = 'opacity 0.5s ease-out';
                badge.style.opacity = '0';

                // Remove the element after animation completes
                setTimeout(() => {
                    if (badge.parentNode) {
                        badge.remove();
                    }
                }, 500);
            });
        }
        // Call the function when page loads
        document.addEventListener('DOMContentLoaded', function() {
            observeCommentsSection();
        });
    </script>

<?php
include "footer.php";
?>
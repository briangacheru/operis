<?php
include "head.php";

if (isset($_GET['task_id'])) {
    $encodedId = $_GET['task_id'];
    $taskId = base64_decode($encodedId);

    $query = "SELECT * FROM tbltasks WHERE id = ? AND email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $taskId, $aid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['alert'] = '<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                <p class="mb-0 flex-1">Task not found or access denied!</p>
                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>';
        header("Location: all-tasks");
        exit();
    }
} else {
    $_SESSION['alert'] = '<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                            <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                            <p class="mb-0 flex-1">Invalid task ID!</p>
                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
    header("Location: all-tasks");
    exit();
}

$taskTopic = $taskSubject = $taskAccount = $taskCreatedOn = $taskStatus = $taskIsPaid = $taskIsConfirmed = $taskDescription = $taskWriter = $taskWriterEmail = $taskDueDate = $taskCPP = $taskPages = $existingFiles = $taskSubmitTime = $submittedOn = $completedOn = '';

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
    $taskIsConfirmed = $rowTask["is_confirmed"];
    $taskDescription = $rowTask["description"];
    $taskWriter = $rowTask["writer"];
    $taskWriterEmail = $rowTask["email"];
    $taskDueDate = $rowTask["due_date"];
    $taskCPP = $rowTask["cpp"];
    $taskPages = $rowTask["pages"];
    $existingFiles = $rowTask['task_files'];
    $submittedFiles = $rowTask['submitted_files'];
    $taskSubmitTime = $rowTask['submitted_on'];
    $submittedOn = $rowTask['submitted_on'];
    $completedOn = $rowTask['completed_on'];
}
$due_date = new DateTime($rowTask['due_date']);
$currentDateTime = new DateTime();
$interval = $currentDateTime->diff($due_date);
$isLate = ($due_date < $currentDateTime) ? true : false;
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
$is_paid = $rowTask['is_paid'];

$statusBadgeClass = ($is_paid == 1) ? 'bg-success' : 'bg-warning';
$statusBadgeText = ($is_paid == 1) ? 'Paid' : 'Unpaid';
$statusBadgePay = "<span class='badge $statusBadgeClass'>$statusBadgeText</span>";

$is_confirmed = $rowTask['is_confirmed'];
$confirmationClass = ($is_confirmed == 0) ? 'bg-light' : 'bg-primary';
$confirmationText = ($is_confirmed == 0) ? 'Confirmed' : 'Unconfirmed';
$confirmation = "<span class='badge $confirmationClass'>$confirmationText</span>";

?>

    <title>View Task #<?php  echo $taskId;?> | iTasker</title>
<?php include "navi.php";?>

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
                            <h6 class="mb-1 badge rounded-pill badge-subtle-info"><?php echo date("jS F Y"); ?> | <span id="timeDisplay"></span></h6>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_GET['message'])) {
        $message = htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8');
        echo "
        <div class='alert alert-success border-0 d-flex align-items-center' role='alert'>
        <div class='bg-success me-3 icon-item'><span class='fas fa-check-circle text-white fs-6'></span></div>
        <p class='mb-0 flex-1'>$message</p>
        <button class='btn-close' type='button' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
        <script>
        // Remove the message parameter from URL after displaying
        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('message');
            window.history.replaceState({}, document.title, url.toString());
        }
        
        // Auto-hide alert after 5 seconds
        setTimeout(function() {
            var alertElement = document.querySelector('.alert-success');
            if (alertElement) {
                alertElement.classList.add('fade');
                alertElement.addEventListener('transitionend', function() {
                    alertElement.remove();
                });
            }
        }, 5000);
        </script>
        ";
    }
    ?>

    <div class="card mb-3">
    <div class="bg-holder bg-card" style="background-image:url(assets/img/icons/spot-illustrations/corner-5.png);">
        </div>

        <div class="card-body position-relative">
            <div class="row g-2 align-items-sm-center">
                <div class="col-auto"><div class="calendar me-2">
                            <span class="calendar-month">
                                <?php
                                $currentMonth = date('M');
                                $currentDay = date('d');
                                echo $currentMonth;?>
                            </span>
                        <span class="calendar-day"><?php echo $currentDay; ?> </span>
                    </div>
                </div>
                    <div class="col">
                    <div class="row align-items-center">
                        <div class="col col-lg-8">
                            <h5 class="mb-sm-0 text-primary fs-7">Task ID: <span class="text-info fw-medium">#<?php  echo $taskId;?></span></h5>
                            <p class="fw-semi-bold fs-10"><span class="me-1">Posted: </span><span class="text-info ms-2"><?php  echo date("d M Y, g:i A", strtotime($taskCreatedOn));?></span>
                            </p>
                            <div class="fs-9 mb-3 mb-sm-0 text-primary">
                                <strong class="me-2">Status: </strong><?php echo $statusBadge; ?>
                                <?php if ($taskStatus == 'Submitted' && !empty($submittedOn)): ?>
                                    <span class="fs-10 text-info ms-2"><?php echo date("d M Y, g:i A", strtotime($submittedOn)); ?></span>
                                <?php elseif ($taskStatus == 'Completed' && !empty($completedOn)): ?>
                                    <span class="fs-10 text-success ms-2"><?php echo date("d M Y, g:i A", strtotime($completedOn)); ?></span>
                                <?php endif; ?>
                                <?php if ($is_confirmed == 1): ?>
                                    <?php echo $confirmation; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto ms-auto">
                            <?php if ($taskStatus == 'In Progress'): ?>
                                <a class="btn btn-outline-primary btn-lg fs-9" href="submission?task_id=<?php $encodedId = $_GET['task_id']; echo $encodedId; ?>#filesSubmission">
                                    <i class="fas fa-upload me-1"></i> Submit Task
                                </a>
                            <?php elseif ($is_confirmed == 1 && $taskStatus != 'Cancelled'): ?>
                                <a class="btn btn-outline-success btn-sm fs-10" href="#" onclick="confirmAction('<?php $encodedId = $_GET['task_id']; echo $encodedId; ?>', 'accept')">
                                    <i class="fas fa-check me-1"></i> Accept Task
                                </a>
                                <a class="btn btn-outline-danger btn-sm fs-10" href="#" onclick="confirmAction('<?php $encodedId = $_GET['task_id']; echo $encodedId; ?>', 'decline')">
                                    <i class="fas fa-times me-1"></i> Decline Task
                                </a>
                            <?php elseif ($taskStatus == 'Submitted' || $taskStatus == 'In Revision'): ?>
                                <a class="btn btn-outline-primary btn-lg fs-9" href="#" onclick="confirmAction('<?php $encodedId = $_GET['task_id']; echo $encodedId; ?>', 'resubmit')">
                                    <i class="fas fa-sync-alt me-1"></i> Resubmit Task
                                </a>
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
                                            <span class="badge rounded-pill badge-subtle-warning fs-10 fw-semi-bold">
                                                Unpaid
                                            </span>
                                        <?php else: ?>
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
                            <span class="fas fa-user text-white me-1"> </span><?php  echo $taskWriter;?></span>
                        <span class="badge rounded-pill badge-subtle-dark border border-300 text-info py-2 px-3">
                            <span class="fas fa-file text-white me-1"> </span><?php  echo $taskPages;?> Pages</span>
                        <span class="badge rounded-pill badge-subtle-dark border border-300 text-info py-2 px-3">
                            <span class="fas fa-credit-card text-white me-1"> </span>Ksh. <?php  echo $taskCPP;?> CPP</span>
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
                    <h6 class='mb-0'>Description</h6>
                </div>
                <div class="card-body position-relative">
                    <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/icons/spot-illustrations/corner-1.png);">
                    </div>
                    <ul class="list-unstyled position-relative fs-9 p-0 m-0">
                        <li class="mb-2">
                            <div class="d-flex">
                                <dd>
                                    <?php
                                    $cleanText = stripslashes($taskDescription);
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
                             style='background-image:url(assets/img/icons/spot-illustrations/corner-2.png);'>
                        </div>
                        <?php
                        $taskFilesQuery = 'SELECT * FROM tbl_task_files WHERE task_id = ? AND file_type = "task" AND is_deleted = 0 ORDER BY upload_time ASC';
                        $stmt = mysqli_prepare($con, $taskFilesQuery);
                        mysqli_stmt_bind_param($stmt, 'i', $taskId);
                        mysqli_stmt_execute($stmt);
                        $taskFilesResult = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($taskFilesResult) > 0) {
                            while ($fileRow = mysqli_fetch_assoc($taskFilesResult)) {
                                $fileName = $fileRow['original_file_name'];
                                $fileUrl = $fileRow['file_url'];
                                $fileSize = $fileRow['file_size'];
                                $uploadTime = $fileRow['upload_time'];
                                $formattedDate = date('d M Y, g:i A', strtotime($uploadTime));

                                $formattedSize = 'Unknown size';
                                if ($fileSize > 0) {
                                    $units = ['B', 'KB', 'MB', 'GB'];
                                    $power = $fileSize > 0 ? floor(log($fileSize, 1024)) : 0;
                                    $formattedSize = round($fileSize / pow(1024, $power), 2) . ' ' . $units[$power];
                                }

                                $thumbnailPath = 'assets/img/icons/docs.png';
                                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                                switch (strtolower($fileExtension)) {
                                    case 'pdf':
                                        $thumbnailPath = 'assets/img/icons/pdf.png';
                                        break;
                                    case 'doc';
                                    case 'docx':
                                    case 'rtf':
                                        $thumbnailPath = 'assets/img/icons/word.png';
                                        break;
                                    case 'xls':
                                    case 'xlsx':
                                    case 'csv':
                                        $thumbnailPath = 'assets/img/icons/excel.png';
                                        break;
                                    case 'ppt':
                                    case 'pptx':
                                        $thumbnailPath = 'assets/img/icons/powerpoint.png';
                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif':
                                        $thumbnailPath = 'assets/img/icons/image.png';
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
                                        $thumbnailPath = 'assets/img/icons/video.png';
                                        break;
                                    case 'zip':
                                    case 'rar':
                                        $thumbnailPath = 'assets/img/icons/zip.png';
                                        break;
                                    default:
                                        $thumbnailPath = 'assets/img/icons/docs.png';
                                        break;
                                }
                                ?>
                                <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                    <div class="file-thumbnail"><img
                                                class="border h-100 w-100 object-fit-cover rounded-2"
                                                src="<?php echo $thumbnailPath; ?>" alt=""/></div>
                                    <div class="ms-3 flex-shrink-1 flex-g$rowTask-1">
                                        <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold"
                                                            href="<?php echo $fileUrl; ?>"
                                                            target="_blank"><?php echo $fileName; ?></a></h6>
                                        <div class="fs-10">
                                            <span class='fw-medium text-600'><?php echo $formattedSize; ?></span>
                                            <span class='fw-medium text-600 mx-1'>•</span>
                                            <span class="fw-medium text-600"><?php echo $formattedDate; ?></span>
                                        </div>
                                        <div class="hover-actions end-0 top-50 translate-middle-y">
                                            <a class="btn btn-tertiary border-300 btn-sm me-1 text-600"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="Download"
                                               href="<?php echo $fileUrl; ?>" download="<?php echo $fileName; ?>"><img
                                                        src="assets/img/icons/cloud-download.svg" alt=""
                                                        width="15"/></a>
                                        </div>
                                    </div>
                                </div>
                                <hr class="text-200"/>
                                <?php
                            }
                        } else {
                            echo '<div>No task files attached.</div>';
                        }
                        mysqli_stmt_close($stmt);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                             style='background-image:url(assets/img/icons/spot-illustrations/corner-7.png);'>
                        </div>
                        <?php
                        $submittedFilesQuery = 'SELECT * FROM tbl_task_files WHERE task_id = ? AND file_type = "submitted" AND is_deleted = 0 ORDER BY upload_time ASC';
                        $stmt = mysqli_prepare($con, $submittedFilesQuery);
                        mysqli_stmt_bind_param($stmt, 'i', $taskId);
                        mysqli_stmt_execute($stmt);
                        $submittedFilesResult = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($submittedFilesResult) > 0) {
                            while ($fileRow = mysqli_fetch_assoc($submittedFilesResult)) {
                                $fileName = $fileRow['original_file_name'];
                                $fileUrl = $fileRow['file_url'];
                                $fileSize = $fileRow['file_size'];
                                $uploadTime = $fileRow['upload_time'];
                                $formattedDate = date('d M Y, g:i A', strtotime($uploadTime));

                                $formattedSize = 'Unknown size';
                                if ($fileSize > 0) {
                                    $units = ['B', 'KB', 'MB', 'GB'];
                                    $power = $fileSize > 0 ? floor(log($fileSize, 1024)) : 0;
                                    $formattedSize = round($fileSize / pow(1024, $power), 2) . ' ' . $units[$power];
                                }

                                $thumbnailPath = 'assets/img/icons/docs.png';
                                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                                switch (strtolower($fileExtension)) {
                                    case 'pdf':
                                        $thumbnailPath = 'assets/img/icons/pdf.png';
                                        break;
                                    case 'doc';
                                    case 'docx':
                                    case 'rtf':
                                        $thumbnailPath = 'assets/img/icons/word.png';
                                        break;
                                    case 'xls':
                                    case 'xlsx':
                                    case 'csv':
                                        $thumbnailPath = 'assets/img/icons/excel.png';
                                        break;
                                    case 'ppt':
                                    case 'pptx':
                                        $thumbnailPath = 'assets/img/icons/powerpoint.png';
                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif':
                                        $thumbnailPath = 'assets/img/icons/image.png';
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
                                        $thumbnailPath = 'assets/img/icons/video.png';
                                        break;
                                    case 'zip':
                                    case 'rar':
                                        $thumbnailPath = 'assets/img/icons/zip.png';
                                        break;
                                    default:
                                        $thumbnailPath = 'assets/img/icons/docs.png';
                                        break;
                                }
                                ?>
                                <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                    <div class="file-thumbnail"><img
                                                class="border h-100 w-100 object-fit-cover rounded-2"
                                                src="<?php echo $thumbnailPath; ?>" alt=""/></div>
                                    <div class="ms-3 flex-shrink-1 flex-grow-1">
                                        <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold"
                                                            href="<?php echo $fileUrl; ?>"
                                                            target="_blank"><?php echo $fileName; ?></a></h6>
                                        <div class="fs-10">
                                            <span class='fw-medium text-600'><?php echo $formattedSize; ?>
                                                <span class='fw-medium text-600 mx-1'>•</span>
                                                <span class="fw-medium text-600"><?php echo $formattedDate; ?></span>
                                        </div>
                                        <div class="hover-actions end-0 top-50 translate-middle-y">
                                            <a class="btn btn-tertiary border-300 btn-sm me-1 text-600"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="Download"
                                               href="<?php echo $fileUrl; ?>" download="<?php echo $fileName; ?>"><img
                                                        src="assets/img/icons/cloud-download.svg" alt=""
                                                        width="15"/></a>
                                        </div>
                                    </div>
                                </div>
                                <hr class="text-200"/>
                                <?php
                            }
                        } else {
                            echo '<div>No submitted files.</div>';
                        }
                        mysqli_stmt_close($stmt);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
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
                    <?php if ($taskStatus !== 'Completed' && $taskStatus !== 'Cancelled'): ?>
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleCommentForm()">
                            <i class="fas fa-plus me-1"></i>Add Comment
                        </button>
                    <?php endif; ?>
                </div>
                <div class='card-body position-relative' style="max-height: 500px; overflow-y: auto;">
                    <?php if ($taskStatus !== 'Completed' && $taskStatus !== 'Cancelled'): ?>
                        <div id="commentForm" class="mb-3" style="display: none;">
                            <form id="addCommentForm" onsubmit="addComment(event)">
                                <div class="mb-2">
                                    <textarea class="form-control" id="commentText" rows="3"
                                              placeholder="Type your message here..." required></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-secondary"
                                            onclick="toggleCommentForm()">Cancel
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i>Send
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                    <div id="commentsContainer">
                        <?php if (empty($comments)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-comment-slash fa-2x mb-2"></i>
                                <p>No messages yet. Start the conversation!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item mb-3 <?php echo $comment['user_type'] === 'admin' ? 'admin-comment' : 'writer-comment'; ?>"
                                     data-comment-id="<?php echo $comment['id']; ?>">
                                    <div class="d-flex align-items-start">
                                        <div class="notification-avatar">
                                            <div class='avatar avatar-2xl me-2'>
                                                <div class="avatar-name rounded-circle <?php echo $comment['user_type'] === 'admin' ? 'bg-success' : 'bg-primary'; ?>">
                                                    <span class='fs-9 text-white'>
                                                        <?php echo strtoupper(substr($comment['username'], 0, 2)); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="comment-bubble p-3 rounded-3 <?php echo $comment['user_type'] === 'admin' ? 'bg-200 border border-success-subtle' : 'bg-100 border border-primary-subtle'; ?>">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="comment-author fw-bold <?php echo $comment['user_type'] === 'admin' ? 'text-success' : 'text-primary'; ?>">
                                                        <?php echo htmlspecialchars($comment['username']); ?>
                                                        <?php if ($comment['user_type'] === 'admin' && $comment['is_read'] == 0): ?>
                                                            <span class="badge badge-subtle-danger ms-2 unread-badge" style="font-size: 0.7em;">NEW</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="fw-medium text-600">
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

    <script>
        function markCommentsAsReadOnLoad() {
            const taskId = <?php echo $taskId; ?>;

            fetch('mark-admin-comments-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    task_id: taskId,
                    action: 'mark_admin_comments_read'
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.count > 0) {
                        //console.log(`Marked ${data.count} admin comments as read`);
                        // Update UI to remove unread indicators after 5 seconds
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
                    threshold: 0.9
                });

                observer.observe(commentsSection);
            }
        }

        function updateCommentsUI() {
            // Remove "NEW" badges from admin comments with fade effect
            const unreadBadges = document.querySelectorAll('.admin-comment .unread-badge');
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

        // Rest of your existing functions...
        function confirmAction(taskId, action) {
            if (action === 'accept' || action === 'decline') {
                let actionText = action === 'accept' ? 'accept' : 'decline';
                if (confirm(`Are you sure you want to ${actionText} this task?`)) {
                    window.location.href = `confirmation?task_id=${taskId}&action=${action}`;
                }
            } else if (action === 'resubmit') {
                if (confirm('Are you sure you want to resubmit this task?')) {
                    window.location.href = `resubmission?task_id=${taskId}#filesResubmission`;
                }
            }
        }

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
                        alert(data.message || 'Failed to add comment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the comment');
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
        document.addEventListener("DOMContentLoaded", function() {
            const timeElement = document.getElementById('time-remaining');
            let remainingSeconds = <?= $remainingSeconds ?>;

            function updateTime() {
                if (!timeElement) return;
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
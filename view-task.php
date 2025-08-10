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
// Fetch all comments for this task
$commentsQuery = 'SELECT * FROM tbl_task_comments WHERE task_id = ? ORDER BY created_at ASC';
$stmt = $con->prepare($commentsQuery);
$stmt->bind_param('i', $taskId);
$stmt->execute();
$commentsResult = $stmt->get_result();
$comments = $commentsResult->fetch_all(MYSQLI_ASSOC);

// Count unread messages (assuming writer is viewing - count unread writer messages)
$unreadQuery = 'SELECT COUNT(*) as unread_count FROM tbl_task_comments WHERE task_id = ? AND user_type = ? AND is_read = 0';
$unreadStmt = $con->prepare($unreadQuery);

// Determine which messages to count as unread based on current user type
if (isset($_SESSION['odmsaid'])) {
    // Admin viewing - count unread writer messages
    $countUserType = 'writer';
} else {
    // Writer viewing - count unread admin messages
    $countUserType = 'admin';
}

$unreadStmt->bind_param('is', $taskId, $countUserType);
$unreadStmt->execute();
$unreadResult = $unreadStmt->get_result();
$unreadData = $unreadResult->fetch_assoc();
$unreadCount = $unreadData['unread_count'];

// Determine conversation status based on task status
$conversationStatus = 'Active conversation';
$statusIcon = 'fa-comment-dots';
$statusClass = 'text-success';

if (in_array($taskStatus, ['Completed', 'Cancelled'])) {
    $conversationStatus = 'Closed conversation';
    $statusIcon = 'fa-comment-slash';
    $statusClass = 'text-muted';
} elseif ($taskStatus === 'Draft') {
    $conversationStatus = 'Draft conversation';
    $statusIcon = 'fa-comment-alt';
    $statusClass = 'text-warning';
} elseif ($taskStatus === 'In Revision') {
    $conversationStatus = 'Under review';
    $statusIcon = 'fa-comment-medical';
    $statusClass = 'text-info';
}
?>
<?php
// Function to get user online status and last seen
function getUserOnlineStatus($con, $userType, $username, $userEmail = null) {
    $onlineData = [
        'is_online' => 0,
        'last_seen' => null,
        'status_text' => 'Offline',
        'status_class' => 'bg-secondary'
    ];

    if ($userType === 'admin') {
        // Check admin online status
        $adminQuery = "SELECT is_online, last_seen FROM tbladmin WHERE email = ? OR username = ? LIMIT 1";
        if ($stmt = mysqli_prepare($con, $adminQuery)) {
            $emailToCheck = $userEmail ?: $username;
            mysqli_stmt_bind_param($stmt, 'ss', $emailToCheck, $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $onlineData['is_online'] = $row['is_online'];
                $onlineData['last_seen'] = $row['last_seen'];
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // Check writer online status
        $writerQuery = "SELECT is_online, last_seen FROM tblwriters WHERE username = ? OR email = ? LIMIT 1";
        if ($stmt = mysqli_prepare($con, $writerQuery)) {
            $emailToCheck = $userEmail ?: $username;
            mysqli_stmt_bind_param($stmt, 'ss', $username, $emailToCheck);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $onlineData['is_online'] = $row['is_online'];
                $onlineData['last_seen'] = $row['last_seen'];
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Determine status based on online flag and last seen
    if ($onlineData['is_online'] == 1) {
        $onlineData['status_text'] = 'Online';
        $onlineData['status_class'] = 'bg-success';
    } else if ($onlineData['last_seen']) {
        $lastSeenTime = new DateTime($onlineData['last_seen']);
        $currentTime = new DateTime();
        $timeDiff = $currentTime->diff($lastSeenTime);

        // Calculate time difference
        if ($timeDiff->days > 0) {
            $onlineData['status_text'] = $timeDiff->days == 1 ? '1 day ago' : $timeDiff->days . ' days ago';
            $onlineData['status_class'] = 'bg-secondary';
        } else if ($timeDiff->h > 0) {
            $onlineData['status_text'] = $timeDiff->h == 1 ? '1 hour ago' : $timeDiff->h . ' hours ago';
            $onlineData['status_class'] = 'bg-warning';
        } else if ($timeDiff->i > 0) {
            $onlineData['status_text'] = $timeDiff->i == 1 ? '1 minute ago' : $timeDiff->i . ' minutes ago';
            $onlineData['status_class'] = 'bg-info';
        } else {
            $onlineData['status_text'] = 'Just now';
            $onlineData['status_class'] = 'bg-success';
        }
    } else {
        $onlineData['status_text'] = 'Unknown';
        $onlineData['status_class'] = 'bg-secondary';
    }

    return $onlineData;
}

// Cache online status for all users in the conversation to avoid repeated queries
$userOnlineStatuses = [];
$userEmails = []; // Cache emails for profile image lookup

foreach ($comments as $comment) {
    $userKey = $comment['user_type'] . '_' . $comment['username'];

    if (!isset($userOnlineStatuses[$userKey])) {
        // Get actual user email and data based on user type
        $userEmailForStatus = null;

        if ($comment['user_type'] === 'writer') {
            // Get writer email from writers table first, then fallback to tasks
            $writerEmailQuery = "SELECT email FROM tblwriters WHERE username = ? LIMIT 1";
            if ($writerEmailStmt = mysqli_prepare($con, $writerEmailQuery)) {
                mysqli_stmt_bind_param($writerEmailStmt, 's', $comment['username']);
                mysqli_stmt_execute($writerEmailStmt);
                mysqli_stmt_bind_result($writerEmailStmt, $userEmailForStatus);
                mysqli_stmt_fetch($writerEmailStmt);
                mysqli_stmt_close($writerEmailStmt);
            }

            // Fallback: try to get from tasks table if not found in writers
            if (!$userEmailForStatus) {
                $taskEmailQuery = "SELECT email FROM tbltasks WHERE writer = ? LIMIT 1";
                if ($taskEmailStmt = mysqli_prepare($con, $taskEmailQuery)) {
                    mysqli_stmt_bind_param($taskEmailStmt, 's', $comment['username']);
                    mysqli_stmt_execute($taskEmailStmt);
                    mysqli_stmt_bind_result($taskEmailStmt, $userEmailForStatus);
                    mysqli_stmt_fetch($taskEmailStmt);
                    mysqli_stmt_close($taskEmailStmt);
                }
            }
        } else {
            // For admin, get email from admin table using the username from comments
            $adminEmailQuery = "SELECT email FROM tbladmin WHERE username = ? OR AdminName = ? OR CONCAT(FirstName, ' ', LastName) = ? LIMIT 1";
            if ($adminEmailStmt = mysqli_prepare($con, $adminEmailQuery)) {
                $fullName = $comment['username']; // In case the username is actually the full name
                mysqli_stmt_bind_param($adminEmailStmt, 'sss', $comment['username'], $comment['username'], $fullName);
                mysqli_stmt_execute($adminEmailStmt);
                mysqli_stmt_bind_result($adminEmailStmt, $userEmailForStatus);
                mysqli_stmt_fetch($adminEmailStmt);
                mysqli_stmt_close($adminEmailStmt);
            }
        }

        // Cache the email for profile image lookup
        $userEmails[$userKey] = $userEmailForStatus;

        // Get online status
        $userOnlineStatuses[$userKey] = getUserOnlineStatus($con, $comment['user_type'], $comment['username'], $userEmailForStatus);
    }
}
?>

    <!-- Task Discussion Card -->
    <div class='row'>
        <div class='col-lg-12 order-1 order-lg-0'>
            <div class='card mb-3 shadow-sm border-0'>
                <!-- Gradient Header -->
                <div class='card-header position-relative overflow-hidden' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;'>
                    <div class="position-absolute top-0 start-0 w-100 h-100 grain-overlay"></div>
                    <div class="position-relative d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3 p-2 rounded-circle bg-white bg-opacity-25">
                                <i class="fas fa-comments text-white fs-5"></i>
                            </div>
                            <div>
                                <h6 class='mb-0 text-white fw-bold'>Task Discussion</h6>
                                <small class="text-white-50">
                                    <i class="fas <?php echo $statusIcon; ?> me-1"></i>
                                    <?php if ($unreadCount > 0): ?>
                                        <span class="badge bg-danger me-2 pulse-animation" style="font-size: 10px;">
                                        <?php echo $unreadCount; ?> unread
                                    </span>
                                    <?php else: ?>
                                        <?php echo count($comments); ?> messages •
                                    <?php endif; ?>
                                    <span class="<?php echo $statusClass; ?>"><?php echo $conversationStatus; ?></span>
                                </small>
                            </div>
                        </div>

                        <!-- Conditional Add Message Button -->
                        <?php if (!in_array($taskStatus, ['Completed', 'Cancelled'])): ?>
                            <button class="btn btn-light btn-sm shadow-sm hover-lift" onclick="toggleCommentForm()" style="transition: all 0.3s ease;">
                                <i class="fas fa-plus me-1"></i>Add Message
                            </button>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-75 text-white px-3 py-2">
                            <i class="fas fa-lock me-1"></i>Conversation Closed
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Enhanced Card Body -->
                <div class='card-body p-0'>

                    <!-- Conditional Comment Form - Only show if conversation is active -->
                    <?php if (!in_array($taskStatus, ['Completed', 'Cancelled'])): ?>
                        <div id="commentForm" class="position-sticky top-0 bg-white shadow-sm border-bottom" style="display: none; z-index: 10;">
                            <div class="p-3">
                                <form id="addCommentForm" onsubmit="addComment(event)">
                                    <div class="mb-3">
                                        <label class="form-label text-muted fw-medium">
                                            <i class="fas fa-edit me-1"></i>Write your message
                                        </label>
                                        <textarea class="form-control border-0 shadow-sm" id="commentText" rows="4"
                                                  placeholder="Share your thoughts, ask questions, or provide updates..."
                                                  style="resize: none; background: #f8f9fc; transition: all 0.3s ease;"
                                                  onfocus="this.style.background='#ffffff'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                                                  onblur="this.style.background='#f8f9fc'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)'"
                                                  required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Press Ctrl+Enter to send quickly
                                        </small>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleCommentForm()">
                                                <i class="fas fa-times me-1"></i>Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                                                <i class="fas fa-paper-plane me-1"></i>Send Message
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Comments Container -->
                    <div id="commentsContainer" class="px-3" style="max-height: 600px; overflow-y: auto;">
                        <?php if (empty($comments)): ?>
                            <!-- Empty State -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px;">
                                        <i class="fas fa-comment-alt text-muted fa-2x"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted mb-2">No messages yet</h6>
                                <?php if (!in_array($taskStatus, ['Completed', 'Cancelled'])): ?>
                                    <p class="text-muted small mb-3">Start the conversation by sharing your thoughts or asking questions.</p>
                                    <button class="btn btn-outline-primary btn-sm" onclick="toggleCommentForm()">
                                        <i class="fas fa-plus me-1"></i>Write first message
                                    </button>
                                <?php else: ?>
                                    <p class="text-muted small">This conversation is closed as the task has been <?php echo strtolower($taskStatus); ?>.</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- Unread Messages Summary -->
                            <?php if ($unreadCount > 0): ?>
                                <div class="unread-summary">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-bell text-danger me-2"></i>
                                        <strong class="text-danger">You have <?php echo $unreadCount; ?> unread message<?php echo $unreadCount > 1 ? 's' : ''; ?></strong>
                                        <button class="btn btn-sm btn-outline-danger ms-auto" onclick="scrollToFirstUnread()">
                                            <i class="fas fa-arrow-down me-1"></i>Jump to first unread
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Messages Thread -->
                            <div class="py-3">
                                <?php foreach ($comments as $index => $comment): ?>
                                    <?php
                                    $isAdmin = $comment['user_type'] === 'admin';
                                    $isLastMessage = $index === count($comments) - 1;

                                    // Check if this is the first unread message for scroll targeting
                                    $isFirstUnread = false;
                                    if (isset($_SESSION['odmsaid']) && $comment['user_type'] === 'writer' && $comment['is_read'] == 0) {
                                        // Check if this is the first unread writer message for admin
                                        $prevUnreadCheck = array_slice($comments, 0, $index);
                                        $hasUnreadBefore = false;
                                        foreach ($prevUnreadCheck as $prevComment) {
                                            if ($prevComment['user_type'] === 'writer' && $prevComment['is_read'] == 0) {
                                                $hasUnreadBefore = true;
                                                break;
                                            }
                                        }
                                        $isFirstUnread = !$hasUnreadBefore;
                                    } elseif (isset($_SESSION['sessionWriter']) && $comment['user_type'] === 'admin' && $comment['is_read'] == 0) {
                                        // Check if this is the first unread admin message for writer
                                        $prevUnreadCheck = array_slice($comments, 0, $index);
                                        $hasUnreadBefore = false;
                                        foreach ($prevUnreadCheck as $prevComment) {
                                            if ($prevComment['user_type'] === 'admin' && $prevComment['is_read'] == 0) {
                                                $hasUnreadBefore = true;
                                                break;
                                            }
                                        }
                                        $isFirstUnread = !$hasUnreadBefore;
                                    }
                                    ?>

                                    <div class="comment-item mb-4 <?php echo $isAdmin ? 'admin-comment' : 'writer-comment'; ?> animate-fade-in"
                                         style="animation-delay: <?php echo $index * 0.1; ?>s;"
                                         <?php if ($isFirstUnread): ?>id="first-unread-message"<?php endif; ?>>

                                        <div class="d-flex align-items-start <?php echo $isAdmin ? '' : 'flex-row-reverse'; ?>" style="gap: 12px;">
                                            <!-- Enhanced Avatar -->
                                            <div class="notification-avatar flex-shrink-0" style="width: 45px;">
                                                <?php
                                                $userKey = $comment['user_type'] . '_' . $comment['username'];
                                                $onlineStatus = $userOnlineStatuses[$userKey];

                                                // Determine status class based on online status
                                                $statusClass = '';
                                                if ($onlineStatus['is_online'] == 1) {
                                                    $statusClass = 'status-online';
                                                } else if ($onlineStatus['last_seen']) {
                                                    $lastSeenTime = new DateTime($onlineStatus['last_seen']);
                                                    $currentTime = new DateTime();
                                                    $timeDiff = $currentTime->diff($lastSeenTime);

                                                    if ($timeDiff->days > 0) {
                                                        $statusClass = 'status-offline';
                                                    } else if ($timeDiff->h > 0) {
                                                        $statusClass = 'status-away';
                                                    } else if ($timeDiff->i <= 5) {
                                                        $statusClass = 'status-idle';
                                                    } else {
                                                        $statusClass = 'status-away';
                                                    }
                                                } else {
                                                    $statusClass = 'status-offline';
                                                }
                                                ?>

                                                <div class='align-items-center avatar avatar-xl  <?php echo $statusClass; ?>'
                                                     data-bs-toggle="tooltip"
                                                     data-bs-placement="top"
                                                     data-bs-html="true"
                                                     title="<strong><?php echo htmlspecialchars($comment['username']); ?></strong><br>
                                                            Status: <?php echo $onlineStatus['status_text']; ?>
                                                            <?php if ($onlineStatus['last_seen'] && $onlineStatus['is_online'] == 0): ?>
                                                                <br><small>Last seen: <?php echo date('M j, Y g:i A', strtotime($onlineStatus['last_seen'])); ?></small>
                                                            <?php endif; ?>">

                                                    <!-- Check if user has profile image -->
                                                    <?php
                                                    $profileImage = null;
                                                    $userKey = $comment['user_type'] . '_' . $comment['username'];
                                                    $userEmailForStatus = $userEmails[$userKey] ?? null;

                                                    // Try to get profile image based on user type
                                                    if ($comment['user_type'] === 'admin') {
                                                        $imgQuery = "SELECT Photo FROM tbladmin WHERE username = ? OR AdminName = ? OR email = ? OR CONCAT(FirstName, ' ', LastName) = ? LIMIT 1";
                                                        if ($imgStmt = mysqli_prepare($con, $imgQuery)) {
                                                            $emailToCheck = $userEmailForStatus ?: $comment['username'];
                                                            $fullName = $comment['username']; // In case username is actually full name
                                                            mysqli_stmt_bind_param($imgStmt, 'ssss', $comment['username'], $comment['username'], $emailToCheck, $fullName);
                                                            mysqli_stmt_execute($imgStmt);
                                                            mysqli_stmt_bind_result($imgStmt, $profileImage);
                                                            mysqli_stmt_fetch($imgStmt);
                                                            mysqli_stmt_close($imgStmt);
                                                        }
                                                    } else {
                                                        $imgQuery = "SELECT Photo FROM tblwriters WHERE username = ? OR email = ? LIMIT 1";
                                                        if ($imgStmt = mysqli_prepare($con, $imgQuery)) {
                                                            $emailToCheck = $userEmailForStatus ? $userEmailForStatus : $comment['username'];
                                                            mysqli_stmt_bind_param($imgStmt, 'ss', $comment['username'], $emailToCheck);
                                                            mysqli_stmt_execute($imgStmt);
                                                            mysqli_stmt_bind_result($imgStmt, $profileImage);
                                                            mysqli_stmt_fetch($imgStmt);
                                                            mysqli_stmt_close($imgStmt);
                                                        }
                                                    }

                                                    // Check if profile image exists and is accessible
                                                    $imageExists = false;
                                                    if ($profileImage) {
                                                        $imagePath = "profileimages/" . $profileImage;
                                                        if (file_exists($imagePath)) {
                                                            $imageExists = true;
                                                        }
                                                    }
                                                    ?>

                                                    <?php if ($imageExists): ?>
                                                        <!-- User has profile image -->
                                                        <img class="rounded-circle" src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($comment['username']); ?>"
                                                             style="width: 45px; height: 45px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <!-- Fallback to initials -->
                                                        <div class="avatar-name rounded-circle shadow-sm <?php echo $isAdmin ? 'bg-primary' : 'bg-success'; ?>"
                                                             style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                                                <span class='fw-bold text-white' style="font-size: 14px;">
                                                                    <?php echo strtoupper(substr($comment['username'], 0, 2)); ?>
                                                                </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Enhanced Message Bubble -->
                                            <div class="flex-1" style="min-width: 0; max-width: calc(100% - 60px);">
                                                <div class="comment-bubble position-relative p-3 shadow-sm <?php echo $isAdmin ? 'bg-primary-subtle border border-primary-subtle' : 'bg-success-subtle border border-success-subtle'; ?>"
                                                     style="border-radius: <?php echo $isAdmin ? '20px 20px 20px 5px' : '20px 20px 5px 20px'; ?>; transition: all 0.3s ease; word-wrap: break-word;">

                                                    <!-- Message Header -->
                                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                                        <div class="comment-author fw-bold <?php echo $isAdmin ? 'text-primary' : 'text-success'; ?>" style="font-size: 13px;">
                                                            <i class="fas <?php echo $isAdmin ? 'fa-user-shield' : 'fa-user-edit'; ?> me-1" style="font-size: 11px;"></i>
                                                            <?php echo htmlspecialchars($comment['username']); ?>

                                                            <?php
                                                            // Show unread indicator based on current user type
                                                            $showUnreadBadge = false;
                                                            if (isset($_SESSION['odmsaid']) && $comment['user_type'] === 'writer' && $comment['is_read'] == 0) {
                                                                // Admin viewing unread writer message
                                                                $showUnreadBadge = true;
                                                            } elseif (isset($_SESSION['sessionWriter']) && $comment['user_type'] === 'admin' && $comment['is_read'] == 0) {
                                                                // Writer viewing unread admin message
                                                                $showUnreadBadge = true;
                                                            }

                                                            if ($showUnreadBadge): ?>
                                                                <span class="badge bg-danger ms-2 pulse-animation unread-message-badge" style="font-size: 9px; animation: pulse 2s infinite;">
                                                                <i class="fas fa-envelope me-1"></i>UNREAD
                                                            </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Enhanced Timestamp -->
                                                        <div class="d-flex align-items-center flex-shrink-0">
                                                            <small class="fw-medium text-muted d-flex align-items-center" style="font-size: 11px;">
                                                                <i class="far fa-clock me-1"></i>
                                                                <?php echo date('M d, g:i A', strtotime($comment['created_at'])); ?>

                                                                <!-- Read Status Ticks -->
                                                                <span class="ms-2">
                                                                   <?php if ($comment['is_read'] == 1): ?>
                                                                       <!-- Double tick for read messages -->
                                                                       <i class="fas fa-check-double text-primary" title="Read" style="font-size: 10px;"></i>
                                                                   <?php else: ?>
                                                                       <!-- Single tick for unread messages -->
                                                                       <i class="fas fa-check text-muted" title="Delivered" style="font-size: 10px;"></i>
                                                                   <?php endif; ?>
                                                               </span>
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <!-- Message Content -->
                                                    <div class="comment-text fs-9">
                                                        <?php
                                                        $unescaped_comment = stripcslashes($comment['comment']);
                                                        $formatted_comment = nl2br(htmlspecialchars($unescaped_comment));

                                                        // Add link detection
                                                        $formatted_comment = preg_replace(
                                                            '/(https?:\/\/[^\s]+)/',
                                                            '<a href="$1" target="_blank" class="text-decoration-none fw-medium">$1 <i class="fas fa-external-link-alt" style="font-size: 10px;"></i></a>',
                                                            $formatted_comment
                                                        );

                                                        echo $formatted_comment;
                                                        ?>
                                                    </div>

                                                    <!-- Message Actions - Only show for active conversations -->
                                                    <?php if (!in_array($taskStatus, ['Completed', 'Cancelled'])): ?>
                                                        <div class="mt-2 d-flex justify-content-end">
                                                            <small class="text-muted hover-actions" style="opacity: 0; transition: opacity 0.3s ease;">
                                                                <i class="fas fa-reply me-2 cursor-pointer" title="Reply"></i>
                                                                <i class="fas fa-heart me-2 cursor-pointer" title="Like"></i>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Message Status -->
                                                <?php if ($isLastMessage): ?>
                                                    <div class="mt-1 <?php echo $isAdmin ? 'text-start' : 'text-end'; ?>">
                                                        <small class="text-muted" style="font-size: 10px;">
                                                            <i class="fas fa-check-double text-success me-1"></i>Latest message
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Scroll to bottom indicator - Only for active conversations -->
                            <?php if (!in_array($taskStatus, ['Completed', 'Cancelled'])): ?>
                                <div class="text-center py-2" id="scrollIndicator" style="display: none;">
                                    <button class="btn btn-sm btn-outline-primary" onclick="scrollToBottom()">
                                        <i class="fas fa-chevron-down me-1"></i>Scroll to latest
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    <script>
        // Enhanced toggle function with smooth animations
        function toggleCommentForm() {
            const form = document.getElementById('commentForm');
            const isVisible = form.style.display !== 'none';

            if (isVisible) {
                // Slide up animation
                form.style.animation = 'slideUp 0.3s ease';
                setTimeout(() => {
                    form.style.display = 'none';
                    document.getElementById('commentText').value = '';
                }, 300);
            } else {
                form.style.display = 'block';
                form.style.animation = 'slideDown 0.3s ease';
                setTimeout(() => {
                    document.getElementById('commentText').focus();
                }, 100);
            }
        }

        // Enhanced addComment function with better UX feedback
        function addComment(event) {
            event.preventDefault();

            const commentText = document.getElementById('commentText').value.trim();
            if (!commentText) {
                showCommentToast('Please enter a message before sending.', 'warning');
                return;
            }

            // Show loading state
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

            const formData = new FormData();
            formData.append('task_id', <?php echo $taskId; ?>);
            formData.append('comment', commentText);
            formData.append('action', 'add_comment');

            fetch('add-task-comment', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    // Check if response is ok
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    // Check content type to ensure it's JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        // If it's not JSON, get the text to see what was returned
                        return response.text().then(text => {
                            console.error('Non-JSON response received:', text);
                            throw new Error('Server returned HTML instead of JSON. Please check the add-task-comment endpoint.');
                        });
                    }

                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        // Show success message
                        showCommentToast('Message sent!', 'success');

                        // Reset form
                        document.getElementById('commentText').value = '';
                        toggleCommentForm();

                        // Add smooth reload with animation
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Handle API error response
                        const errorMessage = data && data.message ? data.message : 'Failed to send message';
                        showCommentToast(errorMessage, 'danger');

                        // Reset button state
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                })
                .catch(error => {
                    console.error('Error details:', error);

                    // Provide user-friendly error messages
                    let userMessage = 'An error occurred while sending the message';

                    if (error.message.includes('HTML instead of JSON')) {
                        userMessage = 'Server configuration error. Please contact administrator.';
                    } else if (error.message.includes('HTTP error')) {
                        userMessage = 'Server error. Please try again later.';
                    } else if (error.message.includes('Failed to fetch')) {
                        userMessage = 'Network error. Please check your connection.';
                    }

                    showCommentToast(userMessage, 'danger');

                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
        }

        // Enhanced scroll to bottom function with smooth animation
        function scrollToBottom() {
            const container = document.getElementById('commentsContainer');
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth'
            });

            // Hide scroll indicator
            const indicator = document.getElementById('scrollIndicator');
            if (indicator) {
                indicator.style.display = 'none';
            }
        }

        // Enhanced toast notification function for comments
        function showCommentToast(message, type = 'success') {
            // Remove any existing comment toast
            const existingToast = document.getElementById('comment-toast');
            if (existingToast) {
                existingToast.remove();
            }

            // Create enhanced toast
            const toast = document.createElement('div');
            toast.id = 'comment-toast';
            toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 350px;
        animation: slideInRight 0.4s ease;
    `;

            // Toast icon mapping
            const icons = {
                success: 'fa-check-circle',
                danger: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            toast.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-lg border-0" role="alert" style="border-radius: 12px;">
            <div class="d-flex align-items-center">
                <i class="fas ${icons[type]} me-2 fs-5"></i>
                <div class="flex-1">
                    <span>${message}</span>
                </div>
            </div>
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

        // Enhanced keyboard shortcuts
        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Ctrl+Enter to send message
                if (e.ctrlKey && e.key === 'Enter') {
                    const commentForm = document.getElementById('addCommentForm');
                    const commentFormVisible = document.getElementById('commentForm').style.display !== 'none';

                    if (commentForm && commentFormVisible) {
                        e.preventDefault();
                        commentForm.dispatchEvent(new Event('submit', { bubbles: true }));
                    }
                }

                // Escape to close comment form
                if (e.key === 'Escape') {
                    const commentForm = document.getElementById('commentForm');
                    if (commentForm && commentForm.style.display !== 'none') {
                        toggleCommentForm();
                    }
                }

                // Ctrl+M to open comment form
                if (e.ctrlKey && e.key === 'm') {
                    e.preventDefault();
                    const commentForm = document.getElementById('commentForm');
                    if (commentForm && commentForm.style.display === 'none') {
                        toggleCommentForm();
                    }
                }
            });
        }

        // Enhanced scroll detection for scroll indicator
        function setupScrollDetection() {
            const container = document.getElementById('commentsContainer');
            if (!container) return;

            container.addEventListener('scroll', function() {
                const indicator = document.getElementById('scrollIndicator');
                if (!indicator) return;

                const isNearBottom = this.scrollTop >= this.scrollHeight - this.clientHeight - 100;

                if (!isNearBottom && this.children.length > 3) {
                    indicator.style.display = 'block';
                    indicator.style.animation = 'fadeIn 0.3s ease';
                } else {
                    indicator.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        indicator.style.display = 'none';
                    }, 300);
                }
            });
        }

        // Enhanced auto-scroll and comment reading functionality
        function setupAutoScroll() {
            const commentsContainer = document.getElementById('commentsContainer');
            if (commentsContainer && commentsContainer.children.length > 0) {
                // Auto-scroll to bottom with delay for better UX
                setTimeout(() => {
                    scrollToBottom();
                }, 500);
            }
        }

        // Enhanced comment reading functionality
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
                        // Show subtle notification
                        showCommentToast(`Marked ${data.count} new messages as read`, 'info');

                        // Update UI after delay
                        setTimeout(() => {
                            updateCommentsUI();
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error marking comments as read:', error);
                });
        }

        // Enhanced comments section observer
        function observeCommentsSection() {
            const commentsSection = document.querySelector('#commentsContainer');

            if (commentsSection) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            // Delay marking as read to ensure user actually sees the messages
                            setTimeout(() => {
                                markCommentsAsReadOnLoad();
                            }, 2000);
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.8,
                    rootMargin: '0px 0px -50px 0px'
                });

                observer.observe(commentsSection);
            }
        }

        // Enhanced UI update function
        function updateCommentsUI() {
            // Remove "NEW" badges with smooth animation
            const unreadBadges = document.querySelectorAll('.writer-comment .unread-badge, .admin-comment .unread-badge');
            unreadBadges.forEach(badge => {
                // Add fade out animation
                badge.style.transition = 'all 0.5s ease-out';
                badge.style.opacity = '0';
                badge.style.transform = 'scale(0.8)';

                // Remove the element after animation completes
                setTimeout(() => {
                    if (badge.parentNode) {
                        badge.remove();
                    }
                }, 500);
            });
        }

        // Enhanced textarea auto-resize functionality
        function setupTextareaAutoResize() {
            const textarea = document.getElementById('commentText');
            if (!textarea) return;

            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 150) + 'px';
            });

            // Reset height when form is closed
            textarea.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.height = 'auto';
                }
            });
        }

        // Enhanced message interaction handlers
        function setupMessageInteractions() {
            // Add hover effects for message actions
            document.addEventListener('mouseover', function(e) {
                const commentBubble = e.target.closest('.comment-bubble');
                if (commentBubble) {
                    const hoverActions = commentBubble.querySelector('.hover-actions');
                    if (hoverActions) {
                        hoverActions.style.opacity = '1';
                    }
                }
            });

            document.addEventListener('mouseout', function(e) {
                const commentBubble = e.target.closest('.comment-bubble');
                if (commentBubble) {
                    const hoverActions = commentBubble.querySelector('.hover-actions');
                    if (hoverActions) {
                        hoverActions.style.opacity = '0';
                    }
                }
            });
        }

        // Enhanced CSS animations
        function injectEnhancedStyles() {
            const style = document.createElement('style');
            style.textContent = `
        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(-20px);
                opacity: 0;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }

        .animate-fade-in {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .comment-bubble:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
            transition: all 0.3s ease;
        }

        #commentsContainer::-webkit-scrollbar {
            width: 6px;
        }

        #commentsContainer::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        #commentsContainer::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #commentsContainer::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Base avatar styling */
.avatar {
    position: relative;
    display: inline-block;
    transition: all 0.3s ease;
}

.avatar-xl {
    width: 45px !important;
    height: 45px !important;
}

/* Online status indicator using ::after pseudo-element */
.avatar::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

/* Status-specific colors and animations */
.status-online::after {
    background-color: #198754; /* Success green */
    animation: onlinePulse 2s infinite;
    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.3);
}

.status-idle::after {
    background-color: #0dcaf0; /* Info blue */
    box-shadow: 0 0 0 2px rgba(13, 202, 240, 0.3);
}

.status-away::after {
    background-color: #ffc107; /* Warning yellow */
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.3);
}

.status-offline::after {
    background-color: #6c757d; /* Secondary gray */
    box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.3);
}

/* Pulse animation for online users */
@keyframes onlinePulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.3);
        opacity: 0.7;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Hover effects */
.avatar:hover {
    transform: scale(1.05);
}

.avatar:hover::after {
    transform: scale(1.2);
}

/* Additional status classes for different states */
.status-busy::after {
    background-color: #dc3545; /* Danger red */
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.3);
}

.status-dnd::after {
    background-color: #dc3545; /* Do not disturb - red */
    animation: none;
}

/* Enhanced tooltip styling */
.tooltip-inner {
    background-color: rgba(0, 0, 0, 0.9) !important;
    color: white !important;
    padding: 10px 15px !important;
    border-radius: 8px !important;
    font-size: 12px !important;
    max-width: 250px !important;
    text-align: left !important;
}

.tooltip .tooltip-arrow {
    border-top-color: rgba(0, 0, 0, 0.9) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .avatar-xl {
        width: 40px !important;
        height: 40px !important;
    }

    .avatar::after {
        width: 10px;
        height: 10px;
        border-width: 1px;
    }
}

/* Status legend (optional - can be added to help users understand statuses) */
.status-legend {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    z-index: 1000;
    font-size: 11px;
}

.status-legend.show {
    display: block;
}

.status-legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 4px;
}

.status-legend-item:last-child {
    margin-bottom: 0;
}

.status-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 6px;
}
@keyframes onlinePulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.7;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.online-indicator {
    transition: all 0.3s ease;
}

.online-indicator:hover {
    transform: scale(1.2);
}

/* Status-specific styling */
.bg-success.online-indicator {
    box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.3);
}

.bg-warning.online-indicator {
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.3);
}

.bg-info.online-indicator {
    box-shadow: 0 0 0 2px rgba(13, 202, 240, 0.3);
}

.bg-secondary.online-indicator {
    box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.3);
}

/* Tooltip styling */
.tooltip-inner {
    background-color: rgba(0, 0, 0, 0.9) !important;
    color: white !important;
    padding: 8px 12px !important;
    border-radius: 6px !important;
    font-size: 12px !important;
    max-width: 200px !important;
}

.tooltip .tooltip-arrow {
    border-top-color: rgba(0, 0, 0, 0.9) !important;
}

        .cursor-pointer {
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .cursor-pointer:hover {
            color: #667eea !important;
        }
    `;
            document.head.appendChild(style);
        }

        // Enhanced JavaScript for unread message handling
        function scrollToFirstUnread() {
            const firstUnread = document.getElementById('first-unread-message');
            if (firstUnread) {
                firstUnread.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Add temporary highlight effect
                firstUnread.style.transform = 'scale(1.02)';
                firstUnread.style.transition = 'transform 0.3s ease';

                setTimeout(() => {
                    firstUnread.style.transform = 'scale(1)';
                }, 1000);
            }
        }

        // Mark unread messages as read when they come into view
        function setupUnreadObserver() {
            const unreadMessages = document.querySelectorAll('[data-unread="true"]');

            if (unreadMessages.length === 0) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const messageElement = entry.target;

                        // Mark as read after 2 seconds of being in view
                        setTimeout(() => {
                            if (entry.isIntersecting) {
                                markMessageAsRead(messageElement);
                            }
                        }, 2000);
                    }
                });
            }, {
                threshold: 0.8,
                rootMargin: '0px 0px -50px 0px'
            });

            unreadMessages.forEach(message => {
                observer.observe(message);
            });
        }

        function markMessageAsRead(messageElement) {
            // Remove unread styling
            messageElement.classList.remove('unread-message-glow');
            messageElement.style.animation = 'none';

            // Remove unread badge
            const badge = messageElement.querySelector('.unread-message-badge');
            if (badge) {
                badge.style.animation = 'fadeOut 0.5s ease';
                setTimeout(() => {
                    badge.remove();
                }, 500);
            }

            // Remove unread indicator
            const indicator = messageElement.querySelector('.position-absolute.top-0.end-0');
            if (indicator) {
                indicator.style.animation = 'fadeOut 0.5s ease';
                setTimeout(() => {
                    indicator.remove();
                }, 500);
            }

            // Update container styling
            const container = messageElement.closest('.comment-item');
            if (container) {
                container.style.background = 'none';
                container.style.animation = 'none';
            }
        }

        // Main initialization function
        function initializeEnhancedTaskDiscussion() {
            // Inject enhanced styles
            injectEnhancedStyles();

            // Setup all enhanced functionalities
            setupKeyboardShortcuts();
            setupScrollDetection();
            setupAutoScroll();
            setupTextareaAutoResize();
            setupMessageInteractions();
            observeCommentsSection();
            setupUnreadObserver();

            // Auto-scroll to first unread if there are unread messages
            const unreadCount = <?php echo $unreadCount; ?>;
            if (unreadCount > 0) {
                setTimeout(() => {
                    scrollToFirstUnread();
                }, 1000);
            }

        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeEnhancedTaskDiscussion();
        });
    </script>
    <script>
        // Initialize tooltips for online indicators
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Optional: Real-time status updates via AJAX (call this periodically)
        function updateOnlineStatuses() {
            const taskId = <?php echo $taskId; ?>;

            fetch('get-online-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    task_id: taskId,
                    action: 'get_user_statuses'
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatusIndicators(data.statuses);
                    }
                })
                .catch(error => {
                    console.error('Error updating online statuses:', error);
                });
        }

        function updateStatusIndicators(statuses) {
            Object.keys(statuses).forEach(userKey => {
                const indicators = document.querySelectorAll(`[data-user-key="${userKey}"]`);
                const status = statuses[userKey];

                indicators.forEach(indicator => {
                    // Update classes
                    indicator.className = indicator.className.replace(/bg-(success|warning|info|secondary)/, status.status_class.replace('bg-', 'bg-'));

                    // Update tooltip
                    const tooltip = bootstrap.Tooltip.getInstance(indicator);
                    if (tooltip) {
                        tooltip.setContent({
                            '.tooltip-inner': status.tooltip_content
                        });
                    }
                });
            });
        }

        // Update statuses every 30 seconds for active conversations
        <?php if (!in_array($taskStatus, ['Completed', 'Cancelled'])): ?>
        setInterval(updateOnlineStatuses, 30000);
        <?php endif; ?>

    </script>

<?php
include "footer.php";
?>
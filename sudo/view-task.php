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

<?php
// 1. Calculate task timeline metrics
$taskMetrics = [];

// Convert dates for calculations
$createdDate = new DateTime($taskCreatedOn);
$dueDate = new DateTime($taskDueDate);
$currentDate = new DateTime();

// Basic timeline calculations
$totalTimeAllotted = max(1, $createdDate->diff($dueDate)->days); // Ensure minimum 1 day
$timeElapsed = $createdDate->diff($currentDate)->days;
$timeRemaining = $currentDate->diff($dueDate)->days;

// Completion timeline
if ($taskStatus == 'Completed' && !empty($completedOn)) {
    $completedDate = new DateTime($completedOn);
    $actualCompletionTime = $createdDate->diff($completedDate)->days;
    $taskMetrics['completion_efficiency'] = $totalTimeAllotted > 0 ? round(($actualCompletionTime / $totalTimeAllotted) * 100, 1) : 0;
    $taskMetrics['completed_early'] = $completedDate < $dueDate;
    $taskMetrics['days_early_late'] = abs($completedDate->diff($dueDate)->days);
}

// Submission timeline
if ($taskStatus == 'Submitted' && !empty($submittedOn)) {
    $submittedDate = new DateTime($submittedOn);
    $submissionTime = $createdDate->diff($submittedDate)->days;
    $taskMetrics['submission_efficiency'] = $totalTimeAllotted > 0 ? round(($submissionTime / $totalTimeAllotted) * 100, 1) : 0;
    $taskMetrics['submitted_early'] = $submittedDate < $dueDate;
}

// Progress metrics
$taskMetrics['progress_percentage'] = $totalTimeAllotted > 0 ? min(100, round(($timeElapsed / $totalTimeAllotted) * 100, 1)) : 100;
$taskMetrics['urgency_level'] = $timeRemaining <= 1 ? 'critical' : ($timeRemaining <= 3 ? 'high' : ($timeRemaining <= 7 ? 'medium' : 'low'));

// 2. Writer performance analytics for this specific task
$writerAnalytics = [];
if (!empty($taskWriter)) {
    // Get writer's overall stats
    $writerStatsQuery = "SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_tasks,
        AVG(CASE WHEN status = 'Completed' AND completed_on IS NOT NULL AND due_date IS NOT NULL 
            THEN DATEDIFF(completed_on, create_date) END) as avg_completion_days,
        SUM(CASE WHEN status = 'Completed' AND completed_on < due_date THEN 1 ELSE 0 END) as early_completions,
        SUM(pages) as total_pages_written,
        AVG(cpp) as avg_cpp
        FROM tbltasks 
        WHERE writer = ?";

    $stmt = mysqli_prepare($con, $writerStatsQuery);
    mysqli_stmt_bind_param($stmt, 's', $taskWriter);
    mysqli_stmt_execute($stmt);
    $writerResult = mysqli_stmt_get_result($stmt);
    $writerAnalytics = mysqli_fetch_assoc($writerResult);

    // Calculate additional metrics
    if ($writerAnalytics['total_tasks'] > 0) {
        $writerAnalytics['completion_rate'] = round(($writerAnalytics['completed_tasks'] / $writerAnalytics['total_tasks']) * 100, 1);
        $writerAnalytics['early_completion_rate'] = $writerAnalytics['completed_tasks'] > 0 ? round(($writerAnalytics['early_completions'] / $writerAnalytics['completed_tasks']) * 100, 1) : 0;
    } else {
        $writerAnalytics['completion_rate'] = 0;
        $writerAnalytics['early_completion_rate'] = 0;
    }

    // Get writer's recent performance trend (last 10 tasks)
    $recentTasksQuery = "SELECT status, 
        DATEDIFF(COALESCE(completed_on, submitted_on, NOW()), create_date) as days_taken,
        CASE WHEN completed_on < due_date THEN 1 ELSE 0 END as completed_early
        FROM tbltasks 
        WHERE writer = ? 
        ORDER BY create_date DESC 
        LIMIT 10";

    $stmt = mysqli_prepare($con, $recentTasksQuery);
    mysqli_stmt_bind_param($stmt, 's', $taskWriter);
    mysqli_stmt_execute($stmt);
    $recentResult = mysqli_stmt_get_result($stmt);

    $recentTasks = [];
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentTasks[] = $row;
    }
    $writerAnalytics['recent_performance'] = $recentTasks;
}

// 3. Subject/Category analytics
$subjectAnalytics = [];
$subjectStatsQuery = "SELECT 
    COUNT(*) as total_tasks,
    AVG(DATEDIFF(COALESCE(completed_on, NOW()), create_date)) as avg_completion_time,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_count,
    AVG(cpp) as avg_cpp,
    AVG(pages) as avg_pages
    FROM tbltasks 
    WHERE subject = ?";

$stmt = mysqli_prepare($con, $subjectStatsQuery);
mysqli_stmt_bind_param($stmt, 's', $taskSubject);
mysqli_stmt_execute($stmt);
$subjectResult = mysqli_stmt_get_result($stmt);
$subjectAnalytics = mysqli_fetch_assoc($subjectResult);

// 4. Financial analytics for this task
$financialAnalytics = [
    'task_value' => $taskPages * $taskCPP,
    'cpp_comparison' => 0,
    'value_comparison' => 0
];

// Compare with average CPP
$avgCppQuery = "SELECT AVG(cpp) as avg_cpp FROM tbltasks WHERE subject = ?";
$stmt = mysqli_prepare($con, $avgCppQuery);
mysqli_stmt_bind_param($stmt, 's', $taskSubject);
mysqli_stmt_execute($stmt);
$avgCppResult = mysqli_stmt_get_result($stmt);
$avgCppData = mysqli_fetch_assoc($avgCppResult);

if ($avgCppData['avg_cpp'] > 0 && $taskCPP > 0) {
    $financialAnalytics['cpp_comparison'] = round((($taskCPP / $avgCppData['avg_cpp']) - 1) * 100, 1);
} else {
    $financialAnalytics['cpp_comparison'] = 0;
}

// 5. File and activity analytics
$fileAnalytics = [];

// Count task files
$taskFilesCount = mysqli_num_rows(mysqli_query($con, "SELECT id FROM tbl_task_files WHERE task_id = '$taskId' AND file_type = 'task' AND is_deleted = 0"));
$submittedFilesCount = mysqli_num_rows(mysqli_query($con, "SELECT id FROM tbl_task_files WHERE task_id = '$taskId' AND file_type = 'submitted' AND is_deleted = 0"));

// Count comments
$commentsCount = mysqli_num_rows(mysqli_query($con, "SELECT id FROM tbl_task_comments WHERE task_id = '$taskId'"));
$adminCommentsCount = mysqli_num_rows(mysqli_query($con, "SELECT id FROM tbl_task_comments WHERE task_id = '$taskId' AND user_type = 'admin'"));
$writerCommentsCount = mysqli_num_rows(mysqli_query($con, "SELECT id FROM tbl_task_comments WHERE task_id = '$taskId' AND user_type = 'writer'"));

$fileAnalytics = [
    'task_files' => $taskFilesCount,
    'submitted_files' => $submittedFilesCount,
    'total_comments' => $commentsCount,
    'admin_comments' => $adminCommentsCount,
    'writer_comments' => $writerCommentsCount,
    'communication_ratio' => $commentsCount > 0 ? round(($adminCommentsCount / $commentsCount) * 100, 1) : 0
];

// 6. Task complexity score calculation
$complexityScore = 0;
$complexityFactors = [];

// Page count factor (normalized to 0-25 points)
$pageScore = $taskPages > 0 ? min(25, ($taskPages / 10) * 25) : 0;
$complexityScore += $pageScore;
$complexityFactors[] = "Pages: " . round($pageScore, 1) . "/25";

// File count factor (0-20 points)
$fileScore = min(20, $taskFilesCount * 5);
$complexityScore += $fileScore;
$complexityFactors[] = "Files: " . round($fileScore, 1) . "/20";

// Timeline factor (0-20 points)
$timelineScore = $totalTimeAllotted <= 1 ? 20 : ($totalTimeAllotted <= 3 ? 15 : ($totalTimeAllotted <= 7 ? 10 : 5));
$complexityScore += $timelineScore;
$complexityFactors[] = "Timeline: " . $timelineScore . "/20";

// Description length factor (0-15 points)
$descLength = strlen($taskDescription);
$descScore = $descLength > 0 ? min(15, ($descLength / 500) * 15) : 0;
$complexityScore += $descScore;
$complexityFactors[] = "Description: " . round($descScore, 1) . "/15";

// Subject complexity (0-20 points) - calculated dynamically
$subjectComplexityQuery = "SELECT 
    subject,
    AVG(DATEDIFF(COALESCE(completed_on, submitted_on, NOW()), create_date)) as avg_days,
    AVG(cpp) as avg_cpp,
    COUNT(*) as task_count,
    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_count
    FROM tbltasks 
    WHERE subject IS NOT NULL AND subject != ''
    GROUP BY subject";

$subjectComplexityResult = mysqli_query($con, $subjectComplexityQuery);
$subjectComplexityScores = [];

while ($row = mysqli_fetch_assoc($subjectComplexityResult)) {
    $avgDays = floatval($row['avg_days']);
    $avgCpp = floatval($row['avg_cpp']);
    $taskCount = intval($row['task_count']);
    $cancelledRate = $taskCount > 0 ? ($row['cancelled_count'] / $taskCount) : 0;

    // Calculate complexity score based on multiple factors
    $dayScore = min(8, $avgDays / 2); // Max 8 points for longer completion times
    $cppScore = min(6, $avgCpp / 200); // Max 6 points for higher cpp rates
    $cancelScore = $cancelledRate * 6; // Max 6 points for higher cancellation rates

    $totalScore = min(20, $dayScore + $cppScore + $cancelScore);
    $subjectComplexityScores[$row['subject']] = round($totalScore, 1);
}

// Get score for current task's subject, or calculate a default
if (isset($subjectComplexityScores[$taskSubject])) {
    $subjectScore = $subjectComplexityScores[$taskSubject];
} else {
    // For new/unknown subjects, assign a moderate score
    $subjectScore = 10;
}

$complexityScore += $subjectScore;
$complexityFactors[] = "Subject: " . $subjectScore . "/20";

// Normalize to 100
$complexityScore = min(100, $complexityScore);

// Determine complexity level
if ($complexityScore >= 80) {
    $complexityLevel = ['level' => 'Very High', 'class' => 'danger', 'icon' => 'fa-exclamation-triangle'];
} elseif ($complexityScore >= 60) {
    $complexityLevel = ['level' => 'High', 'class' => 'warning', 'icon' => 'fa-exclamation-circle'];
} elseif ($complexityScore >= 40) {
    $complexityLevel = ['level' => 'Medium', 'class' => 'info', 'icon' => 'fa-info-circle'];
} else {
    $complexityLevel = ['level' => 'Low', 'class' => 'success', 'icon' => 'fa-check-circle'];
}

// 7. Risk assessment
$riskFactors = [];
$riskScore = 0;

// Timeline risk
if ($isLate && in_array($taskStatus, ['In Progress', 'Unconfirmed'])) {
    $riskFactors[] = "Task is overdue";
    $riskScore += 30;
}

// Writer performance risk
if (isset($writerAnalytics['completion_rate']) && $writerAnalytics['completion_rate'] < 80) {
    $riskFactors[] = "Writer has low completion rate (" . $writerAnalytics['completion_rate'] . "%)";
    $riskScore += 20;
}

// Communication risk
if ($commentsCount == 0 && $taskStatus == 'In Progress') {
    $riskFactors[] = "No communication since task started";
    $riskScore += 15;
}

// Payment risk
if ($taskStatus == 'Completed' && $is_paid == 0) {
    $riskFactors[] = "Completed task not yet paid";
    $riskScore += 25;
}

// High value risk
if ($financialAnalytics['task_value'] > 5000) {
    $riskFactors[] = "High-value task requires attention";
    $riskScore += 10;
}

$riskLevel = $riskScore >= 50 ? 'High' : ($riskScore >= 25 ? 'Medium' : 'Low');
$riskClass = $riskScore >= 50 ? 'danger' : ($riskScore >= 25 ? 'warning' : 'success');

// 8. Recommendations generation
$recommendations = [];

if ($taskStatus == 'In Progress') {
    if ($isLate) {
        $recommendations[] = "Contact writer immediately about overdue task";
    } elseif ($timeRemaining <= 2) {
        $recommendations[] = "Follow up with writer - task due soon";
    }

    if ($commentsCount == 0) {
        $recommendations[] = "Initiate communication to check progress";
    }
}

if ($taskStatus == 'Submitted') {
    $recommendations[] = "Review submitted work and provide feedback promptly";
}

if ($taskStatus == 'Completed' && $is_paid == 0) {
    $recommendations[] = "Process payment for completed task";
}

if (isset($writerAnalytics['completion_rate']) && $writerAnalytics['completion_rate'] < 70) {
    $recommendations[] = "Monitor this writer's performance closely";
}

if ($complexityScore > 70 && $timeRemaining < 3) {
    $recommendations[] = "Consider extending deadline for complex task";
}
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

// Count unread messages (assuming admin is viewing - count unread writer messages)
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
                                                        $imagePath = "../profileimages/" . $profileImage;
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

    <!-- Analytics Dashboard Card -->
    <div class="card mb-3">
        <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-chart-line me-2 text-primary"></i>
                <h6 class="mb-0">Task Analytics & Insights</h6>
            </div>
            <button class="btn btn-sm btn-outline-primary" onclick="toggleAnalytics()">
                <i class="fas fa-chevron-down" id="analytics-toggle-icon"></i>
            </button>
        </div>
        <div class="card-body" id="analytics-content" style="display: none;">
            <div class="row g-3">

                <!-- Key Metrics Row -->
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card border-0 bg-primary-subtle h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                    <h5 class="text-primary mb-1"><?php echo $taskMetrics['progress_percentage']; ?>%</h5>
                                    <small class="text-600">Timeline Progress</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 bg-<?php echo $complexityLevel['class']; ?>-subtle h-100">
                                <div class="card-body text-center">
                                    <i class="fas <?php echo $complexityLevel['icon']; ?> fa-2x text-<?php echo $complexityLevel['class']; ?> mb-2"></i>
                                    <h5 class="text-<?php echo $complexityLevel['class']; ?> mb-1"><?php echo round($complexityScore); ?></h5>
                                    <small class="text-600">Complexity Score</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 bg-info-subtle h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-bill-wave fa-2x text-info mb-2"></i>
                                    <h5 class="text-info mb-1">Ksh. <?php echo number_format($financialAnalytics['task_value']); ?></h5>
                                    <small class="text-600">Task Value</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 bg-<?php echo $riskClass; ?>-subtle h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-shield-alt fa-2x text-<?php echo $riskClass; ?> mb-2"></i>
                                    <h5 class="text-<?php echo $riskClass; ?> mb-1"><?php echo $riskLevel; ?></h5>
                                    <small class="text-600">Risk Level</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Writer Performance -->
                <?php if (!empty($writerAnalytics)): ?>
                    <div class="col-md-6">
                        <div class="card border border-300 h-100">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Writer Performance</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-600">Completion Rate</small>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-1 me-2" style="height: 8px;">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: <?php echo isset($writerAnalytics['completion_rate']) ? $writerAnalytics['completion_rate'] : 0; ?>%"></div>
                                            </div>
                                            <small class="fw-bold"><?php echo isset($writerAnalytics['completion_rate']) ? $writerAnalytics['completion_rate'] : 0; ?>%</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-600">Early Completion</small>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar"
                                                     style="width: <?php echo isset($writerAnalytics['early_completion_rate']) ? $writerAnalytics['early_completion_rate'] : 0; ?>%"></div>
                                            </div>
                                            <small class="fw-bold"><?php echo isset($writerAnalytics['early_completion_rate']) ? $writerAnalytics['early_completion_rate'] : 0; ?>%</small>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <small class="text-600">Total Tasks: <?php echo isset($writerAnalytics['total_tasks']) ? $writerAnalytics['total_tasks'] : 0; ?> |
                                            Avg Completion: <?php echo isset($writerAnalytics['avg_completion_days']) ? round($writerAnalytics['avg_completion_days'], 1) : 'N/A'; ?> days</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Communication & Files -->
                <div class="col-md-6">
                    <div class="card border border-300 h-100">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-comments me-2"></i>Activity Overview</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-600">Task Files:</span>
                                <span class="fw-bold"><?php echo $fileAnalytics['task_files']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-600">Submitted Files:</span>
                                <span class="fw-bold"><?php echo $fileAnalytics['submitted_files']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-600">Total Messages:</span>
                                <span class="fw-bold"><?php echo $fileAnalytics['total_comments']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-600">Communication Balance:</span>
                                <span class="fw-bold"><?php echo $fileAnalytics['communication_ratio']; ?>% Admin</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Risk Assessment -->
                <?php if (!empty($riskFactors)): ?>
                    <div class="col-12">
                        <div class="card border border-<?php echo $riskClass; ?> bg-<?php echo $riskClass; ?>-subtle">
                            <div class="card-header bg-<?php echo $riskClass; ?> text-white">
                                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Risk Assessment</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($riskFactors as $risk): ?>
                                        <li class="mb-1"><i class="fas fa-caret-right me-2 text-<?php echo $riskClass; ?>"></i><?php echo $risk; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recommendations -->
                <?php if (!empty($recommendations)): ?>
                    <div class="col-12">
                        <div class="card border border-success bg-success-subtle">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Recommendations</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($recommendations as $recommendation): ?>
                                        <li class="mb-1"><i class="fas fa-arrow-right me-2 text-success"></i><?php echo $recommendation; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Detailed Breakdown -->
                <div class="col-12">
                    <div class="card border border-300">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Detailed Analytics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <h6 class="text-primary">Timeline Analysis</h6>
                                    <small class="text-600">
                                        Total Duration: <?php echo $totalTimeAllotted; ?> days<br>
                                        Time Elapsed: <?php echo $timeElapsed; ?> days<br>
                                        Urgency Level: <span class="badge badge-subtle-<?php echo $taskMetrics['urgency_level'] == 'critical' ? 'danger' : ($taskMetrics['urgency_level'] == 'high' ? 'warning' : 'success'); ?>"><?php echo ucfirst($taskMetrics['urgency_level']); ?></span>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-primary">Financial Analysis</h6>
                                    <small class="text-600">
                                        Pages: <?php echo $taskPages; ?> × Ksh. <?php echo $taskCPP; ?><br>
                                        CPP vs Average: <?php echo $financialAnalytics['cpp_comparison'] > 0 ? '+' : ''; ?><?php echo $financialAnalytics['cpp_comparison']; ?>%<br>
                                        Subject Average: Ksh. <?php echo isset($avgCppData['avg_cpp']) ? round($avgCppData['avg_cpp'], 2) : 'N/A'; ?>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-primary">Complexity Factors</h6>
                                    <small class="text-600">
                                        <?php foreach (array_slice($complexityFactors, 0, 3) as $factor): ?>
                                            <?php echo $factor; ?><br>
                                        <?php endforeach; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- File Preview Modal -->
    <div class='modal fade' id='filePreviewModal' tabindex='-1' aria-labelledby='filePreviewModalLabel' aria-hidden='true'>
        <div class='modal-dialog' style='max-width: 100vw; width: 100vw; height: 100vh; margin: 0;'>
            <div class='modal-content' style='height: 100vh; border-radius: 0;'>
                <div class='modal-header' style='flex-shrink: 0;'>
                    <h5 class='modal-title' id='filePreviewModalLabel'>itasker file preview</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body' id='filePreviewContent'
                     style='flex: 1; display: flex; justify-content: center; align-items: center; padding: 0; overflow: hidden;'>
                    <!-- Preview content will be injected here -->
                    <div id='previewLoading' style='text-align:center;'>
                        <div class='spinner-border text-primary' role='status'>
                            <span class='visually-hidden'>Loading...</span>
                        </div>
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
                        showBootstrapToast('Task completed!', 'success');

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
            iframe.style.height = '95vh';
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
        function toggleAnalytics() {
            const content = document.getElementById('analytics-content');
            const icon = document.getElementById('analytics-toggle-icon');

            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // Auto-expand analytics if there are high-risk factors or recommendations
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($riskScore >= 50 || !empty($recommendations)): ?>
            toggleAnalytics();
            <?php endif; ?>
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
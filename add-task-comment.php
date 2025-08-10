<?php
ob_start();
include 'check-login.php';
ob_clean();
header('Content-Type: application/json');

// Ensure no caching
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
}

// Get and validate input
$taskId = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$parentId = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

if ($taskId <= 0) {
    sendJsonResponse(false, 'Invalid task ID');
}

if (empty($comment)) {
    sendJsonResponse(false, 'Comment cannot be empty');
}

// Check if database connection exists
if (!isset($con) || !$con) {
    sendJsonResponse(false, 'Database connection not available');
}

// Check session and determine user type
$userEmail = null;
$userType = null;
$userName = null;
$userId = null; // Add user ID variable

if (isset($_SESSION['sessionWriter'])) {
    $userEmail = $_SESSION['sessionWriter'];
    $userType = 'writer';

    // Get writer name and ID from database
    $writerQuery = 'SELECT id, username FROM tblwriters WHERE username = ? OR email = ? LIMIT 1';
    if ($writerStmt = mysqli_prepare($con, $writerQuery)) {
        mysqli_stmt_bind_param($writerStmt, 'ss', $userEmail, $userEmail);
        mysqli_stmt_execute($writerStmt);
        mysqli_stmt_bind_result($writerStmt, $writerId, $writerName);
        if (mysqli_stmt_fetch($writerStmt)) {
            $userName = $writerName ? $writerName : $userEmail;
            $userId = $writerId;
        }
        mysqli_stmt_close($writerStmt);
    }

    // Fallback: try to get from tbltasks if not found in tblwriters
    if (!$userId) {
        $taskWriterQuery = 'SELECT writer FROM tbltasks WHERE email = ? LIMIT 1';
        if ($taskWriterStmt = mysqli_prepare($con, $taskWriterQuery)) {
            mysqli_stmt_bind_param($taskWriterStmt, 's', $userEmail);
            mysqli_stmt_execute($taskWriterStmt);
            mysqli_stmt_bind_result($taskWriterStmt, $writerName);
            if (mysqli_stmt_fetch($taskWriterStmt)) {
                $userName = $writerName ? $writerName : $userEmail;
                // Set a default user ID for writers found in tasks but not in writers table
                $userId = 0; // or you can use a specific default ID
            }
            mysqli_stmt_close($taskWriterStmt);
        }
    }

    if (empty($userName)) {
        $userName = $userEmail; // Fallback to email if name not found
    }
    if (!$userId) {
        $userId = 0; // Default user ID if not found
    }

} elseif (isset($_SESSION['odmsaid'])) {
    $userEmail = $_SESSION['odmsaid'];
    $userType = 'admin';

    // Get ACTUAL admin username from database instead of hardcoding "Admin"
    $adminQuery = 'SELECT id, username, AdminName, CONCAT(FirstName, " ", LastName) as fullname FROM tbladmin WHERE email = ? LIMIT 1';
    if ($adminStmt = mysqli_prepare($con, $adminQuery)) {
        mysqli_stmt_bind_param($adminStmt, 's', $userEmail);
        mysqli_stmt_execute($adminStmt);
        $result = mysqli_stmt_get_result($adminStmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $userId = $row['id'];
            // Use the actual username from tbladmin
            $userName = $row['username'] ?: $row['AdminName'] ?: $row['fullname'] ?: 'Admin';
        }
        mysqli_stmt_close($adminStmt);
    }

    // If no admin data found, fallback
    if (!$userName) {
        $userName = $userEmail ?: 'Admin';
    }
    if (!$userId) {
        $userId = 1;
    }
}
else {
    sendJsonResponse(false, 'User session not found. Please login again.');
}

// Verify user has access to this task
if ($userType == 'writer') {
    // For writers, check if they are assigned to this task
    $accessQuery = 'SELECT id FROM tbltasks WHERE id = ? AND email = ?';
    if ($accessStmt = mysqli_prepare($con, $accessQuery)) {
        mysqli_stmt_bind_param($accessStmt, 'is', $taskId, $userEmail);
        mysqli_stmt_execute($accessStmt);
        $accessResult = mysqli_stmt_get_result($accessStmt);

        if (mysqli_num_rows($accessResult) == 0) {
            mysqli_stmt_close($accessStmt);
            sendJsonResponse(false, 'Access denied - Task not assigned to you');
        }
        mysqli_stmt_close($accessStmt);
    } else {
        sendJsonResponse(false, 'Database error: ' . mysqli_error($con));
    }
} elseif ($userType == 'admin') {
    // Admins can access all tasks - verify task exists
    $accessQuery = 'SELECT id FROM tbltasks WHERE id = ?';
    if ($accessStmt = mysqli_prepare($con, $accessQuery)) {
        mysqli_stmt_bind_param($accessStmt, 'i', $taskId);
        mysqli_stmt_execute($accessStmt);
        $accessResult = mysqli_stmt_get_result($accessStmt);

        if (mysqli_num_rows($accessResult) == 0) {
            mysqli_stmt_close($accessStmt);
            sendJsonResponse(false, 'Task not found');
        }
        mysqli_stmt_close($accessStmt);
    } else {
        sendJsonResponse(false, 'Database error: ' . mysqli_error($con));
    }
} else {
    sendJsonResponse(false, 'Invalid user type: ' . $userType);
}

// Validate parent comment if provided
if ($parentId !== null) {
    $parentQuery = 'SELECT id FROM tbl_task_comments WHERE id = ? AND task_id = ?';
    if ($parentStmt = mysqli_prepare($con, $parentQuery)) {
        mysqli_stmt_bind_param($parentStmt, 'ii', $parentId, $taskId);
        mysqli_stmt_execute($parentStmt);
        $parentResult = mysqli_stmt_get_result($parentStmt);

        if (mysqli_num_rows($parentResult) == 0) {
            mysqli_stmt_close($parentStmt);
            sendJsonResponse(false, 'Parent comment not found');
        }
        mysqli_stmt_close($parentStmt);
    } else {
        sendJsonResponse(false, 'Database error: ' . mysqli_error($con));
    }
}

// Sanitize input
$comment = trim($comment);

// ================ FIXED INSERT SECTION WITH USER_ID ================
// Check if the table has AUTO_INCREMENT on id column
$hasAutoIncrement = false;
$checkAutoIncQuery = "SHOW COLUMNS FROM tbl_task_comments WHERE Field = 'id'";
$autoIncResult = mysqli_query($con, $checkAutoIncQuery);

if ($autoIncResult && $row = mysqli_fetch_assoc($autoIncResult)) {
    $hasAutoIncrement = strpos(strtolower($row['Extra']), 'auto_increment') !== false;
}

// Choose the appropriate INSERT method based on table structure
if ($hasAutoIncrement) {
    // Table has auto-increment, exclude id from INSERT but include user_id
    $insertQuery = 'INSERT INTO tbl_task_comments (task_id, user_id, user_type, username, comment, parent_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())';

    if ($insertStmt = mysqli_prepare($con, $insertQuery)) {
        mysqli_stmt_bind_param($insertStmt, 'iisssi', $taskId, $userId, $userType, $userName, $comment, $parentId);

        if (mysqli_stmt_execute($insertStmt)) {
            $commentId = mysqli_insert_id($con);
            mysqli_stmt_close($insertStmt);
        } else {
            $error = mysqli_stmt_error($insertStmt);
            mysqli_stmt_close($insertStmt);
            sendJsonResponse(false, 'Failed to add comment: ' . $error);
        }
    } else {
        sendJsonResponse(false, 'Database error: ' . mysqli_error($con));
    }
} else {
    // Table does not have auto-increment, generate ID manually and include user_id
    $getMaxIdQuery = 'SELECT COALESCE(MAX(id), 0) + 1 as next_id FROM tbl_task_comments';
    $maxIdResult = mysqli_query($con, $getMaxIdQuery);
    $nextId = 1;

    if ($maxIdResult && $row = mysqli_fetch_assoc($maxIdResult)) {
        $nextId = $row['next_id'];
    }

    $insertQuery = 'INSERT INTO tbl_task_comments (id, task_id, user_id, user_type, username, comment, parent_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())';

    if ($insertStmt = mysqli_prepare($con, $insertQuery)) {
        mysqli_stmt_bind_param($insertStmt, 'iiisssi', $nextId, $taskId, $userId, $userType, $userName, $comment, $parentId);

        if (mysqli_stmt_execute($insertStmt)) {
            $commentId = $nextId;
            mysqli_stmt_close($insertStmt);
        } else {
            $error = mysqli_stmt_error($insertStmt);
            mysqli_stmt_close($insertStmt);
            sendJsonResponse(false, 'Failed to add comment: ' . $error);
        }
    } else {
        sendJsonResponse(false, 'Database error: ' . mysqli_error($con));
    }
}

// Get the inserted comment details for response
$selectQuery = 'SELECT id, task_id, user_id, user_type, username, comment, parent_id, created_at FROM tbl_task_comments WHERE id = ?';
if ($selectStmt = mysqli_prepare($con, $selectQuery)) {
    mysqli_stmt_bind_param($selectStmt, 'i', $commentId);
    mysqli_stmt_execute($selectStmt);
    $result = mysqli_stmt_get_result($selectStmt);
    $commentData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($selectStmt);

    if ($commentData) {
        sendJsonResponse(true, 'Comment added successfully', [
            'comment' => [
                'id' => $commentData['id'],
                'task_id' => $commentData['task_id'],
                'user_id' => $commentData['user_id'],
                'user_type' => $commentData['user_type'],
                'username' => $commentData['username'],
                'comment' => $commentData['comment'],
                'parent_id' => $commentData['parent_id'],
                'created_at' => $commentData['created_at'],
                'formatted_date' => date('M j, Y g:i A', strtotime($commentData['created_at']))
            ]
        ]);
    } else {
        sendJsonResponse(true, 'Comment added successfully');
    }
} else {
    sendJsonResponse(true, 'Comment added successfully');
}

ob_end_flush();
?>
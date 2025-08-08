<?php
include 'check-login.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate input
$taskId = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$parentId = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

if ($taskId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit;
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

// Check session and determine user type
$userEmail = null;
$userType = null;
$userName = null;

if (isset($_SESSION['sessionWriter'])) {
    $userEmail = $_SESSION['sessionWriter'];
    $userType = 'writer';

    // Get writer name from database
    $writerQuery = 'SELECT writer FROM tbltasks WHERE email = ? LIMIT 1';
    if ($writerStmt = mysqli_prepare($con, $writerQuery)) {
        mysqli_stmt_bind_param($writerStmt, 's', $userEmail);
        mysqli_stmt_execute($writerStmt);
        mysqli_stmt_bind_result($writerStmt, $writerName);
        if (mysqli_stmt_fetch($writerStmt)) {
            $userName = $writerName;
        }
        mysqli_stmt_close($writerStmt);
    }

    if (empty($userName)) {
        $userName = $userEmail; // Fallback to email if name not found
    }

} elseif (isset($_SESSION['odmsaid'])) {
    $userEmail = $_SESSION['odmsaid'];
    $userType = 'admin';
    $userName = 'Admin'; // Or get from admin table if you have one
} else {
    echo json_encode(['success' => false, 'message' => 'User session not found. Please login again.']);
    exit;
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
            echo json_encode(['success' => false, 'message' => 'Access denied - Task not assigned to you']);
            exit;
        }
        mysqli_stmt_close($accessStmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        exit;
    }
} elseif ($userType == 'admin') {
    // Admins can access all tasks - verify task exists
    $accessQuery = 'SELECT id FROM tbltasks WHERE id = ?';
    if ($accessStmt = mysqli_prepare($con, $accessQuery)) {
        mysqli_stmt_bind_param($accessStmt, 'i', $taskId);
        mysqli_stmt_execute($accessStmt);
        $accessResult = mysqli_stmt_get_result($accessStmt);

        if (mysqli_num_rows($accessResult) == 0) {
            echo json_encode(['success' => false, 'message' => 'Task not found']);
            exit;
        }
        mysqli_stmt_close($accessStmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid user type: ' . $userType]);
    exit;
}

// Validate parent comment if provided
if ($parentId !== null) {
    $parentQuery = 'SELECT id FROM tbl_task_comments WHERE id = ? AND task_id = ?';
    if ($parentStmt = mysqli_prepare($con, $parentQuery)) {
        mysqli_stmt_bind_param($parentStmt, 'ii', $parentId, $taskId);
        mysqli_stmt_execute($parentStmt);
        $parentResult = mysqli_stmt_get_result($parentStmt);

        if (mysqli_num_rows($parentResult) == 0) {
            echo json_encode(['success' => false, 'message' => 'Parent comment not found']);
            exit;
        }
        mysqli_stmt_close($parentStmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        exit;
    }
}

// Sanitize input
$comment = mysqli_real_escape_string($con, $comment);

// Insert comment using the variables we defined earlier
$insertQuery = 'INSERT INTO tbl_task_comments (task_id, user_type, username, comment, parent_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())';

if ($insertStmt = mysqli_prepare($con, $insertQuery)) {
    mysqli_stmt_bind_param($insertStmt, 'isssi', $taskId, $userType, $userName, $comment, $parentId);

    if (mysqli_stmt_execute($insertStmt)) {
        $commentId = mysqli_insert_id($con);

        // Get the inserted comment details for response
        $selectQuery = 'SELECT id, user_type, username, comment, parent_id, created_at FROM tbl_task_comments WHERE id = ?';
        if ($selectStmt = mysqli_prepare($con, $selectQuery)) {
            mysqli_stmt_bind_param($selectStmt, 'i', $commentId);
            mysqli_stmt_execute($selectStmt);
            $result = mysqli_stmt_get_result($selectStmt);
            $commentData = mysqli_fetch_assoc($result);

            echo json_encode([
                'success' => true,
                'message' => 'Comment added successfully',
                'comment' => [
                    'id' => $commentData['id'],
                    'user_type' => $commentData['user_type'],
                    'username' => $commentData['username'],
                    'comment' => $commentData['comment'],
                    'parent_id' => $commentData['parent_id'],
                    'created_at' => $commentData['created_at'],
                    'formatted_date' => date('M j, Y g:i A', strtotime($commentData['created_at']))
                ]
            ]);

            mysqli_stmt_close($selectStmt);
        } else {
            echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add comment: ' . mysqli_stmt_error($insertStmt)]);
    }

    mysqli_stmt_close($insertStmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
}
?>
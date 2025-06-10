<?php
include('check-login.php');
if (isset($_POST['task_id'])) {
    $taskId = intval($_POST['task_id']);
    $result = mysqli_query($con, "UPDATE tbltasks SET admin_acknowledged = 1 WHERE id = '$taskId'");
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No task ID provided']);
}
?>
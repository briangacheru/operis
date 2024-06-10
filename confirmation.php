<?php
include "check-login.php";

if (isset($_GET['task_id']) && isset($_GET['action'])) {
    $encodedId = $_GET['task_id'];
    $taskId = base64_decode($encodedId);
    $action = $_GET['action'];

    if ($action == 'accept') {
        // Update the task to be accepted
        $sql = "UPDATE tbltasks SET is_confirmed = 0, status = 'In Progress' WHERE id = '$taskId'";
    } elseif ($action == 'decline') {
        // Update the task to be declined
        $sql = "UPDATE tbltasks SET is_confirmed = 2, status = 'Draft' WHERE id = '$taskId'";
    }

    if (mysqli_query($con, $sql)) {
        $_SESSION['alert'] = '<div class="alert alert-success border-0 d-flex align-items-center" role="alert">
                                    <div class="bg-success me-3 icon-item"><span class="fas fa-check-circle text-white fs-6"></span></div>
                                    <p class="mb-0 flex-1">Task updated successfully!</p>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    } else {
        $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                    <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                    <p class="mb-0 flex-1">Error updating task status!</p>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    }

    header('Location: view-task.php?task_id=' . $encodedId);
    exit();
} else {
    $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                <p class="mb-0 flex-1">Invalid request!</p>
                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>';
    header('Location: view-task.php');
    exit();
}
?>

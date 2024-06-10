<?php
include "check-login.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['task_id'])) {
        $encodedId = $_GET['task_id'];
        $taskId = base64_decode($encodedId);
    } else {
        $_SESSION['alert'] = '<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                    <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                    <p class="mb-0 flex-1">Invalid task ID!</p>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        header('Location: view-task.php');
        exit();
    }

    $uploadedFiles = [];

    // Retrieve existing submitted files
    $sql = "SELECT submitted_files FROM tbltasks WHERE id='$taskId'";
    $result = mysqli_query($con, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $existingFiles = $row['submitted_files'];

        // Convert existing files into an array
        $existingFilesArray = !empty($existingFiles) ? explode(',', $existingFiles) : [];

        // Check if files were uploaded
        if (!empty($_FILES['taskfiles']['name'][0])) {
            $totalFiles = count($_FILES['taskfiles']['name']);

            for ($i = 0; $i < $totalFiles; $i++) {
                $fileName = $_FILES['taskfiles']['name'][$i];
                $fileTmpName = $_FILES['taskfiles']['tmp_name'][$i];
                $fileSize = $_FILES['taskfiles']['size'][$i];
                $fileError = $_FILES['taskfiles']['error'][$i];
                $fileType = $_FILES['taskfiles']['type'][$i];

                // Handle file upload errors
                if ($fileError === 0) {
                    $fileDestination = 'taskfiles/' . $fileName;
                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        $uploadedFiles[] = $fileName;
                    } else {
                        $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                                    <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                                    <p class="mb-0 flex-1">Error uploading files!</p>
                                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>';
                        header('Location: view-task.php?task_id=' . $encodedId);
                        exit();
                    }
                } else {
                    $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                                <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                                <p class="mb-0 flex-1">There was an error uploading your files!</p>
                                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                    header('Location: view-task.php?task_id=' . $encodedId);
                    exit();
                }
            }

            // Merge existing and new files
            $allFiles = array_merge($existingFilesArray, $uploadedFiles);
            $submittedFiles = implode(',', $allFiles);
            $submittedOn = date('Y-m-d H:i:s');
            $sql = "UPDATE tbltasks SET submitted_files = '$submittedFiles', submitted_on = '$submittedOn', status = 'Submitted' WHERE id = '$taskId'";

            if (mysqli_query($con, $sql)) {
                $_SESSION['alert'] = '<div class="alert alert-success border-0 d-flex align-items-center" role="alert">
                                            <div class="bg-success me-3 icon-item"><span class="fas fa-check-circle text-white fs-6"></span></div>
                                            <p class="mb-0 flex-1">Files uploaded successfully and task submitted!</p>
                                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
                header('Location: view-task.php?task_id=' . $encodedId);
                exit();
            } else {
                $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                            <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                            <p class="mb-0 flex-1">Error updating task status in the database!</p>
                                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
                header('Location: view-task.php?task_id=' . $encodedId);
                exit();
            }
        } else {
            $_SESSION['alert'] = '<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                        <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                        <p class="mb-0 flex-1">No files selected for upload!</p>
                                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
            header('Location: view-task.php?task_id=' . $encodedId);
            exit();
        }
    } else {
        $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                    <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                    <p class="mb-0 flex-1">Error retrieving task data!</p>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        header('Location: view-task.php?task_id=' . $encodedId);
        exit();
    }
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

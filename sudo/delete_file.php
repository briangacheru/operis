<?php
include "check-login.php";

if ($_POST['action'] == 'deleteFile' && !empty($_POST['filePath'])) {
    $filePath = $_POST['filePath'];

    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo json_encode(['status' => 'success', 'message' => 'File deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete the file.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>

<?php
include "check-login.php";
require_once 'spaces-helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'Upload failed with error code: ' . $file['error']]);
            exit;
        }

        // Create a temporary file path
        $tempFilePath = $file['tmp_name'];
        $originalFileName = $file['name'];
        $fileSize = $file['size'];

        // Upload to Digital Ocean Spaces in the taskfiles/submissions folder
        $spacesHelper = new SpacesHelper();
        $result = $spacesHelper->uploadFile($tempFilePath, $originalFileName, 'taskfiles/submissions');

        if ($result['success']) {
            echo json_encode([
                'status' => 'success',
                'filePath' => $result['key'],
                'fileUrl' => $result['url'],
                'fileName' => $originalFileName,
                'fileSize' => $fileSize
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>

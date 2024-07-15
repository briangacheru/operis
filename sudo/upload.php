<?php
include "check-login.php";

$targetDir = "../taskfiles/";
$response = ['status' => 'error', 'message' => 'File upload failed.'];

// Check if file was uploaded
if (isset($_FILES['file']['name'])) {
    $file = $_FILES['file'];
    $originalFileName = basename($file['name']);
    $newFileName = preg_replace('/[^a-zA-Z0-9 ._-]/', '-', $originalFileName); // Replace special characters except space, dot, underscore, and hyphen with a dash
    $targetFilePath = $targetDir . $newFileName;

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'File upload error code: ' . $file['error'];
    } elseif (!is_uploaded_file($file['tmp_name'])) {
        $response['message'] = 'Potential file upload attack detected.';
    } elseif (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        $response = ['status' => 'success', 'filePath' => $targetFilePath];
    } else {
        $response['message'] = 'Sorry, there was an error moving your file.';
    }
} else {
    $response['message'] = 'No file was uploaded.';
}

echo json_encode($response);
?>

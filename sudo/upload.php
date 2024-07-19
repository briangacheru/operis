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
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            if (!is_uploaded_file($file['tmp_name'])) {
                $response['message'] = 'Potential file upload attack detected.';
            } elseif (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                $response = ['status' => 'success', 'filePath' => $targetFilePath];
            } else {
                $response['message'] = 'Sorry, there was an error moving your file.';
            }
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $response['message'] = 'The uploaded file exceeds the maximum file size limit.';
            break;
        case UPLOAD_ERR_PARTIAL:
            $response['message'] = 'The uploaded file was only partially uploaded.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $response['message'] = 'No file was uploaded.';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $response['message'] = 'Missing a temporary folder.';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $response['message'] = 'Failed to write file to disk.';
            break;
        case UPLOAD_ERR_EXTENSION:
            $response['message'] = 'File upload stopped by a PHP extension.';
            break;
        default:
            $response['message'] = 'Unknown upload error.';
            break;
    }
} else {
    $response['message'] = 'No file was uploaded.';
}

echo json_encode($response);
?>

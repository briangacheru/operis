<?php
include "check-login.php";

$targetDir = "../taskfiles/"; // Ensure this directory exists and is writable

$response = ['status' => 'error', 'message' => 'File upload failed.'];

if (isset($_FILES['file']['name'])) {
    $originalFileName = basename($_FILES['file']['name']);
    $newFileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalFileName); // Replace special characters with underscores
    $targetFilePath = $targetDir . $newFileName;

    // Move the file to the server directory
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
        $response = ['status' => 'success', 'filePath' => $newFileName];
    } else {
        $response['message'] = 'Sorry, there was an error uploading your file.';
    }
}

echo json_encode($response);
?>

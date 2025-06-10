<?php
include "check-login.php";
require_once 'spaces-helper.php';

// Generate a 4-character unique ID
function generateShortId($length = 4) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

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

        // Process filename to add unique ID
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $fileNameWithoutExt = pathinfo($originalFileName, PATHINFO_FILENAME);

        // Generate unique 4-character ID
        $uniqueId = generateShortId(4);

        // Create new filename: originalname_uniqueID.extension
        $newFileName = $fileNameWithoutExt . '_' . $uniqueId . '.' . $fileExtension;

        // Upload to Digital Ocean Spaces in the taskfiles/submissions folder
        $spacesHelper = new SpacesHelper();
        $result = $spacesHelper->uploadFile($tempFilePath, $newFileName, 'taskfiles/submissions');

        if ($result['success']) {
            echo json_encode([
                'status' => 'success',
                'filePath' => $result['key'],
                'fileUrl' => $result['url'],
                'fileName' => $originalFileName, // Keep original filename for display
                'actualFileName' => $newFileName, // Actual filename with unique ID
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
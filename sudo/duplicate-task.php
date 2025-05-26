<?php
include 'check-login.php'; // Ensure this includes your database connection settings
require_once 'spaces-helper.php'; // Include the SpacesHelper class

if (isset($_GET['task_id'])) {
    $taskId = base64_decode($_GET['task_id']);

    // Fetch the original task
    $query = "SELECT * FROM tbltasks WHERE id='$taskId'";
    $result = mysqli_query($con, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        // Exclude the ID from the columns to duplicate
        $topic = mysqli_real_escape_string($con, $row['topic']);
        $subject = mysqli_real_escape_string($con, $row['subject']);
        $account = mysqli_real_escape_string($con, $row['account']);
        $description = mysqli_real_escape_string($con, $row['description']);
        $writer = mysqli_real_escape_string($con, $row['writer']);
        $writerEmail = mysqli_real_escape_string($con, $row['email']);
        $due_date = mysqli_real_escape_string($con, $row['due_date']);
        $cpp = mysqli_real_escape_string($con, $row['cpp']);
        $pages = mysqli_real_escape_string($con, $row['pages']);
        $is_confirmed = mysqli_real_escape_string($con, $row['is_confirmed']);
        $filesString = $row['task_files']; // Keep JSON format
        $fileUrlsString = isset($row['file_urls']) ? $row['file_urls'] : ''; // Get file URLs
        $is_duplicate = 1; // Set the is_duplicate flag to 1

        // Create a SpacesHelper instance
        $spacesHelper = new SpacesHelper();

        // If there are files in JSON format, duplicate them in Digital Ocean Spaces
        $newFilesData = [];
        $newFileUrls = [];

        if (!empty($filesString)) {
            $filesData = json_decode($filesString, true);

            if (is_array($filesData)) {
                foreach ($filesData as $fileData) {
                    $filePath = $fileData['filePath'];
                    $fileName = $fileData['fileName'];

                    // Get the file URL from Digital Ocean
                    $fileUrl = '';
                    if (!empty($fileUrlsString)) {
                        $fileUrlsData = json_decode($fileUrlsString, true);
                        if (is_array($fileUrlsData)) {
                            foreach ($fileUrlsData as $urlData) {
                                if (isset($urlData['filePath']) && $urlData['filePath'] === $filePath) {
                                    $fileUrl = $urlData['fileUrl'];
                                    break;
                                }
                            }
                        }
                    }

                    if (!empty($fileUrl)) {
                        // Create a temporary file
                        $tempFile = tempnam(sys_get_temp_dir(), 'duplicate_');

                        // Download the file from Digital Ocean
                        $fileContent = @file_get_contents($fileUrl);

                        if ($fileContent !== false) {
                            // Save to temporary file
                            file_put_contents($tempFile, $fileContent);

                            // Upload to Digital Ocean with a new name (add 'duplicate_' prefix)
                            $newFileName = 'duplicate_' . $fileName;
                            $result = $spacesHelper->uploadFile($tempFile, $newFileName, 'taskfiles');

                            if ($result['success']) {
                                // Add to new files data
                                $newFilesData[] = [
                                    'fileName' => $newFileName,
                                    'filePath' => $result['key']
                                ];

                                // Add to new file URLs
                                $newFileUrls[] = [
                                    'filePath' => $result['key'],
                                    'fileUrl' => $result['url']
                                ];
                            }

                            // Clean up temporary file
                            @unlink($tempFile);
                        }
                    } else {
                        // If no URL found, just copy the original file data
                        $newFilesData[] = $fileData;
                    }
                }
            }
        }

        // Convert arrays to JSON strings
        $newFilesString = !empty($newFilesData) ? json_encode($newFilesData) : $filesString;
        $newFileUrlsString = !empty($newFileUrls) ? json_encode($newFileUrls) : $fileUrlsString;

        // Escape JSON strings for SQL
        $newFilesString = mysqli_real_escape_string($con, $newFilesString);
        $newFileUrlsString = mysqli_real_escape_string($con, $newFileUrlsString);

        // Insert the duplicate task
        $duplicateQuery = "INSERT INTO tbltasks (topic, subject, account, description, writer, email, due_date, cpp, pages, is_confirmed, is_duplicate, task_files, file_urls) 
                           VALUES ('$topic', '$subject', '$account', '$description', '$writer', '$writerEmail', '$due_date', '$cpp', '$pages', '$is_confirmed', '$is_duplicate', '$newFilesString', '$newFileUrlsString')";

        if (mysqli_query($con, $duplicateQuery)) {
            $newTaskId = mysqli_insert_id($con); // Get the ID of the new task
            $encodedId = base64_encode($newTaskId);

            // Set success message
            $_SESSION['alert'] = '<div class="alert alert-success border-0 d-flex align-items-center" role="alert">
                <div class="bg-success me-3 icon-item"><span class="fas fa-check-circle text-white fs-6"></span></div>
                <p class="mb-0 flex-1">Task duplicated successfully!</p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';

            header("Location: view-task?task_id=$encodedId"); // Redirect to the new task
            exit;
        } else {
            // Set error message
            $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                <div class="bg-danger me-3 icon-item"><span class="fas fa-times-circle text-white fs-6"></span></div>
                <p class="mb-0 flex-1">Error duplicating task: ' . mysqli_error($con) . '</p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';

            header("Location: view-task?task_id=" . base64_encode($taskId)); // Redirect back to original task
            exit;
        }
    } else {
        // Set error message
        $_SESSION['alert'] = '<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
            <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
            <p class="mb-0 flex-1">Original task not found.</p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';

        header("Location: index"); // Redirect to dashboard
        exit;
    }
} else {
    // Set error message
    $_SESSION['alert'] = '<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
        <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
        <p class="mb-0 flex-1">No task ID provided.</p>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';

    header("Location: index"); // Redirect to dashboard
    exit;
}
?>
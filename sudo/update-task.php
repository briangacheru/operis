<?php
include "check-login.php";
require_once 'spaces-helper.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';


if ($_POST['action'] == 'submitForm') {
    $requiredFields = ['topic', 'subject', 'account', 'description', 'writer', 'email', 'due_date', 'cpp', 'pages'];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => "The field {$field} is required."]);
            exit;
        }
    }

    $taskId = mysqli_real_escape_string($con, $_POST['taskId']);
    $topic = mysqli_real_escape_string($con, $_POST['topic']);
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $account = mysqli_real_escape_string($con, $_POST['account']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $writer = mysqli_real_escape_string($con, $_POST['writer']);
    $writerEmail = mysqli_real_escape_string($con, $_POST['email']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $due_date = mysqli_real_escape_string($con, $_POST['due_date']);
    $cpp = mysqli_real_escape_string($con, $_POST['cpp']);
    $pages = mysqli_real_escape_string($con, $_POST['pages']);
    $is_confirmed = mysqli_real_escape_string($con, $_POST['is_confirmed']);
    $sendEmail = isset($_POST['sendEmail']) ? mysqli_real_escape_string($con, $_POST['sendEmail']) : '0';

    // Get existing file data from the database
    $existingDataQuery = "SELECT file_urls, file_sizes FROM tbltasks WHERE id = ?";
    $stmt = mysqli_prepare($con, $existingDataQuery);
    mysqli_stmt_bind_param($stmt, 'i', $taskId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $existingFileUrls, $existingFileSizes);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Process existing files (those not removed)
    $existingFiles = $_POST['existingFiles'] ?? [];
    $removedFiles = !empty($_POST['removedFiles']) ? json_decode($_POST['removedFiles'], true) : [];

    // Filter out the removed files from the existing files array
    $remainingFiles = array_filter($existingFiles, function($filePath) use ($removedFiles) {
        return !in_array($filePath, $removedFiles);
    });

    // Process newly uploaded files
    $uploadedFiles = json_decode($_POST['uploadedFiles'], true);
    if (!is_array($uploadedFiles)) {
        $uploadedFiles = []; // Set to empty array if not valid
    }

    // Get filenames for task_files column (comma-separated list of filenames)
    $uploadedFileNames = array_map(function($file) {
        return $file['filePath'];
    }, $uploadedFiles);

    $allFiles = array_merge($remainingFiles, $uploadedFileNames);
    $filesString = implode(',', $allFiles);

    // Handle file_urls and file_sizes for Digital Ocean Spaces
    $spacesHelper = new SpacesHelper();

    // Process existing file URLs and sizes
    $existingUrlsArray = !empty($existingFileUrls) ? explode(',', $existingFileUrls) : [];
    $existingSizesArray = !empty($existingFileSizes) ? explode(',', $existingFileSizes) : [];

    // Remove URLs and sizes for deleted files
    if (!empty($removedFiles)) {
        foreach ($removedFiles as $removedFile) {
            // Find and remove the URL and size that contains this filename
            foreach ($existingUrlsArray as $key => $url) {
                if (strpos($url, basename($removedFile)) !== false) {
                    unset($existingUrlsArray[$key]);
                    // Also remove the corresponding file size
                    if (isset($existingSizesArray[$key])) {
                        unset($existingSizesArray[$key]);
                    }
                    // Optionally delete the file from Digital Ocean
                    $spacesHelper->deleteFile('taskfiles/' . basename($removedFile));
                    break;
                }
            }
        }
        // Reindex arrays after removal
        $existingUrlsArray = array_values($existingUrlsArray);
        $existingSizesArray = array_values($existingSizesArray);
    }

    // Add new file URLs and sizes
    $newUrls = [];
    $newSizes = [];
    foreach ($uploadedFiles as $file) {
        $fileUrl = $spacesHelper->getFileUrl('taskfiles/' . $file['filePath']);
        $newUrls[] = $fileUrl;
        $newSizes[] = isset($file['fileSize']) ? $file['fileSize'] : '0';
    }

    // Combine remaining existing URLs and sizes with new ones
    $allUrls = array_merge($existingUrlsArray, $newUrls);
    $allSizes = array_merge($existingSizesArray, $newSizes);

    $urlsString = implode(',', array_filter($allUrls));
    $sizesString = implode(',', array_filter($allSizes));

    // Update the database with task_files, file_urls, and file_sizes
    $sql = "UPDATE tbltasks SET topic=?, subject=?, account=?, description=?, writer=?, email=?, status=?, due_date=?, cpp=?, pages=?, is_confirmed=?, task_files=?, file_urls=?, file_sizes=? WHERE id=?";

    if ($stmt = mysqli_prepare($con, $sql)) {
        // Add 's' for file_sizes parameter
        mysqli_stmt_bind_param($stmt, 'ssssssssssssssi', $topic, $subject, $account, $description, $writer, $writerEmail, $status, $due_date, $cpp, $pages, $is_confirmed, $filesString, $urlsString, $sizesString, $taskId);

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $emailStatus = '';

                if ($sendEmail == '1') {
                    $encodedId = base64_encode((string)$taskId);
                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'mail.monkbrian.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'support@monkbrian.com';
                        $mail->Password   = 'EDU+pass.';
                        $mail->SMTPSecure = 'ssl';
                        $mail->Port       = 465;

                        $mail->setFrom('support@monkbrian.com', 'itasker');
                        $mail->addReplyTo('bryo4419@gmail.com', 'Bryo Gacheru');
                        $mail->addAddress($writerEmail);
                        $mail->addAddress('bryo4419@gmail.com', 'iTasker Admin');

                        $tempFiles = []; // Store all temp files
                        foreach ($uploadedFiles as $file) {
                            $filePath = $file['filePath'];
                            $fileName = basename($filePath);
                            $tempFile = tempnam(sys_get_temp_dir(), 'email_attachment_');
                            $tempFiles[] = $tempFile;

                            // Use cURL to download the file from Digital Ocean
                            $fileUrl = $spacesHelper->getFileUrl($filePath);
                            $ch = curl_init($fileUrl);
                            $fp = fopen($tempFile, 'wb');
                            curl_setopt($ch, CURLOPT_FILE, $fp);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_exec($ch);
                            curl_close($ch);
                            fclose($fp);

                            // Add the downloaded file as an attachment
                            $mail->addAttachment($tempFile, $fileName);
                        }

                        // Content
                        $mail->isHTML(true); // Set email format to HTML
                        $mail->Subject = 'Task ID: ' . $taskId . ' - ' . $topic . ' - [ ' . $account . ' ] ';

// Email Body with Logo and Modern Formatting
                        $companyLogo = 'https://web.monkbrian.com/assets/img/team/itasker-email-header.png';
                        $taskDetailsUrl = "https://web.monkbrian.com/view-task?task_id=" . $encodedId;

                        $mail->Body = "
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    background-color: #f4f4f4;
                                    padding: 20px;
                                }
                                .email-container {
                                    max-width: 600px;
                                    background: #ffffff;
                                    margin: 0 auto;
                                    padding: 20px;
                                    border-radius: 8px;
                                    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
                                }
                                .email-header {
                                    text-align: center;
                                    border-bottom: 2px solid #0073e6;
                                    padding-bottom: 15px;
                                }
                                .email-header img {
                                    max-width: 100%;
                                    height: auto;
                                    max-height:100px;
                                }
                                .email-content {
                                    padding: 20px;
                                }
                                .email-content h2 {
                                    color: #0073e6;
                                    text-align: center;
                                }
                                .email-content p {
                                    font-size: 16px;
                                    line-height: 1.5;
                                    color: #333;
                                }
                                .highlight {
                                    font-weight: bold;
                                    color: #0073e6;
                                }
                                .btn {
                                    display: block;
                                    text-align: center;
                                    background: #0073e6;
                                    color: #ffffff;
                                    padding: 12px;
                                    border-radius: 5px;
                                    text-decoration: none;
                                    font-size: 16px;
                                    font-weight: bold;
                                    margin-top: 20px;
                                    transition: background 0.3s ease-in-out, color 0.3s ease-in-out;
                                }
                                .btn:hover {
                                    background: #005bb5; /* Darker blue on hover */
                                    color: #ffffff !important;
                                }
                                .footer {
                                    text-align: center;
                                    padding-top: 15px;
                                    font-size: 12px;
                                    color: #777;
                                }
                            </style>
                        </head>
                        <body>
                            <div class='email-container'>
                                <div class='email-header'>
                                    <img src='{$companyLogo}' alt='Company Logo'>
                                </div>
                                <div class='email-content'>
                                    <h2>Task Updated - " . ($status == 'Draft' ? 'Unconfirmed' : $status) . "</h2>
                                    <p>Hello <span class='highlight'>$writer</span>,</p>
                                    <p>Your task has been updated. Below are the current details:</p>
                                    <p><strong>Status:</strong> <span class='highlight'>" . ($status == 'Draft' ? 'Unconfirmed' : $status) . "</span></p>
                                    <p><strong>Topic:</strong> <span class='highlight'>$topic</span></p>
                                    <p><strong>Subject:</strong> $subject</p>
                                    <p><strong>Due Date:</strong> <span class='highlight'>$due_date</span></p>
                                    <p><strong>Pages:</strong> $pages</p>
                                    <p><strong>Price per Page:</strong> Ksh $cpp</p>
                                    <p><strong>Description:</strong> <span class='highlight'>$description</span></p>
                                    
                                    <a class='btn' href='$taskDetailsUrl'>View More Task Details</a>
                                </div>
                                <div class='footer'>
                                    <p>For any questions, contact <a href='mailto:bryo4419@gmail.com'>bryo4419@gmail.com</a></p>
                                    <p>&copy; " . date('Y') . " iTasker. All rights reserved.</p>
                                </div>
                            </div>
                        </body>
                        </html>";

                        $mail->AltBody = "Task Updated - " . ($status == 'Draft' ? 'Unconfirmed' : $status) . "\n\n
                    Hello $writer,\n
                    Your task has been updated.\n
                    Status: " . ($status == 'Draft' ? 'Unconfirmed' : $status) . "\n
                    Topic: $topic\n
                    Subject: $subject\n
                    Due Date: $due_date\n
                    Pages: $pages\n
                    Price per Page: Ksh $cpp\n
                    Description: $description\n
                    View Task Details: $taskDetailsUrl\n\n
                    For any questions, contact bryo4419@gmail.com";

                        $mail->send();
                        $emailStatus = 'Email sent successfully.';

                        // Clean up temporary files
                        foreach ($tempFiles as $tempFile) {
                            @unlink($tempFile);
                        }
                    } catch (Exception $e) {
                        error_log("Email Error: " . $mail->ErrorInfo); // Log the exact error
                        $emailStatus = "Email sending failed: {$mail->ErrorInfo}";
                    }
                }

                header('Content-Type: application/json');
                $message = 'Task updated successfully.';
                if (!empty($emailStatus)) {
                    $message .= ' ' . $emailStatus;
                }
                echo json_encode(['status' => 'success', 'message' => $message, 'task_id' => base64_encode($taskId)]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'No changes were made or task not found.']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'No action performed.']);
}
?>
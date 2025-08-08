<?php
include 'check-login.php';
require_once 'spaces-helper.php';

// AJAX file deletion (before form submission)
if (isset($_POST['action']) && $_POST['action'] === 'delete_file') {
    header('Content-Type: application/json');

    if (!isset($_POST['file_id']) || empty($_POST['file_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'File ID is required']);
        exit;
    }

    $fileId = (int)$_POST['file_id'];

    try {
        // Use the same query structure as your existing code
        $deleteFileQuery = 'UPDATE tbl_task_files SET is_deleted = 1 WHERE id = ?';
        $deleteStmt = mysqli_prepare($con, $deleteFileQuery);
        mysqli_stmt_bind_param($deleteStmt, 'i', $fileId);

        if (mysqli_stmt_execute($deleteStmt)) {
            $affectedRows = mysqli_stmt_affected_rows($deleteStmt);
            mysqli_stmt_close($deleteStmt);

            if ($affectedRows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'File removed successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File not found or already deleted']);
            }
        } else {
            mysqli_stmt_close($deleteStmt);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete file']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

    exit;
}

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
    $admin_acknowledged = mysqli_real_escape_string($con, $_POST['admin_acknowledged']);
    $acknowledged = mysqli_real_escape_string($con, $_POST['acknowledged']);
    $sendEmail = isset($_POST['sendEmail']) ? mysqli_real_escape_string($con, $_POST['sendEmail']) : '0';

    // Update the main task record (without file columns)
    $sql = 'UPDATE tbltasks SET topic=?, subject=?, account=?, description=?, writer=?, email=?, status=?, due_date=?, cpp=?, pages=?, is_confirmed=?, admin_acknowledged=?, acknowledged=? WHERE id=?';

    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ssssssssssiiii', $topic, $subject, $account, $description, $writer, $writerEmail, $status, $due_date, $cpp, $pages, $is_confirmed, $admin_acknowledged, $acknowledged, $taskId);

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) >= 0) { // Changed to >= 0 to handle cases where no changes were made

                // Handle file operations
                // 1. Mark removed files as deleted
                $removedFileIds = !empty($_POST['removedFileIds']) ? json_decode($_POST['removedFileIds'], true) : [];
                if (!empty($removedFileIds)) {
                    foreach ($removedFileIds as $fileId) {
                        $deleteFileQuery = 'UPDATE tbl_task_files SET is_deleted = 1 WHERE id = ? AND task_id = ?';
                        $deleteStmt = mysqli_prepare($con, $deleteFileQuery);
                        mysqli_stmt_bind_param($deleteStmt, 'ii', $fileId, $taskId);
                        mysqli_stmt_execute($deleteStmt);
                        mysqli_stmt_close($deleteStmt);
                    }
                }

                // 2. Add new uploaded files
                $uploadedFiles = [];
                if (isset($_POST['uploadedFiles']) && !empty($_POST['uploadedFiles'])) {
                    $uploadedFilesData = json_decode($_POST['uploadedFiles'], true);
                    if (is_array($uploadedFilesData)) {
                        foreach ($uploadedFilesData as $fileData) {
                            $fileName = mysqli_real_escape_string($con, basename($fileData['filePath']));
                            $originalFileName = mysqli_real_escape_string($con, $fileData['fileName']);
                            $filePath = mysqli_real_escape_string($con, $fileData['filePath']);
                            $fileUrl = mysqli_real_escape_string($con, $fileData['fileUrl']);
                            $fileSize = (int)$fileData['fileSize'];

                            // Insert file record into tbl_task_files
                            $fileInsertQuery = "INSERT INTO tbl_task_files (task_id, file_name, original_file_name, file_path, file_url, file_size, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, 'task', ?)";
                            $fileStmt = mysqli_prepare($con, $fileInsertQuery);
                            mysqli_stmt_bind_param($fileStmt, 'issssis', $taskId, $fileName, $originalFileName, $filePath, $fileUrl, $fileSize, $writer);

                            if (mysqli_stmt_execute($fileStmt)) {
                                // Store file data for email attachments
                                $uploadedFiles[] = $fileData;
                            } else {
                                error_log('Failed to insert file record: ' . mysqli_stmt_error($fileStmt));
                            }
                            mysqli_stmt_close($fileStmt);
                        }
                    }
                }

                $emailStatus = '';

                if ($sendEmail == '1') {
                    $encodedId = base64_encode((string)$taskId);
                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host = 'mail.monkbrian.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'support@monkbrian.com';
                        $mail->Password = 'EDU+pass.';
                        $mail->SMTPSecure = 'ssl';
                        $mail->Port = 465;

                        $mail->setFrom('support@monkbrian.com', 'itasker');
                        $mail->addReplyTo('bryo4419@gmail.com', 'Bryo Gacheru');
                        $mail->addAddress($writerEmail);
                        $mail->addAddress('bryo4419@gmail.com', 'iTasker Admin');

                        // Attach new files only
                        $tempFiles = [];
                        if (!empty($uploadedFiles)) {
                            foreach ($uploadedFiles as $fileData) {
                                $tempFile = tempnam(sys_get_temp_dir(), 'email_attachment_');
                                $tempFiles[] = $tempFile;

                                // URL encode to handle spaces and special characters
                                $encodedUrl = str_replace(' ', '%20', $fileData['fileUrl']);

                                $ch = curl_init($encodedUrl);
                                $fp = fopen($tempFile, 'wb');

                                if ($fp === false) {
                                    error_log("Failed to open temp file: $tempFile");
                                    continue;
                                }

                                curl_setopt($ch, CURLOPT_FILE, $fp);
                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                                $success = curl_exec($ch);

                                if ($success === false) {
                                    error_log('cURL error: ' . curl_error($ch) . ' for URL: ' . $encodedUrl);
                                } else {
                                    $mail->addAttachment($tempFile, $fileData['fileName']);
                                }

                                curl_close($ch);
                                fclose($fp);
                            }
                        }

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Task ID: ' . $taskId . ' - ' . $topic . ' - [ ' . $account . ' ] ';

                        // Email Body with Logo and Modern Formatting
                        $companyLogo = 'https://web.monkbrian.com/assets/img/team/itasker-email-header.png';
                        $taskDetailsUrl = 'https://web.monkbrian.com/view-task?task_id=' . $encodedId;

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
                            background: #ffff;
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
                            color: #ffff;
                            padding: 12px;
                            border-radius: 5px;
                            text-decoration: none;
                            font-size: 16px;
                            font-weight: bold;
                            margin-top: 20px;
                            transition: background 0.3s ease-in-out, color 0.3s ease-in-out;
                        }
                        .btn:hover {
                            background: #005bb5;
                            color: #ffff !important;
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
                                <p>&copy; " . date('Y') . ' iTasker. All rights reserved.</p>
                            </div>
                        </div>
                        </body>
                        </html>';

                        $mail->AltBody = 'Task Updated - ' . ($status == 'Draft' ? 'Unconfirmed' : $status) . "\n\n
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
                        error_log('Email Error: ' . $mail->ErrorInfo);
                        $emailStatus = "Email sending failed: {$mail->ErrorInfo}";
                    }
                }

                header('Content-Type: application/json');
                $response = [
                    'status' => 'success',
                    'message' => 'Task updated successfully.',
                    'task_id' => base64_encode($taskId)
                ];

                if (!empty($emailStatus)) {
                    $response['emailStatus'] = $emailStatus;
                }

                echo json_encode($response);
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
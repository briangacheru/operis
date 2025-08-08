<?php
include "check-login.php";
require_once 'spaces-helper.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
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

    $topic = mysqli_real_escape_string($con, $_POST['topic']);
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $account = mysqli_real_escape_string($con, $_POST['account']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $writer = mysqli_real_escape_string($con, $_POST['writer']);
    $writerEmail = mysqli_real_escape_string($con, $_POST['email']);
    $due_date = mysqli_real_escape_string($con, $_POST['due_date']);
    $cpp = mysqli_real_escape_string($con, $_POST['cpp']);
    $pages = mysqli_real_escape_string($con, $_POST['pages']);
    $is_confirmed = mysqli_real_escape_string($con, $_POST['is_confirmed']);
    $writerInfo = explode('|', mysqli_real_escape_string($con, $_POST['writer']));
    $writerName = $writerInfo[0];
    // Determine the status based on is_confirmed value
    $status = ($is_confirmed == 0) ? 'In Progress' : 'Draft';

    $sql = 'INSERT INTO tbltasks (topic, subject, account, description, writer, email, due_date, cpp, pages, is_confirmed, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

    if ($stmt = mysqli_prepare($con, $sql)) {
        // Bind parameters and execute statement
        mysqli_stmt_bind_param($stmt, 'sssssssssss', $topic, $subject, $account, $description, $writerName, $writerEmail, $due_date, $cpp, $pages, $is_confirmed, $status);

        if (mysqli_stmt_execute($stmt)) {
            // Check if insert was successful
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $task_id = mysqli_insert_id($con); // Get the last inserted task ID
                $encodedId = base64_encode((string)$task_id);

                // Handle file uploads - Insert into new tbl_task_files table
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
                            $fileInsertQuery = "INSERT INTO tbl_task_files (task_id, file_name, original_file_name, file_path, file_url, file_size, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, 'task', 'Admin')";
                            $fileStmt = mysqli_prepare($con, $fileInsertQuery);
                            mysqli_stmt_bind_param($fileStmt, 'issssi', $task_id, $fileName, $originalFileName, $filePath, $fileUrl, $fileSize);

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

                // Send email using PHPMailer
                $mail = new PHPMailer(true);
                $emailSent = false;

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'mail.monkbrian.com'; // Your SMTP server
                    $mail->SMTPAuth = true;
                    $mail->Username = 'support@monkbrian.com'; // SMTP username
                    $mail->Password = 'EDU+pass.'; // SMTP password
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = 465;

                    // Recipients
                    $mail->setFrom('support@monkbrian.com', 'itasker');
                    $mail->addReplyTo('bryo4419@gmail.com', 'Bryo Gacheru');
                    $mail->addAddress($writerEmail); // Writer's email
                    $mail->addAddress('bryo4419@gmail.com', 'itasker Admin'); // Example admin email, replace with actual admin email

                    // Attachments - now using the files from the new table
                    if (!empty($uploadedFiles)) {
                        $spacesHelper = new SpacesHelper();
                        $tempFiles = []; // Track temp files for cleanup

                        foreach ($uploadedFiles as $fileData) {
                            // Add attachments using the file URL
                            $tempFile = tempnam(sys_get_temp_dir(), 'email_attachment_');
                            $tempFiles[] = $tempFile; // Track this temp file

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
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Add timeout

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
                    $mail->isHTML(true); // Set email format to HTML
                    $mail->Subject = 'Task ID: ' . $task_id . ' - ' . $topic . ' - [ ' . $account . ' ] ';

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
                        background: #005bb5; /* Darker blue on hover */
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
                            <img src='{$companyLogo}' alt='itasker logo'>
                        </div>
                        <div class='email-content'>
                            <h2>New " . ($status == 'Draft' ? 'Unconfirmed' : $status) . " Task Assigned</h2>
                            <p>Hello <span class='highlight'>$writerName</span>,</p>
                            <p>A new task has been assigned to you. Below are the details:</p>
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

                    $mail->AltBody = 'New ' . ($status == 'Draft' ? 'Unconfirmed' : $status) . "Task Assigned\n\n
                    Hello $writerName,\n
                    A new task has been assigned to you.\n
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
                    $emailSent = true;

                    // Clean up - delete all temp files
                    foreach ($tempFiles as $tempFile) {
                        @unlink($tempFile);
                    }
                } catch (Exception $e) {
                    // Handle email sending error
                    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }

                $emailStatus = $emailSent ? 'Email sent successfully.' : 'Email sending failed.';
                $successMessage = "Task created successfully. {$emailStatus}";

                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => $successMessage,
                    'task_id' => $encodedId,
                    'emailSent' => $emailSent
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Failed to create task.']);
            }
        } else {
            // Handle execution error
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt); // Close statement
    } else {
        // Handle preparation error
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'No action performed.']);
}
?>
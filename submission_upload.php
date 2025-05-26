<?php
include "check-login.php";
require_once 'spaces-helper.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_POST['action'] == 'submitForm') {
    // Ensure taskfiles has at least one file
    if (empty($_POST['uploadedFiles']) || $_POST['uploadedFiles'] === '[]') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'You must submit at least one file.']);
        exit;
    }

    // Retrieve and sanitize input data
    $taskId = isset($_POST['taskId']) ? mysqli_real_escape_string($con, $_POST['taskId']) : '';
    $topic = isset($_POST['topic']) ? mysqli_real_escape_string($con, $_POST['topic']) : '';
    $due = isset($_POST['due']) && !empty($_POST['due']) ? mysqli_real_escape_string($con, $_POST['due']) : 'Not Provided';
    $writer = isset($_POST['writer']) && !empty($_POST['writer']) ? mysqli_real_escape_string($con, $_POST['writer']) : 'Not Provided';
    $account = isset($_POST['account']) ? mysqli_real_escape_string($con, $_POST['account']) : '';
    $writerEmail = isset($_POST['email']) ? mysqli_real_escape_string($con, $_POST['email']) : '';
    $sendEmail = isset($_POST['sendEmail']) ? mysqli_real_escape_string($con, $_POST['sendEmail']) : '0';
    $pages = $_POST['pages'] ?? '';
    $cpp = $_POST['cpp'] ?? '';

// Fetch additional details from the database if needed
    $query = "SELECT due_date, writer, pages, cpp FROM tbltasks WHERE id = ?";
    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, 'i', $taskId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $due_date, $writer, $pages, $cpp);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Use database values if available
        $due = $due_date ?? $due;
        $writer = $writer ?? $writer;
        $cpp = $cpp ?? $cpp;
        $pages = $pages ?? $pages;
    }

    if (empty($taskId) || empty($topic) || empty($account) || empty($writerEmail)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Task ID, Topic, Account, and Email are required.']);
        exit;
    }

    // Fetch existing files from the database
    $query = "SELECT submitted_files FROM tbltasks WHERE id = ?";
    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, 'i', $taskId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $existingFilesString);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
        exit;
    }

    $existingFiles = !empty($existingFilesString) ? explode(',', $existingFilesString) : [];
    $existingFileUrls = !empty($existingFileUrlsString) ? explode(',', $existingFileUrlsString) : [];

    $uploadedFiles = json_decode($_POST['uploadedFiles'], true);
    if (!is_array($uploadedFiles)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid uploaded files data.']);
        exit;
    }

    $uploadedFilePaths = array_map(function($file) {
        return $file['filePath'];
    }, $uploadedFiles);
    $uploadedFileUrls = array_map(function($file) {
        return $file['fileUrl'];
    }, $uploadedFiles);

    $allFilePaths = array_merge($existingFiles, $uploadedFilePaths);
    $allFileUrls = array_merge($existingFileUrls, $uploadedFileUrls);

    $filesPathString = implode(',', $allFilePaths);
    $filesUrlString = implode(',', $allFileUrls);
    $submittedOn = date('Y-m-d H:i:s');

    $sql = "UPDATE tbltasks SET submitted_files=?, submitted_file_urls=?, submitted_on=?, status='Submitted' WHERE id=?";

    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, 'sssi', $filesPathString, $filesUrlString, $submittedOn, $taskId);

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                if ($sendEmail == '1') {
                    $encodedId = base64_encode((string)$taskId);
                    $total_price = $pages * $cpp;
                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'mail.monkbrian.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'support@monkbrian.com';
                        $mail->Password   = 'EDU+pass.';
                        $mail->SMTPSecure = 'ssl';
                        $mail->Port       = 465;

                        $mail->setFrom('support@monkbrian.com', 'Bryo Gacheru');
                        $mail->addReplyTo('bryo4419@gmail.com', 'Bryo Gacheru');
                        $mail->addAddress($writerEmail);
                        $mail->addAddress('bryo4419@gmail.com', 'iTasker Admin');

                        foreach ($uploadedFiles as $file) {
                            // Download the file from Digital Ocean to a temporary location
                            $tempFile = tempnam(sys_get_temp_dir(), 'email_attachment_');
                            $fileContent = file_get_contents($file['fileUrl']);

                            if ($fileContent !== false) {
                                file_put_contents($tempFile, $fileContent);
                                $fileName = basename($file['filePath']);
                                $mail->addAttachment($tempFile, $fileName);
                            }
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
                                        <h2>Task Submitted Successfully!</h2>
                                        <p>Hello <span class='highlight'>$writer</span>,</p>
                                        <p>Task <strong>$taskId</strong> has been submitted successfully. Below are the task details:</p>
                                        <p><strong>Topic:</strong> <span class='highlight'>$topic</span></p>
                                        <p><strong>Pages:</strong> $pages</p>
                                        <p><strong>Price per Page:</strong> Ksh $cpp</p>
                                        <p><strong>Total Price:</strong> <span class='highlight'>Ksh $total_price</span></p>
                                        <p><strong>Due Date:</strong> <span class='highlight'>$due_date</span></p>
                                        <p><strong>Submitted:</strong> <span class='highlight'>$submittedOn</span></p>
                                        
                                        <a class='btn' href='$taskDetailsUrl'>View More Task Details</a>
                                    </div>
                                    <div class='footer'>
                                        <p>For any questions, contact <a href='mailto:bryo4419@gmail.com'>bryo4419@gmail.com</a></p>
                                        <p>&copy; " . date('Y') . " iTasker. All rights reserved.</p>
                                    </div>
                                </div>
                            </body>
                            </html>";

                        $mail->AltBody = "Task Submitted Successfully!\n\n
                    Hello $writer,\n
                    Task $taskId has been submitted successfully. Below are the task details:\n
                    Topic: $topic\n
                    Pages: $pages\n
                    Price per Page: Ksh $cpp\n
                    Total Price: Ksh $total_price\n
                    Due Date: $due_date\n
                    Description: $submittedOn\n
                    View Task Details: $taskDetailsUrl\n\n
                    For any questions, contact bryo4419@gmail.com";

                        $mail->send();
                        // Clean up temporary files
                        foreach ($uploadedFiles as $file) {
                            @unlink($tempFile);
                        }

                        $emailStatus = 'Email sent successfully.';
                    } catch (Exception $e) {
                        $emailStatus = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }

                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Task submitted successfully. ' . ($sendEmail == '1' ? $emailStatus : ''), 'task_id' => base64_encode($taskId)]);
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

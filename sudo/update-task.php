<?php
include "check-login.php";

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

    $existingFiles = $_POST['existingFiles'] ?? [];
    $removedFiles = !empty($_POST['removedFiles']) ? json_decode($_POST['removedFiles'], true) : [];

    // Filter out the removed files from the existing files array
    $remainingFiles = array_filter($existingFiles, function($filePath) use ($removedFiles) {
        return !in_array($filePath, $removedFiles);
    });

    $uploadedFiles = json_decode($_POST['uploadedFiles'], true);
    if (!is_array($uploadedFiles)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid uploaded files data.']);
        exit;
    }

    $uploadedFileNames = array_map(function($file) {
        return basename($file['filePath']);
    }, $uploadedFiles);

    $allFiles = array_merge($remainingFiles, $uploadedFileNames);
    $filesString = implode(',', $allFiles);

    $sql = "UPDATE tbltasks SET topic=?, subject=?, account=?, description=?, writer=?, email=?, status=?, due_date=?, cpp=?, pages=?, is_confirmed=?, task_files=? WHERE id=?";

    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ssssssssssssi', $topic, $subject, $account, $description, $writer, $writerEmail, $status, $due_date, $cpp, $pages, $is_confirmed, $filesString, $taskId);

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
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

                        $mail->setFrom('support@monkbrian.com', 'Bryo Gacheru');
                        $mail->addReplyTo('bryo4419@gmail.com', 'Bryo Gacheru');
                        $mail->addAddress($writerEmail);
                        $mail->addAddress('bryo4419@gmail.com', 'iTasker Admin');

                        foreach ($uploadedFiles as $file) {
                            $filePath = "../taskfiles/" . basename($file['filePath']);
                            $mail->addAttachment($filePath);
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
                                    <h2>New " . ($status == 'Draft' ? 'Unconfirmed' : $status) . " Task Assigned</h2>
                                    <p>Hello <span class='highlight'>$writer</span>,</p>
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
                                    <p>&copy; " . date('Y') . " iTasker. All rights reserved.</p>
                                </div>
                            </div>
                        </body>
                        </html>";

                        $mail->AltBody = "New " . ($status == 'Draft' ? 'Unconfirmed' : $status) . " Task Assigned\n\n
                    Hello $writer,\n
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
                        $emailStatus = 'Email sent successfully.';
                    } catch (Exception $e) {
                        $emailStatus = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }

                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Task updated successfully. ' . ($sendEmail == '1' ? $emailStatus : ''), 'task_id' => base64_encode($taskId)]);
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

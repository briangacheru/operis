<?php
include "check-login.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function sanitizeFileName($filename) {
    $sanitized = str_replace(['#', ','], '_', $filename);
    return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $sanitized);
}

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

    $uploadedFiles = json_decode($_POST['uploadedFiles'], true);
    if (!is_array($uploadedFiles)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid uploaded files data.']);
        exit;
    }

    $uploadedFileNames = array_map(function($file) {
        return sanitizeFileName(basename($file['filePath']));
    }, $uploadedFiles);

    $allFiles = array_merge($existingFiles, $uploadedFileNames);
    $filesString = implode(',', $allFiles);

    $sql = "UPDATE tbltasks SET topic=?, subject=?, account=?, description=?, writer=?, email=?, status=?, due_date=?, cpp=?, pages=?, is_confirmed=?, task_files=? WHERE id=?";

    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ssssssssssssi', $topic, $subject, $account, $description, $writer, $writerEmail, $status, $due_date, $cpp, $pages, $is_confirmed, $filesString, $taskId);

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                if ($sendEmail == '1') {
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
                            $filePath = "../taskfiles/" . sanitizeFileName(basename($file['filePath']));
                            $mail->addAttachment($filePath);
                        }

                        $mail->isHTML(true);
                        $mail->Subject = 'Task ID: ' . $taskId . ' - ' . $topic . ' - [ ' . $account. ' ] ';
                        $mail->Body    = "<h1>Task Details</h1>
                                          <p><strong>Topic:</strong> $topic</p>
                                          <p><strong>Subject:</strong> $subject</p>
                                          <p><strong>Due Date:</strong> $due_date</p>
                                          <p><strong>Pages:</strong> $pages</p>
                                          <p><strong>Description:</strong> $description</p>";
                        $mail->AltBody = "New Task Details\nTopic: $topic\nSubject: $subject\nDue Date: $due_date\nPages: $pages\nDescription: $description";

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

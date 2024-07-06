<?php
include "check-login.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Function to sanitize file names
function sanitizeFileName($filename) {
    // Replace special characters with hyphen
    $sanitized = preg_replace('/[^A-Za-z0-9_\-\.]/', '-', $filename);
    return $sanitized;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['task_id'])) {
        $encodedId = $_GET['task_id'];
        $taskId = base64_decode($encodedId);
    } else {
        $_SESSION['alert'] = '<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                    <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                    <p class="mb-0 flex-1">Invalid task ID!</p>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        header('Location: view-task.php');
        exit();
    }

    $uploadedFiles = [];

    // Retrieve existing submitted files and other task details
    $sql = "SELECT * FROM tbltasks WHERE id='$taskId'";
    $result = mysqli_query($con, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $existingFiles = $row['submitted_files'];
        $topic = $row['topic'];
        $account = $row['account'];
        $subject = $row['subject'];
        $due_date = $row['due_date'];
        $submitted_on = $row['submitted_on'];
        $pages = $row['pages'];
        $description = $row['description'];
        $writerEmail = $row['email'];

        // Convert existing files into an array
        $existingFilesArray = !empty($existingFiles) ? explode(',', $existingFiles) : [];

        // Check if files were uploaded
        if (!empty($_FILES['taskfiles']['name'][0])) {
            $totalFiles = count($_FILES['taskfiles']['name']);

            for ($i = 0; $i < $totalFiles; $i++) {
                $fileName = sanitizeFileName($_FILES['taskfiles']['name'][$i]);
                $fileTmpName = $_FILES['taskfiles']['tmp_name'][$i];
                $fileSize = $_FILES['taskfiles']['size'][$i];
                $fileError = $_FILES['taskfiles']['error'][$i];
                $fileType = $_FILES['taskfiles']['type'][$i];

                // Handle file upload errors
                if ($fileError === 0) {
                    $fileDestination = 'taskfiles/' . $fileName;
                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        $uploadedFiles[] = $fileName;
                    } else {
                        $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                                    <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                                    <p class="mb-0 flex-1">Error uploading files!</p>
                                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>';
                        header('Location: view-task.php?task_id=' . $encodedId);
                        exit();
                    }
                } else {
                    $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                                <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                                <p class="mb-0 flex-1">There was an error uploading your files!</p>
                                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                    header('Location: view-task.php?task_id=' . $encodedId);
                    exit();
                }
            }

            // Merge existing and new files
            $allFiles = array_merge($existingFilesArray, $uploadedFiles);
            $submittedFiles = implode(',', $allFiles);
            $submittedOn = date('Y-m-d H:i:s');
            $sql = "UPDATE tbltasks SET submitted_files = '$submittedFiles', submitted_on = '$submittedOn', status = 'Submitted' WHERE id = '$taskId'";

            if (mysqli_query($con, $sql)) {
                // Send email with attachment using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'mail.monkbrian.com'; // Your SMTP server
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'support@monkbrian.com'; // SMTP username
                    $mail->Password   = 'EDU+pass.'; // SMTP password
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port       = 465;

                    // Recipients
                    $mail->setFrom('support@monkbrian.com', 'Bryo Gacheru');
                    $mail->addReplyTo('bryo4419@gmail.com', 'Bryo Gacheru');
                    $mail->addAddress($writerEmail); // Writer's email
                    $mail->addAddress('bryo4419@gmail.com', 'iTasker Admin'); // Example admin email, replace with actual admin email

                    // Attachments
                    foreach ($uploadedFiles as $file) {
                        $mail->addAttachment('taskfiles/' . $file);
                    }

                    // Content
                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->Subject = 'Task ID: ' . $taskId . ' - ' . $topic . ' - [ ' . $account. ' ] ';
                    $mail->Body    = "<h1>Submission</h1>
                                      <p><strong>Due Date:</strong> $due_date</p>
                                      <p><strong>Date Submitted:</strong> $submittedOn</p>";
                    $mail->AltBody = "Task Details\nTopic: $topic\nSubject: $subject\nDue Date: $due_date\nPages: $pages\nDescription: $description\nDate Submitted: $submittedOn";

                    $mail->send();
                    $_SESSION['alert'] = '<div class="alert alert-success border-0 d-flex align-items-center" role="alert">
                                                <div class="bg-success me-3 icon-item"><span class="fas fa-check-circle text-white fs-6"></span></div>
                                                <p class="mb-0 flex-1">Files uploaded successfully, task submitted, and email sent!</p>
                                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                } catch (Exception $e) {
                    $_SESSION['alert'] = '<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                                <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                                <p class="mb-0 flex-1">Files uploaded successfully and task submitted, but email could not be sent. Mailer Error: ' . $mail->ErrorInfo . '</p>
                                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>';
                }

                header('Location: view-task.php?task_id=' . $encodedId);
                exit();
            } else {
                $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                            <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                            <p class="mb-0 flex-1">Error updating task status in the database!</p>
                                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
                header('Location: view-task.php?task_id=' . $encodedId);
                exit();
            }
        } else {
            $_SESSION['alert'] = '<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                        <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                        <p class="mb-0 flex-1">No files selected for upload!</p>
                                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
            header('Location: view-task.php?task_id=' . $encodedId);
            exit();
        }
    } else {
        $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                    <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                    <p class="mb-0 flex-1">Error retrieving task data!</p>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        header('Location: view-task.php?task_id=' . $encodedId);
        exit();
    }
} else {
    $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                <p class="mb-0 flex-1">Invalid request!</p>
                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>';
    header('Location: view-task.php');
    exit();
}
?>

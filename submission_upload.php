<?php
include "check-login.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_POST['action'] == 'submitForm') {
    // Ensure taskfiles has at least one file
    if (empty($_FILES['taskfiles']['name'][0])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'You must submit at least one file.']);
        exit;
    }

    // Retrieve and sanitize input data
    $taskId = isset($_POST['taskId']) ? mysqli_real_escape_string($con, $_POST['taskId']) : '';
    $topic = isset($_POST['topic']) ? mysqli_real_escape_string($con, $_POST['topic']) : '';
    $account = isset($_POST['account']) ? mysqli_real_escape_string($con, $_POST['account']) : '';
    $writerEmail = isset($_POST['email']) ? mysqli_real_escape_string($con, $_POST['email']) : '';
    $sendEmail = isset($_POST['sendEmail']) ? mysqli_real_escape_string($con, $_POST['sendEmail']) : '0';

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

    $uploadedFiles = json_decode($_POST['uploadedFiles'], true);
    if (!is_array($uploadedFiles)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid uploaded files data.']);
        exit;
    }

    $uploadedFileNames = array_map(function($file) {
        return basename($file['filePath']);
    }, $uploadedFiles);

    $allFiles = array_merge($existingFiles, $uploadedFileNames);
    $filesString = implode(',', $allFiles);
    $submittedOn = date('Y-m-d H:i:s');

    $sql = "UPDATE tbltasks SET submitted_files=?, submitted_on=?, status='Submitted' WHERE id=?";

    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ssi', $filesString, $submittedOn, $taskId);

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
                            $filePath = "taskfiles/" . basename($file['filePath']);
                            $mail->addAttachment($filePath);
                        }

                        // Content
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = 'Task ID: ' . $taskId . ' - ' . $topic . ' - [ ' . $account. ' ] ';
                        $mail->Body    = "<h1>Submission</h1>
                                          <p><strong>Date Submitted:</strong> $submittedOn</p>";
                        $mail->AltBody = "Task Details\nDate Submitted: $submittedOn";

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

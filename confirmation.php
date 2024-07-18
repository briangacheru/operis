<?php
include "check-login.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function sendEmail($writerEmail, $taskId, $action, $topic, $account) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.monkbrian.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'support@monkbrian.com';
        $mail->Password   = 'EDU+pass.';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('support@monkbrian.com', 'Bryo Gacheru');
        $mail->addReplyTo('bryo4419@gmail.com', 'Bryo Gacheru');
        $mail->addAddress($writerEmail);
        $mail->addAddress('bryo4419@gmail.com', 'iTasker Admin');

        // Content
        $status = $action == 'accept' ? 'ACCEPTED' : 'DECLINED';
        $mail->isHTML(true);
        $mail->Subject = 'Task ID: ' . $taskId . ' - ' . $topic . ' - [ ' . $account. ' ] ';
        $mail->Body    = "<h1>Task ID: $taskId has been $status.</h1>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

if (isset($_GET['task_id']) && isset($_GET['action'])) {
    $encodedId = $_GET['task_id'];
    $taskId = base64_decode($encodedId);
    $action = $_GET['action'];

    // Fetch task details
    $sql = "SELECT email, topic, account FROM tbltasks WHERE id = '$taskId'";
    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $task = mysqli_fetch_assoc($result);
        $writerEmail = $task['email'];
        $topic = $task['topic'];
        $account = $task['account'];
    } else {
        $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                    <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                    <p class="mb-0 flex-1">Task not found!</p>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        header('Location: view-task');
        exit();
    }

    if ($action == 'accept') {
        // Update the task to be accepted
        $sql = "UPDATE tbltasks SET is_confirmed = 0, status = 'In Progress' WHERE id = '$taskId'";
    } elseif ($action == 'decline') {
        // Update the task to be declined
        $sql = "UPDATE tbltasks SET is_confirmed = 2, status = 'Draft' WHERE id = '$taskId'";
    }

    if (mysqli_query($con, $sql)) {
        // Send email notification
        sendEmail($writerEmail, $taskId, $action, $topic, $account);

        $status = $action == 'accept' ? 'accepted' : 'declined';
        $_SESSION['alert'] = '<div class="alert alert-success border-0 d-flex align-items-center" role="alert">
                                    <div class="bg-success me-3 icon-item"><span class="fas fa-check-circle text-white fs-6"></span></div>
                                    <p class="mb-0 flex-1">Task status updated successfully! The task has been ' . $status . '.</p>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    } else {
        $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                    <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                    <p class="mb-0 flex-1">Error updating task status!</p>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    }

    header('Location: view-task?task_id=' . $encodedId);
    exit();
} else {
    $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                                <div class="bg-danger me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                <p class="mb-0 flex-1">Invalid request!</p>
                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>';
    header('Location: view-task');
    exit();
}
?>

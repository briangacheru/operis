<?php
include "check-login.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Assuming $con is a valid mysqli connection object established in "check-login.php" or elsewhere
if ($_POST['action'] == 'submitForm') {
    // List of required fields
    $requiredFields = ['topic', 'subject', 'account', 'description', 'writer', 'email', 'due_date', 'cpp', 'pages'];

    // Check each required field
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            header('Content-Type: application/json');
            // Respond with an error message indicating which field is missing
            echo json_encode(['status' => 'error', 'message' => "The field {$field} is required."]);
            exit; // Stop the script execution
        }
    }

    // Since FILTER_SANITIZE_STRING is deprecated, consider alternative sanitization
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
    $writerInfo = explode('|', mysqli_real_escape_string($con, $_POST['writer'])); // Split the writer string into an array
    $writerName = $writerInfo[0]; // Get the writer name

    // Decode and process uploaded file paths as before
    $uploadedFiles = json_decode($_POST['uploadedFiles'], true);
    if (!is_array($uploadedFiles)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid uploaded files data.']);
        exit; // Stop the script execution if the uploaded files data is invalid
    }

    // Rename the actual files on the server
    foreach ($uploadedFiles as $index => $fileData) {
        $filePath = $fileData['filePath'];
        $newFilePath = dirname($filePath) . '/' . basename($filePath); // Keep the original file name
        if (!rename($filePath, $newFilePath)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Failed to rename file: ' . $filePath]);
            exit; // Stop the script execution if a file rename fails
        }
        $uploadedFiles[$index]['filePath'] = $newFilePath;
    }

    // Convert the array of sanitized file names to a string to store in your database
    $filesString = implode(',', array_column($uploadedFiles, 'filePath'));

    // Determine the status based on is_confirmed value
    $status = ($is_confirmed == 0) ? 'In Progress' : 'Draft';

    // Prepare SQL statement with placeholders
    $sql = "INSERT INTO tbltasks (topic, subject, account, description, writer, email, due_date, cpp, pages, is_confirmed, status, task_files) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($con, $sql)) {
        // Bind parameters and execute statement
        mysqli_stmt_bind_param($stmt, 'ssssssssssss', $topic, $subject, $account, $description, $writerName, $writerEmail, $due_date, $cpp, $pages, $is_confirmed, $status, $filesString);

        if (mysqli_stmt_execute($stmt)) {
            // Check if insert was successful
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                header('Content-Type: application/json');
                $task_id = mysqli_insert_id($con); // This gets the last inserted ID
                $encodedId = base64_encode((string)$task_id);

                // Send email using PHPMailer
                $mail = new PHPMailer(true);
                $emailSent = false;

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
                    foreach ($uploadedFiles as $fileData) {
                        $mail->addAttachment($fileData['filePath']); // Add attachments
                    }

                    // Content
                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->Subject = 'Task ID: ' . $task_id . ' - ' . $topic . ' - [ ' . $account. ' ] ';
                    $mail->Body    = "<h1>Task Details</h1>
                                      <p><strong>Topic:</strong> $topic</p>
                                      <p><strong>Subject:</strong> $subject</p>
                                      <p><strong>Due Date:</strong> $due_date</p>
                                      <p><strong>Pages:</strong> $pages</p>
                                      <p><strong>Description:</strong> $description</p>";
                    $mail->AltBody = "New Task Details\nTopic: $topic\nSubject: $subject\nDue Date: $due_date\nPages: $pages\nDescription: $description";

                    $mail->send();
                    $emailSent = true;
                } catch (Exception $e) {
                    // Handle email sending error
                    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }

                echo json_encode(['status' => 'success', 'message' => 'Task created successfully.', 'task_id' => $encodedId, 'emailSent' => $emailSent]);
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

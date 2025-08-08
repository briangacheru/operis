<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'vin101.truehost.cloud';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'support@monkbrian.com';
    $mail->Password   = 'EDU+pass.';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('support@monkbrian.com', 'Test');
    $mail->addAddress('bryo4419@gmail.com'); // your email to receive test

    $mail->Subject = 'SMTP Test';
    $mail->Body    = 'This is a test email to check SMTP settings.';

    $mail->send();
    echo 'Test email sent successfully';
} catch (Exception $e) {
    echo "Test email failed: {$mail->ErrorInfo}";
}
?>

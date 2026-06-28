<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    configureMail($mail);

    $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    $mail->addAddress('bryo4419@gmail.com'); // your email to receive test

    $mail->Subject = 'SMTP Test';
    $mail->Body    = 'This is a test email to check SMTP settings.';

    $mail->send();
    echo 'Test email sent successfully';
} catch (Exception $e) {
    echo "Test email failed: {$mail->ErrorInfo}";
}
?>

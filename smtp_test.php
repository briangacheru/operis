<?php
require_once 'config.php';
require_once 'functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    configureMail($mail);

    $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    $mail->addAddress(env('MAIL_ADMIN_EMAIL'));

    $mail->Subject = 'SMTP Test';
    $mail->Body    = 'This is a test email to check SMTP settings.';

    $mail->send();
    echo 'Test email sent successfully';
} catch (Exception $e) {
    echo "Test email failed: {$mail->ErrorInfo}";
}

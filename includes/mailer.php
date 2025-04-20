<?php
require_once 'config.php';
require_once __DIR__ . '/../vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = MAIL_SMTP_AUTH;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_SMTP_SECURE;
        $mail->Port       = MAIL_PORT;

        // Recipients
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for File Management System';
        $mail->Body    = "Your OTP is: <b>$otp</b><br>This OTP is valid for 10 minutes.";
        $mail->AltBody = "Your OTP is: $otp\nThis OTP is valid for 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
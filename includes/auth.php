<?php
require_once 'db.php';
require_once 'functions.php';
session_start();

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user = getUserByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        if ($email === 'srivasthavavarma@gmail.com') {
            // Admin bypasses OTP
            loginUser($user);
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            // Regular user - go to OTP page
            $_SESSION['pending_email'] = $email;
            $otp = generateOTP();
            storeOTP($email, $otp);
            // In production, you'd email the OTP. For now, show on screen for testing
            $_SESSION['otp_debug'] = $otp;
            header("Location: ../otp.php");
            exit();
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
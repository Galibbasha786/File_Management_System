<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
    exit();
}

if (!isset($_SESSION['temp_email'])) {
    header("Location: login.php");
    exit();
}

$error = '';

// Check if the logged-in user is an admin and skip OTP verification for admin
if ($_SESSION['temp_email'] === 'srivasthavavarma@gmail.com') {
    // Get the admin email from the session
    $email = $_SESSION['temp_email'];

    // Ensure the admin account is marked as verified if it's not already verified
    $user = getUserByEmail($email);
    
    // Automatically mark admin as verified if not verified already
    if (!$user['is_verified']) {
        verifyUser($email);  // Mark admin as verified
        $user['is_verified'] = true;
    }

    // Log the admin in (admin doesn't need OTP)
    loginUser($user);

    // Clear temp session after login
    unset($_SESSION['temp_email']);

    // Redirect to admin dashboard
    header("Location: admin/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);
    $email = $_SESSION['temp_email'];
    
    if (empty($otp)) {
        $error = 'OTP is required.';
    } else {
        $otpRecord = verifyOTP($email, $otp);
        
        if ($otpRecord) {
            $user = getUserByEmail($email);
            
            // If this is a new registration, verify the user
            if (!$user['is_verified']) {
                verifyUser($email);
                $user['is_verified'] = true;
            }
            
            // Log the user in
            loginUser($user);
            
            // Clear temp session
            unset($_SESSION['temp_email']);
            
            // Redirect to appropriate dashboard
            header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
            exit();
        } else {
            $error = 'Invalid or expired OTP.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Verify OTP</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <p>We've sent an OTP to your email. Please enter it below to verify your account.</p>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="otp">OTP</label>
                    <input type="text" id="otp" name="otp" required>
                </div>
                
                <button type="submit" class="btn">Verify</button>
            </form>
        </div>
    </div>
</body>
</html>
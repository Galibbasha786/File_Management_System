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
    <style>
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .otp-input {
            width: 40px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            border: 2px solid #ccc;
            border-radius: 5px;
        }
        .otp-input:focus {
            border-color: #4CAF50;
            outline: none;
        }
        .hidden-otp {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Verify OTP</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <p>We've sent an OTP to your email. Please enter it below to verify your account.</p>
            
            <form method="POST" action="" id="otpForm">
                <div class="otp-container">
                    <input type="text" class="otp-input" maxlength="1" data-index="1" autofocus>
                    <input type="text" class="otp-input" maxlength="1" data-index="2">
                    <input type="text" class="otp-input" maxlength="1" data-index="3">
                    <input type="text" class="otp-input" maxlength="1" data-index="4">
                    <input type="text" class="otp-input" maxlength="1" data-index="5">
                    <input type="text" class="otp-input" maxlength="1" data-index="6">
                </div>
                
                <!-- Hidden input that will contain the full OTP -->
                <input type="text" name="otp" id="fullOtp" class="hidden-otp" required>
                
                <button type="submit" class="btn">Verify</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const otpInputs = document.querySelectorAll('.otp-input');
            const fullOtpInput = document.getElementById('fullOtp');
            const form = document.getElementById('otpForm');
            
            // Focus first input on load
            otpInputs[0].focus();
            
            // Handle input events
            otpInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    const value = e.target.value;
                    const index = parseInt(e.target.dataset.index);
                    
                    // If input has value, move to next input
                    if (value.length === 1 && index < otpInputs.length) {
                        otpInputs[index].focus();
                    }
                    
                    // Update the hidden full OTP field
                    updateFullOtp();
                });
                
                // Handle backspace
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && e.target.value === '') {
                        const index = parseInt(e.target.dataset.index);
                        if (index > 1) {
                            otpInputs[index - 2].focus();
                        }
                    }
                    updateFullOtp();
                });
            });
            
            // Update the hidden full OTP field
            function updateFullOtp() {
                let otp = '';
                otpInputs.forEach(input => {
                    otp += input.value;
                });
                fullOtpInput.value = otp;
            }
            
            // Validate OTP length before form submission
            form.addEventListener('submit', function(e) {
                updateFullOtp();
                if (fullOtpInput.value.length !== otpInputs.length) {
                    e.preventDefault();
                    alert('Please enter the complete OTP code.');
                }
            });
        });
    </script>
</body>
</html>
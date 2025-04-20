<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error = 'Both email and password are required.';
    } else {
        $user = getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_verified']) {
                // Generate and send OTP
                $otp = generateOTP();
                if (storeOTP($email, $otp)) {
                    require_once 'includes/mailer.php';
                    if (sendOTP($email, $otp)) {
                        $_SESSION['temp_email'] = $email;
                        header("Location: verify-otp.php");
                        exit();
                    } else {
                        $error = 'Failed to send OTP. Please try again.';
                    }
                } else {
                    $error = 'Failed to generate OTP. Please try again.';
                }
            } else {
                $error = 'Account not verified. Please check your email for verification link.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-blue-600 to-blue-800 h-screen flex items-center justify-center">

    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 space-y-6">
        <h1 class="text-3xl font-semibold text-center text-gray-800">Login</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error text-red-600 p-3 bg-red-100 rounded-md text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" id="email" name="email" required class="w-full p-3 mt-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your email">
            </div>
            
            <div>
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" id="password" name="password" required class="w-full p-3 mt-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your password">
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Login
            </button>
        </form>
        
        <div class="text-center">
            <p class="text-sm text-gray-600">Don't have an account? <a href="register.php" class="text-blue-600 font-semibold">Register here</a></p>
        </div>
    </div>

</body>
</html>
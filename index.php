<?php
require_once 'includes/auth.php';

// Redirect to appropriate dashboard based on user role
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
} else {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>
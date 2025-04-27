<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/otp.php';

if (!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['temp_user'];
$inputOtp = trim($_POST['otp']);

if (verifyUserOtp($pdo, $user['id'], $inputOtp)) {
    // OTP is verified. Log the user in.
    $_SESSION['user'] = $user;
    unset($_SESSION['temp_user']);
    header("Location: dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid or expired OTP.";
    header("Location: otp_verify.php");
    exit();
}
?>

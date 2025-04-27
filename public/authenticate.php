<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/otp.php';

$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$password = trim($_POST['password']);

$stmt = $pdo->prepare("SELECT id, name, password, role, email, status, approved FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);



$stmt = $pdo->prepare("SELECT id, name, password, role, email, status, approved FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// ✅ Debugging: Log user details to confirm correct status
error_log(print_r($user, true));

if ($user && password_verify($password, $user['password'])) {
    if ($user['status'] != 'Active') {
        $_SESSION['error'] = "Your account is inactive. Contact admin.";
        header("Location: login.php");
        exit();
    

    } else {
        $_SESSION['error'] = "Failed to set OTP.";
        header("Location: login.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid credentials.";
    header("Location: login.php");
    exit();
}



?>

<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';  // Correct path

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // ✅ Generate unique reset token
    $token = bin2hex(random_bytes(32));

    // ✅ Store token in database
    $stmt = $pdo->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
    $stmt->execute([$token, $email]);

    // ✅ Send the reset link via PHPMailer SMTP
    $resetLink = "http://localhost/waste_management_system/public/reset_password.php?token=" . $token;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';  // Correct Mailtrap host
        $mail->SMTPAuth = true;
        $mail->Port = 2525;  // Correct Mailtrap port
        $mail->Username = '7464c81b9e57c8';  // Your Mailtrap username
        $mail->Password = '9d085d659726f8';  // Your Mailtrap password

        // Optional: For local testing, ignore SSL warnings
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // ✅ Set the email sender and recipient
        $mail->setFrom('onetwelve12@hotmail.com', 'Jimstar Support');  // From address
        $mail->addAddress($email);  // Recipient's email address

        // ✅ Email subject and body
        $mail->Subject = "Password Reset Request";
        $mail->Body = "Click the link below to reset your password:\n\n" . $resetLink;

        // ✅ Send the email
        $mail->send();
        $_SESSION['success'] = "Check your email for the reset link!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to send email: " . $mail->ErrorInfo;
    }

    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | Jimstar Waste Management</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <form method="POST" action="forgot_password.php">
        <input type="email" name="email" required placeholder="Enter your email">
        <button type="submit">Request Reset Link</button>
    </form>
</body>
</html>

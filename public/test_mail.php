<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$to = "mumeenbinssah@gmail.com"; // Replace with a valid email address
$subject = "Test Email";
$message = "Hello, this is a test email!";
$headers = "From: one12sys@gmail.com\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Failed to send email.";
}
?>

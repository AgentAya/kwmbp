<?php
session_start();
require_once '../config/database.php';

$paystack_secret = "sk_test_b466344538b2608acf67c3f108212d2af766828e";

$payment_id = $_GET['payment_id'] ?? null;

if (!$payment_id) {
    $_SESSION['error'] = "Invalid payment ID.";
    header("Location: dashboard.php");
    exit();
}

// Fetch reference using the payment ID
$stmt = $pdo->prepare("SELECT reference FROM payments WHERE id = ?");
$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    $_SESSION['error'] = "Payment record not found.";
    header("Location: dashboard.php");
    exit();
}

$reference = $payment['reference'];

// Query Paystack API
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $paystack_secret"]
]);

$response = curl_exec($curl);
curl_close($curl);

$result = json_decode($response, true);

if (!$result['status']) {
    $_SESSION['error'] = "Could not verify payment. Please try again.";
    header("Location: dashboard.php");
    exit();
}

$paystack_status = $result['data']['status'];

if ($paystack_status === 'success') {
    // Update payment record
    $stmt = $pdo->prepare("UPDATE payments SET status = 'paid' WHERE reference = ?");
    $stmt->execute([$reference]);
    $_SESSION['success'] = "✅ Payment verified successfully.";
} elseif ($paystack_status === 'abandoned') {
    $stmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE reference = ?");
    $stmt->execute([$reference]);
    $_SESSION['error'] = "⚠️ Payment was abandoned by the user.";
} elseif ($paystack_status === 'failed') {
    $stmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE reference = ?");
    $stmt->execute([$reference]);
    $_SESSION['error'] = "❌ Payment failed.";
} else {
    $_SESSION['error'] = "⏳ Payment is still pending. Please check again later.";
}

header("Location: dashboard.php");
exit();

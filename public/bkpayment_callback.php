<?php
session_start(); // ✅ Needed for $_SESSION to work
require_once '../config/database.php';

$paystack_secret = "sk_test_b466344538b2608acf67c3f108212d2af766828e";
$reference = $_GET['reference'] ?? '';

if ($reference) {
    // Step 1: Requery Paystack to verify the transaction
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . urlencode($reference),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $paystack_secret",
            "Content-Type: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($response, true);

    if ($result && $result['status'] && $result['data']['status'] === 'success') {
        $paid_at = $result['data']['paid_at'];

        // ✅ Update status only if not already paid
        $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = ? WHERE reference = ? AND status != 'paid'");
        $stmt->execute([$paid_at, $reference]);

        // ✅ Get payment ID to redirect to receipt
        $stmt = $pdo->prepare("SELECT id FROM payments WHERE reference = ?");
        $stmt->execute([$reference]);
        $payment = $stmt->fetch();

        if ($payment) {
            $_SESSION['success'] = "Payment verified successfully!";
            header("Location: receipt.php?payment_id=" . $payment['id']);
            exit();
        } else {
            $_SESSION['error'] = "Payment not found.";
        }

    } else {
        // ❌ Mark as failed
        $stmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE reference = ?");
        $stmt->execute([$reference]);

        $_SESSION['error'] = "Payment verification failed or still pending.";
    }
} else {
    $_SESSION['error'] = "Invalid reference.";
}

header("Location: dashboard.php");
exit();

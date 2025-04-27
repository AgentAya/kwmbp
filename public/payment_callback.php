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

        // ✅ Fetch payment ID for receipt generation
$stmt = $pdo->prepare("SELECT id FROM payments WHERE reference = ?");
$stmt->execute([$reference]);
$payment = $stmt->fetch();

if ($payment) {
    $paymentId = $payment['id'];

    // ✅ Get operator ID
    $stmt = $pdo->prepare("SELECT h.operator_id FROM payments p JOIN houses h ON p.house_id = h.id WHERE p.id = ?");
    $stmt->execute([$paymentId]);
    $operator = $stmt->fetch();

    $operatorId = $operator ? intval($operator['operator_id']) : 0;
    $now = new DateTime();
    $monthCode = $now->format('Ym'); // YYYYMM

    $receiptNumber = 'JSW-' . $operatorId . '-' . $monthCode . '-' . str_pad($paymentId, 6, '0', STR_PAD_LEFT);

    // ✅ Update payment with receipt number and status
    $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = ?, receipt_number = ? WHERE id = ? AND status != 'paid'");
    $stmt->execute([$paid_at, $receiptNumber, $paymentId]);

    $_SESSION['success'] = "Payment verified successfully!";
    header("Location: receipt.php?payment_id=" . $paymentId);
    exit();

} else {
    $_SESSION['error'] = "Payment not found.";
}


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

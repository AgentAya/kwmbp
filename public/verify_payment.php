<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['ref'])) {
    die("No reference provided.");
}

$ref = $_GET['ref'];
$stmt = $pdo->prepare("SELECT * FROM payments WHERE reference = ?");
$stmt->execute([$ref]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($payment) {
    echo "Payment Verified:<br>";
    echo "House: " . htmlspecialchars($payment['house_id']) . "<br>";
    echo "Amount: ₦" . number_format($payment['amount'], 2) . "<br>";
    echo "Status: " . htmlspecialchars($payment['status']);
} else {
    echo "Payment not found.";
}
?>

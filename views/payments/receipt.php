<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendors/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_GET['payment_id'])) {
    die("Invalid request.");
}

$payment_id = intval($_GET['payment_id']);

// Fetch payment details
$stmt = $pdo->prepare("
SELECT p.*, h.house_number, h.street, h.city, h.state 
FROM payments p 
JOIN houses h ON p.house_id = h.id 
WHERE p.id = ?
");
$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("Payment not found.");
}

// Generate QR Code linking to a verification URL
$verificationUrl = "http://localhost/waste_management_system/public/verify_payment.php?ref=" . urlencode($payment['reference']);
$result = Builder::create()
    ->writer(new PngWriter())
    ->data($verificationUrl)
    ->size(150)
    ->margin(10)
    ->build();

$qrImageData = $result->getString();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Receipt | Jimstar Waste Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .receipt-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .receipt-header {
            text-align: center;
            padding: 15px;
            background: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .receipt-footer {
            text-align: center;
            padding: 15px;
            margin-top: 20px;
            background: #343a40;
            color: white;
            border-radius: 8px;
        }
        .receipt-footer h4 {
            color: #ffc107;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="/waste_management_system/public/assets/images/jslogo.png" alt="Logo" width="50">
                Jimstar Waste Management
            </a>
        </div>
    </nav>

    <!-- Receipt Container -->
    <div class="receipt-container">
        <div class="receipt-header">
            <h2>Payment Receipt</h2>
        </div>

        <div class="p-4">
            <p><strong>🏠 House Address:</strong> <?= htmlspecialchars("{$payment['house_number']} {$payment['street']}, {$payment['city']}, {$payment['state']}") ?></p>
            <p><strong>💰 Amount:</strong> ₦<?= number_format($payment['amount'], 2) ?></p>
            <p><strong>🔗 Reference:</strong> <?= htmlspecialchars($payment['reference']) ?></p>
            <p><strong>📅 Date:</strong> <?= htmlspecialchars($payment['created_at']) ?></p>
        </div>

        <div class="text-center">
            <h3>Scan QR Code to Verify Payment</h3>
            <img src="data:image/png;base64,<?= base64_encode($qrImageData) ?>" alt="QR Code">
        </div>

        <div class="receipt-footer">
            <h4>Cleaner Osun begins with YOU! 🌍</h4>
            <p><strong>📞 Contact Support:</strong> +234 8038974866 | <strong>Email:</strong> support@jimstar.com</p>
        </div>

        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-primary">⬅️ Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

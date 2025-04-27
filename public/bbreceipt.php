<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendors/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_GET['payment_id']) || !isset($_SESSION['agent_id'])) {
    die("Invalid request.");
}

$payment_id = intval($_GET['payment_id']);
$agent_id = intval($_SESSION['agent_id']);

// Fetch payment details with house and agent validation
$stmt = $pdo->prepare("SELECT p.*, h.house_number, h.street, h.city, h.state, h.building_type, h.number_of_units FROM payments p JOIN houses h ON p.house_id = h.id WHERE p.id = ? AND p.agent_id = ?");
$stmt->execute([$payment_id, $agent_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("Payment not found.");
}

if ($payment['status'] !== 'paid') {
    echo "<div style='text-align: center; padding: 50px; font-family: Arial;'>
            <h3>⚠️ Payment not yet confirmed.</h3>
            <p>Status: <strong>" . htmlspecialchars($payment['status']) . "</strong></p>
            <p>Please confirm payment before generating a receipt.</p>
            <a href='dashboard.php' class='btn btn-secondary'>⬅️ Back to Dashboard</a>
          </div>";
    exit();
}

$verificationUrl = "http://localhost/waste_management_system/public/verify_payment.php?ref=" . urlencode($payment['reference']);
$result = Builder::create()
    ->writer(new PngWriter())
    ->data($verificationUrl)
    ->size(120)
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
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
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
        .qr-box {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .receipt-container, .receipt-container * {
                visibility: visible;
            }
            .receipt-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .navbar, .btn, .d-print-none {
                display: none !important;
            }
            .receipt-header {
                background-color: #007bff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark d-print-none">
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

    <div class="qr-box">
        <img src="data:image/png;base64,<?= base64_encode($qrImageData) ?>" alt="QR Code">
    </div>

    <div class="p-4">
        <p><strong>🏠 House Address:</strong> <?= htmlspecialchars("{$payment['house_number']} {$payment['street']}, {$payment['city']}, {$payment['state']}") ?></p>
        <p><strong>🏢 Building Type:</strong> <?= htmlspecialchars($payment['building_type']) ?></p>
        <p><strong>🔢 No. of Units:</strong> <?= htmlspecialchars($payment['number_of_units']) ?></p>
        <p><strong>💰 Amount:</strong> ₦<?= number_format($payment['amount'], 2) ?></p>
        <p><strong>🔗 Reference:</strong> <?= htmlspecialchars($payment['reference']) ?></p>
        <p><strong>📅 Date:</strong> <?= htmlspecialchars($payment['created_at']) ?></p>
    </div>

    <div class="receipt-footer">
        <h4>Cleaner Osun begins with YOU! 🌍</h4>
        <p><strong>📞 Contact Support:</strong> +234 8038974866 | <strong>Email:</strong> support@jimstar.com</p>
    </div>

    <div class="text-center mt-3 d-print-none">
        <a href="dashboard.php" class="btn btn-primary">⬅️ Back to Dashboard</a>
        <button onclick="window.print()" class="btn btn-success">🖨️ Print Receipt</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

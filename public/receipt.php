 <?php
session_start();
$role = '';




require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendors/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

// Check login status for Agent and Guest
$isAgent = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'agent';
$isGuest = isset($_SESSION['guest']) && isset($_SESSION['guest']['phone']);

if (!$isAgent && !$isGuest) {
    die("Access denied.");
}

// Validate and sanitize payment ID
if (!isset($_GET['payment_id']) || !is_numeric($_GET['payment_id'])) {
    die("Invalid request.");
}

$payment_id = intval($_GET['payment_id']);
$payment = false;

// Agent query
if ($isAgent) {
    $agent_id = $_SESSION['user']['id'];
    $stmt = $pdo->prepare("
        SELECT p.*, h.house_number, h.street, h.area, h.city, h.state, h.building_type, h.number_of_units, h.owner,
               ag.id AS agent_id, ag.name AS agent_name, h.operator_id
        FROM payments p
        JOIN houses h ON p.house_id = h.id
        JOIN users ag ON p.agent_id = ag.id
        WHERE p.id = ? AND p.agent_id = ?
    ");
    $stmt->execute([$payment_id, $agent_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Guest query (if agent check failed or not applicable)
if ($isGuest && !$payment) {
    $guest_phone = $_SESSION['guest']['phone'];
    $stmt = $pdo->prepare("
        SELECT p.*, h.house_number, h.street, h.area, h.city, h.state, h.building_type, h.number_of_units, h.owner,
               ag.id AS agent_id, ag.name AS agent_name, h.operator_id
        FROM payments p
        JOIN houses h ON p.house_id = h.id
        JOIN users ag ON p.agent_id = ag.id
        WHERE p.id = ? AND h.owner_phone = ?
    ");
    $stmt->execute([$payment_id, $guest_phone]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$payment) {
    echo "<h4>❌ Payment not found or access denied.</h4>";
    exit();
}

// Check if payment is confirmed
if ($payment['status'] !== 'paid') {
    echo "<div style='text-align: center; padding: 50px; font-family: Arial;'>
            <h3>⚠️ Payment not yet confirmed.</h3>
            <p>Status: <strong>" . htmlspecialchars($payment['status']) . "</strong></p>
            <a href='" . ($isGuest ? "guest_dashboard.php" : "dashboard.php") . "' class='btn btn-secondary'>⬅️ Back to Dashboard</a>
          </div>";
    exit();
}

// Generate QR Code for verification
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
    <title>Payment Receipt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .receipt-container {
            max-width: 600px; margin: 40px auto; padding: 20px;
            background: white; border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .receipt-header {
            text-align: center; padding: 15px; background: #007bff;
            color: white; border-radius: 10px 10px 0 0;
        }
        .qr-section img { width: 100%; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .navbar, .btn, .receipt-footer, .mt-3 a { display: none !important; }
            .receipt-container {
                box-shadow: none; border: 1px solid #ccc;
                max-width: 100%; margin: 0; padding: 30px; font-size: 14pt;
            }
            .receipt-header { background: #000 !important; color: white !important; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="receipt-header">
        <img src="/waste_management_system/public/assets/images/jslogo.png" width="60" alt="Logo">
        <h2>Jimstar Waste Management</h2>
        <h5>Official Payment Receipt</h5>
        <p><strong>🧾 Receipt No:</strong> <?= htmlspecialchars($payment['receipt_number'] ?? 'N/A') ?></p>
    </div>

    <div class="p-4">
        <p><strong>🏠 Address:</strong> <?= htmlspecialchars("{$payment['house_number']} {$payment['street']}, {$payment['area']}, {$payment['city']}, {$payment['state']}") ?></p>
        <p><strong>👤 House Owner:</strong> <?= htmlspecialchars($payment['owner']) ?></p>
        <p><strong>🏗️ Building Type:</strong> <?= htmlspecialchars($payment['building_type']) ?></p>
        <p><strong>🏢 No. of Units:</strong> <?= intval($payment['number_of_units']) ?></p>
        <p><strong>💰 Amount:</strong> ₦<?= number_format($payment['amount'], 2) ?></p>
        <p><strong>🔗 Reference:</strong> <?= htmlspecialchars($payment['reference']) ?></p>
        <p><strong>📅 Date:</strong> <?= htmlspecialchars($payment['created_at']) ?></p>
        <p><strong>🤵 Agent Name:</strong> <?= htmlspecialchars($payment['agent_name']) ?></p>
        <p><strong>🆔 Agent ID:</strong> <?= intval($payment['agent_id']) ?></p>
        <p><strong>👨‍💼 Operator ID:</strong> <?= intval($payment['operator_id']) ?></p>
    </div>

    <div class="d-flex justify-content-between align-items-center px-4">
        <div>
            <small>Cleaner Osun begins with YOU! 🌍</small><br>
            <small><strong>📞 Support:</strong> +234 8038974866</small><br>
            <small><strong>📧 Email:</strong> support@jimstar.com</small>
        </div>
        <div class="text-center qr-section">
            <h5>Scan to Verify</h5>
            <img src="data:image/png;base64,<?= base64_encode($qrImageData) ?>" alt="QR Code">
        </div>
    </div>

    <div class="text-center mt-3 d-print-none">
        <button onclick="window.print()" class="btn btn-success">🖨️ Print Receipt</button>
        <?php
$back_link = "login.php";

if (isset($_SESSION['guest'])) {
    $back_link = "guest_dashboard.php";
} elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'agent') {
    $back_link = "dashboard.php";
} elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'operator') {
    $back_link = "operator_dashboard.php";
}
?>
<a href="<?= $back_link ?>" class="btn btn-secondary">⬅ Back to Dashboard</a>

    </div>

</body>
</html>

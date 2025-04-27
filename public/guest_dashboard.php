<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['guest'])) {
    header("Location: guest_login.php");
    exit();
}

// Access guest data
$guest = $_SESSION['guest'];
$guestPhone = $guest['phone'] ?? null;

if (!$guestPhone) {
    echo "<p class='alert alert-danger'>Session expired or invalid access. Please <a href='guest_login.php'>log in</a> again.</p>";
    exit();
}

// Fetch house IDs for the guest
$stmtHouse = $pdo->prepare("SELECT id FROM houses WHERE owner_phone = ?");
$stmtHouse->execute([$guestPhone]);
$houseIds = $stmtHouse->fetchAll(PDO::FETCH_COLUMN);

// Initialize payment data
$payments = [];
if (!empty($houseIds)) {
    // Fetch payment history
    $placeholders = implode(',', array_fill(0, count($houseIds), '?'));
    $stmtPayments = $pdo->prepare("
        SELECT p.*, h.house_number, h.street, h.area, h.city
        FROM payments p
        JOIN houses h ON p.house_id = h.id
        WHERE p.house_id IN ($placeholders) AND p.status = 'paid'
        ORDER BY p.created_at DESC
    ");
    $stmtPayments->execute($houseIds);
    $payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
 <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .navbar-brand img {
            width: 50px;
            margin-right: 10px;
        }
        .dashboard-header {
            background: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
        }
        
        .card {
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th {
            background-color: #007bff;
            color: white;
        }
    
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="/waste_management_system/public/assets/images/jslogo.png" alt="Logo">
                Jimstar Waste Management
            </a>
        </div>
    </nav>
<div class="container mt-4">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <a class="navbar-brand" href="#">House Owner Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="guest_profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="welcome.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Welcome Section -->
    <div class="alert alert-primary" role="alert">
        <h4 class="alert-heading">Welcome, <?= htmlspecialchars($guest['owner']) ?>!</h4>
        <p><strong>Phone:</strong> <?= htmlspecialchars($guest['phone']) ?></p>
    </div>

    <!-- House Details Section -->
    <?php if (empty($houseIds)): ?>
        <div class="alert alert-warning" role="alert">
            <p>No registered houses found for your phone number. <a href="register_house.php">Register a house</a>.</p>
        </div>
    <?php else: ?>
        <h4 class="mt-4">🏠 Your Registered Houses</h4>
<table class="table table-striped">
    <thead>
        <tr>
            <th>House Number</th>
            <th>Street</th>
            <th>Area</th>
            <th>City</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $stmtDetails = $pdo->prepare("SELECT id, house_number, street, area, city FROM houses WHERE id IN ($placeholders)");
    $stmtDetails->execute($houseIds);
    $houses = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

    foreach ($houses as $house): ?>
        <tr>
            <td><?= htmlspecialchars($house['house_number']) ?></td>
            <td><?= htmlspecialchars($house['street']) ?></td>
            <td><?= htmlspecialchars($house['area']) ?></td>
            <td><?= htmlspecialchars($house['city']) ?></td>
            <td>
            <form action="guest_collect_payment.php" method="POST">
            <input type="hidden" name="house_id" value="<?= $house['id'] ?>">
                    <button type="submit" class="btn btn-success btn-sm">Make Payment</button>
                
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

    <?php endif; ?>

    <!-- Payment History Section -->
    <h4 class="mt-4">🧾 Payment History</h4>
    <?php if (empty($payments)): ?>
        <div class="alert alert-warning" role="alert">
            <p>No payments found for your registered houses.</p>
        </div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $pay): ?>
                    <tr>
                        <td><?= htmlspecialchars($pay['receipt_number']) ?></td>
                        <td>₦<?= number_format($pay['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($pay['reference']) ?></td>
                        <td><?= htmlspecialchars("{$pay['house_number']} {$pay['street']}, {$pay['area']}, {$pay['city']}") ?></td>
                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($pay['created_at']))) ?></td>
                        <td><a href="receipt.php?payment_id=<?= $pay['id'] ?>" class="btn btn-sm btn-primary">View Receipt</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

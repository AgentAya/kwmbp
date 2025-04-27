<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Initialize variables
$filtered_collection = 0;
$operator_performance = [];

// Fetch Overall Total Collections (Default: all-time total)
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $stmt = $pdo->prepare("
        SELECT SUM(payments.amount) AS filtered_collections 
        FROM payments 
        WHERE status = 'paid' AND created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$_GET['start_date'], $_GET['end_date']]);
    $filtered_collection = $stmt->fetch(PDO::FETCH_ASSOC)['filtered_collections'] ?? 0;
} else {
    $stmt = $pdo->query("SELECT SUM(payments.amount) AS overall_collections FROM payments WHERE status = 'paid'");
    $overall_collection = $stmt->fetch(PDO::FETCH_ASSOC)['overall_collections'] ?? 0;
    $filtered_collection = $overall_collection;
}


// Fetch Operator Performance Metrics (Weekly & Monthly Trends)
try {
    $stmt = $pdo->query("
        SELECT users.name AS operator_name, 
               SUM(CASE WHEN payments.status = 'paid' AND payments.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN payments.amount ELSE 0 END) AS last_week_collections,
               SUM(CASE WHEN payments.status = 'paid' AND payments.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN payments.amount ELSE 0 END) AS last_month_collections,
               SUM(CASE WHEN payments.status = 'paid' THEN payments.amount ELSE 0 END) AS total_collected
        FROM payments 
        JOIN houses ON payments.house_id = houses.id 
        JOIN users ON houses.operator_id = users.id 
        WHERE users.role = 'operator'
        GROUP BY houses.operator_id
        ORDER BY total_collected DESC
    ");
    $operator_performance = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Retrieve operator details and status 
$stmt = $pdo->query("
    SELECT id, name, email, status 
    FROM users 
    WHERE role = 'operator'
");
$operators = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch Total Number of Operators
$stmt = $pdo->query("
    SELECT COUNT(*) AS total_operators 
    FROM users 
    WHERE role = 'operator'
");
$operator_count = $stmt->fetch(PDO::FETCH_ASSOC)['total_operators'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | Jimstar Waste Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
            padding: 20px;
            text-align: center;
            border-radius: 10px;
        }
        .card {
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            text-align: center;
        }
        .table {
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

 <!-- Navbar -->
 <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="/waste_management_system/public/assets/images/jslogo.png" alt="Logo">
                Jimstar Waste Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
<div class="container mt-4">
    <div class="dashboard-header">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']); ?> 👋</h2>
        <p>Role: <strong>Administrator</strong></p>
        <a href="welcome.php" class="btn btn-danger">Log Out</a>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <h4>Overall Total Collections</h4>
                <h2>₦<?= number_format($filtered_collection, 2); ?></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <h4>Total Operators Managed</h4>
                <h2><?= $operator_count; ?></h2>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="mt-3">
        <label>Start Date:</label>
        <input type="date" name="start_date" required>
        <label>End Date:</label>
        <input type="date" name="end_date" required>
        <button type="submit" class="btn btn-primary">Filter Data</button>
        <a href="export_report.php" class="btn btn-info">Export Collections (CSV)</a>

    </form>

    <div class="card p-3 mt-4">
        <h3>📊 Operator Performance Metrics</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Operator Name</th>
                    <th>Collections Last 7 Days</th>
                    <th>Collections Last 30 Days</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($operator_performance as $operator): ?>
                    <tr>
                        <td><?= htmlspecialchars($operator['operator_name']); ?></td>
                        <td>₦<?= number_format($operator['last_week_collections'], 2); ?></td>
                        <td>₦<?= number_format($operator['last_month_collections'], 2); ?></td>
                        <td>₦<?= number_format($operator['total_collected'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="card p-3 mt-4">
    <h3>👨‍💼 Operator Management</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Operator Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($operators as $operator): ?>
                <tr>
                    <td><?= htmlspecialchars($operator['name']); ?></td>
                    <td><?= htmlspecialchars($operator['email']); ?></td>
                    <td>
                        <span class="badge <?= ($operator['status'] == 'Active') ? 'bg-success' : (($operator['status'] == 'Pending') ? 'bg-warning' : 'bg-danger'); ?>">
                            <?= htmlspecialchars($operator['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="manage_operator.php?action=approve&id=<?= $operator['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                        <a href="manage_operator.php?action=suspend&id=<?= $operator['id']; ?>" class="btn btn-warning btn-sm">Suspend</a>
                        <a href="manage_operator.php?action=remove&id=<?= $operator['id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// ✅ Ensure Database Connection
require_once __DIR__ . '/../config/database.php'; 

// ✅ Check if $pdo is defined
if (!isset($pdo)) {
    die("Database connection error.");
}
// Redirect admins away from the agent dashboard
if ($_SESSION['user']['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

// Redirect operators away from the agent dashboard
if ($_SESSION['user']['role'] === 'operator') {
    header("Location: operator_dashboard.php");
    exit();
}


// Get the user from session
$user = $_SESSION['user'];

try {
    $houses = [];
    $payments = [];
    $totalHouses = 0;
    $totalPayments = 0;

    if ($user['role'] === 'agent') {
        $agent_id = $user['id'];

        // Fetch houses securely using prepared statements
        $stmt = $pdo->prepare("SELECT id, house_number, street, area, city, state, owner, created_at FROM houses WHERE agent_id = ? AND status = 'active'");
        $stmt->execute([$user['id']]);
        $houses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalHouses = count($houses);

        // Fetch payment information securely
        $stmt = $pdo->prepare("SELECT payments.*, houses.house_number, houses.street,houses.area, houses.city, houses.state 
        FROM payments 
        JOIN houses ON payments.house_id = houses.id 
        WHERE houses.agent_id = ? AND payments.status = 'paid'");

        $stmt->execute([$agent_id]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate total payments collected
        $stmt = $pdo->prepare("SELECT SUM(amount) FROM payments 
        JOIN houses ON payments.house_id = houses.id 
        WHERE houses.agent_id = ? AND payments.status = 'paid'");
$stmt->execute([$agent_id]);
$totalPayments = $stmt->fetchColumn() ?: 0;

    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agent Dashboard | Jimstar Waste Management</title>
     
    <!-- Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="container mt-4">
            <div class="dashboard-header">
            <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?> 👋</h2>
            <p>Role: <strong><?php echo ucfirst($user['role']); ?></strong></p>
            <a href="welcome.php" class="btn btn-danger">Log Out</a>
        </div>

        <?php if ($user['role'] === 'agent'): ?>
            <div class="mt-4">
                <h3>📊 Summary</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card p-3 text-center bg-info text-white">
                            <h4>Total Houses Registered</h4>
                            <h2><?php echo $totalHouses; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-3 text-center bg-success text-white">
                            <h4>Total Payments Collected</h4>
                            <h2>₦<?php echo number_format($totalPayments, 2); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <a href="house_register.php" class="btn btn-primary">Register a House</a>
                <a href="view_payments.php" class="btn btn-secondary">View Payments Details</a>
                <a href= "collect_payment.php" class="btn btn-secondary">Collect Payments<a>
            </div>

            <div class="mt-4">
                <h3>🏡 Registered Houses</h3>
                <div class="card p-3">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>House ID</th>
                                <th>Address</th>
                                <th>Owner</th>
                                <th>Registered Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($houses as $house): ?>
    <tr>
        <td><?php echo htmlspecialchars($house['id']); ?></td>
        <td><?php echo htmlspecialchars($house['house_number'] . ' ' . $house['street'] . ' ,' .$house['area'] . ' ,'.$house['city'] . ', ' . $house['state']); ?></td>
        
        <td><?php echo htmlspecialchars($house['owner'] ?? 'N/A'); ?></td>
        <td><?php echo htmlspecialchars($house['created_at'] ?? 'N/A'); ?></td>
    </tr>
<?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <h3>💰 Payment Information</h3>
                <div class="card p-3">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>House Address</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($payments as $payment): ?>
    <tr>
        <td><?php echo htmlspecialchars($payment['id']); ?></td>
        <td><?php echo htmlspecialchars($payment['house_number'] . ' ' . $payment['street'] . ', ' . $payment['area']. ' ,' .$payment['city'] . ', ' . $payment['state']); ?></td>
        <td>₦<?php echo number_format($payment['amount'], 2); ?></td>
        <td class="<?php echo ($payment['status'] === 'paid') ? 'text-success' : 'text-danger'; ?>">
            <strong><?php echo ucfirst(htmlspecialchars($payment['status'])); ?></strong>
        </td>
        <td><?php echo htmlspecialchars($payment['created_at'] ?? 'N/A'); ?></td>
    </tr>
<?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="footer">&copy; <?php echo date('Y'); ?> Jimstar Waste Management. All Rights Reserved.</div>

    <!-- Bootstrap JS for interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
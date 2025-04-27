<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$user = $_SESSION['user'];
$role = $user['role'];
$user_id = $user['id'];

$query = "SELECT p.id, h.house_number, u_agent.name AS agent_name, u_operator.name AS operator_name, 
                 p.amount, p.reference, p.status, p.created_at 
          FROM payments p
          JOIN houses h ON p.house_id = h.id
          JOIN users u_agent ON p.agent_id = u_agent.id
          JOIN users u_operator ON p.operator_id = u_operator.id";

if ($role == 'agent') {
    $query .= " WHERE p.agent_id = ?";
} elseif ($role == 'operator') {
    $query .= " WHERE p.operator_id = ?";
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);

if ($role == 'agent' || $role == 'operator') {
    $stmt->execute([$user_id]);
} else {
    $stmt->execute();
}

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payments</title>

    <!-- Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
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
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
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
        .status-paid {
            color: green;
            font-weight: bold;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        .status-failed {
            color: red;
            font-weight: bold;
        }
        .container {
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="dashboard-header">
            <h2>💰 Payment Records</h2>
        </div>

        <div class="card">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>House Number</th>
                        <th>Agent</th>
                        <th>Operator</th>
                        <th>Amount (₦)</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= htmlspecialchars($payment['house_number']) ?></td>
                        <td><?= htmlspecialchars($payment['agent_name']) ?></td>
                        <td><?= htmlspecialchars($payment['operator_name']) ?></td>
                        <td>₦<?= number_format($payment['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($payment['reference']) ?></td>
                        <td class="<?= 
    $payment['status'] === 'paid' ? 'status-paid' :
    ($payment['status'] === 'pending' ? 'status-pending' : 'status-failed')
?>">
    <?= ucfirst(htmlspecialchars($payment['status'])) ?>
    <?php if ($payment['status'] === 'pending'): ?>
        <span title="You can requery this payment for update">🕒</span>
    <?php endif; ?>
</td>

                        <td><?= htmlspecialchars($payment['created_at']) ?></td>
                        <td>
    <a href="receipt.php?payment_id=<?= $payment['id'] ?>" class="btn btn-primary btn-sm mb-1">🧾 Receipt</a>
    
    <?php if ($payment['status'] === 'pending'): ?>
        <a href="requery_payment.php?payment_id=<?= $payment['id'] ?>" class="btn btn-warning btn-sm">🔄 Requery</a>
    <?php endif; ?>
</td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
        </div>
    </div>

    <!-- Bootstrap JS for interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

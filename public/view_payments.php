<?php
session_start();

if (!isset($_SESSION['user']) && !isset($_SESSION['guest'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$role = '';
$user_id = null;
$guest_phone = null;
$payments = [];

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $role = $user['role'];
    $user_id = $user['id'];
} elseif (isset($_SESSION['guest'])) {
    $role = 'guest';
    $guest_phone = $_SESSION['guest']['phone'];
}

if ($role === 'guest') {
    // Get all house IDs for guest
    $stmtHouse = $pdo->prepare("SELECT id FROM houses WHERE owner_phone = ?");
    $stmtHouse->execute([$guest_phone]);
    $houseIds = $stmtHouse->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($houseIds)) {
        $placeholders = implode(',', array_fill(0, count($houseIds), '?'));
        $query = "SELECT p.id, h.house_number, u_agent.name AS agent_name, u_operator.name AS operator_name, 
                         p.amount, p.reference, p.status, p.created_at 
                  FROM payments p
                  JOIN houses h ON p.house_id = h.id
                  JOIN users u_agent ON p.agent_id = u_agent.id
                  JOIN users u_operator ON p.operator_id = u_operator.id
                  WHERE p.house_id IN ($placeholders) AND p.status = 'paid'
                  ORDER BY p.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($houseIds);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $query = "SELECT p.id, h.house_number, u_agent.name AS agent_name, u_operator.name AS operator_name, 
                     p.amount, p.reference, p.status, p.created_at 
              FROM payments p
              JOIN houses h ON p.house_id = h.id
              JOIN users u_agent ON p.agent_id = u_agent.id
              JOIN users u_operator ON p.operator_id = u_operator.id";

    if ($role === 'agent') {
        $query .= " WHERE p.agent_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
    } elseif ($role === 'operator') {
        $query .= " WHERE p.operator_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
    }

    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Payments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Arial', sans-serif; }
        .dashboard-header { background: #343a40; color: white; padding: 15px; text-align: center; border-radius: 10px; }
        .card { border: none; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); background: white; padding: 20px; border-radius: 10px; margin-top: 20px; }
        table { background: white; border-radius: 10px; overflow: hidden; }
        th { background-color: #007bff; color: white; }
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
        .status-failed { color: red; font-weight: bold; }
        .container { margin-top: 30px; }
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
                <?php if (count($payments) === 0): ?>
                    <tr><td colspan="8" class="text-center text-muted">No payments found.</td></tr>
                <?php else: ?>
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
                            <?php if ($payment['status'] === 'pending' && $role !== 'guest'): ?>
                                <a href="requery_payment.php?payment_id=<?= $payment['id'] ?>" class="btn btn-warning btn-sm">🔄 Requery</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="<?= $role === 'guest' ? 'guest_dashboard.php' : 'dashboard.php' ?>" class="btn btn-secondary">⬅️ Back to Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
require_once __DIR__ . '/../config/database.php';

// Example: Total collections and count of payments
$stmt = $pdo->query("SELECT COUNT(*) as count, SUM(amount) as total FROM payments WHERE status = 'completed'");
$report = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
</head>
<body>
    <h2>Payment Reports</h2>
    <p>Total Payments: <?php echo htmlspecialchars($report['count']); ?></p>
    <p>Total Collections: ₦<?php echo number_format($report['total'], 2); ?></p>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>

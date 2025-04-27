







<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'operator') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Operator ID
$operator_id = $_SESSION['user']['id'];

// Fetch total collections per waste agent under this operator
$stmt = $pdo->prepare("SELECT users.name AS agent_name, SUM(payments.amount) AS total_collected 
                       FROM payments 
                       JOIN houses ON payments.house_id = houses.id 
                       JOIN users ON houses.agent_id = users.id
                       WHERE houses.operator_id = ?
                       GROUP BY houses.agent_id");
$stmt->execute([$operator_id]);
$agent_collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch overall collections (total revenue)
$stmt = $pdo->prepare("SELECT SUM(payments.amount) AS overall_collections 
                       FROM payments 
                       JOIN houses ON payments.house_id = houses.id 
                       WHERE houses.operator_id = ?");
$stmt->execute([$operator_id]);
$overall_collection = $stmt->fetch(PDO::FETCH_ASSOC)['overall_collections'] ?? 0;

// Fetch highest collection agent
$stmt = $pdo->prepare("SELECT users.name AS highest_agent, SUM(payments.amount) AS highest_amount 
                       FROM payments 
                       JOIN houses ON payments.house_id = houses.id 
                       JOIN users ON houses.agent_id = users.id
                       WHERE houses.operator_id = ?
                       GROUP BY houses.agent_id
                       ORDER BY highest_amount DESC LIMIT 1");
$stmt->execute([$operator_id]);
$top_agent = $stmt->fetch(PDO::FETCH_ASSOC) ?? null;

$graph_data = json_encode($agent_collections);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator Dashboard</title>

    <!-- Bootstrap & Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
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
        .collection-stat {
            font-size: 22px;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>

    <div class="container mt-4">
        <div class="dashboard-header">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']); ?> 👋</h2>
            <p>Role: <strong>Operator</strong></p>
            <a href="logout.php" class="btn btn-danger">Log Out</a>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <h4>Total Collections</h4>
                    <h2>₦<?= number_format($overall_collection, 2); ?></h2>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-info text-white">
                    <h4>Top Collector</h4>
                    <?php if ($top_agent): ?>
                        <h5><?= htmlspecialchars($top_agent['highest_agent']); ?></h5>
                        <h2>₦<?= number_format($top_agent['highest_amount'], 2); ?></h2>
                    <?php else: ?>
                        <h5>No collections recorded yet</h5>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <h3>📊 Collections per Agent</h3>
            <canvas id="agentChart"></canvas>
        </div>
    </div>

    <!-- Chart.js for Visualization -->
    <script>
        let chartData = <?= $graph_data; ?>;
        let agentNames = chartData.map(agent => agent.agent_name);
        let collections = chartData.map(agent => agent.total_collected);

        let ctx = document.getElementById('agentChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: agentNames,
                datasets: [{
                    label: 'Total Collected (₦)',
                    data: collections,
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

</body>
</html>

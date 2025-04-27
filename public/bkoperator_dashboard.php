<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'operator') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Operator ID
$operator_id = $_SESSION['user']['id'];

// Fetch Pending Waste Agents for Approval
$stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'agent' AND approved = 0");
$pending_agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Pending Houses for Approval
$stmt = $pdo->prepare("SELECT * FROM houses WHERE operator_id = ? AND status = 'pending'");
$stmt->execute([$operator_id]);
$pending_houses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Total Collections by Agents under this Operator
$stmt = $pdo->prepare("SELECT users.id AS agent_id, users.name AS agent_name, SUM(payments.amount) AS total_collected 
                       FROM payments 
                       JOIN houses ON payments.house_id = houses.id 
                       JOIN users ON houses.agent_id = users.id
                       WHERE houses.operator_id = ?
                       GROUP BY houses.agent_id");
$stmt->execute([$operator_id]);
$agent_collections = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch Overall Collections (Total Revenue)
$stmt = $pdo->prepare("SELECT SUM(payments.amount) AS overall_collections 
                       FROM payments 
                       JOIN houses ON payments.house_id = houses.id 
                       WHERE houses.operator_id = ?");
$stmt->execute([$operator_id]);
$overall_collection = $stmt->fetch(PDO::FETCH_ASSOC)['overall_collections'] ?? 0;

// Fetch Highest Collection Agent
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

        <div class="card p-3">
            <h3>👤 Agent Approvals</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_agents as $agent): ?>
                        <tr>
                            <td><?= htmlspecialchars($agent['name']); ?></td>
                            <td><?= htmlspecialchars($agent['email']); ?></td>
                            <td>
                                <form action="approve_agent.php" method="POST">
                                    <input type="hidden" name="agent_id" value="<?= $agent['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form action="decline_agent.php" method="POST">
                                    <input type="hidden" name="agent_id" value="<?= $agent['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <div class="card p-3">
             <h3>👤 House Approvals</h3>    
            <table class="table table-striped">
    <thead>
        <tr>
            <th>House Number</th>
            <th>Street</th>
            <th>City</th>
            <th>State</th>
            <th>Owner</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pending_houses as $house): ?>
            <tr>
                <td><?= htmlspecialchars($house['house_number']); ?></td>
                <td><?= htmlspecialchars($house['street']); ?></td>
                <td><?= htmlspecialchars($house['city']); ?></td>
                <td><?= htmlspecialchars($house['state']); ?></td>
                <td><?= htmlspecialchars($house['owner']); ?></td>
                <td>
                    <form action="approve_house.php" method="POST">
                        <input type="hidden" name="house_id" value="<?= $house['id']; ?>">
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
        
        </div>
    <div class="card mt-4">
    <h3>💰 Collections per Agent</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Agent Name</th>
                <th>Total Houses Managed</th>
                <th>Total Payments Collected</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($agent_collections as $agent): ?>
                <tr>
                    <td><?= htmlspecialchars($agent['agent_name']); ?></td>
                    <td>
                        <?php
                        $stmt = $pdo->prepare("
                        SELECT COUNT(id) AS total_houses 
                        FROM houses 
                        WHERE agent_id = ? AND operator_id = ?
                    ");
                    $stmt->execute([$agent['agent_id'], $operator_id]);
                    $houses_count = $stmt->fetch(PDO::FETCH_ASSOC)['total_houses'] ?? 0;
                    
                        echo $houses_count;
                        ?>
                    </td>
                    <td>₦<?= number_format($agent['total_collected'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
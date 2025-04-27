 <?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'agent') {
    header("Location: login.php");
    exit();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/csrf.php';
$csrf_token = generateCsrfToken();

// Retrieve houses registered by this agent
$agent_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT id, house_number FROM houses WHERE agent_id = ?");
$stmt->execute([$agent_id]);
$houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Collect Payment</title>
</head>
<body>
    <h2>Collect Payment for a House</h2>
    <?php
    if(isset($_SESSION['error'])) { 
        echo "<p style='color:red;'>" . htmlspecialchars($_SESSION['error']) . "</p>"; 
        unset($_SESSION['error']);
    }
    if(isset($_SESSION['success'])) { 
        echo "<p style='color:green;'>" . htmlspecialchars($_SESSION['success']) . "</p>"; 
        unset($_SESSION['success']);
    }
    ?>
    <form action="collect_payment_process.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <label>House:</label>
        <select name="house_id" required>
            <?php foreach($houses as $house): ?>
                <option value="<?= $house['id'] ?>"><?= htmlspecialchars($house['house_number']) ?></option>
            <?php endforeach; ?>
        </select><br>
        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" required placeholder="Enter amount"><br>
        <button type="submit">Record Payment</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>



<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}

$agent_id = $_SESSION['user_id'];
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get list of operators for selection
$operators = $conn->query("SELECT id, name FROM users WHERE role='operator'");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $operator_id = $_POST['operator_id'];
    $house_number = $_POST['house_number'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $state = $_POST['state'];

    $stmt = $conn->prepare("INSERT INTO houses (agent_id, operator_id, house_number, street, city, state) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $agent_id, $operator_id, $house_number, $street, $city, $state);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "House registered successfully!";
    } else {
        $_SESSION['error'] = "Registration failed!";
    }
    $stmt->close();
}

$conn->close();
?>

<form method="POST">
    <label>Operator:</label>
    <select name="operator_id" required>
        <?php while ($op = $operators->fetch_assoc()): ?>
            <option value="<?= $op['id'] ?>"><?= $op['name'] ?></option>
        <?php endwhile; ?>
    </select>

    <input type="text" name="house_number" required placeholder="House Number">
    <input type="text" name="street" required placeholder="Street">
    <input type="text" name="city" required placeholder="City">
    <input type="text" name="state" required placeholder="State">
    <button type="submit">Register House</button>
</form>

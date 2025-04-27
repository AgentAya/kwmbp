<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_id = $_POST['agent_id'];
    $stmt = $pdo->prepare("UPDATE users SET approved = -1 WHERE id = ?");
    $stmt->execute([$agent_id]);

    $_SESSION['error'] = "Agent registration declined.";
    header("Location: operator_dashboard.php");
    exit();
}
?>

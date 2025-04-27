<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $house_id = $_POST['house_id'];
    $stmt = $pdo->prepare("UPDATE houses SET status = 'denied' WHERE id = ?");
    $stmt->execute([$house_id]);

    $_SESSION['error'] = "House registration denied.";
    header("Location: operator_dashboard.php");
    exit();
}
?>

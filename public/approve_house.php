
<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'operator') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['house_id'])) {
    $house_id = intval($_POST['house_id']);

    $stmt = $pdo->prepare("UPDATE houses SET status = 'active' WHERE id = ?");
    $stmt->execute([$house_id]);

    $_SESSION['success'] = "House approved successfully!";
    header("Location: operator_dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: operator_dashboard.php");
    exit();
}
?>

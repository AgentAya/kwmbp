<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Get action & operator ID
$action = $_GET['action'] ?? '';
$operator_id = $_GET['id'] ?? '';

if ($operator_id) {
    if ($action == 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'Active', approved = 1 WHERE id = ?");
        $stmt->execute([$operator_id]);
     
    } elseif ($action == 'suspend') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'Suspended', approved =0 WHERE id = ?");
    } elseif ($action == 'remove') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    } else {
        die("Invalid action.");
    }

    $stmt->execute([$operator_id]);
}

header("Location: admin_dashboard.php");// Redirect back to admin dashboard
 
exit();
?>

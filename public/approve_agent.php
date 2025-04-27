<?php
session_start();
require_once '../config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['agent_id']) || empty($_POST['agent_id'])) {
        $_SESSION['error'] = "Invalid request. Agent ID is missing.";
        header("Location: operator_dashboard.php");
        exit();
    }

    $agent_id = $_POST['agent_id'];
    $operator_id = $_SESSION['user']['id']; // Logged-in operator's ID

    // Verify agent exists and is assigned to this operator
    $stmt = $pdo->prepare("SELECT id, operator_id, status, approved FROM users WHERE id = ? AND role = 'agent'");
    $stmt->execute([$agent_id]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agent) {
        $_SESSION['error'] = "Agent not found.";
        header("Location: operator_dashboard.php");
        exit();
    }

    if ($agent['operator_id'] == $operator_id && $agent['status'] == 'Pending' && $agent['approved'] == 0) {
        // Approve agent by updating status to 'Active' and approved = 1
        $stmt = $pdo->prepare("UPDATE users SET status = 'Active', approved = 1 WHERE id = ?");
        $result = $stmt->execute([$agent_id]);

        if ($result) {
            $_SESSION['success'] = "Agent approved successfully!";
        } else {
            $_SESSION['error'] = "Failed to approve agent.";
        }
    } else {
        $_SESSION['error'] = "You are not authorized to approve this agent.";
    }

    header("Location: operator_dashboard.php");
    exit();
}

<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/address_utils.php';

header('Content-Type: application/json');
// Verify user session and role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agent') {
    echo json_encode(['error' => 'Unauthorized.']);
    exit();
}
// Fetch operator assigned to the agent
$agent_id = $_SESSION['user']['id'];

try {
    $stmt = $pdo->prepare("SELECT operator_id FROM users WHERE id = ?");
    $stmt->execute([$agent_id]);
    $operator_id = $stmt->fetchColumn();

    if (!$operator_id) {
        echo json_encode(['error' => 'No operator assigned to agent.']);
        exit();
    }
// Fetch and sanitize input
    $house_number = trim($_POST['house_number'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');

    if (!$house_number || !$street || !$city) {
        echo json_encode(['error' => 'Missing required fields.']);
        exit();
    }

    $normalized_address = normalizeAddress($house_number, $street, $city);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM houses WHERE normalized_address = ? AND operator_id = ?");
    $stmt->execute([$normalized_address, $operator_id]);
    $exists = $stmt->fetchColumn() > 0;

    echo json_encode(['exists' => $exists]);
} catch (PDOException $e) {
    error_log("Duplicate check error: " . $e->getMessage());
    echo json_encode(['error' => 'System error. Try again later.']);
}
?>


 
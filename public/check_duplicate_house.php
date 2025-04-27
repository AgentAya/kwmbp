<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Validate HTTP request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

// Verify user session and role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agent') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Validate operator_id
$operator_id = $_SESSION['user']['operator_id'] ?? null;
if (empty($operator_id)) {
    echo json_encode(['error' => 'Operator ID is missing from the session']);
    exit();
}

// Collect and validate input
$house_number = trim($_POST['house_number'] ?? '');
$street = trim($_POST['street'] ?? '');
$city = trim($_POST['city'] ?? '');
$area = trim($_POST['area'] ?? '');

if (empty($house_number)) {
    echo json_encode(['error' => 'House number is required']);
    exit();
}
if (empty($street)) {
    echo json_encode(['error' => 'Street is required']);
    exit();
}
if (empty($city)) {
    echo json_encode(['error' => 'City is required']);
    exit();
}
if (empty($area)) {
    echo json_encode(['error' => 'Area is required']);
    exit();
}

// Normalize the address
function normalizeAddress($house_number, $street, $area, $city) {
    $normalized = strtolower(trim("$house_number $street $area $city"));
    return preg_replace('/\s+/', ' ', $normalized); // Collapse multiple spaces
}
$normalized_address = normalizeAddress($house_number, $street, $area, $city);

try {
    // Check for duplicates
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM houses
        WHERE normalized_address = ? AND operator_id = ?
    ");
    $stmt->execute([$normalized_address, $operator_id]);
    $exists = $stmt->fetchColumn() > 0;

    echo json_encode(['exists' => $exists]);
} catch (Exception $e) {
    // Log error and return generic message
    error_log("Error checking duplicate house: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while checking for duplicates']);
}
function normalizeField($value) {
    return preg_replace('/\s+/', ' ', strtolower(trim($value)));
}

$house_number = normalizeField($house_number);
$street = normalizeField($street);
$area = normalizeField($area);
$city = normalizeField($city);
$normalized_address = "$house_number $street $area $city";

exit();
?>

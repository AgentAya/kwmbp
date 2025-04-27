<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'])) {
            throw new Exception("Invalid CSRF token.");
        }

        // Collect and sanitize input
        $house_number = trim($_POST['house_number']);
        $street = trim($_POST['street']);
        $area = trim($_POST['area']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $operator_id = intval($_POST['operator_id']);
        $owner = trim($_POST['owner']);
        $owner_phone = trim($_POST['owner_phone']);
        $building_type = trim($_POST['building_type']);
        $number_of_units = intval($_POST['number_of_units']);
        $agent_id = $_SESSION['user']['id'] ?? null;

        if (!$agent_id) {
            throw new Exception("Agent ID is missing from session.");
        }

        // Validate inputs
        if (!ctype_digit($house_number) || intval($house_number) <= 0) {
            throw new Exception("House number must be a positive integer.");
        }
        if (empty($street)) {
            throw new Exception("Street is required.");
        }
        if (empty($area)) {
            throw new Exception("Area is required.");
        }
        if (empty($city)) {
            throw new Exception("City is required.");
        }
        if (empty($state)) {
            throw new Exception("State is required.");
        }
        if (!preg_match('/^\+?\d{10,15}$/', $owner_phone)) {
            throw new Exception("Invalid phone number format. Use 10-15 digits.");
        }
        if ($number_of_units <= 0) {
            throw new Exception("Number of units must be a positive number.");
        }

        // Normalize address
        function normalizeAddress($house_number, $street, $area, $city) {
            $normalized = strtolower(trim("$house_number $street $area $city"));
            return preg_replace('/\s+/', ' ', $normalized);
        }
        $normalized_address = normalizeAddress($house_number, $street, $area, $city);

        // Check for duplicates
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM houses
            WHERE normalized_address = ? AND operator_id = ?
        ");
        $stmt->execute([$normalized_address, $operator_id]);

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("This house is already registered in the system.");
        }

        // Insert house record
        $stmt = $pdo->prepare("
            INSERT INTO houses (
                house_number, street, area, city, state, operator_id, owner, owner_phone,
                building_type, number_of_units, agent_id, status, normalized_address
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
        ");
        $stmt->execute([
            $house_number, $street, $area, $city, $state, $operator_id, $owner,
            $owner_phone, $building_type, $number_of_units, $agent_id,
            $normalized_address
        ]);
        $pdo->commit();

        $_SESSION['success'] = "House registered successfully! Waiting for operator approval.";
        header("Location: house_register.php");
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        error_log("House registration failed: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: house_register.php");
        exit();
    }
}
?>

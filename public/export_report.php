<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Set CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="operator_collections.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV column headings
fputcsv($output, ['Operator Name', 'Total Collected']);

// Fetch operator collections
$stmt = $pdo->query("
    SELECT users.name AS operator_name, SUM(payments.amount) AS total_collected 
    FROM payments 
    JOIN houses ON payments.house_id = houses.id 
    JOIN users ON houses.operator_id = users.id 
    WHERE users.role = 'operator'
    GROUP BY houses.operator_id
    ORDER BY total_collected DESC
");

$operator_collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add data to CSV
foreach ($operator_collections as $operator) {
    fputcsv($output, [$operator['operator_name'], $operator['total_collected']]);
}

// Close output stream
fclose($output);
exit();
?>

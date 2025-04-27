<?php
require_once __DIR__ . '/../config/database.php';

$payment_id = 6; // use a known good ID
$guest_phone = '08038974866'; // exact value from the database

$stmt = $pdo->prepare("
    SELECT p.*, h.owner_phone
    FROM payments p
    JOIN houses h ON p.house_id = h.id
    WHERE p.id = ? AND h.owner_phone = ?
");
$stmt->execute([$payment_id, $guest_phone]);

$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    echo "NOT FOUND";
} else {
    echo "FOUND: ";
    print_r($payment);
}

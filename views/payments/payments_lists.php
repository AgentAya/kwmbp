<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT p.id, h.house_number, p.amount, p.reference, p.status 
                        FROM payments p 
                        JOIN houses h ON p.house_id = h.id");

?>

<h2>Collected Payments</h2>
<table border="1">
    <tr>
        <th>House Number</th>
        <th>Amount</th>
        <th>Reference</th>
        <th>Status</th>
        <th>Receipt</th>
    </tr>
    <?php while ($payment = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $payment['house_number'] ?></td>
            <td><?= $payment['amount'] ?></td>
            <td><?= $payment['reference'] ?></td>
            <td><?= $payment['status'] ?></td>
            <td><a href="receipt.php?payment_id=<?= $payment['id'] ?>" target="_blank">View Receipt</a></td>
        </tr>
    <?php endwhile; ?>
</table>

<?php $conn->close(); ?>

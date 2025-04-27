<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT h.id, h.house_number, h.street, h.city, h.state, u.name AS operator_name 
                        FROM houses h 
                        JOIN users u ON h.operator_id = u.id");

?>

<h2>Registered Houses</h2>
<table border="1">
    <tr>
        <th>House Number</th>
        <th>Street</th>
        <th>City</th>
        <th>State</th>
        <th>Operator</th>
    </tr>
    <?php while ($house = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $house['house_number'] ?></td>
            <td><?= $house['street'] ?></td>
            <td><?= $house['city'] ?></td>
            <td><?= $house['state'] ?></td>
            <td><?= $house['operator_name'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<?php $conn->close(); ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['house_id'])) {
    $house_id = $_POST['house_id'];

    $stmt = $pdo->prepare("UPDATE houses SET status = 'active' WHERE id = ?");
    $stmt->execute([$house_id]);

    $_SESSION['success'] = "House approved successfully!";
    header("Location: operator_dashboard.php"); // Adjust URL for the operator dashboard
    exit();
}

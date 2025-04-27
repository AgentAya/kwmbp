 <?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/csrf.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'agent') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: collect_payment.php");
        exit();
    }

    $house_id = intval($_POST['house_id']);
    $amount = floatval($_POST['amount']);
    $agent_id = $_SESSION['user']['id'];

    try {
        // Get operator_id from house
        $stmt = $pdo->prepare("SELECT operator_id FROM houses WHERE id = ?");
        $stmt->execute([$house_id]);
        $operator_id = $stmt->fetchColumn();

        // Generate a unique payment reference
        $reference = uniqid('PAY_');

        // Insert payment record
        $stmt = $pdo->prepare("INSERT INTO payments (house_id, agent_id, operator_id, amount, reference, status) VALUES (?, ?, ?, ?, ?, ?)");
        $status = 'completed'; // For production, you might set this as pending until verified
        $stmt->execute([$house_id, $agent_id, $operator_id, $amount, $reference, $status]);
        $_SESSION['success'] = "Payment recorded successfully!";
    } catch (PDOException $e) {
        error_log("Payment Collection Error: " . $e->getMessage());
        $_SESSION['error'] = "Payment recording failed. Please try again later.";
    }
    header("Location: collect_payment.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: collect_payment.php");
    exit();
}
?>

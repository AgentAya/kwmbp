 <?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/csrf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: house_register.php");
        exit();
    }

    $house_number = trim($_POST['house_number']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $operator_id = intval($_POST['operator_id']);
    $agent_id = $_SESSION['user']['id'];

    if (empty($house_number) || empty($street) || empty($city) || empty($state)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: house_register.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO houses (agent_id, operator_id, house_number, street, city, state) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$agent_id, $operator_id, $house_number, $street, $city, $state]);
        $_SESSION['success'] = "House registered successfully!";
    } catch (PDOException $e) {
        error_log("House Registration Error: " . $e->getMessage());
        $_SESSION['error'] = "Registration failed. Please try again later.";
    }
    header("Location: house_register.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: house_register.php");
    exit();
}
?>

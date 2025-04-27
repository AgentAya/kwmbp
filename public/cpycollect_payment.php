<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . "/error_log.txt");

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/csrf.php';

// Ensure user is logged in and is an agent
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'agent') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate form input
    if (!isset($_POST['house_id']) || !isset($_POST['amount']) || empty($_POST['house_id']) || empty($_POST['amount'])) {
        $_SESSION['error'] = "Please select a house and enter a valid amount.";
        header("Location: collect_payment.php");
        exit();
    }

    $house_id = intval($_POST['house_id']);
    $amount = floatval($_POST['amount']);
    $agent_id = $_SESSION['user']['id'];

    if ($house_id <= 0 || $amount <= 0) {
        $_SESSION['error'] = "Invalid house selection or amount.";
        header("Location: collect_payment.php");
        exit();
    }

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: collect_payment.php");
        exit();
    }

    // Fetch operator ID dynamically
    $stmt = $pdo->prepare("SELECT operator_id FROM houses WHERE id = ? AND agent_id = ?");
    $stmt->execute([$house_id, $agent_id]);
    $operator = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$operator || empty($operator['operator_id'])) {
        $_SESSION['error'] = "Error: Could not determine operator ID.";
        header("Location: collect_payment.php");
        exit();
    }

    $operator_id = $operator['operator_id'];

    // Ensure operator ID exists in the `users` table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$operator_id]);
    $operatorExists = $stmt->fetchColumn();

    if (!$operatorExists) {
        $_SESSION['error'] = "Error: Operator ID does not exist in the users table.";
        header("Location: collect_payment.php");
        exit();
    }

    // Generate transaction reference
    $reference = "PAY_" . uniqid();

    // Paystack API configuration (Use live keys when ready)
    $paystack_secret = "sk_test_b466344538b2608acf67c3f108212d2af766828e";

    // Prepare data for Paystack initialization
    $data = [
        "email" => "customer@example.com",  // Replace with house owner's email dynamically
        "amount" => $amount * 100, // Convert to kobo
        "currency" => "NGN",
        "reference" => $reference,
        "callback_url" => "http://localhost/waste_management_system/public/payment_callback.php"
    ];

    // Initialize cURL and send request to Paystack
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $paystack_secret",
            "Content-Type: application/json"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
    }
    curl_close($curl);

    if (isset($error_msg)) {
        $_SESSION['error'] = "cURL error: " . $error_msg;
        header("Location: collect_payment.php");
        exit();
    }

    $result = json_decode($response, true);

    if (!$result || !isset($result['status'])) {
        $_SESSION['error'] = "Invalid response from Paystack API.";
        header("Location: collect_payment.php");
        exit();
    }

    if ($result['status'] === true) {
        try {
            $stmt = $pdo->prepare("INSERT INTO payments (house_id, agent_id, operator_id, amount, reference, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$house_id, $agent_id, $operator_id, $amount, $reference]);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            $_SESSION['error'] = "Payment recording failed. Please try again.";
            header("Location: collect_payment.php");
            exit();
        }

        // Redirect to Paystack payment page
        header("Location: " . $result['data']['authorization_url']);
        exit();
    } else {
        $_SESSION['error'] = "Payment initialization failed: " . $result['message'];
        header("Location: collect_payment.php");
        exit();
    }
} else {
    // GET request: Display Payment Collection Form
    $agent_id = $_SESSION['user']['id'];
    $stmt = $pdo->prepare("SELECT id, house_number FROM houses WHERE agent_id = ?");
    $stmt->execute([$agent_id]);
    $houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $csrf_token = generateCsrfToken();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Collect Payment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Collect Payment for a House</h2>
        <?php
        if (isset($_SESSION['error'])) {
            echo "<p class='text-danger'>" . htmlspecialchars($_SESSION['error']) . "</p>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<p class='text-success'>" . htmlspecialchars($_SESSION['success']) . "</p>";
            unset($_SESSION['success']);
        }
        ?>
        <form action="collect_payment.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <label for="house_id">Select House:</label>
            <select name="house_id" id="house_id" required>
                <option value="">-- Select House --</option>
                <?php foreach ($houses as $house): ?>
                    <option value="<?= htmlspecialchars($house['id']) ?>">
                        <?= htmlspecialchars($house['house_number']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label>Amount (NGN):</label>
            <input type="number" step="0.01" name="amount" required placeholder="Enter amount"><br>

            <button type="submit" class="btn btn-primary">Proceed to Payment</button>
        </form>

        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>

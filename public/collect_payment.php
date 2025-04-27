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
   // Fetch phone number from the house record
$stmt = $pdo->prepare("SELECT owner_phone FROM houses WHERE id = ?");
$stmt->execute([$house_id]);
$houseInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Use phone number to generate a fake but unique email
$phone = $houseInfo['owner_phone'] ?? '0000000000';
$cleanPhone = preg_replace('/\D/', '', $phone); // Remove non-digits
$fake_email = "user" . $cleanPhone . "@example.com";


// Prepare data for Paystack initialization
$data = [
    "email" => $fake_email,
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

$agent_id = $_SESSION['user']['id']; // <-- Make sure this is here!

    $stmt = $pdo->prepare("
    SELECT h.id, h.house_number, h.street, h.area, h.city, h.building_type, h.number_of_units, bp.amount 
    FROM houses h
    JOIN building_price bp ON h.building_type = bp.building_type
    WHERE h.agent_id = ?
");
$stmt->execute([$agent_id]);
$houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$csrf_token = generateCsrfToken();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collect Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .payment-form {
            max-width: 600px;
            margin: auto;
        }
        .form-label {
            font-weight: bold;
        }
        .card {
            border: none;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="payment-form">
        <div class="card p-4">
            <h3 class="mb-4 text-center">💳 Collect Payment</h3>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form action="collect_payment.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="mb-3">
                    <label for="house_id" class="form-label">Select House</label>
                    <select name="house_id" id="house_id" class="form-select" required onchange="updateAmount(this)">
    <option value="">-- Select House --</option>
    <?php foreach ($houses as $house): ?>
        <option 
            value="<?= htmlspecialchars($house['id']) ?>" 
            data-amount="<?= htmlspecialchars($house['amount'] * $house['number_of_units']) ?>">
            <?= htmlspecialchars("{$house['house_number']} - {$house['street']} - {$house['area']} - {$house['city']} ({$house['building_type']}, {$house['number_of_units']} unit(s))") ?>
        </option>
    <?php endforeach; ?>
</select>
<script>
function updateAmount(select) {
    const selectedOption = select.options[select.selectedIndex];
    const amount = selectedOption.getAttribute('data-amount');
    document.getElementById('amount').value = amount || '';
}
</script>


                </div>

                <div class="mb-3">
                    <label for="amount" class="form-label">Amount (₦)</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" required readonly>
                    </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>

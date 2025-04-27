<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . "/error_log.txt");

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/csrf.php';

if (!isset($_SESSION['guest'])) {
    header("Location: guest_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['house_id']) || !isset($_POST['amount']) || empty($_POST['house_id']) || empty($_POST['amount'])) {
        $_SESSION['error'] = "Please select a house and enter a valid amount.";
        header("Location: guest_collect_payment.php");
        exit();
    }

    $house_id = intval($_POST['house_id']);
    $amount = floatval($_POST['amount']);

    if ($house_id <= 0 || $amount <= 0) {
        $_SESSION['error'] = "Invalid house selection or amount.";
        header("Location: guest_collect_payment.php");
        exit();
    }

    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: guest_collect_payment.php");
        exit();
    }

    // Fetch agent_id, operator_id, and owner_phone from the house
    $stmt = $pdo->prepare("SELECT agent_id, operator_id, owner_phone FROM houses WHERE id = ?");
    $stmt->execute([$house_id]);
    $house = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$house) {
        $_SESSION['error'] = "House not found.";
        header("Location: guest_collect_payment.php");
        exit();
    }

    $agent_id = $house['agent_id'];
    $operator_id = $house['operator_id'];
    $phone = $house['owner_phone'];

    // Generate email and transaction reference
    $cleanPhone = preg_replace('/\D/', '', $phone);
    $fake_email = "user" . $cleanPhone . "@example.com";
    $reference = "PAY_" . uniqid();

    $paystack_secret = "sk_test_b466344538b2608acf67c3f108212d2af766828e";

    $data = [
        "email" => $fake_email,
        "amount" => $amount * 100,
        "currency" => "NGN",
        "reference" => $reference,
        "callback_url" => "http://localhost/waste_management_system/public/payment_callback.php"
    ];

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
        header("Location: guest_collect_payment.php");
        exit();
    }

    $result = json_decode($response, true);

    if (!$result || !isset($result['status'])) {
        $_SESSION['error'] = "Invalid response from Paystack API.";
        header("Location: guest_collect_payment.php");
        exit();
    }

    if ($result['status'] === true) {
        try {
            $stmt = $pdo->prepare("INSERT INTO payments (house_id, agent_id, operator_id, amount, reference, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$house_id, $agent_id, $operator_id, $amount, $reference]);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            $_SESSION['error'] = "Payment recording failed. Please try again.";
            header("Location: guest_collect_payment.php");
            exit();
        }

        header("Location: " . $result['data']['authorization_url']);
        exit();
    } else {
        $_SESSION['error'] = "Payment initialization failed: " . $result['message'];
        header("Location: guest_collect_payment.php");
        exit();
    }
} else {
    // Display form
    $owner_phone = $_SESSION['guest']['phone'];

    $stmt = $pdo->prepare("
        SELECT h.id, h.house_number, h.street, h.area, h.city, h.building_type, h.number_of_units, bp.amount 
        FROM houses h
        JOIN building_price bp ON h.building_type = bp.building_type
        WHERE h.owner_phone = ?
    ");
    $stmt->execute([$owner_phone]);
    $houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $csrf_token = generateCsrfToken();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Make Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body { background-color: #f8f9fa; }
        .payment-form { max-width: 600px; margin: auto; }
        .form-label { font-weight: bold; }
        .card { border: none; box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="payment-form">
        <div class="card p-4">
            <h3 class="mb-4 text-center">🏠 Make a Payment</h3>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form action="guest_collect_payment.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="mb-3">
                    <label for="house_id" class="form-label">Select Your House</label>
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
                </div>

                <script>
                    function updateAmount(select) {
                        const selectedOption = select.options[select.selectedIndex];
                        const amount = selectedOption.getAttribute('data-amount');
                        document.getElementById('amount').value = amount || '';
                    }
                </script>

                <div class="mb-3">
                    <label for="amount" class="form-label">Amount (₦)</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" required readonly>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Proceed to Paystack</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="guest_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>

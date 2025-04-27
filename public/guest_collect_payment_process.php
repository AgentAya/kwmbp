<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . "/error_log.txt");

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/csrf.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guest') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: guest_collect_payment.php");
        exit();
    }
}
    // Retrieve form inputs
    $house_id = intval($_POST['house_id']);
    $amount = floatval($_POST['amount']); // Amount in NGN
    $agent_id = $_SESSION['user']['id'];
    
    // Paystack API configuration
    $paystack_secret = "sk_test_b466344538b2608acf67c3f108212d2af766828e"; // Replace with your live key in production
    $reference = "PAY_" . uniqid(); // Generate unique reference
    
    // Prepare data for Paystack initialization
    // Replace the email with the house owner's email if available from your database.
    $data = [
        "email" => "customer@example.com", // Replace with dynamic email if available
        "amount" => $amount * 100, // Convert to kobo
        "currency" => "NGN",
        "reference" => $reference,
        "callback_url" => "http://localhost/waste_management_system/public/payment_callback.php" // Update to your production callback URL
    ];
    
    // Initialize cURL and send API request to Paystack
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
    
    // Decode the JSON response from Paystack
    $result = json_decode($response, true);
    
    if (!$result || !isset($result['status'])) {
        $_SESSION['error'] = "Invalid response from Paystack API: " . $response;
        header("Location: guest_collect_payment.php");
        exit();
    }
    
    if ($result['status'] === true) {
        // Insert the payment record into your database with status 'pending'
        try {
            $stmt = $pdo->prepare("INSERT INTO payments (house_id, agent_id, amount, reference, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$house_id, $agent_id, $amount, $reference]);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            $_SESSION['error'] = "Payment recording failed. Please try again later.";
            header("Location: guest_collect_payment.php");
            exit();
        }
    }
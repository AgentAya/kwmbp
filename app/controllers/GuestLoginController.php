<?php
namespace App\Controllers;

use PDO;

class GuestLoginController {
    
    protected $pdo;

    public function __construct() {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
        session_start();
    }

    // Show guest login page
    public function showLoginPage() {
        require __DIR__ . '/../../public/guest_login.php';
    }

    // Process guest login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $phone = trim($_POST['phone']);

            // Validate phone number
            if (empty($phone)) {
                $_SESSION['error'] = "Phone number is required.";
                header("Location: /waste_management_system/public/guest_login.php");
                exit();
            }

            // Check if the phone number exists in the system
            $stmt = $this->pdo->prepare("SELECT * FROM houses WHERE owner_phone = ?");
            $stmt->execute([$phone]);
            $guest = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($guest) {
                // Guest exists, start a session for the guest
                $_SESSION['guest_phone'] = $phone;
                $_SESSION['guest'] = [
                    'owner' => $guest['owner'], // assuming 'owner_name' is in the 'houses' table
                    'phone' => $guest['owner_phone']
                ];

                header("Location: /waste_management_system/public/guest_dashboard.php");
                exit();
            } else {
                // Guest not found
                $_SESSION['error'] = "Phone number not registered.";
                header("Location: /waste_management_system/public/guest_login.php");
                exit();
            }
        }
    }

    // Guest logout
    public function logout() {
        session_unset();
        session_destroy();
        header("Location: /waste_management_system/public/guest_login.php");
        exit();
    }
}

<?php
namespace App\Controllers;

require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../Models/User.php';

use PDO;

class AuthController {

    protected $pdo;

    public function __construct() {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
        session_start();
    }

    // Show login page
    public function showLoginPage() {
        require __DIR__ . '/../../views/auth/login.php';
    }

    // Process login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die("Invalid CSRF token.");
            }
            
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password = trim($_POST['password']);

            $user = \App\Models\User::findByEmail($this->pdo, $email);
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                $_SESSION['user'] = $user;
                header("Location: /waste-management/public/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid credentials.";
                header("Location: /waste-management/public/login.php");
                exit();
            }
        }
    }

    // Show registration page
    public function showRegisterPage() {
        require __DIR__ . '/../../views/auth/register.php';
    }

    // Process registration
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die("Invalid CSRF token.");
            }
    
            $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password = trim($_POST['password']);
            $role = trim($_POST['role']); // Expect values: admin, operator, consultant, agent
            $operator_id = ($role === 'agent') ? $_POST['operator_id'] : null;
    
            // Validate required fields
            if (empty($name) || empty($email) || empty($password) || empty($role) || ($role === 'agent' && empty($operator_id))) {
                $_SESSION['error'] = "All fields are required.";
                header("Location: /waste-management/public/register.php");
                exit();
            }
    
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
            // Insert user into database with operator_id for agents
            $userId = \App\Models\User::create($this->pdo, [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role,
                'operator_id' => $operator_id,
                'status' => 'Pending'
            ]);
    
            if ($userId) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: /waste-management/public/login.php");
                exit();
            } else {
                $_SESSION['error'] = "Registration failed.";
                header("Location: /waste-management/public/register.php");
                exit();
            }
        }
    }
    
    // Logout
    public function logout() {
        session_destroy();
        header("Location: /waste-management/public/login.php");
        exit();
    }
}
?>

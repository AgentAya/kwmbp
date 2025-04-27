<?php
namespace App\Controllers;

use App\Models\User;
use PDO;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf.php';

class AuthController {
    protected $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Show login form
    public function showLoginPage() {
        require __DIR__ . '/../../views/auth/login.php';
    }

    // Process login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login.php');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->abort("Invalid CSRF token.");
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        $user = User::findByEmail($this->pdo, $email);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = $user;

            $this->redirect('/dashboard.php');
        }

        $_SESSION['error'] = "Invalid credentials.";
        $this->redirect('/login.php');
    }

    // Show register form
    public function showRegisterPage() {
        require __DIR__ . '/../../views/auth/register.php';
    }

    // Process registration
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register.php');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->abort("Invalid CSRF token.");
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? '',
            'operator_id' => ($_POST['role'] ?? '') === 'agent' ? $_POST['operator_id'] : null,
        ];

        if (in_array('', [$data['name'], $data['email'], $data['password'], $data['role']]) ||
            ($data['role'] === 'agent' && empty($data['operator_id']))) {
            $_SESSION['error'] = "All fields are required.";
            $this->redirect('/register.php');
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['status'] = 'Pending';

        if (User::create($this->pdo, $data)) {
            $_SESSION['success'] = "Registration successful! Please login.";
            $this->redirect('/login.php');
        } else {
            $_SESSION['error'] = "Registration failed.";
            $this->redirect('/register.php');
        }
    }

    // Logout
    public function logout() {
        session_destroy();
        $this->redirect('/login.php');
    }

    // Helpers
    private function redirect(string $path) {
        header("Location: /waste_management_system/public{$path}");
        exit();
    }

    private function abort(string $message) {
        http_response_code(403);
        exit($message);
    }
}

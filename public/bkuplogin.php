<?php
 session_start();

 if (isset($_SESSION['user'])) {
     unset($_SESSION['user']); // remove user session if it exists
 }
 if (isset($_SESSION['guest'])) {
     unset($_SESSION['guest']); // remove guest session if it exists
 }
 
 require_once __DIR__ . '/../config/database.php';
 

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user data along with approval status for agents
    $stmt = $pdo->prepare("SELECT id, name, password, role, approved FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        //operators and agent have to be approved before log in
        if (($user['role'] == 'agent' || $user['role'] == 'operator') && !$user['approved']) {
            $_SESSION['error'] = "Your account has not been approved yet.";
            header("Location: login.php");
            exit();
        }
    
        session_regenerate_id(true);
        $_SESSION['user'] = ($user['approved'] == 1) ? $user : null;

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] == 'Suspended') {
                $_SESSION['error'] = "Your account is currently suspended.";
                header("Location: login.php");
                exit();
            } elseif ($user['status'] == 'Active') {
                session_regenerate_id(true);
                $_SESSION['user'] = $user;
                header("Location: dashboard.php");
                exit();
            }
           
        }
        
        // Redirect based on user role
        header("Location: " . ($user['role'] === 'operator' ? "operator_dashboard.php" : "dashboard.php"));
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
    /* General Body Styling */
    body {
        background-color: #f8f9fa; /* Light gray for a modern look */
        font-family: 'Arial', sans-serif;
    }

    /* Navbar Styling */
    .navbar {
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }
    .navbar-brand img {
        height: 50px;
        margin-right: 10px;
    }
    .navbar-brand span {
        font-weight: bold;
        font-size: 24px;
    }

    /* Login Form Container */
    .login-container {
        max-width: 400px;
        margin: 40px auto;
        padding: 30px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    .login-container h2 {
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 20px;
        color: #007bff;
    }

    /* Input Fields */
    .form-control {
        border-radius: 5px;
        border: 1px solid #ced4da;
    }
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0px 0px 6px rgba(0, 123, 255, 0.5);
    }

    /* Submit Button */
    .btn-primary {
        background-color: #007bff;
        border: none;
    }
    .btn-primary:hover {
        background-color: #0056b3;
    }

    /* Links */
    .text-center a {
        text-decoration: none;
        color: #007bff;
    }
    .text-center a:hover {
        text-decoration: underline;
    }

    /* Responsive Design */
    @media screen and (max-width: 576px) {
        .login-container {
            margin: 20px;
            padding: 20px;
        }
        .navbar-brand span {
            font-size: 20px;
        }
    }
</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Jimstar Waste Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="/waste_management_system/public/assets/images/jslogo.png" alt="Logo" width="50">
                Jimstar Waste Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <li class="nav-item"><a class="nav-link" href="guest_login.php">Make Payment</a></li>
                    <li class="nav-item"><a class="nav-link" href="guest_login.php">Payment History</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="faq.php">FAQ</a></li>
                 </ul>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="container login-container">
    <h2>Login</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($_SESSION['error']); ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="mt-4 text-center">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <p><a href="forgot_password.php">Forgot Password?</a></p>
    </div>
</div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

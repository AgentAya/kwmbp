<?php
session_start();
require_once __DIR__ . '/../config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=waste_managements;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitize input values
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = $_POST['role'];
        $operator_id = ($role === 'agent') ? $_POST['operator_id'] : null;

        // Validate required fields
        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            $_SESSION['error'] = "Please fill in all the required fields.";
            header("Location: register.php");
            exit();
        }

        if ($role === 'agent' && empty($operator_id)) {
            $_SESSION['error'] = "As a Waste Agent, you must select an Operator.";
            header("Location: register.php");
            exit();
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email already exists. Please use a different email.";
            header("Location: register.php");
            exit();
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into database
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, operator_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $email, $hashedPassword, $role, $operator_id]);

// ✅ Get the newly inserted user ID
$newUserId = $pdo->lastInsertId();

// Set operator ID for the new operator role
if ($role === 'operator') {
    $newUserId = $pdo->lastInsertId();
    $updateStmt = $pdo->prepare("UPDATE users SET operator_id = ? WHERE id = ?");
    $updateStmt->execute([$newUserId, $newUserId]);
}

// Success message and redirect
$_SESSION['success'] = "Registration successful! Please log in.";
header("Location: login.php");
exit();
}
    
} catch (PDOException $e) {
    // Log the error for debugging (avoid showing raw database errors to the user)
    error_log("Registration error: " . $e->getMessage());

    // User-friendly error message
    $_SESSION['error'] = "Something went wrong during registration. Please try again.";
    header("Location: register.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Jimstar Waste Management</title>
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
                     <li class="nav-item"><a class="nav-link" href="payment.php">Make Payment</a></li>
                    <li class="nav-item"><a class="nav-link" href="guest_login.php">Payment History</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="faq.php">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="container mt-5">
        <h2 class="text-center">Register</h2>
        
        <?php if(isset($_SESSION['error'])): ?>
            <p class="text-danger text-center"><?= $_SESSION['error']; ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <p class="text-success text-center"><?= $_SESSION['success']; ?></p>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="POST" class="w-50 mx-auto">
            <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
            
            <label>Select Role:</label>
            <select name="role" class="form-control mb-3" required onchange="toggleOperatorSelection(this.value)">
                <option value="agent">Waste Agent</option>
                <option value="operator">Operator</option>
                <option value="consultant">Consultant</option>
                <option value="admin">Admin</option>
            </select>

            <!-- Operator Selection (Only for Agents) -->
            <div id="operatorSelection" style="display: none;">
                <label>Select Operator:</label>
                <select name="operator_id" class="form-control mb-3">
                    <option value="">Select Operator</option>
                    <?php
                    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'operator' AND status = 'Active'");
                    while ($operator = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$operator['id']}'>{$operator['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <script>
    function toggleOperatorSelection(role) {
        // Show or hide the operator selection based on role
        document.getElementById('operatorSelection').style.display = (role === 'agent') ? 'block' : 'none';
    }

    // Trigger the function for the default role on page load
    window.onload = function() {
        const defaultRole = document.querySelector("select[name='role']").value;
        toggleOperatorSelection(defaultRole);
    };
</script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

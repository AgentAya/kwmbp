<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Validate token retrieval and form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'] ?? '';  // Get the token from the form submission
    $newPassword = password_hash($_POST['new_password'], PASSWORD_BCRYPT);  // Hash the new password

    // Debugging: Log the retrieved token
    error_log("Received token (POST): " . $token);

    // Verify token existence in database
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update password and clear reset token
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE id = ?");
        $stmt->execute([$newPassword, $user['id']]);

        $_SESSION['success'] = "Password updated successfully!";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid or expired reset token.";
        header("Location: reset_password.php");
        exit();
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Jimstar Waste Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .navbar-brand img {
            width: 50px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    
    <!-- Navbar -->
 <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="/waste_management_system/public/assets/images/jslogo.png" alt="Logo">
                Jimstar Waste Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h3>Reset Your Password</h3>
                </div>
                <div class="card-body">
                    <!-- Display Success Message -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success text-center">
                            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Display Error Message -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger text-center">
                            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Reset Password Form -->
                    <form method="POST" action="reset_password.php" class="mt-3">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? ''); ?>"> <!-- Hidden Token -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" required placeholder="Enter new password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="text-muted small">Make sure your new password is secure.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

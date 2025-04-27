<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');

    if (empty($phone)) {
        $error = "Phone number is required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM houses WHERE owner_phone = ?");
        $stmt->execute([$phone]);
        $house = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($house) {
            if (isset($_SESSION['user'])) {
                unset($_SESSION['user']);
            }

            $_SESSION['guest'] = [
                'owner'    => $house['owner'],
                'phone'    => $house['owner_phone'],
                'house_id' => $house['id']
            ];
            header("Location: guest_dashboard.php");
            exit;
        } else {
            $error = "No house found for this phone number.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guest Login - Waste Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f0f2f5;
        }
        .login-container {
            max-width: 450px;
            margin: 80px auto;
        }
        .card {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

    
        .navbar-brand img {
            width: 50px;
            margin-right: 10px;
        }
        .dashboard-header {
            background: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
        }
        
         
        table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th {
            background-color: #007bff;
            color: white;
        }
         
    </style>
</head>
<body>
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
<div class="container login-container">
    <div class="card p-4">
        <h3 class="text-center mb-4">🏠 House Owner Login</h3>
        <p class="text-muted text-center">Enter your phone number to access your dashboard and manage your payments.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" name="phone" id="phone" class="form-control" placeholder="e.g. 08012345678" required pattern="^\d{10,15}$">
                <div class="invalid-feedback">Please enter a valid phone number (10 to 15 digits).</div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>

        <p class="text-center mt-3 text-muted" style="font-size: 0.9rem;">
            Need help? Contact your local waste agent or the service operator for assistance.
        </p>
    </div>
</div>

<!-- Bootstrap JS for validation -->
<script>
    (function () {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated');
            }, false)
        })
    })();
</script>

</body>
</html>

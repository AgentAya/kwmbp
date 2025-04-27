<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'agent') {
    header("Location: login.php");
    exit();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/csrf.php';
$csrf_token = generateCsrfToken();

// Fetch available operators for the dropdown
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'operator'");
$operators = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register House</title>

    <!-- Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            text-align: center;
            padding: 15px;
            background: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn-submit {
            width: 100%;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <div class="form-header">
            <h2>🏠 Register a House</h2>
        </div>

        <?php
        if (isset($_SESSION['error'])) {
            echo "<p class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</p>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<p class='alert alert-success'>" . htmlspecialchars($_SESSION['success']) . "</p>";
            unset($_SESSION['success']);
        }
        ?>

        <form action="house_register_process.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label><strong>House Number:</strong></label>
                <input type="text" name="house_number" class="form-control" required>
            </div>

            <div class="form-group">
                <label><strong>Street:</strong></label>
                <input type="text" name="street" class="form-control" required>
            </div>

            <div class="form-group">
                <label><strong>City:</strong></label>
                <input type="text" name="city" class="form-control" required>
            </div>

            <div class="form-group">
                <label><strong>State:</strong></label>
                <input type="text" name="state" class="form-control" required>
            </div>

            <div class="form-group">
                <label><strong>Operator:</strong></label>
                <select name="operator_id" class="form-select" required>
                    <option value="">Select Operator</option>
                    <?php foreach ($operators as $operator): ?>
                        <option value="<?= $operator['id']; ?>"><?= htmlspecialchars($operator['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-submit">Register House</button>
        </form>

        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
        </div>
    </div>

    <!-- Bootstrap JS for interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

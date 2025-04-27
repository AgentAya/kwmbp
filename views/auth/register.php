<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <?php
    session_start();
    require_once __DIR__ . '/../../helpers/csrf.php';
    $csrf_token = generateCsrfToken();
    if(isset($_SESSION['error'])) {
        echo "<p style='color:red;'>".$_SESSION['error']."</p>";
        unset($_SESSION['error']);
    }
    ?>
    <form method="POST" action="register_process.php">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="role" required>
            <option value="agent">Waste Agent</option>
            <option value="operator">Operator</option>
            <option value="consultant">Consultant</option>
            <option value="admin">Admin</option>
        </select><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login Here</a></p>
</body>
</html>



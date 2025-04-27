<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php
    session_start();
    require_once __DIR__ . '/../../helpers/csrf.php';
    $csrf_token = generateCsrfToken();
    if(isset($_SESSION['error'])) {
        echo "<p style='color:red;'>".$_SESSION['error']."</p>";
        unset($_SESSION['error']);
    }
    ?>
    <form method="POST" action="authenticate.php">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register Here</a></p>
</body>
</html>


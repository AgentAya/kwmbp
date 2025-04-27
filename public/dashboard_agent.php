<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Waste Agent Dashboard</title>
</head>
<body>
    <h2>Waste Agent Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> (Waste Agent)</p>
    
    <!-- Agent-specific functionalities -->
    <ul>
        <li><a href="house_register.php">Register a House</a></li>
        <li><a href="collect_payment.php">Collect Payment</a></li>
        <li><a href="view_payments.php">View Payment History</a></li>
    </ul>
    
    <a href="logout.php">Logout</a>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Admin Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> (Admin)</p>
    
    <!-- Admin-specific functionalities -->
    <ul>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="view_reports.php">View Reports</a></li>
        <li><a href="system_settings.php">System Settings</a></li>
    </ul>
    
    <a href="logout.php">Logout</a>
</body>
</html>

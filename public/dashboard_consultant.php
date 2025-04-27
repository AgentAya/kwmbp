<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consultant Dashboard</title>
</head>
<body>
    <h2>Consultant Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> (Consultant)</p>
    
    <!-- Consultant-specific functionalities -->
    <ul>
        <li><a href="view_overview.php">System Overview</a></li>
        <li><a href="analyze_trends.php">Analyze Collection Trends</a></li>
        <li><a href="generate_reports.php">Generate Reports</a></li>
    </ul>
    
    <a href="logout.php">Logout</a>
</body>
</html>

 <?php
// Load environment variables if needed (or hard-code for production)
$host     = 'localhost';
$dbname   = 'waste_managements';
$username = 'root';
$password = '';

// Create a PDO instance with error handling and prepared statements
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Use emulated prepared statements false if possible
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection failed.");
}
?>

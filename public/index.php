 <?php
 require_once __DIR__ . '/../config/database.php';
 require_once __DIR__ . '/../routes/web.php';

session_start();

// Redirect to welcome page
header("Location: welcome.php");
exit;
?>

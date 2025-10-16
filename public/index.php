<?php
session_start();
 require_once __DIR__ . '/../config/database.php';
 require_once __DIR__ . '/../routes/web.php';
// Redirect to welcome page
header("Location: welcome.php");
exit;
?>

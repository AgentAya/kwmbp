 <?php
require_once __DIR__ . '/../app/Controllers/AuthController.php';
$auth = new \App\Controllers\AuthController();
$auth->logout();
?>

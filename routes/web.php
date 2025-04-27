<?php
require_once __DIR__ . '/../app/Controllers/AuthController.php';

$uri = trim($_SERVER['REQUEST_URI'], '/');

// Adjust path if needed
$basePath = 'waste_management_system/public';
$uri = str_replace($basePath, '', $uri);

// Routing logic
if ($uri === '' || $uri === '/') {
    require_once __DIR__ . '/../views/dashboard.php';
} elseif ($uri === 'login') {
    require_once __DIR__ . '/../views/login.php';
} elseif ($uri === 'register') {
    require_once __DIR__ . '/../views/register.php';
} else {
    http_response_code(404);
    echo "404 Not Found";
}
?>

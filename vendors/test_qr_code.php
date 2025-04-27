<?php
require_once __DIR__ . '/../vendors/autoload.php';

use Endroid\QrCode\Builder\Builder;

try {
    // Generate a simple QR code
    $result = Builder::create()
        ->data('Testing QR Code Functionality')
        ->writer(new \Endroid\QrCode\Writer\PngWriter())
        ->build();

    // Display the QR code in the browser
    header('Content-Type: '.$result->getMimeType());
    echo $result->getString();
} catch (Exception $e) {
    // Display the error message
    echo 'Error: ' . $e->getMessage();
}
?>

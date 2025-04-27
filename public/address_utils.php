<?php
// address_utils.php

function normalizeAddress($house_number, $street, $city) {
    // Replace common street abbreviations for consistency (optional and extendable)
    $replacements = [
        'st.' => 'street',
        'rd.' => 'road',
        'ave' => 'avenue',
        'blvd' => 'boulevard',
        'ln' => 'lane',
        'dr' => 'drive',
        'ct' => 'court'
    ];

    // Normalize each component
    $house_number = strtolower(trim($house_number));
    $street = strtolower(trim($street));
    $city = strtolower(trim($city));

    // Expand common street abbreviations
    foreach ($replacements as $short => $full) {
        $street = preg_replace('/\b' . preg_quote($short, '/') . '\b/', $full, $street);
    }

    // Concatenate and clean up
    $normalized = $house_number . ' ' . $street . ', ' . $city;
    $normalized = preg_replace('/\s+/', ' ', $normalized); // collapse multiple spaces
    $normalized = trim($normalized);

    return $normalized;
}
?>

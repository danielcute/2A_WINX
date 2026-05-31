<?php
/**
 * Debug script to check what customization options are being loaded
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Customization.php';

$customization = new Customization();
$allOptions = $customization->getAllOptions();

echo "<h2>All Customization Options from Database</h2>";
echo "Total options: " . count($allOptions) . "<br><br>";

// Group by category
$grouped = [];
foreach ($allOptions as $opt) {
    if (!isset($grouped[$opt['category']])) {
        $grouped[$opt['category']] = [];
    }
    $grouped[$opt['category']][] = $opt;
}

// Display by category
foreach ($grouped as $category => $options) {
    echo "<h3>$category (" . count($options) . " items)</h3>";
    echo "<ul>";
    foreach ($options as $opt) {
        echo "<li>";
        echo "ID: {$opt['option_id']}, ";
        echo "Name: {$opt['name']}, ";
        echo "Price: ₱{$opt['price']}, ";
        echo "Active: " . ($opt['is_active'] ? 'Yes' : 'No');
        echo "</li>";
    }
    echo "</ul>";
}

// Specifically check for 'Other' color option
echo "<h3 style='color: red;'>Specific Check: 'Other' Color Option</h3>";
$colorOptions = $grouped['Color Combinations'] ?? [];
$otherFound = false;
foreach ($colorOptions as $opt) {
    if ($opt['name'] === 'Other') {
        echo "✓ Found! ID: {$opt['option_id']}, Price: ₱{$opt['price']}<br>";
        $otherFound = true;
    }
}
if (!$otherFound) {
    echo "✗ 'Other' option NOT found in Color Combinations<br>";
}

// Check for Sweets
echo "<h3 style='color: red;'>Specific Check: Sweets Options</h3>";
$sweetsOptions = $grouped['Sweets'] ?? [];
if (empty($sweetsOptions)) {
    echo "✗ No Sweets options found<br>";
} else {
    echo "✓ Found " . count($sweetsOptions) . " Sweets options<br>";
}
?>

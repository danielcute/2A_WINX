<?php
// Debug script to test wardrobe retrieval for admin panel
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', dirname(__DIR__));

// Include database and model
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Wardrobe.php';

// Create Wardrobe instance
$wardrobe = new Wardrobe();

// Get wardrobes by category
$wardrobesByCategory = $wardrobe->getAllByCategory();

echo "<h1>Wardrobe Test - Admin Side</h1>";
echo "<h2>Total Categories: " . count($wardrobesByCategory) . "</h2>";

if (empty($wardrobesByCategory)) {
    echo "<p style='color: red;'>NO WARDROBES FOUND!</p>";
} else {
    foreach ($wardrobesByCategory as $category => $wardrobes) {
        echo "<h3>Category: " . htmlspecialchars($category) . " (" . count($wardrobes) . " items)</h3>";
        echo "<ul>";
        foreach ($wardrobes as $wardrobe) {
            echo "<li>" . htmlspecialchars($wardrobe['name']) . " - ₱" . number_format($wardrobe['rental_price'], 2) . "</li>";
        }
        echo "</ul>";
    }
}

echo "<h2>Raw Data Test:</h2>";
echo "<pre>";
print_r($wardrobesByCategory);
echo "</pre>";

<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Wardrobe.php';

echo "<h2>Wardrobe Model Test</h2>";

try {
    $wardrobe = new Wardrobe();
    $wardrobesByCategory = $wardrobe->getAllByCategory();
    
    echo "<p><strong>Successfully fetched wardrobes!</strong></p>";
    echo "<p><strong>Categories found: </strong>" . count($wardrobesByCategory) . "</p>";
    
    if (!empty($wardrobesByCategory)) {
        echo "<p><strong>Category list:</strong></p>";
        foreach ($wardrobesByCategory as $category => $items) {
            echo "- $category: " . count($items) . " items<br>";
        }
    }
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

?>

<?php
/**
 * Add color data to existing color combinations
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Adding Color Data to Existing Combinations</h2>";

// Map existing combinations to their colors
$colorMappings = [
    'Romantic Gold & Blush' => ['#FFD700', '#FFC0CB'],
    'Ocean Blue & Silver' => ['#006994', '#C0C0C0'],
    'Emerald & Gold' => ['#50C878', '#DAA520'],
    'Burgundy & Champagne' => ['#800020', '#F7E7CE'],
    'Coral & Ivory' => ['#FF7F50', '#FFFFF0'],
    'Sage Green & Taupe' => ['#9DC183', '#B38B6D']
];

foreach ($colorMappings as $name => $colors) {
    $colorsJson = json_encode($colors);
    
    $sql = "UPDATE customization_options_tbl SET colors_json = ? WHERE name = ? AND category = 'Color Combinations'";
    $stmt = $db->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param('ss', $colorsJson, $name);
        if ($stmt->execute()) {
            echo "✓ Updated: " . $name . " with " . count($colors) . " colors<br>";
        } else {
            echo "✗ Error updating " . $name . ": " . $db->error . "<br>";
        }
        $stmt->close();
    } else {
        echo "✗ Prepare error: " . $db->error . "<br>";
    }
}

echo "<br><strong style='color: green;'>✓ All existing combinations updated with colors!</strong>";
?>

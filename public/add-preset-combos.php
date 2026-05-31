<?php
/**
 * Add preset 3-color and 5-color combinations to database
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Adding Preset Color Combinations</h2>";

// First, check and add colors_json column if it doesn't exist
$checkColumn = $db->query("SHOW COLUMNS FROM customization_options_tbl LIKE 'colors_json'");
if ($checkColumn->num_rows === 0) {
    echo "Adding colors_json column to customization_options_tbl...<br>";
    if ($db->query("ALTER TABLE customization_options_tbl ADD COLUMN colors_json JSON NULL AFTER description")) {
        echo "✓ Column added<br><br>";
    } else {
        echo "✗ Error adding column: " . $db->error . "<br>";
    }
}

// 3-Color Combinations
$threColorCombos = [
    [
        'name' => '3-Color: Blush, Gold & Ivory',
        'colors' => ['#FFC0CB', '#FFD700', '#FFFFF0'],
        'description' => 'Warm and romantic blush tones with gold accents'
    ],
    [
        'name' => '3-Color: Navy, Silver & Cream',
        'colors' => ['#000080', '#C0C0C0', '#FFFDD0'],
        'description' => 'Elegant and sophisticated navy theme'
    ],
    [
        'name' => '3-Color: Emerald, Gold & White',
        'colors' => ['#50C878', '#DAA520', '#FFFFFF'],
        'description' => 'Fresh and luxurious emerald combination'
    ],
    [
        'name' => '3-Color: Burgundy, Rose Gold & Ivory',
        'colors' => ['#800020', '#B76E79', '#FFFFF0'],
        'description' => 'Rich and romantic burgundy palette'
    ],
    [
        'name' => '3-Color: Lavender, Sage & Cream',
        'colors' => ['#E6E6FA', '#9DC183', '#FFFDD0'],
        'description' => 'Soft and romantic pastel combination'
    ]
];

// 5-Color Combinations
$fiveColorCombos = [
    [
        'name' => '5-Color: Romantic Dream',
        'colors' => ['#FFC0CB', '#FFD700', '#B76E79', '#FFFFF0', '#E6E6FA'],
        'description' => 'Blush pink, gold, rose gold, ivory, and lavender'
    ],
    [
        'name' => '5-Color: Ocean Breeze',
        'colors' => ['#006994', '#000080', '#C0C0C0', '#98FF98', '#FFFFFF'],
        'description' => 'Ocean blue, navy, silver, mint, and white'
    ],
    [
        'name' => '5-Color: Garden Elegance',
        'colors' => ['#50C878', '#9DC183', '#DAA520', '#FFD700', '#F7E7CE'],
        'description' => 'Emerald, sage green, gold, champagne accents'
    ],
    [
        'name' => '5-Color: Sunset Romance',
        'colors' => ['#FF7F50', '#FFDAB9', '#FFD700', '#DAA520', '#800020'],
        'description' => 'Coral, peach, gold, and burgundy sunset tones'
    ],
    [
        'name' => '5-Color: Modern Luxe',
        'colors' => ['#000080', '#C0C0C0', '#FFFFFF', '#DAA520', '#800020'],
        'description' => 'Navy, silver, white, gold, and burgundy'
    ]
];

$allCombos = array_merge($threColorCombos, $fiveColorCombos);

foreach ($allCombos as $combo) {
    // Check if already exists
    $checkSql = "SELECT option_id FROM customization_options_tbl WHERE name = ? AND category = 'Color Combinations'";
    $stmt = $db->prepare($checkSql);
    $stmt->bind_param('s', $combo['name']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Store colors as JSON
        $colorsJson = json_encode($combo['colors']);
        
        $insertSql = "INSERT INTO customization_options_tbl (category, name, description, price, is_active, colors_json) VALUES ('Color Combinations', ?, ?, 3000, 1, ?)";
        $stmt = $db->prepare($insertSql);
        $stmt->bind_param('sss', $combo['name'], $combo['description'], $colorsJson);
        
        if ($stmt->execute()) {
            echo "✓ Added: " . $combo['name'] . " (ID: " . $db->insert_id . ")<br>";
        } else {
            echo "✗ Error adding " . $combo['name'] . ": " . $db->error . "<br>";
        }
    } else {
        echo "~ Already exists: " . $combo['name'] . "<br>";
    }
    $stmt->close();
}

echo "<br><strong style='color: green;'>✓ All preset combinations added!</strong>";
?>

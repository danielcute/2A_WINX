<?php
/**
 * Add aesthetic color combinations including black & red
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Adding Aesthetic Color Combinations</h2>";

// New aesthetic combinations
$aestheticCombos = [
    // Black & Red combinations
    [
        'name' => '3-Color: Black, Red & Gold',
        'colors' => ['#000000', '#DC143C', '#FFD700'],
        'description' => 'Bold and luxurious black with crimson red and gold accents'
    ],
    [
        'name' => '3-Color: Black, Burgundy & Champagne',
        'colors' => ['#1A1A1A', '#800020', '#F7E7CE'],
        'description' => 'Sophisticated black with rich burgundy and champagne tones'
    ],
    [
        'name' => '5-Color: Black & Red Elegance',
        'colors' => ['#000000', '#DC143C', '#8B0000', '#FFD700', '#FFFFFF'],
        'description' => 'Black, crimson red, dark red, gold, and white for dramatic elegance'
    ],
    [
        'name' => '5-Color: Modern Dark Luxe',
        'colors' => ['#1A1A1A', '#E91E63', '#FFD700', '#C0C0C0', '#FFFFFF'],
        'description' => 'Deep black with hot pink, gold, silver, and white'
    ],
    
    // Other modern aesthetic combinations
    [
        'name' => '3-Color: Midnight & Blush',
        'colors' => ['#191970', '#FFB6C1', '#FFFACD'],
        'description' => 'Deep midnight blue with soft blush pink and light yellow'
    ],
    [
        'name' => '3-Color: Forest & Gold',
        'colors' => ['#0B3D2C', '#DAA520', '#FFFFF0'],
        'description' => 'Deep forest green with warm gold and ivory'
    ],
    [
        'name' => '5-Color: Romantic Red Night',
        'colors' => ['#8B0000', '#FFB6C1', '#DAA520', '#FFFFFF', '#E6E6FA'],
        'description' => 'Dark red, light pink, gold, white, and lavender'
    ],
    [
        'name' => '5-Color: Black Pearl Glamour',
        'colors' => ['#000000', '#E8E8E8', '#D4AF37', '#FF1493', '#FFFFFF'],
        'description' => 'Black, pearl gray, champagne gold, deep pink, and white'
    ],
    [
        'name' => '3-Color: Deep Red & Black',
        'colors' => ['#2F0000', '#DC143C', '#FAFAFA'],
        'description' => 'Very dark red, crimson red, and off-white for dramatic contrast'
    ],
    [
        'name' => '5-Color: Midnight Magic',
        'colors' => ['#000033', '#1a1a4d', '#CC0033', '#FFD700', '#FFFFFF'],
        'description' => 'Deep midnight, dark navy, crimson red, gold, and white'
    ]
];

foreach ($aestheticCombos as $combo) {
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

echo "<br><strong style='color: green;'>✓ All aesthetic combinations added!</strong>";
?>

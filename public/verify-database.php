<?php
/**
 * Database Verification & Seeding Script
 * Run this once to ensure customization options are properly in database
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Customization.php';

echo "<h2>Database Verification & Seeding</h2>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if customization_options_tbl exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'customization_options_tbl'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        echo "✓ customization_options_tbl exists<br>";
        
        // Count existing records
        $countResult = $db->query("SELECT COUNT(*) as count FROM customization_options_tbl");
        $countData = $countResult->fetch_assoc();
        echo "✓ Total records: " . $countData['count'] . "<br>";
        
        // Check for "Other" color option
        $otherCheck = $db->query("SELECT * FROM customization_options_tbl WHERE name = 'Other' AND category = 'Color Combinations'");
        if ($otherCheck && $otherCheck->num_rows > 0) {
            $otherData = $otherCheck->fetch_assoc();
            echo "✓ 'Other' color option found (ID: " . $otherData['option_id'] . ", Price: ₱" . $otherData['price'] . ")<br>";
        } else {
            echo "✗ 'Other' color option NOT found - inserting now...<br>";
            $insertOther = $db->query("INSERT INTO customization_options_tbl (category, name, description, price, is_active) VALUES ('Color Combinations', 'Other', 'Choose your own custom color combination', 5000, 1)");
            if ($insertOther) {
                echo "✓ 'Other' color option inserted successfully<br>";
            }
        }
        
        // List all Color Combinations
        echo "<br><strong>Color Combinations in Database:</strong><br>";
        $colorResult = $db->query("SELECT option_id, name, price, is_active FROM customization_options_tbl WHERE category = 'Color Combinations' ORDER BY name");
        if ($colorResult) {
            while ($row = $colorResult->fetch_assoc()) {
                $status = $row['is_active'] ? '✓ Active' : '✗ Inactive';
                echo "- ID {$row['option_id']}: {$row['name']} - ₱{$row['price']} [{$status}]<br>";
            }
        }
        
        // List all Sweets options
        echo "<br><strong>Sweets in Database:</strong><br>";
        $sweetsResult = $db->query("SELECT option_id, name, price, is_active FROM customization_options_tbl WHERE category = 'Sweets' ORDER BY name");
        if ($sweetsResult && $sweetsResult->num_rows > 0) {
            while ($row = $sweetsResult->fetch_assoc()) {
                $status = $row['is_active'] ? '✓ Active' : '✗ Inactive';
                echo "- ID {$row['option_id']}: {$row['name']} - ₱{$row['price']} [{$status}]<br>";
            }
        } else {
            echo "✗ No Sweets options found - inserting now...<br>";
            $sweetsSql = "INSERT INTO customization_options_tbl (category, name, description, price, is_active) VALUES 
            ('Sweets', 'Chocolate Fountain Station', 'Dipped strawberries, marshmallows, and treats', 12000, 1),
            ('Sweets', 'Candy Bar Setup', 'Assorted candies and sweets in decorative display', 8000, 1),
            ('Sweets', 'Macarons & Petit Fours', 'French macarons and elegant petit fours', 10000, 1),
            ('Sweets', 'Donut Wall', 'Decorative donut wall with assorted flavors', 9000, 1)";
            
            if ($db->query($sweetsSql)) {
                echo "✓ Sweets options inserted successfully<br>";
                
                // List newly inserted sweets
                $sweetsResult2 = $db->query("SELECT option_id, name, price FROM customization_options_tbl WHERE category = 'Sweets' ORDER BY name");
                while ($row = $sweetsResult2->fetch_assoc()) {
                    echo "  - ID {$row['option_id']}: {$row['name']} - ₱{$row['price']}<br>";
                }
            } else {
                echo "✗ Error inserting Sweets options: " . $db->error . "<br>";
            }
        }
        
        // Check custom_color_selections table
        $customColorCheck = $db->query("SHOW TABLES LIKE 'custom_color_selections'");
        if ($customColorCheck && $customColorCheck->num_rows > 0) {
            echo "<br>✓ custom_color_selections table exists<br>";
        } else {
            echo "<br>✗ custom_color_selections table does NOT exist - creating...<br>";
            $customization = new Customization();
            if ($customization->ensureCustomColorTableExists()) {
                echo "✓ custom_color_selections table created<br>";
            }
        }
        
    } else {
        echo "✗ customization_options_tbl does NOT exist<br>";
        echo "Creating table and seeding data...<br>";
        
        $customization = new Customization();
        echo "✓ Customization model initialized (table should now exist)<br>";
        
        // Verify it was created
        $tableCheck2 = $db->query("SHOW TABLES LIKE 'customization_options_tbl'");
        if ($tableCheck2 && $tableCheck2->num_rows > 0) {
            echo "✓ Table created successfully<br>";
        }
    }
    
    echo "<br><strong style='color: green;'>✓ Database verification complete!</strong>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

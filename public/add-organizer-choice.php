<?php
/**
 * Add "Organizer Choice" option to database
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Adding Organizer Choice Option</h2>";

// Check if "Organizer Choice" already exists
$checkSql = "SELECT option_id FROM customization_options_tbl WHERE name = 'Organizer Choice' AND category = 'Color Combinations'";
$result = $db->query($checkSql);

if ($result->num_rows === 0) {
    // Add Organizer Choice option
    $insertSql = "INSERT INTO customization_options_tbl (category, name, description, price, is_active, colors_json) 
                  VALUES ('Color Combinations', 'Organizer Choice', 'Let the organizer choose the perfect colors for your event', 0, 1, NULL)";
    
    if ($db->query($insertSql)) {
        $organierChoiceId = $db->insert_id;
        echo "✓ 'Organizer Choice' option added (ID: " . $organierChoiceId . ")<br>";
        
        // Create organizer_choices table if it doesn't exist
        $tableCheck = $db->query("SHOW TABLES LIKE 'organizer_color_choices'");
        if ($tableCheck->num_rows === 0) {
            $createTableSql = "CREATE TABLE organizer_color_choices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                booking_id INT UNIQUE NOT NULL,
                plan_id INT NOT NULL,
                colors_json JSON NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_booking (booking_id),
                INDEX idx_plan (plan_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($db->query($createTableSql)) {
                echo "✓ organizer_color_choices table created<br>";
            } else {
                echo "✗ Error creating table: " . $db->error . "<br>";
            }
        } else {
            echo "~ organizer_color_choices table already exists<br>";
        }
    } else {
        echo "✗ Error adding Organizer Choice: " . $db->error . "<br>";
    }
} else {
    echo "~ 'Organizer Choice' option already exists<br>";
}

echo "<br><strong style='color: green;'>✓ Done!</strong>";
?>

<?php
/**
 * Database Migration: Add Rental Fields to Wardrobes
 * Run this to update the existing wardrobes table
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

try {
    echo "Starting wardrobe table migration...\n";
    
    // Check if columns exist and add them if they don't
    $checkSql = "SHOW COLUMNS FROM wardrobes_tbl LIKE 'rental_price'";
    $result = $db->query($checkSql);
    
    if ($result && $result->num_rows === 0) {
        echo "Adding rental_price column...\n";
        $db->query("ALTER TABLE wardrobes_tbl ADD COLUMN rental_price DECIMAL(10, 2) DEFAULT 0 AFTER description");
    }
    
    $result = $db->query("SHOW COLUMNS FROM wardrobes_tbl LIKE 'availability_count'");
    if ($result && $result->num_rows === 0) {
        echo "Adding availability_count column...\n";
        $db->query("ALTER TABLE wardrobes_tbl ADD COLUMN availability_count INT DEFAULT 1 AFTER rental_price");
    }
    
    $result = $db->query("SHOW COLUMNS FROM wardrobes_tbl LIKE 'rental_duration_days'");
    if ($result && $result->num_rows === 0) {
        echo "Adding rental_duration_days column...\n";
        $db->query("ALTER TABLE wardrobes_tbl ADD COLUMN rental_duration_days INT DEFAULT 1 AFTER availability_count");
    }
    
    $result = $db->query("SHOW COLUMNS FROM wardrobes_tbl LIKE 'sizes_available'");
    if ($result && $result->num_rows === 0) {
        echo "Adding sizes_available column...\n";
        $db->query("ALTER TABLE wardrobes_tbl ADD COLUMN sizes_available VARCHAR(255) DEFAULT 'Standard' AFTER rental_duration_days");
    }
    
    $result = $db->query("SHOW COLUMNS FROM wardrobes_tbl LIKE 'condition_status'");
    if ($result && $result->num_rows === 0) {
        echo "Adding condition_status column...\n";
        $db->query("ALTER TABLE wardrobes_tbl ADD COLUMN condition_status ENUM('excellent', 'good', 'fair', 'needs_cleaning') DEFAULT 'excellent' AFTER sizes_available");
    }
    
    $result = $db->query("SHOW COLUMNS FROM wardrobes_tbl LIKE 'updated_at'");
    if ($result && $result->num_rows === 0) {
        echo "Adding updated_at column...\n";
        $db->query("ALTER TABLE wardrobes_tbl ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    }
    
    // Update existing wardrobes with rental prices (copy from price column if it exists)
    echo "Updating existing wardrobe rental prices...\n";
    $db->query("UPDATE wardrobes_tbl SET rental_price = price WHERE rental_price = 0 AND price > 0");
    
    // Set default values for wardrobes that don't have rentals set up yet
    echo "Setting default rental values for existing wardrobes...\n";
    $db->query("UPDATE wardrobes_tbl SET availability_count = 1 WHERE availability_count = 0");
    $db->query("UPDATE wardrobes_tbl SET rental_duration_days = 1 WHERE rental_duration_days = 0");
    $db->query("UPDATE wardrobes_tbl SET sizes_available = 'Standard' WHERE sizes_available = ''");
    
    // Create wardrobe_selections table if it doesn't exist
    echo "Creating wardrobe_selections_tbl if needed...\n";
    $tableCheck = $db->query("SHOW TABLES LIKE 'wardrobe_selections_tbl'");
    
    if ($tableCheck && $tableCheck->num_rows === 0) {
        $createTableSql = "CREATE TABLE IF NOT EXISTS `wardrobe_selections_tbl` (
            `selection_id` INT AUTO_INCREMENT PRIMARY KEY,
            `plan_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `wardrobe_id` INT NOT NULL,
            `quantity_selected` INT DEFAULT 1,
            `size_selected` VARCHAR(50),
            `rental_start_date` DATE,
            `rental_end_date` DATE,
            `subtotal_price` DECIMAL(10, 2) DEFAULT 0,
            `selection_notes` TEXT,
            `status` ENUM('pending', 'confirmed', 'rented', 'returned', 'cancelled') DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_plan` (`plan_id`),
            INDEX `idx_user` (`user_id`),
            INDEX `idx_wardrobe` (`wardrobe_id`),
            INDEX `idx_status` (`status`),
            FOREIGN KEY (`wardrobe_id`) REFERENCES `wardrobes_tbl`(`wardrobe_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->query($createTableSql);
        echo "Created wardrobe_selections_tbl\n";
    }
    
    echo "\n✓ Migration completed successfully!\n";
    echo "Run the PHP migration to verify:\n";
    echo "  php /SINTA/db-migrate-wardrobe.php\n";
    
} catch (Exception $e) {
    echo "✗ Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

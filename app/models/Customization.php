<?php
/**
 * Customization Model
 * Handles event customization options
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';

class Customization {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTableExists();
        $this->ensureCustomColorTableExists();
        $this->ensureColorPaletteImagesTableExists();
    }
    
    /**
     * Ensure customization_options_tbl exists, create if missing
     */
    private function ensureTableExists() {
        // Check if table exists
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'customization_options_tbl'");
        
        if ($tableCheck && $tableCheck->num_rows === 0) {
            // Table doesn't exist, create it
            error_log("customization_options_tbl missing, creating...");
            
            $createTableSql = "CREATE TABLE IF NOT EXISTS `customization_options_tbl` (
                `option_id` INT AUTO_INCREMENT PRIMARY KEY,
                `category` VARCHAR(100) NOT NULL,
                `name` VARCHAR(150) NOT NULL,
                `description` TEXT,
                `price` DECIMAL(10, 2) DEFAULT 0,
                `image` LONGBLOB,
                `image_type` VARCHAR(50),
                `is_active` TINYINT DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_category` (`category`),
                INDEX `idx_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($this->db->query($createTableSql)) {
                error_log("customization_options_tbl created successfully");
                $this->seedCustomizationOptions();
            } else {
                error_log("Failed to create customization_options_tbl: " . $this->db->error);
            }
        } else {
            // Table exists, check if it has the new columns and reseed if empty
            $columnCheck = $this->db->query("SHOW COLUMNS FROM customization_options_tbl LIKE 'description'");
            if (!$columnCheck || $columnCheck->num_rows === 0) {
                // Add missing description column
                $this->db->query("ALTER TABLE customization_options_tbl ADD COLUMN description TEXT");
                error_log("Added description column to customization_options_tbl");
            }
            
            $columnCheck = $this->db->query("SHOW COLUMNS FROM customization_options_tbl LIKE 'image'");
            if (!$columnCheck || $columnCheck->num_rows === 0) {
                // Add missing image columns
                $this->db->query("ALTER TABLE customization_options_tbl ADD COLUMN image LONGBLOB");
                $this->db->query("ALTER TABLE customization_options_tbl ADD COLUMN image_type VARCHAR(50)");
                error_log("Added image columns to customization_options_tbl");
            }

            $columnCheck = $this->db->query("SHOW COLUMNS FROM customization_options_tbl LIKE 'colors_json'");
            if (!$columnCheck || $columnCheck->num_rows === 0) {
                // Add missing colors_json column
                $this->db->query("ALTER TABLE customization_options_tbl ADD COLUMN colors_json JSON NULL AFTER description");
                error_log("Added colors_json column to customization_options_tbl");
            }
            
            // Check if table is empty or only has old data, then reseed
            $countCheck = $this->db->query("SELECT COUNT(*) as count FROM customization_options_tbl");
            $countResult = $countCheck->fetch_assoc();
            
            // If table has Photography, Decorations, or Lighting categories, clear and reseed
            $oldDataCheck = $this->db->query("SELECT COUNT(*) as count FROM customization_options_tbl WHERE category IN ('Photography', 'Decorations', 'Lighting', 'Sound System', 'Seating')");
            $oldDataResult = $oldDataCheck->fetch_assoc();
            
            if ($oldDataResult['count'] > 0) {
                // Clear old data and reseed
                error_log("Found old customization data, clearing and reseeding...");
                $this->db->query("DELETE FROM customization_options_tbl WHERE category NOT IN ('Theme', 'Venue Deco', 'Color Combinations', 'Venue', 'Food', 'Catering', 'Pastries', 'Beverages', 'Add-ons')");
                
                // Only seed if main categories are missing
                $mainCheck = $this->db->query("SELECT COUNT(*) as count FROM customization_options_tbl WHERE category IN ('Theme', 'Venue Deco', 'Color Combinations', 'Venue', 'Food', 'Catering', 'Pastries', 'Beverages', 'Add-ons')");
                $mainResult = $mainCheck->fetch_assoc();
                if ($mainResult['count'] < 20) {
                    $this->seedCustomizationOptions();
                }
            } elseif ($countResult['count'] == 0) {
                // Table is empty, seed it
                error_log("customization_options_tbl is empty, seeding...");
                $this->seedCustomizationOptions();
            }
        }
    }
    
    /**
     * Seed customization options
     */
    private function seedCustomizationOptions() {
        $insertSql = "INSERT INTO customization_options_tbl (category, name, description, price, is_active) VALUES 
        ('Theme', 'Garden Romance', 'Whimsical floral arrangements, fairy lights, natural elegance', 25000, 1),
        ('Theme', 'Rustic Charm', 'Woodland vibes, mason jars, burlap accents, warm tones', 20000, 1),
        ('Theme', 'Modern Elegance', 'Sleek lines, minimalist decor, contemporary chic', 35000, 1),
        ('Theme', 'Tropical Paradise', 'Vibrant colors, exotic flowers, tiki torches, island vibes', 30000, 1),
        ('Color Combinations', 'Romantic Gold & Blush', 'Elegant gold tones with soft blush pink accents', 12000, 1),
        ('Color Combinations', 'Ocean Blue & Silver', 'Cool ocean blue with shimmering silver details', 12000, 1),
        ('Color Combinations', 'Emerald & Gold', 'Rich emerald green with luxurious gold accents', 15000, 1),
        ('Color Combinations', 'Burgundy & Champagne', 'Deep burgundy with champagne and ivory touches', 12000, 1),
        ('Color Combinations', 'Coral & Ivory', 'Vibrant coral with clean ivory and white elements', 10000, 1),
        ('Color Combinations', 'Sage Green & Taupe', 'Soft sage green with warm taupe for natural elegance', 10000, 1),
        ('Color Combinations', 'Other', 'Choose your own custom color combination', 5000, 1),
        ('Venue', 'Garden Venue', 'Beautiful outdoor setting, up to 150 guests', 50000, 1),
        ('Venue', 'Hotel Ballroom', 'Elegant indoor venue, up to 300 guests', 80000, 1),
        ('Venue', 'Beach Resort', 'Oceanfront paradise, up to 200 guests', 120000, 1),
        ('Venue', 'Private Estate', 'Luxury mansion setting, up to 400 guests', 150000, 1),
        ('Food', 'Appetizer Tasting Station', 'Selection of gourmet appetizers and canapés', 20000, 1),
        ('Food', 'Dessert Station', 'Sweet treats and specialty desserts', 15000, 1),
        ('Food', 'BBQ Grilling Station', 'Fresh-grilled meats and seafood station', 25000, 1),
        ('Food', 'Italian Pasta Bar', 'Made-to-order pasta with various sauces', 18000, 1),
        ('Sweets', 'Chocolate Fountain Station', 'Dipped strawberries, marshmallows, and treats', 12000, 1),
        ('Sweets', 'Candy Bar Setup', 'Assorted candies and sweets in decorative display', 8000, 1),
        ('Sweets', 'Macarons & Petit Fours', 'French macarons and elegant petit fours', 10000, 1),
        ('Sweets', 'Donut Wall', 'Decorative donut wall with assorted flavors', 9000, 1),
        ('Catering', 'Premium Buffet', 'Wide selection of dishes, live stations, 100 pax', 45000, 1),
        ('Catering', 'Plated Dinner', 'Elegant 5-course meal, 120 pax', 75000, 1),
        ('Catering', 'Food Truck Fiesta', 'Casual dining with gourmet food trucks, 100 pax', 55000, 1),
        ('Catering', 'Seafood Extravaganza', 'Fresh seafood buffet, oyster bar, 100 pax', 95000, 1),
        ('Pastries', 'Classic White Cake', 'Traditional multi-layer cake with buttercream frosting', 8000, 1),
        ('Pastries', 'Chocolate Deluxe', 'Rich chocolate cake with ganache and berries', 10000, 1),
        ('Pastries', 'Customized Cupcakes', 'Assorted flavored cupcakes with custom designs, 100 pieces', 12000, 1),
        ('Pastries', 'Dessert Bar Combo', 'Macarons, pastries, chocolates, and petit fours selection', 15000, 1),
        ('Beverages', 'Premium Coffee Bar', 'Espresso, cappuccino, latte, and specialty coffee drinks', 8000, 1),
        ('Beverages', 'Signature Mocktails', 'Non-alcoholic cocktails with fresh fruits and flavors', 12000, 1),
        ('Beverages', 'Champagne Toasting Package', 'Premium champagne service for toasts and celebrations', 20000, 1),
        ('Beverages', 'Refreshment Station', 'Assorted juices, soft drinks, and flavored water', 6000, 1),
        ('Add-ons', 'Premium Photography', 'Professional photographer with edited album', 30000, 1),
        ('Add-ons', 'Cinematic Videography', 'Full day videography with post-production', 40000, 1),
        ('Add-ons', 'Floral Arrangements', 'Customized floral décor throughout venue', 18000, 1),
        ('Add-ons', 'Live Band / DJ', 'Professional entertainment for entire event', 50000, 1),
        ('Add-ons', 'Lighting & Sound', 'Professional lighting and sound system', 25000, 1),
        ('Add-ons', 'Photo Booth', 'Interactive photo booth with props and prints', 15000, 1),
        ('Add-ons', 'Day-of Coordinator', 'Professional event coordinator for event day', 20000, 1)";
        
        if ($this->db->query($insertSql)) {
            error_log("Customization options seeded successfully");
        } else {
            error_log("Failed to seed customization options: " . $this->db->error);
        }
    }
    
    public function getOptionsByCategory($category) {
        $category = $this->db->real_escape_string($category);
        $stmt = $this->db->prepare("
            SELECT * FROM customization_options_tbl 
            WHERE category = ? AND is_active = 1
            ORDER BY name
        ");
        
        if (!$stmt) {
            error_log("getOptionsByCategory prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $options = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $options;
    }
    
    public function getAllOptions() {
        $stmt = $this->db->prepare("
            SELECT * FROM customization_options_tbl 
            WHERE is_active = 1 
            ORDER BY category, name
        ");
        
        if (!$stmt) {
            // Check if it's a "table doesn't exist" error
            if (strpos($this->db->error, "doesn't exist") !== false) {
                error_log("customization_options_tbl doesn't exist, attempting to create...");
                $this->ensureTableExists();
                
                // Try again after creating
                $stmt = $this->db->prepare("
                    SELECT * FROM customization_options_tbl 
                    WHERE is_active = 1 
                    ORDER BY category, name
                ");
                
                if (!$stmt) {
                    error_log("getAllOptions prepare failed after table creation: " . $this->db->error);
                    return [];
                }
            } else {
                error_log("getAllOptions prepare failed: " . $this->db->error);
                return [];
            }
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $options = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $options;
    }
    
    public function getOptionById($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("SELECT * FROM customization_options_tbl WHERE option_id = ?");
        
        if (!$stmt) {
            error_log("getOptionById prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $option = $result->fetch_assoc();
        $stmt->close();
        return $option;
    }
    
    public function calculateTotal($selectedOptions) {
        $total = 0;
        if (is_array($selectedOptions)) {
            foreach ($selectedOptions as $optionId) {
                $option = $this->getOptionById($optionId);
                if ($option) {
                    $total += $option['price'];
                }
            }
        }
        return $total;
    }
    
    public function create($data) {
        $category = $this->db->real_escape_string($data['category']);
        $name = $this->db->real_escape_string($data['name']);
        $description = isset($data['description']) ? $this->db->real_escape_string($data['description']) : '';
        $price = (float)($data['price'] ?? 0);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $image = isset($data['image']) ? $data['image'] : null;
        $imageType = isset($data['image_type']) ? $data['image_type'] : null;
        
        if ($image === null) {
            // No image provided
            $stmt = $this->db->prepare("
                INSERT INTO customization_options_tbl 
                (category, name, description, price, is_active) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            if (!$stmt) {
                error_log("create prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("sssdi", $category, $name, $description, $price, $isActive);
        } else {
            // Image provided
            $stmt = $this->db->prepare("
                INSERT INTO customization_options_tbl 
                (category, name, description, price, image, image_type, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            if (!$stmt) {
                error_log("create prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("sssdssi", $category, $name, $description, $price, $image, $imageType, $isActive);
        }
        
        if ($stmt->execute()) {
            $result = $this->db->insert_id;
            $stmt->close();
            return $result;
        } else {
            error_log("create execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    public function update($id, $data) {
        $id = (int)$id;
        $sets = [];
        $types = "";
        $values = [];
        $refs = [];
        
        if (isset($data['category'])) {
            $sets[] = "category = ?";
            $types .= "s";
            $values['category'] = $this->db->real_escape_string($data['category']);
            $refs[] = &$values['category'];
        }
        if (isset($data['name'])) {
            $sets[] = "name = ?";
            $types .= "s";
            $values['name'] = $this->db->real_escape_string($data['name']);
            $refs[] = &$values['name'];
        }
        if (isset($data['description'])) {
            $sets[] = "description = ?";
            $types .= "s";
            $values['description'] = $this->db->real_escape_string($data['description']);
            $refs[] = &$values['description'];
        }
        if (isset($data['price'])) {
            $sets[] = "price = ?";
            $types .= "d";
            $values['price'] = (float)$data['price'];
            $refs[] = &$values['price'];
        }
        if (isset($data['image'])) {
            $sets[] = "image = ?";
            $types .= "s";
            $values['image'] = $data['image'];
            $refs[] = &$values['image'];
        }
        if (isset($data['image_type'])) {
            $sets[] = "image_type = ?";
            $types .= "s";
            $values['image_type'] = $data['image_type'];
            $refs[] = &$values['image_type'];
        }
        if (isset($data['colors_json'])) {
            $sets[] = "colors_json = ?";
            $types .= "s";
            $values['colors_json'] = $data['colors_json'];
            $refs[] = &$values['colors_json'];
        }
        if (isset($data['is_active'])) {
            $sets[] = "is_active = ?";
            $types .= "i";
            $values['is_active'] = (int)$data['is_active'];
            $refs[] = &$values['is_active'];
        }
        
        if (empty($sets)) return false;
        
        $types .= "i";
        $values['id'] = $id;
        $refs[] = &$values['id'];
        
        $sql = "UPDATE customization_options_tbl SET " . implode(', ', $sets) . " WHERE option_id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("update prepare failed: " . $this->db->error);
            return false;
        }
        
        $bindParams = array_merge([$types], $refs);
        call_user_func_array([$stmt, 'bind_param'], $bindParams);
        $result = $stmt->execute();
        if (!$result) {
            error_log("update execute failed: " . $stmt->error);
        }
        $stmt->close();
        return $result;
    }
    
    public function delete($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("DELETE FROM customization_options_tbl WHERE option_id = ?");
        
        if (!$stmt) {
            error_log("delete prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Store custom color selection for a plan/booking
     */
    public function storeCustomColors($planId, $customColors, $description) {
        $customColorsJson = json_encode($customColors);
        $description = $this->db->real_escape_string($description);
        
        $stmt = $this->db->prepare("
            INSERT INTO custom_color_selections (plan_id, colors_json, description, created_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE 
                colors_json = VALUES(colors_json),
                description = VALUES(description),
                created_at = CURRENT_TIMESTAMP
        ");
        
        if (!$stmt) {
            error_log("storeCustomColors prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("iss", $planId, $customColorsJson, $description);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Get custom colors for a plan/booking
     */
    public function getCustomColors($planId) {
        $planId = (int)$planId;
        $stmt = $this->db->prepare("
            SELECT colors_json, description FROM custom_color_selections 
            WHERE plan_id = ?
        ");
        
        if (!$stmt) {
            error_log("getCustomColors prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        if ($data) {
            $data['colors'] = json_decode($data['colors_json'], true);
        }
        return $data;
    }

    /**
     * Ensure custom_color_selections table exists
     */
    public function ensureCustomColorTableExists() {
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'custom_color_selections'");
        
        if ($tableCheck && $tableCheck->num_rows === 0) {
            $createTableSql = "CREATE TABLE IF NOT EXISTS `custom_color_selections` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `plan_id` INT NOT NULL UNIQUE,
                `colors_json` JSON,
                `description` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_plan_id` (`plan_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($this->db->query($createTableSql)) {
                error_log("custom_color_selections table created successfully");
                return true;
            } else {
                error_log("Failed to create custom_color_selections table: " . $this->db->error);
                return false;
            }
        }
        return true;
    }

    public function ensureColorPaletteImagesTableExists() {
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'color_palette_images'");
        
        if ($tableCheck && $tableCheck->num_rows === 0) {
            $createTableSql = "CREATE TABLE IF NOT EXISTS `color_palette_images` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `option_id` INT NOT NULL,
                `hex_code` VARCHAR(7) NOT NULL,
                `color_name` VARCHAR(100),
                `image` LONGBLOB,
                `image_type` VARCHAR(50),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_option_hex` (`option_id`, `hex_code`),
                INDEX `idx_option_id` (`option_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($this->db->query($createTableSql)) {
                error_log("color_palette_images table created successfully");
                return true;
            } else {
                error_log("Failed to create color_palette_images table: " . $this->db->error);
                return false;
            }
        }
        return true;
    }

    /**
     * Get color palette images for an option
     */
    public function getColorPaletteImages($optionId) {
        $sql = "SELECT * FROM color_palette_images WHERE option_id = ? ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("getColorPaletteImages prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("i", $optionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        
        return $images;
    }

    /**
     * Save or update color palette image
     */
    public function saveColorPaletteImage($optionId, $hexCode, $colorName, $imageData, $imageType) {
        $sql = "INSERT INTO color_palette_images (option_id, hex_code, color_name, image, image_type)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    color_name = VALUES(color_name),
                    image = VALUES(image),
                    image_type = VALUES(image_type),
                    updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("saveColorPaletteImage prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("issss", $optionId, $hexCode, $colorName, $imageData, $imageType);
        if (!$stmt->execute()) {
            error_log("saveColorPaletteImage execute failed: " . $stmt->error);
            return false;
        }
        
        return true;
    }

    /**
     * Delete color palette image
     */
    public function deleteColorPaletteImage($imageId) {
        $sql = "DELETE FROM color_palette_images WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("deleteColorPaletteImage prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $imageId);
        if (!$stmt->execute()) {
            error_log("deleteColorPaletteImage execute failed: " . $stmt->error);
            return false;
        }
        
        return true;
    }
}
?>
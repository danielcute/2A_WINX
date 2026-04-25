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
            
            // Check if table is empty or only has old data, then reseed
            $countCheck = $this->db->query("SELECT COUNT(*) as count FROM customization_options_tbl");
            $countResult = $countCheck->fetch_assoc();
            
            // If table has Photography, Decorations, or Lighting categories, clear and reseed
            $oldDataCheck = $this->db->query("SELECT COUNT(*) as count FROM customization_options_tbl WHERE category IN ('Photography', 'Decorations', 'Lighting', 'Sound System', 'Seating')");
            $oldDataResult = $oldDataCheck->fetch_assoc();
            
            if ($oldDataResult['count'] > 0) {
                // Clear old data and reseed
                error_log("Found old customization data, clearing and reseeding...");
                $this->db->query("DELETE FROM customization_options_tbl WHERE category NOT IN ('Theme', 'Venue', 'Catering', 'Extras')");
                
                // Only seed if main categories are missing
                $mainCheck = $this->db->query("SELECT COUNT(*) as count FROM customization_options_tbl WHERE category IN ('Theme', 'Venue', 'Catering', 'Extras')");
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
        ('Venue', 'Garden Venue', 'Beautiful outdoor setting, up to 150 guests', 50000, 1),
        ('Venue', 'Hotel Ballroom', 'Elegant indoor venue, up to 300 guests', 80000, 1),
        ('Venue', 'Beach Resort', 'Oceanfront paradise, up to 200 guests', 120000, 1),
        ('Venue', 'Private Estate', 'Luxury mansion setting, up to 400 guests', 150000, 1),
        ('Catering', 'Premium Buffet', 'Wide selection of dishes, live stations, 100 pax', 45000, 1),
        ('Catering', 'Plated Dinner', 'Elegant 5-course meal, 120 pax', 75000, 1),
        ('Catering', 'Food Truck Fiesta', 'Casual dining with gourmet food trucks, 100 pax', 55000, 1),
        ('Catering', 'Seafood Extravaganza', 'Fresh seafood buffet, oyster bar, 100 pax', 95000, 1),
        ('Extras', 'Premium Photography', 'Professional photographer with edited album', 30000, 1),
        ('Extras', 'Cinematic Videography', 'Full day videography with post-production', 40000, 1),
        ('Extras', 'Floral Arrangements', 'Customized floral décor throughout venue', 18000, 1),
        ('Extras', 'Live Band / DJ', 'Professional entertainment for entire event', 50000, 1),
        ('Extras', 'Designer Cake', 'Custom-designed multi-tier wedding cake', 8000, 1),
        ('Extras', 'Lighting & Sound', 'Professional lighting and sound system', 25000, 1),
        ('Extras', 'Photo Booth', 'Interactive photo booth with props and prints', 15000, 1),
        ('Extras', 'Day-of Coordinator', 'Professional event coordinator for event day', 20000, 1)";
        
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
        
        if (isset($data['category'])) {
            $sets[] = "category = ?";
            $types .= "s";
            $values[] = $this->db->real_escape_string($data['category']);
        }
        if (isset($data['name'])) {
            $sets[] = "name = ?";
            $types .= "s";
            $values[] = $this->db->real_escape_string($data['name']);
        }
        if (isset($data['description'])) {
            $sets[] = "description = ?";
            $types .= "s";
            $values[] = $this->db->real_escape_string($data['description']);
        }
        if (isset($data['price'])) {
            $sets[] = "price = ?";
            $types .= "d";
            $values[] = (float)$data['price'];
        }
        if (isset($data['image'])) {
            $sets[] = "image = ?";
            $types .= "s";
            $values[] = $data['image'];
        }
        if (isset($data['image_type'])) {
            $sets[] = "image_type = ?";
            $types .= "s";
            $values[] = $data['image_type'];
        }
        if (isset($data['is_active'])) {
            $sets[] = "is_active = ?";
            $types .= "i";
            $values[] = (int)$data['is_active'];
        }
        
        if (empty($sets)) return false;
        
        $types .= "i";
        $values[] = $id;
        
        $sql = "UPDATE customization_options_tbl SET " . implode(', ', $sets) . " WHERE option_id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("update prepare failed: " . $this->db->error);
            return false;
        }
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $values));
        $result = $stmt->execute();
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
}
?>
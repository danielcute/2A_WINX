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
                `price` DECIMAL(10, 2) DEFAULT 0,
                `is_active` TINYINT DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_category` (`category`),
                INDEX `idx_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($this->db->query($createTableSql)) {
                error_log("customization_options_tbl created successfully");
                
                // Insert sample data
                $insertSql = "INSERT INTO customization_options_tbl (category, name, price, is_active) VALUES 
                ('Decorations', 'Gold Decorations', 5000, 1),
                ('Decorations', 'Silver Decorations', 3000, 1),
                ('Catering', 'Premium Menu', 8000, 1),
                ('Catering', 'Standard Menu', 5000, 1),
                ('Entertainment', 'Live Band', 10000, 1),
                ('Entertainment', 'DJ Service', 5000, 1),
                ('Photography', 'Full Day Photography', 15000, 1),
                ('Photography', 'Half Day Photography', 10000, 1),
                ('Venue Setup', 'Complete Setup', 3000, 1),
                ('Lighting', 'LED Lighting Package', 7000, 1)";
                
                if ($this->db->query($insertSql)) {
                    error_log("Sample customization data inserted");
                }
            } else {
                error_log("Failed to create customization_options_tbl: " . $this->db->error);
            }
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
        $price = (float)($data['price'] ?? 0);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        
        $stmt = $this->db->prepare("
            INSERT INTO customization_options_tbl 
            (category, name, price, is_active) 
            VALUES (?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("create prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("ssdi", $category, $name, $price, $isActive);
        
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
        if (isset($data['price'])) {
            $sets[] = "price = ?";
            $types .= "d";
            $values[] = (float)$data['price'];
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
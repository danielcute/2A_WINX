<?php
/**
 * Customization Management Controller
 * Handles CRUD operations for package customizations/add-ons
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';

class CustomizationController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all customizations
     */
    public function getAll() {
        $sql = "SELECT c.*, p.name as package_name
                FROM customizations_tbl c
                LEFT JOIN packages_tbl p ON c.package_id = p.package_id
                ORDER BY c.created_at DESC";
        
        $result = $this->db->query($sql);
        if (!$result) {
            return ['error' => $this->db->error];
        }
        
        $customizations = [];
        while ($row = $result->fetch_assoc()) {
            $customizations[] = $row;
        }
        return $customizations;
    }
    
    /**
     * Get customization by ID
     */
    public function getById($customization_id) {
        $stmt = $this->db->prepare("
            SELECT c.*, p.package_name
            FROM customizations_tbl c
            LEFT JOIN packages_tbl p ON c.package_id = p.package_id
            WHERE c.customization_id = ?
        ");
        
        if (!$stmt) {
            return ['error' => $this->db->error];
        }
        
        $stmt->bind_param("i", $customization_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Get customizations by package
     */
    public function getByPackage($package_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM customizations_tbl 
            WHERE package_id = ?
            ORDER BY created_at DESC
        ");
        
        if (!$stmt) {
            return ['error' => $this->db->error];
        }
        
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $customizations = [];
        while ($row = $result->fetch_assoc()) {
            $customizations[] = $row;
        }
        return $customizations;
    }
    
    /**
     * Create new customization
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO customizations_tbl 
            (package_id, category, name, description, price, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        $package_id = $data['package_id'] ?? 0;
        $category = $data['category'] ?? '';
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0;
        $status = $data['status'] ?? 'active';
        
        $stmt->bind_param(
            "isssds",
            $package_id,
            $category,
            $name,
            $description,
            $price,
            $status
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'customization_id' => $this->db->insert_id];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Update customization
     */
    public function update($customization_id, $data) {
        $sets = [];
        $types = "";
        $values = [];
        
        $allowed_fields = ['package_id', 'category', 'name', 'description', 'price', 'status'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $sets[] = "$field = ?";
                if ($field === 'package_id' || $field === 'price') {
                    $types .= ($field === 'price') ? 'd' : 'i';
                } else {
                    $types .= 's';
                }
                $values[] = $data[$field];
            }
        }
        
        if (empty($sets)) {
            return ['success' => false, 'error' => 'No fields to update'];
        }
        
        $types .= "i";
        $values[] = $customization_id;
        
        $sql = "UPDATE customizations_tbl SET " . implode(", ", $sets) . " WHERE customization_id = ?";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        $params = array_merge([$types], $values);
        if (!call_user_func_array([$stmt, 'bind_param'], $params)) {
            return ['success' => false, 'error' => $stmt->error];
        }
        
        if (!$stmt->execute()) {
            return ['success' => false, 'error' => $stmt->error];
        }
        
        return ['success' => true];
    }
    
    /**
     * Delete customization
     */
    public function delete($customization_id) {
        $stmt = $this->db->prepare("DELETE FROM customizations_tbl WHERE customization_id = ?");
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        $stmt->bind_param("i", $customization_id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Get customization statistics
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(DISTINCT package_id) as packages_with_options,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    AVG(price) as avg_price,
                    MAX(price) as max_price,
                    MIN(price) as min_price
                FROM customizations_tbl";
        
        $result = $this->db->query($sql);
        if (!$result) {
            return ['error' => $this->db->error];
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get customizations by category
     */
    public function getByCategory($category) {
        $stmt = $this->db->prepare("
            SELECT c.*, p.package_name
            FROM customizations_tbl c
            LEFT JOIN packages_tbl p ON c.package_id = p.package_id
            WHERE c.category = ?
            ORDER BY c.created_at DESC
        ");
        
        if (!$stmt) {
            return ['error' => $this->db->error];
        }
        
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $customizations = [];
        while ($row = $result->fetch_assoc()) {
            $customizations[] = $row;
        }
        return $customizations;
    }

    /**
     * Upload customization image
     */
    public function uploadImage($file, $customization_id) {
        $upload_dir = ROOT_PATH . '/public/assets/img/customizations/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.'];
        }
        
        if ($file['size'] > $max_size) {
            return ['success' => false, 'error' => 'File too large. Maximum 5MB allowed.'];
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'custom_' . $customization_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            return ['success' => true, 'path' => '/SINTA/public/assets/img/customizations/' . $new_filename];
        } else {
            return ['success' => false, 'error' => 'Failed to upload image'];
        }
    }
}
?>

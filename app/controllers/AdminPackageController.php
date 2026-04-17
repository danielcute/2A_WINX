<?php
/**
 * Admin Package Management Controller
 * Handles CRUD operations for packages
 */

define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';

class AdminPackageController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all packages
     */
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM packages_tbl ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get package by ID
     */
    public function getById($package_id) {
        $stmt = $this->db->prepare("SELECT * FROM packages_tbl WHERE package_id = ?");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Create new package
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO packages_tbl 
            (package_name, description, price, event_type, image, category, features, max_guests, duration_hours, venue_type, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        $stmt->bind_param(
            "ssdssssiss",
            $data['package_name'],
            $data['description'],
            $data['price'],
            $data['event_type'],
            $data['image'] ?? null,
            $data['category'] ?? null,
            $data['features'] ?? null,
            $data['max_guests'] ?? 100,
            $data['duration_hours'] ?? 4,
            $data['venue_type'] ?? null,
            $data['status'] ?? 'active'
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'package_id' => $this->db->insert_id];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Update package
     */
    public function update($package_id, $data) {
        $sets = [];
        $types = "";
        $values = [];
        
        $allowed_fields = ['package_name', 'description', 'price', 'event_type', 'image', 'category', 'features', 'max_guests', 'duration_hours', 'venue_type', 'status'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $sets[] = "$field = ?";
                $types .= ($field === 'price' || $field === 'max_guests' || $field === 'duration_hours') ? 'd' : 's';
                $values[] = $data[$field];
            }
        }
        
        if (empty($sets)) return ['success' => false, 'error' => 'No fields to update'];
        
        $types .= "i";
        $values[] = $package_id;
        
        $sql = "UPDATE packages_tbl SET " . implode(", ", $sets) . " WHERE package_id = ?";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $values));
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Delete package
     */
    public function delete($package_id) {
        $stmt = $this->db->prepare("DELETE FROM packages_tbl WHERE package_id = ?");
        $stmt->bind_param("i", $package_id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Handle image upload
     */
    public function uploadImage($file, $package_id) {
        $upload_dir = ROOT_PATH . '/public/assets/img/packages/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error'];
        }
        
        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed'];
        }
        
        if ($file['size'] > $max_size) {
            return ['success' => false, 'error' => 'File too large. Max 5MB'];
        }
        
        $file_name = 'package_' . $package_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $target_file = $upload_dir . $file_name;
        $relative_path = '/SINTA/public/assets/img/packages/' . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return ['success' => true, 'path' => $relative_path];
        } else {
            return ['success' => false, 'error' => 'Failed to save file'];
        }
    }
}

?>

<?php
require_once ROOT_PATH . '/config/database.php';

class Package {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll($occasionId = null) {
        $sql = "SELECT p.*, o.events as occasion_name 
                FROM packages_tbl p 
                LEFT JOIN occasions_tbl o ON p.occasion_id = o.occasion_id";
        
        if ($occasionId) {
            $occasionId = (int)$occasionId;
            $sql .= " WHERE p.occasion_id = $occasionId";
        }
        
        $sql .= " ORDER BY p.price ASC";
        $result = $this->db->query($sql);
        
        $packages = [];
        while ($row = $result->fetch_assoc()) {
            // Get inclusions
            $row['inclusions'] = $this->getInclusions($row['package_id']);
            $row['images'] = $this->getImages($row['package_id']);
            $packages[] = $row;
        }
        return $packages;
    }
    
    public function getInclusions($packageId) {
        $packageId = (int)$packageId;
        $result = $this->db->query("SELECT * FROM package_inclusions_tbl WHERE package_id = $packageId");
        $inclusions = [];
        while ($row = $result->fetch_assoc()) {
            $inclusions[] = $row['item'];
        }
        return $inclusions;
    }
    
    public function getImages($packageId) {
        $packageId = (int)$packageId;
        $result = $this->db->query("SELECT * FROM package_images_tbl WHERE package_id = $packageId");
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row['image_path'];
        }
        return $images;
    }
    
    public function findById($id) {
        $id = (int)$id;
        $result = $this->db->query("SELECT p.*, o.events as occasion_name 
                                    FROM packages_tbl p 
                                    LEFT JOIN occasions_tbl o ON p.occasion_id = o.occasion_id 
                                    WHERE p.package_id = $id");
        $package = $result->fetch_assoc();
        if ($package) {
            $package['inclusions'] = $this->getInclusions($id);
            $package['images'] = $this->getImages($id);
        }
        return $package;
    }
    
    public function create($data) {
        $name = $this->db->real_escape_string($data['name']);
        $description = $this->db->real_escape_string($data['description'] ?? '');
        $occasionId = (int)$data['occasion_id'];
        $price = (float)$data['price'];
        
        $sql = "INSERT INTO packages_tbl (name, description, occasion_id, price) 
                VALUES ('$name', '$description', $occasionId, $price)";
        
        if ($this->db->query($sql)) {
            $packageId = $this->db->insert_id;
            
            // Save inclusions
            if (isset($data['inclusions']) && is_array($data['inclusions'])) {
                foreach ($data['inclusions'] as $item) {
                    if (!empty($item)) {
                        $item = $this->db->real_escape_string($item);
                        $this->db->query("INSERT INTO package_inclusions_tbl (package_id, item) VALUES ($packageId, '$item')");
                    }
                }
            }
            
            // Save images
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imagePath) {
                    if (!empty($imagePath)) {
                        $imagePath = $this->db->real_escape_string($imagePath);
                        $this->db->query("INSERT INTO package_images_tbl (package_id, image_path) VALUES ($packageId, '$imagePath')");
                    }
                }
            }
            
            return $packageId;
        }
        return false;
    }
    
    public function update($id, $data) {
        $id = (int)$id;
        $sets = [];
        
        if (isset($data['name'])) {
            $sets[] = "name = '" . $this->db->real_escape_string($data['name']) . "'";
        }
        if (isset($data['description'])) {
            $sets[] = "description = '" . $this->db->real_escape_string($data['description']) . "'";
        }
        if (isset($data['price'])) {
            $sets[] = "price = " . (float)$data['price'];
        }
        if (isset($data['occasion_id'])) {
            $sets[] = "occasion_id = " . (int)$data['occasion_id'];
        }
        
        if (empty($sets)) return false;
        
        $sql = "UPDATE packages_tbl SET " . implode(', ', $sets) . " WHERE package_id = $id";
        $result = $this->db->query($sql);
        
        // Update inclusions if provided
        if (isset($data['inclusions']) && is_array($data['inclusions'])) {
            // Delete old inclusions
            $this->db->query("DELETE FROM package_inclusions_tbl WHERE package_id = $id");
            // Add new inclusions
            foreach ($data['inclusions'] as $item) {
                if (!empty($item)) {
                    $item = $this->db->real_escape_string($item);
                    $this->db->query("INSERT INTO package_inclusions_tbl (package_id, item) VALUES ($id, '$item')");
                }
            }
        }
        
        // Update images if provided
        if (isset($data['images']) && is_array($data['images'])) {
            // Delete old images
            $this->db->query("DELETE FROM package_images_tbl WHERE package_id = $id");
            // Add new images
            foreach ($data['images'] as $imagePath) {
                if (!empty($imagePath)) {
                    $imagePath = $this->db->real_escape_string($imagePath);
                    $this->db->query("INSERT INTO package_images_tbl (package_id, image_path) VALUES ($id, '$imagePath')");
                }
            }
        }
        
        return $result;
    }
    
    public function delete($id) {
        $id = (int)$id;
        // Delete associated inclusions and images first
        $this->db->query("DELETE FROM package_inclusions_tbl WHERE package_id = $id");
        $this->db->query("DELETE FROM package_images_tbl WHERE package_id = $id");
        return $this->db->query("DELETE FROM packages_tbl WHERE package_id = $id");
    }
    
    public function getOccasionPackages($occasionId) {
        return $this->getAll($occasionId);
    }
}
?>
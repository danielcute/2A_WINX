<?php
/**
 * Package Model
 * Handles event packages and related data
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
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
            // Parse features as array if stored as string
            $row['inclusions'] = !empty($row['features']) ? array_filter(explode(',', $row['features'])) : [];
            // Get image as array
            $row['images'] = !empty($row['image']) ? [$row['image']] : [];
            $packages[] = $row;
        }
        return $packages;
    }
    
    public function getInclusions($packageId) {
        $packageId = (int)$packageId;
        $result = $this->db->query("SELECT features FROM packages_tbl WHERE package_id = $packageId");
        $inclusions = [];
        if ($result && $row = $result->fetch_assoc()) {
            if (!empty($row['features'])) {
                $inclusions = array_filter(explode(',', $row['features']));
            }
        }
        return $inclusions;
    }
    
    public function getImages($packageId) {
        $packageId = (int)$packageId;
        $result = $this->db->query("SELECT image FROM packages_tbl WHERE package_id = $packageId");
        $images = [];
        if ($result && $row = $result->fetch_assoc()) {
            if (!empty($row['image'])) {
                $images[] = $row['image'];
            }
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
            // Parse features as array
            $package['inclusions'] = !empty($package['features']) ? array_filter(explode(',', $package['features'])) : [];
            // Get image as array
            $package['images'] = !empty($package['image']) ? [$package['image']] : [];
        }
        return $package;
    }
    
    public function create($data) {
        $name = $this->db->real_escape_string($data['name']);
        $description = $this->db->real_escape_string($data['description'] ?? '');
        $occasionId = (int)$data['occasion_id'];
        $price = (float)$data['price'];
        
        // Handle features/inclusions as comma-separated string
        $features = '';
        if (isset($data['inclusions']) && is_array($data['inclusions'])) {
            $features = implode(',', array_map([$this->db, 'real_escape_string'], $data['inclusions']));
        } elseif (isset($data['features'])) {
            $features = $this->db->real_escape_string($data['features']);
        }
        
        // Handle image
        $image = '';
        if (isset($data['image'])) {
            $image = $this->db->real_escape_string($data['image']);
        } elseif (isset($data['images']) && is_array($data['images']) && !empty($data['images'][0])) {
            $image = $this->db->real_escape_string($data['images'][0]);
        }
        
        $sql = "INSERT INTO packages_tbl (name, description, occasion_id, price, features, image) 
                VALUES ('$name', '$description', $occasionId, $price, '$features', '$image')";
        
        if ($this->db->query($sql)) {
            return $this->db->insert_id;
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
        
        // Handle features/inclusions as comma-separated string
        if (isset($data['inclusions']) && is_array($data['inclusions'])) {
            $features = implode(',', array_map([$this->db, 'real_escape_string'], $data['inclusions']));
            $sets[] = "features = '$features'";
        } elseif (isset($data['features'])) {
            $sets[] = "features = '" . $this->db->real_escape_string($data['features']) . "'";
        }
        
        // Handle image
        if (isset($data['image'])) {
            $sets[] = "image = '" . $this->db->real_escape_string($data['image']) . "'";
        } elseif (isset($data['images']) && is_array($data['images']) && !empty($data['images'][0])) {
            $sets[] = "image = '" . $this->db->real_escape_string($data['images'][0]) . "'";
        }
        
        if (empty($sets)) return false;
        
        $sql = "UPDATE packages_tbl SET " . implode(', ', $sets) . " WHERE package_id = $id";
        return $this->db->query($sql);
    }
    
    public function delete($id) {
        $id = (int)$id;
        return $this->db->query("DELETE FROM packages_tbl WHERE package_id = $id");
    }
    
    public function getOccasionPackages($occasionId) {
        return $this->getAll($occasionId);
    }
}
?>
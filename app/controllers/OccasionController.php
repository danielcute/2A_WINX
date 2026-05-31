<?php
if (!defined('ROOT_PATH')) {
    // Check if app folder exists at current level (production) or parent level (local)
    $appDir = dirname(dirname(__DIR__));
    if (is_dir($appDir . '/app')) {
        define('ROOT_PATH', $appDir);
    } else {
        // Go up 3 levels from controllers folder
        define('ROOT_PATH', $appDir);
    }
}
require_once ROOT_PATH . '/app/models/Occasion.php';
require_once ROOT_PATH . '/config/database.php';

class OccasionController {
    private $occasionModel;
    
    public function __construct() {
        $this->occasionModel = new Occasion();
    }
    
    public function index() {
        $page = 'occasions';
        $occasions = $this->occasionModel->getAll();
        include VIEW_PATH . '/user/occasions.php';
    }
    
    /**
     * Get all occasions with package counts
     */
    public function getAll() {
        $db = Database::getInstance()->getConnection();
        $occasions = [];
        
        $query = "SELECT o.*, COUNT(p.package_id) as packages_count 
                  FROM occasions_tbl o 
                  LEFT JOIN packages_tbl p ON o.occasion_id = p.occasion_id 
                  GROUP BY o.occasion_id 
                  ORDER BY o.events ASC";
        
        $result = $db->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $occasions[] = $row;
            }
        }
        
        return $occasions;
    }
    
    /**
     * Get occasion by ID
     */
    public function getById($id) {
        $db = Database::getInstance()->getConnection();
        $id = intval($id);
        
        $query = "SELECT * FROM occasions_tbl WHERE occasion_id = $id";
        $result = $db->query($query);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create new occasion
     */
    public function create($events, $descriptions = '', $image_data = null, $image_name = null) {
        $db = Database::getInstance()->getConnection();
        
        // Escape strings
        $events = $db->real_escape_string($events);
        $descriptions = $db->real_escape_string($descriptions);
        
        $stmt = $db->prepare("INSERT INTO occasions_tbl (events, descriptions, image, image_name) VALUES (?, ?, ?, ?)");
        
        if (!$stmt) {
            return ['success' => false, 'error' => $db->error];
        }
        
        $stmt->bind_param("ssbs", $events, $descriptions, $image_data, $image_name);
        
        if ($stmt->execute()) {
            $id = $db->insert_id;
            $stmt->close();
            return ['success' => true, 'id' => $id];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'error' => $error];
        }
    }
    
    /**
     * Update occasion
     */
    public function update($id, $events, $descriptions = '', $image_data = null, $image_name = null) {
        $db = Database::getInstance()->getConnection();
        
        // Escape strings
        $events = $db->real_escape_string($events);
        $descriptions = $db->real_escape_string($descriptions);
        $id = intval($id);
        
        if ($image_data !== null) {
            // Update with image
            $stmt = $db->prepare("UPDATE occasions_tbl SET events = ?, descriptions = ?, image = ?, image_name = ? WHERE occasion_id = ?");
            if (!$stmt) {
                return ['success' => false, 'error' => $db->error];
            }
            $stmt->bind_param("ssbsi", $events, $descriptions, $image_data, $image_name, $id);
        } else {
            // Update without image
            $stmt = $db->prepare("UPDATE occasions_tbl SET events = ?, descriptions = ? WHERE occasion_id = ?");
            if (!$stmt) {
                return ['success' => false, 'error' => $db->error];
            }
            $stmt->bind_param("ssi", $events, $descriptions, $id);
        }
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'error' => $error];
        }
    }
    
    /**
     * Delete occasion
     */
    public function delete($id) {
        $db = Database::getInstance()->getConnection();
        $id = intval($id);
        
        // Check if occasion has packages
        $check = $db->query("SELECT COUNT(*) as count FROM packages_tbl WHERE occasion_id = $id");
        $count = $check->fetch_assoc()['count'];
        
        if ($count > 0) {
            return ['success' => false, 'error' => "Cannot delete occasion with $count associated packages"];
        }
        
        $query = "DELETE FROM occasions_tbl WHERE occasion_id = $id";
        
        if ($db->query($query)) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => $db->error];
    }
}
?>
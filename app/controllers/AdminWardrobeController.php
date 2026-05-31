<?php
/**
 * Admin Wardrobe Controller
 * Handles admin-side wardrobe management
 */
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
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Wardrobe.php';

class AdminWardrobeController {
    private $wardrobe;
    private $db;
    
    public function __construct() {
        $this->wardrobe = new Wardrobe();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Display all wardrobes
     */
    public function listAll() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $page = 'admin-wardrobe';
        $wardrobesByCategory = $this->wardrobe->getAllByCategory();
        
        include VIEW_PATH . '/admin/admin-wardrobe.php';
    }
    
    /**
     * Display add wardrobe form
     */
    public function addForm() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $page = 'admin-wardrobe-add';
        $categories = $this->wardrobe->getCategories();
        
        // Allow custom categories
        $customCategories = [
            'Wedding',
            'Birthday',
            'Corporate Gala',
            'Debut',
            'Anniversary',
            'Other Events'
        ];
        
        // Merge with existing categories and remove duplicates
        $allCategories = array_unique(array_merge($customCategories, $categories));
        sort($allCategories);
        
        include VIEW_PATH . '/admin/admin-wardrobe-add.php';
    }
    
    /**
     * Handle add wardrobe submission
     */
    public function add() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }
            
            $data = [
                'category' => trim($_POST['category'] ?? ''),
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'rental_price' => isset($_POST['rental_price']) ? (float)$_POST['rental_price'] : 0,
                'availability_count' => isset($_POST['availability_count']) ? (int)$_POST['availability_count'] : 1,
                'rental_duration_days' => isset($_POST['rental_duration_days']) ? (int)$_POST['rental_duration_days'] : 1,
                'sizes_available' => trim($_POST['sizes_available'] ?? 'Standard'),
                'image' => null,
                'image_type' => null
            ];
            
            // Validation
            if (empty($data['category']) || empty($data['name'])) {
                echo json_encode(['success' => false, 'message' => 'Category and name are required']);
                exit;
            }
            
            if ($data['rental_price'] < 0) {
                echo json_encode(['success' => false, 'message' => 'Rental price must be a positive number']);
                exit;
            }
            
            if ($data['availability_count'] < 1) {
                echo json_encode(['success' => false, 'message' => 'Availability count must be at least 1']);
                exit;
            }
            
            // Handle image upload
            if (isset($_FILES['wardrobe_image']) && $_FILES['wardrobe_image']['error'] === UPLOAD_ERR_OK) {
                $image = $_FILES['wardrobe_image'];
                
                // Validate image
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!in_array($image['type'], $allowedTypes)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid image format. Allowed: JPG, PNG, GIF, WebP']);
                    exit;
                }
                
                if ($image['size'] > $maxSize) {
                    echo json_encode(['success' => false, 'message' => 'Image size must be less than 5MB']);
                    exit;
                }
                
                // Read image data
                $imageData = file_get_contents($image['tmp_name']);
                if ($imageData === false) {
                    echo json_encode(['success' => false, 'message' => 'Failed to read image file']);
                    exit;
                }
                
                $data['image'] = $imageData;
                $data['image_type'] = $image['type'];
            }
            
            $wardrobeId = $this->wardrobe->create($data);
            
            if ($wardrobeId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Wardrobe added successfully',
                    'wardrobe_id' => $wardrobeId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add wardrobe']);
            }
        } catch (Exception $e) {
            error_log('AdminWardrobeController add error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
        exit;
    }
    
    /**
     * Display edit wardrobe form
     */
    public function editForm() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $wardrobeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($wardrobeId === 0) {
            header('Location: ' . APP_URL . '/admin-wardrobe');
            exit;
        }
        
        $page = 'admin-wardrobe-edit';
        $wardrobe = $this->wardrobe->getById($wardrobeId);
        
        if (!$wardrobe) {
            header('Location: ' . APP_URL . '/admin-wardrobe');
            exit;
        }
        
        $categories = $this->wardrobe->getCategories();
        $customCategories = [
            'Wedding',
            'Birthday',
            'Corporate Gala',
            'Debut',
            'Anniversary',
            'Other Events'
        ];
        
        $allCategories = array_unique(array_merge($customCategories, $categories));
        sort($allCategories);
        
        include VIEW_PATH . '/admin/admin-wardrobe-edit.php';
    }
    
    /**
     * Handle edit wardrobe submission
     */
    public function update() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }
            
            $wardrobeId = isset($_POST['wardrobe_id']) ? (int)$_POST['wardrobe_id'] : 0;
            
            if ($wardrobeId === 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid wardrobe ID']);
                exit;
            }
            
            $data = [
                'category' => trim($_POST['category'] ?? ''),
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'rental_price' => isset($_POST['rental_price']) ? (float)$_POST['rental_price'] : 0,
                'availability_count' => isset($_POST['availability_count']) ? (int)$_POST['availability_count'] : 1,
                'rental_duration_days' => isset($_POST['rental_duration_days']) ? (int)$_POST['rental_duration_days'] : 1,
                'sizes_available' => trim($_POST['sizes_available'] ?? 'Standard')
            ];
            
            // Validation
            if (empty($data['category']) || empty($data['name'])) {
                echo json_encode(['success' => false, 'message' => 'Category and name are required']);
                exit;
            }
            
            if ($data['rental_price'] < 0) {
                echo json_encode(['success' => false, 'message' => 'Rental price must be a positive number']);
                exit;
            }
            
            if ($data['availability_count'] < 1) {
                echo json_encode(['success' => false, 'message' => 'Availability count must be at least 1']);
                exit;
            }
            
            // Handle image upload
            if (isset($_FILES['wardrobe_image']) && $_FILES['wardrobe_image']['error'] === UPLOAD_ERR_OK) {
                $image = $_FILES['wardrobe_image'];
                
                // Validate image
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!in_array($image['type'], $allowedTypes)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid image format. Allowed: JPG, PNG, GIF, WebP']);
                    exit;
                }
                
                if ($image['size'] > $maxSize) {
                    echo json_encode(['success' => false, 'message' => 'Image size must be less than 5MB']);
                    exit;
                }
                
                // Read image data
                $imageData = file_get_contents($image['tmp_name']);
                if ($imageData === false) {
                    echo json_encode(['success' => false, 'message' => 'Failed to read image file']);
                    exit;
                }
                
                $data['image'] = $imageData;
                $data['image_type'] = $image['type'];
            }
            
            $success = $this->wardrobe->update($wardrobeId, $data);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Wardrobe updated successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update wardrobe']);
            }
        } catch (Exception $e) {
            error_log('AdminWardrobeController update error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
        exit;
    }
    
    /**
     * Handle delete wardrobe
     */
    public function delete() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }
            
            $wardrobeId = isset($_POST['wardrobe_id']) ? (int)$_POST['wardrobe_id'] : 0;
            
            if ($wardrobeId === 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid wardrobe ID']);
                exit;
            }
            
            $success = $this->wardrobe->delete($wardrobeId);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Wardrobe deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete wardrobe']);
            }
        } catch (Exception $e) {
            error_log('AdminWardrobeController delete error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
        exit;
    }
    
    /**
     * Get all wardrobes for API calls
     */
    public function getAll() {
        return $this->wardrobe->getAll();
    }
}
?>

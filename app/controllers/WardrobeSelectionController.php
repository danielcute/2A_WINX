<?php
/**
 * Wardrobe Selection Controller
 * Handles user wardrobe selection for bookings
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
require_once ROOT_PATH . '/app/models/Plan.php';

class WardrobeSelectionController {
    private $wardrobe;
    private $plan;
    private $db;
    
    public function __construct() {
        $this->wardrobe = new Wardrobe();
        $this->plan = new Plan();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Display wardrobe selection page
     */
    public function selectWardrobes() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check user authentication
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/index.php?route=signin");
            exit;
        }
        
        $plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;
        
        if ($plan_id === 0) {
            header("Location: " . BASE_URL . "/index.php?route=homepage");
            exit;
        }
        
        // Get plan details
        $plan = $this->plan->findById($plan_id);
        
        if (!$plan || $plan['user_id'] != $_SESSION['user_id']) {
            header("Location: " . BASE_URL . "/index.php?route=homepage");
            exit;
        }
        
        // Get all wardrobe categories
        $categories = $this->wardrobe->getCategories();
        
        // Get existing selections for this plan
        $existing_selections = $this->wardrobe->getSelectionsByPlan($plan_id);
        
        $page = 'wardrobe-selection';
        $page_title = 'Select Wardrobes';
        
        include VIEW_PATH . '/user/wardrobe-selection.php';
    }
    
    /**
     * Get wardrobes by category (AJAX)
     */
    public function getByCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }
        
        $category = $_GET['category'] ?? '';
        $search = $_GET['search'] ?? '';
        
        if ($category) {
            $wardrobes = $this->wardrobe->getByCategory($category);
        } else {
            $wardrobes = $this->wardrobe->getAll();
        }
        
        // Filter by search term if provided
        if ($search) {
            $search_lower = strtolower($search);
            $wardrobes = array_filter($wardrobes, function($w) use ($search_lower) {
                return strpos(strtolower($w['name']), $search_lower) !== false ||
                       strpos(strtolower($w['description']), $search_lower) !== false;
            });
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'wardrobes' => array_values($wardrobes)
        ]);
    }
}
?>

<?php
/**
 * Customization Controller
 * Handles customization options for both users and admins
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Customization.php';

class CustomizeController {
    private $customization;
    
    public function __construct() {
        $this->customization = new Customization();
    }
    
    /**
     * User view - show customization options
     */
    public function index() {
        $page = 'customize';
        $occasion = $_GET['occasion'] ?? 'wedding';
        $options = $this->customization->getAllOptions();
        include VIEW_PATH . '/user/customize.php';
    }
    
    /**
     * Admin: List all customization options
     */
    public function listAll() {
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || !$this->isAdmin($_SESSION['user_id'])) {
            header("Location: /SINTA/public/index.php?route=home");
            exit;
        }
        
        $options = $this->customization->getAllOptions();
        $categories = $this->getCategories();
        include VIEW_PATH . '/admin/admin-customize.php';
    }
    
    /**
     * Admin: Show add customization form
     */
    public function addForm() {
        if (!isset($_SESSION['user_id']) || !$this->isAdmin($_SESSION['user_id'])) {
            header("Location: /SINTA/public/index.php?route=home");
            exit;
        }
        
        $categories = $this->getCategories();
        include VIEW_PATH . '/admin/admin-customize-add.php';
    }
    
    /**
     * Admin: Create new customization option
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /SINTA/public/index.php?route=admin-customize");
            exit;
        }
        
        if (!isset($_SESSION['user_id']) || !$this->isAdmin($_SESSION['user_id'])) {
            header("Location: /SINTA/public/index.php?route=home");
            exit;
        }
        
        $data = [
            'category' => $_POST['category'] ?? '',
            'name' => $_POST['name'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if (empty($data['category']) || empty($data['name'])) {
            $_SESSION['error'] = "Category and name are required!";
            header("Location: /SINTA/public/index.php?route=admin-customize-add");
            exit;
        }
        
        $result = $this->customization->create($data);
        
        if ($result) {
            $_SESSION['success'] = "Customization option created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create customization option!";
        }
        
        header("Location: /SINTA/public/index.php?route=admin-customize");
        exit;
    }
    
    /**
     * Admin: Show edit customization form
     */
    public function editForm() {
        if (!isset($_SESSION['user_id']) || !$this->isAdmin($_SESSION['user_id'])) {
            header("Location: /SINTA/public/index.php?route=home");
            exit;
        }
        
        $optionId = $_GET['id'] ?? null;
        
        if (!$optionId) {
            header("Location: /SINTA/public/index.php?route=admin-customize");
            exit;
        }
        
        $option = $this->customization->getOptionById($optionId);
        
        if (!$option) {
            $_SESSION['error'] = "Customization option not found!";
            header("Location: /SINTA/public/index.php?route=admin-customize");
            exit;
        }
        
        $categories = $this->getCategories();
        include VIEW_PATH . '/admin/admin-customize-edit.php';
    }
    
    /**
     * Admin: Update customization option
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /SINTA/public/index.php?route=admin-customize");
            exit;
        }
        
        if (!isset($_SESSION['user_id']) || !$this->isAdmin($_SESSION['user_id'])) {
            header("Location: /SINTA/public/index.php?route=home");
            exit;
        }
        
        $optionId = $_POST['option_id'] ?? null;
        
        if (!$optionId) {
            $_SESSION['error'] = "Invalid option ID!";
            header("Location: /SINTA/public/index.php?route=admin-customize");
            exit;
        }
        
        $data = [
            'category' => $_POST['category'] ?? '',
            'name' => $_POST['name'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if (empty($data['category']) || empty($data['name'])) {
            $_SESSION['error'] = "Category and name are required!";
            header("Location: /SINTA/public/index.php?route=admin-customize-edit&id=" . $optionId);
            exit;
        }
        
        $result = $this->customization->update($optionId, $data);
        
        if ($result) {
            $_SESSION['success'] = "Customization option updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update customization option!";
        }
        
        header("Location: /SINTA/public/index.php?route=admin-customize");
        exit;
    }
    
    /**
     * Admin: Delete customization option
     */
    public function delete() {
        if (!isset($_SESSION['user_id']) || !$this->isAdmin($_SESSION['user_id'])) {
            header("Location: /SINTA/public/index.php?route=home");
            exit;
        }
        
        $optionId = $_GET['id'] ?? null;
        
        if (!$optionId) {
            $_SESSION['error'] = "Invalid option ID!";
            header("Location: /SINTA/public/index.php?route=admin-customize");
            exit;
        }
        
        $result = $this->customization->delete($optionId);
        
        if ($result) {
            $_SESSION['success'] = "Customization option deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete customization option!";
        }
        
        header("Location: /SINTA/public/index.php?route=admin-customize");
        exit;
    }
    
    /**
     * Check if user is admin
     */
    private function isAdmin($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT role FROM users_tbl WHERE user_id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        return $user && $user['role'] === 'admin';
    }
    
    /**
     * Get all categories
     */
    private function getCategories() {
        $db = Database::getInstance()->getConnection();
        $result = $db->query("SELECT DISTINCT category FROM customization_options_tbl ORDER BY category");
        $categories = [];
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    }
}
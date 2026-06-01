<?php
if (!defined('ROOT_PATH')) {
    $appDir = dirname(dirname(__DIR__));
    if (is_dir($appDir . '/app')) {
        define('ROOT_PATH', $appDir);
    } else {
        define('ROOT_PATH', $appDir);
    }
}
require_once ROOT_PATH . '/config/database.php';

class CustomizeController {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function listAll() {
        require_once ROOT_PATH . '/app/models/Customization.php';
        $customization = new Customization();
        $allOptions = $customization->getAllOptions();
        
        if (empty($allOptions)) {
            $allOptions = $customization->getAllOptions();
        }

        $mainCategories = ['Theme', 'Color Combinations', 'Venue', 'Food', 'Sweets', 'Catering', 'Pastries', 'Beverages', 'Add-ons'];
        $options = $allOptions;
        include ROOT_PATH . '/app/views/admin/admin-customize.php';
    }

    public function addForm() {
        require_once ROOT_PATH . '/app/models/Customization.php';
        $customization = new Customization();
        $allOptions = $customization->getAllOptions();
        $mainCategories = ['Theme', 'Color Combinations', 'Venue', 'Food', 'Sweets', 'Catering', 'Pastries', 'Beverages', 'Add-ons'];
        $options = array_filter($allOptions, function($opt) use ($mainCategories) {
            return in_array($opt['category'], $mainCategories);
        });
        $categories = $mainCategories;
        $page_title = 'Add Customization Option';
        include ROOT_PATH . '/app/views/admin/admin-customize-add.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=admin-customize');
            exit;
        }

        $imageData = null;
        $imageType = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $imageType = $_FILES['image']['type'];
        }

        $data = [
            'category' => trim($_POST['category'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => $_POST['price'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'image' => $imageData,
            'image_type' => $imageType,
        ];

        if (empty($data['category']) || empty($data['name'])) {
            $_SESSION['error'] = 'Category and name are required.';
            header('Location: ' . BASE_URL . '/index.php?route=admin-customize-add');
            exit;
        }

        require_once ROOT_PATH . '/app/models/Customization.php';
        $customization = new Customization();
        $result = $customization->create($data);

        if ($result !== false) {
            $_SESSION['success'] = 'Customization option created successfully.';
        } else {
            $_SESSION['error'] = 'Failed to create customization option.';
        }

        header('Location: ' . BASE_URL . '/index.php?route=admin-customize');
        exit;
    }

    public function editForm() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'Invalid customization option ID.';
            header('Location: ' . BASE_URL . '/index.php?route=admin-customize');
            exit;
        }

        require_once ROOT_PATH . '/app/models/Customization.php';
        $customization = new Customization();
        $option = $customization->getOptionById($id);

        if (!$option) {
            $_SESSION['error'] = 'Customization option not found.';
            header('Location: ' . BASE_URL . '/index.php?route=admin-customize');
            exit;
        }

        $allOptions = $customization->getAllOptions();
        $mainCategories = ['Theme', 'Venue Deco', 'Color Combinations', 'Venue', 'Food', 'Catering', 'Pastries', 'Beverages', 'Add-ons'];
        $options = array_filter($allOptions, function($opt) use ($mainCategories) {
            return in_array($opt['category'], $mainCategories);
        });
        $categories = $mainCategories;

        include ROOT_PATH . '/app/views/admin/admin-customize-edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=admin-customize');
            exit;
        }

        $optionId = isset($_POST['option_id']) ? (int)$_POST['option_id'] : 0;
        if ($optionId <= 0) {
            $this->returnJSON(false, 'Invalid customization option ID.', 400);
        }

        $imageData = null;
        $imageType = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $imageType = $_FILES['image']['type'];
        }

        $data = [
            'category' => trim($_POST['category'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => $_POST['price'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($imageData !== null) {
            $data['image'] = $imageData;
            $data['image_type'] = $imageType;
        }

        if (!empty($_POST['colors_json'])) {
            $colors_json = trim($_POST['colors_json']);
            if (json_decode($colors_json, true) !== null) {
                $data['colors_json'] = $colors_json;
            }
        }

        if (empty($data['category']) || empty($data['name'])) {
            $this->returnJSON(false, 'Category and name are required.', 400);
        }

        require_once ROOT_PATH . '/app/models/Customization.php';
        $customization = new Customization();
        $result = $customization->update($optionId, $data);

        if ($result) {
            if ($data['category'] === 'Color Combinations' && !empty($_POST['colors']) && isset($_FILES['color_images'])) {
                $colors = $_POST['colors'];
                $files = $_FILES['color_images'];
                
                foreach ($colors as $index => $hex) {
                    if (isset($files['error'][$index]) && $files['error'][$index] === UPLOAD_ERR_OK) {
                        $imgData = file_get_contents($files['tmp_name'][$index]);
                        $imgType = $files['type'][$index];
                        
                        $customization->saveColorPaletteImage(
                            $optionId,
                            $hex,
                            '',
                            $imgData,
                            $imgType
                        );
                    }
                }
            }
            
            $this->returnJSON(true, 'Customization option updated successfully.');
        } else {
            $this->returnJSON(false, 'Failed to update customization option.', 500);
        }
    }

    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'Invalid customization option ID.';
            header('Location: ' . BASE_URL . '/index.php?route=admin-customize');
            exit;
        }

        require_once ROOT_PATH . '/app/models/Customization.php';
        $customization = new Customization();
        $result = $customization->delete($id);

        if ($result) {
            $_SESSION['success'] = 'Customization option deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete customization option.';
        }

        header('Location: ' . BASE_URL . '/index.php?route=admin-customize');
        exit;
    }

    /**
     * Get all customization options by category (MySQLi version)
     */
    public function getCustomizationOptions() {
        $sql = "SELECT option_id, category, name, price, is_active 
                FROM customization_options_tbl 
                WHERE is_active = 1 
                ORDER BY category, name";
        
        $result = $this->conn->query($sql);
        if (!$result) {
            return ['success' => false, 'message' => 'Database error: ' . $this->conn->error];
        }
        
        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[$row['category']][] = $row;
        }
        
        return ['success' => true, 'data' => $options];
    }
    
    /**
     * Get package details with its customization options (MySQLi version)
     */
    public function getPackageWithOptions($packageId) {
        $packageId = (int)$packageId;
        
        $stmt = $this->conn->prepare(
            "SELECT p.*, o.events as occasion_name 
             FROM packages_tbl p
             LEFT JOIN occasions_tbl o ON p.occasion_id = o.occasion_id
             WHERE p.package_id = ? AND p.status = 'active'"
        );
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error: ' . $this->conn->error];
        }
        $stmt->bind_param('i', $packageId);
        $stmt->execute();
        $package = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$package) {
            return ['success' => false, 'message' => 'Package not found'];
        }
        
        $stmt2 = $this->conn->prepare(
            "SELECT co.* 
             FROM customization_options_tbl co
             WHERE co.is_active = 1
             ORDER BY co.category, co.name"
        );
        if (!$stmt2) {
            return ['success' => false, 'message' => 'Database error: ' . $this->conn->error];
        }
        $stmt2->execute();
        $result = $stmt2->get_result();
        
        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[$row['category']][] = $row;
        }
        $stmt2->close();
        
        $package['customization_options'] = $options;
        
        return ['success' => true, 'data' => $package];
    }
    
    /**
     * Save user customization (MySQLi version)
     */
    public function saveCustomization($userId, $data) {
        if (empty($data['package_id']) || empty($data['occasion_id'])) {
            return ['success' => false, 'message' => 'Package and occasion are required'];
        }
        
        $userId = (int)$userId;
        $packageId = (int)$data['package_id'];
        $occasionId = (int)$data['occasion_id'];
        $eventDate = $this->conn->real_escape_string($data['event_date'] ?? '');
        $status = $this->conn->real_escape_string($data['status'] ?? 'draft');
        
        // Calculate total price
        $totalPrice = $this->calculateTotalPrice($packageId, $data['selections'] ?? []);
        
        if (isset($data['customization_id']) && $data['customization_id'] > 0) {
            $customizationId = (int)$data['customization_id'];
            $stmt = $this->conn->prepare(
                "UPDATE user_customizations 
                 SET package_id = ?, occasion_id = ?, event_date = ?, total_price = ?, status = ?, updated_at = NOW()
                 WHERE customization_id = ? AND user_id = ?"
            );
            if (!$stmt) return ['success' => false, 'message' => 'DB error: ' . $this->conn->error];
            $stmt->bind_param('iisdsii', $packageId, $occasionId, $eventDate, $totalPrice, $status, $customizationId, $userId);
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO user_customizations (user_id, package_id, occasion_id, event_date, total_price, status) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            if (!$stmt) return ['success' => false, 'message' => 'DB error: ' . $this->conn->error];
            $stmt->bind_param('iiidss', $userId, $packageId, $occasionId, $totalPrice, $eventDate, $status);
        }
        
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'message' => 'DB error: ' . $error];
        }
        
        if (!isset($data['customization_id']) || $data['customization_id'] <= 0) {
            $customizationId = (int)$this->conn->insert_id;
        }
        $stmt->close();
        
        // Delete existing selections if updating
        if (isset($data['customization_id']) && $data['customization_id'] > 0) {
            $delStmt = $this->conn->prepare("DELETE FROM customization_selections WHERE customization_id = ?");
            if ($delStmt) {
                $delStmt->bind_param('i', $customizationId);
                $delStmt->execute();
                $delStmt->close();
            }
        }
        
        // Insert selections
        if (!empty($data['selections'])) {
            $insStmt = $this->conn->prepare(
                "INSERT INTO customization_selections (customization_id, category, option_id, quantity, unit_price) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            if ($insStmt) {
                foreach ($data['selections'] as $selection) {
                    $cat = $selection['category'];
                    $optId = (int)$selection['option_id'];
                    $qty = (int)($selection['quantity'] ?? 1);
                    $unitPrice = (float)($selection['price'] ?? 0);
                    $insStmt->bind_param('isiid', $customizationId, $cat, $optId, $qty, $unitPrice);
                    $insStmt->execute();
                }
                $insStmt->close();
            }
        }
        
        return [
            'success' => true, 
            'message' => 'Customization saved successfully',
            'customization_id' => $customizationId,
            'total_price' => $totalPrice
        ];
    }
    
    /**
     * Calculate total price for a customization (MySQLi version)
     */
    private function calculateTotalPrice($packageId, $selections) {
        $packageId = (int)$packageId;
        $stmt = $this->conn->prepare("SELECT price FROM packages_tbl WHERE package_id = ?");
        if (!$stmt) return 0;
        $stmt->bind_param('i', $packageId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        $total = $row ? (float)$row['price'] : 0;
        
        foreach ($selections as $selection) {
            $total += (float)($selection['price'] ?? 0) * (int)($selection['quantity'] ?? 1);
        }
        
        return $total;
    }
    
    /**
     * Get user's saved customizations (MySQLi version)
     */
    public function getUserCustomizations($userId) {
        $userId = (int)$userId;
        $stmt = $this->conn->prepare(
            "SELECT uc.*, p.name as package_name, o.events as occasion_name
             FROM user_customizations uc
             LEFT JOIN packages_tbl p ON uc.package_id = p.package_id
             LEFT JOIN occasions_tbl o ON uc.occasion_id = o.occasion_id
             WHERE uc.user_id = ?
             ORDER BY uc.created_at DESC"
        );
        if (!$stmt) return ['success' => false, 'message' => 'DB error: ' . $this->conn->error];
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $customizations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        foreach ($customizations as &$customization) {
            $selStmt = $this->conn->prepare("SELECT * FROM customization_selections WHERE customization_id = ?");
            if ($selStmt) {
                $selStmt->bind_param('i', $customization['customization_id']);
                $selStmt->execute();
                $customization['selections'] = $selStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $selStmt->close();
            }
        }
        
        return ['success' => true, 'data' => $customizations];
    }
    
    /**
     * Get single customization by ID (MySQLi version)
     */
    public function getCustomization($customizationId, $userId) {
        $customizationId = (int)$customizationId;
        $userId = (int)$userId;
        
        $stmt = $this->conn->prepare(
            "SELECT uc.*, p.name as package_name, p.description as package_description,
                    p.price as base_price, o.events as occasion_name
             FROM user_customizations uc
             LEFT JOIN packages_tbl p ON uc.package_id = p.package_id
             LEFT JOIN occasions_tbl o ON uc.occasion_id = o.occasion_id
             WHERE uc.customization_id = ? AND uc.user_id = ?"
        );
        if (!$stmt) return ['success' => false, 'message' => 'DB error: ' . $this->conn->error];
        $stmt->bind_param('ii', $customizationId, $userId);
        $stmt->execute();
        $customization = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$customization) {
            return ['success' => false, 'message' => 'Customization not found'];
        }
        
        $selStmt = $this->conn->prepare("SELECT * FROM customization_selections WHERE customization_id = ?");
        if ($selStmt) {
            $selStmt->bind_param('i', $customizationId);
            $selStmt->execute();
            $customization['selections'] = $selStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $selStmt->close();
        }
        
        return ['success' => true, 'data' => $customization];
    }
    
    /**
     * Delete customization (MySQLi version)
     */
    public function deleteCustomization($customizationId, $userId) {
        $customizationId = (int)$customizationId;
        $userId = (int)$userId;
        
        $stmt = $this->conn->prepare(
            "DELETE FROM user_customizations WHERE customization_id = ? AND user_id = ?"
        );
        if (!$stmt) return ['success' => false, 'message' => 'DB error: ' . $this->conn->error];
        $stmt->bind_param('ii', $customizationId, $userId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($affected > 0) {
            return ['success' => true, 'message' => 'Customization deleted successfully'];
        }
        return ['success' => false, 'message' => 'Customization not found'];
    }
    
    /**
     * Helper method to return JSON responses
     */
    private function returnJSON($success, $message = '', $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
        exit;
    }
    
    /**
     * Add new customization option (Admin only, MySQLi version)
     */
    public function addCustomizationOption($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO customization_options_tbl (category, name, price, is_active) VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) return ['success' => false, 'message' => 'DB error: ' . $this->conn->error];
        
        $category = $data['category'];
        $name = $data['name'];
        $price = (float)$data['price'];
        $isActive = (int)$data['is_active'];
        $stmt->bind_param('ssdi', $category, $name, $price, $isActive);
        
        if ($stmt->execute()) {
            $id = (int)$this->conn->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Option added successfully', 'option_id' => $id];
        }
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'DB error: ' . $error];
    }
    
    /**
     * Update customization option (Admin only, MySQLi version)
     */
    public function updateCustomizationOption($optionId, $data) {
        $optionId = (int)$optionId;
        $stmt = $this->conn->prepare(
            "UPDATE customization_options_tbl 
             SET category = ?, name = ?, price = ?, is_active = ?
             WHERE option_id = ?"
        );
        if (!$stmt) return ['success' => false, 'message' => 'DB error: ' . $this->conn->error];
        
        $category = $data['category'];
        $name = $data['name'];
        $price = (float)$data['price'];
        $isActive = (int)$data['is_active'];
        $stmt->bind_param('ssdii', $category, $name, $price, $isActive, $optionId);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Option updated successfully'];
        }
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'DB error: ' . $error];
    }
}

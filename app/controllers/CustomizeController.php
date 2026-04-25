<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
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
        // Filter to only main categories
        $mainCategories = ['Theme', 'Venue', 'Catering', 'Extras'];
        $options = array_filter($allOptions, function($opt) use ($mainCategories) {
            return in_array($opt['category'], $mainCategories);
        });
        $options = array_values($options);
        include ROOT_PATH . '/app/views/admin/admin-customize.php';
    }

    public function addForm() {
        require_once ROOT_PATH . '/app/models/Customization.php';
        $customization = new Customization();
        $allOptions = $customization->getAllOptions();
        // Filter to only main categories
        $mainCategories = ['Theme', 'Venue', 'Catering', 'Extras'];
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

        // Handle image upload
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
        // Filter to only main categories
        $mainCategories = ['Theme', 'Venue', 'Catering', 'Extras'];
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
            $_SESSION['error'] = 'Invalid customization option ID.';
            header('Location: ' . BASE_URL . '/index.php?route=admin-customize');
            exit;
        }

        $imageData = null;
        $imageType = null;

        // Handle image upload (only if a new image is provided)
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

        // Only add image data if a new image was provided
        if ($imageData !== null) {
            $data['image'] = $imageData;
            $data['image_type'] = $imageType;
        }

        if (empty($data['category']) || empty($data['name'])) {
            $_SESSION['error'] = 'Category and name are required.';
            header('Location: ' . BASE_URL . '/index.php?route=admin-customize-edit&id=' . $optionId);
            exit;
        }

        require_once ROOT_PATH . '/app/models/Customization.php';
        $customization = new Customization();
        $result = $customization->update($optionId, $data);

        if ($result) {
            $_SESSION['success'] = 'Customization option updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update customization option.';
        }

        header('Location: ' . BASE_URL . '/index.php?route=admin-customize');
        exit;
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
     * Get all customization options by category
     */
    public function getCustomizationOptions() {
        try {
            $query = "SELECT option_id, category, name, price, is_active 
                      FROM customization_options_tbl 
                      WHERE is_active = 1 
                      ORDER BY category, name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $options = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options[$row['category']][] = $row;
            }
            
            return ['success' => true, 'data' => $options];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get package details with its customization options
     */
    public function getPackageWithOptions($packageId) {
        try {
            // Get package details
            $query = "SELECT p.*, o.events as occasion_name 
                      FROM packages_tbl p
                      LEFT JOIN occasions_tbl o ON p.occasion_id = o.occasion_id
                      WHERE p.package_id = :package_id AND p.status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':package_id', $packageId);
            $stmt->execute();
            $package = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$package) {
                return ['success' => false, 'message' => 'Package not found'];
            }
            
            // Get package-specific customization options
            $query = "SELECT co.*, pco.is_required 
                      FROM customization_options_tbl co
                      INNER JOIN package_customization_options pco ON co.option_id = pco.option_id
                      WHERE pco.package_id = :package_id AND co.is_active = 1
                      ORDER BY co.category, co.name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':package_id', $packageId);
            $stmt->execute();
            
            $options = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options[$row['category']][] = $row;
            }
            
            $package['customization_options'] = $options;
            
            return ['success' => true, 'data' => $package];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Save user customization
     */
    public function saveCustomization($userId, $data) {
        try {
            $this->conn->beginTransaction();
            
            // Validate required fields
            if (empty($data['package_id']) || empty($data['occasion_id'])) {
                return ['success' => false, 'message' => 'Package and occasion are required'];
            }
            
            // Calculate total price
            $totalPrice = $this->calculateTotalPrice($data['package_id'], $data['selections'] ?? []);
            
            // Insert or update customization record
            if (isset($data['customization_id']) && $data['customization_id'] > 0) {
                // Update existing
                $query = "UPDATE user_customizations 
                          SET package_id = :package_id, 
                              occasion_id = :occasion_id, 
                              event_date = :event_date,
                              total_price = :total_price,
                              status = :status,
                              updated_at = CURRENT_TIMESTAMP
                          WHERE customization_id = :customization_id AND user_id = :user_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':customization_id', $data['customization_id']);
            } else {
                // Insert new
                $query = "INSERT INTO user_customizations 
                          (user_id, package_id, occasion_id, event_date, total_price, status) 
                          VALUES (:user_id, :package_id, :occasion_id, :event_date, :total_price, :status)";
                
                $stmt = $this->conn->prepare($query);
            }
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':package_id', $data['package_id']);
            $stmt->bindParam(':occasion_id', $data['occasion_id']);
            $stmt->bindParam(':event_date', $data['event_date']);
            $stmt->bindParam(':total_price', $totalPrice);
            $status = isset($data['status']) ? $data['status'] : 'draft';
            $stmt->bindParam(':status', $status);
            
            $stmt->execute();
            
            // Get the customization ID
            if (isset($data['customization_id']) && $data['customization_id'] > 0) {
                $customizationId = $data['customization_id'];
            } else {
                $customizationId = $this->conn->lastInsertId();
            }
            
            // Delete existing selections if updating
            if (isset($data['customization_id']) && $data['customization_id'] > 0) {
                $deleteQuery = "DELETE FROM customization_selections WHERE customization_id = :customization_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->bindParam(':customization_id', $customizationId);
                $deleteStmt->execute();
            }
            
            // Insert selections
            if (!empty($data['selections'])) {
                $insertQuery = "INSERT INTO customization_selections 
                                (customization_id, category, option_id, quantity, unit_price) 
                                VALUES (:customization_id, :category, :option_id, :quantity, :unit_price)";
                $insertStmt = $this->conn->prepare($insertQuery);
                
                foreach ($data['selections'] as $selection) {
                    $insertStmt->bindParam(':customization_id', $customizationId);
                    $insertStmt->bindParam(':category', $selection['category']);
                    $insertStmt->bindParam(':option_id', $selection['option_id']);
                    $insertStmt->bindParam(':quantity', $selection['quantity']);
                    $insertStmt->bindParam(':unit_price', $selection['price']);
                    $insertStmt->execute();
                }
            }
            
            $this->conn->commit();
            
            return [
                'success' => true, 
                'message' => 'Customization saved successfully',
                'customization_id' => $customizationId,
                'total_price' => $totalPrice
            ];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Calculate total price for a customization
     */
    private function calculateTotalPrice($packageId, $selections) {
        // Get package base price
        $query = "SELECT price FROM packages_tbl WHERE package_id = :package_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':package_id', $packageId);
        $stmt->execute();
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total = $package ? floatval($package['price']) : 0;
        
        // Add selected options prices
        foreach ($selections as $selection) {
            $total += floatval($selection['price']) * intval($selection['quantity']);
        }
        
        return $total;
    }
    
    /**
     * Get user's saved customizations
     */
    public function getUserCustomizations($userId) {
        try {
            $query = "SELECT uc.*, p.name as package_name, o.events as occasion_name
                      FROM user_customizations uc
                      LEFT JOIN packages_tbl p ON uc.package_id = p.package_id
                      LEFT JOIN occasions_tbl o ON uc.occasion_id = o.occasion_id
                      WHERE uc.user_id = :user_id
                      ORDER BY uc.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get selections for each customization
            foreach ($customizations as &$customization) {
                $selQuery = "SELECT * FROM customization_selections 
                             WHERE customization_id = :customization_id";
                $selStmt = $this->conn->prepare($selQuery);
                $selStmt->bindParam(':customization_id', $customization['customization_id']);
                $selStmt->execute();
                $customization['selections'] = $selStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return ['success' => true, 'data' => $customizations];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get single customization by ID
     */
    public function getCustomization($customizationId, $userId) {
        try {
            $query = "SELECT uc.*, p.name as package_name, p.description as package_description,
                             p.price as base_price, o.events as occasion_name
                      FROM user_customizations uc
                      LEFT JOIN packages_tbl p ON uc.package_id = p.package_id
                      LEFT JOIN occasions_tbl o ON uc.occasion_id = o.occasion_id
                      WHERE uc.customization_id = :customization_id AND uc.user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customization_id', $customizationId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $customization = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customization) {
                return ['success' => false, 'message' => 'Customization not found'];
            }
            
            // Get selections
            $selQuery = "SELECT * FROM customization_selections 
                         WHERE customization_id = :customization_id";
            $selStmt = $this->conn->prepare($selQuery);
            $selStmt->bindParam(':customization_id', $customizationId);
            $selStmt->execute();
            $customization['selections'] = $selStmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $customization];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete customization
     */
    public function deleteCustomization($customizationId, $userId) {
        try {
            $query = "DELETE FROM user_customizations 
                      WHERE customization_id = :customization_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customization_id', $customizationId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Customization deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Customization not found'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Add new customization option (Admin only)
     */
    public function addCustomizationOption($data) {
        try {
            $query = "INSERT INTO customization_options_tbl 
                      (category, name, price, is_active) 
                      VALUES (:category, :name, :price, :is_active)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':is_active', $data['is_active']);
            
            $stmt->execute();
            
            return [
                'success' => true, 
                'message' => 'Option added successfully',
                'option_id' => $this->conn->lastInsertId()
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update customization option (Admin only)
     */
    public function updateCustomizationOption($optionId, $data) {
        try {
            $query = "UPDATE customization_options_tbl 
                      SET category = :category, 
                          name = :name, 
                          price = :price, 
                          is_active = :is_active
                      WHERE option_id = :option_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':is_active', $data['is_active']);
            $stmt->bindParam(':option_id', $optionId);
            
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Option updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
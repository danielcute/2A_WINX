<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
require_once ROOT_PATH . '/app/middleware/AuthMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$controller = new CustomizeController();
$auth = new AuthMiddleware();

// Verify authentication for all endpoints except getting options
if ($action !== 'get_options' && $action !== 'get_package_options') {
    $user = $auth->validateToken();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    $userId = $user['user_id'];
}

switch ($method) {
    case 'GET':
        switch ($action) {
            case 'get_options':
                $result = $controller->getCustomizationOptions();
                break;
            case 'get_package_options':
                $packageId = isset($_GET['package_id']) ? $_GET['package_id'] : null;
                if (!$packageId) {
                    $result = ['success' => false, 'message' => 'Package ID required'];
                } else {
                    $result = $controller->getPackageWithOptions($packageId);
                }
                break;
            case 'get_user_customizations':
                $result = $controller->getUserCustomizations($userId);
                break;
            case 'get_customization':
                $customizationId = isset($_GET['id']) ? $_GET['id'] : null;
                if (!$customizationId) {
                    $result = ['success' => false, 'message' => 'Customization ID required'];
                } else {
                    $result = $controller->getCustomization($customizationId, $userId);
                }
                break;
            default:
                $result = ['success' => false, 'message' => 'Invalid action'];
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        switch ($action) {
            case 'save':
                $result = $controller->saveCustomization($userId, $data);
                break;
            case 'add_option':
                // Check if user is admin
                if ($user['role'] !== 'admin') {
                    $result = ['success' => false, 'message' => 'Admin access required'];
                } else {
                    $result = $controller->addCustomizationOption($data);
                }
                break;
            default:
                $result = ['success' => false, 'message' => 'Invalid action'];
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($action === 'update_option') {
            $optionId = isset($_GET['id']) ? $_GET['id'] : null;
            if (!$optionId) {
                $result = ['success' => false, 'message' => 'Option ID required'];
            } elseif ($user['role'] !== 'admin') {
                $result = ['success' => false, 'message' => 'Admin access required'];
            } else {
                $result = $controller->updateCustomizationOption($optionId, $data);
            }
        } else {
            $result = ['success' => false, 'message' => 'Invalid action'];
        }
        break;
        
    case 'DELETE':
        if ($action === 'delete') {
            $customizationId = isset($_GET['id']) ? $_GET['id'] : null;
            if (!$customizationId) {
                $result = ['success' => false, 'message' => 'Customization ID required'];
            } else {
                $result = $controller->deleteCustomization($customizationId, $userId);
            }
        } else {
            $result = ['success' => false, 'message' => 'Invalid action'];
        }
        break;
        
    default:
        $result = ['success' => false, 'message' => 'Method not allowed'];
}

echo json_encode($result);
?>
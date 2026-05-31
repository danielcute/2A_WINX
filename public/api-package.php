<?php
/**
 * API endpoint for package management
 * Handles AJAX requests for fetching package data
 */

header('Content-Type: application/json');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/controllers/AdminPackageController.php';

// Check if admin
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$controller = new AdminPackageController();

switch ($action) {
    case 'get_package':
        $package_id = intval($_GET['id'] ?? 0);
        if (!$package_id) {
            echo json_encode(['success' => false, 'error' => 'Invalid package ID']);
            exit;
        }
        
        $package = $controller->getById($package_id);
        if ($package) {
            echo json_encode(['success' => true, 'package' => $package]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Package not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
?>

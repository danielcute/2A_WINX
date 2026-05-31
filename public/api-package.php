<?php
/**
 * API endpoint for package management
 * Handles AJAX requests for fetching package data
 */

// Prevent PHP from outputting HTML errors/warnings that break JSON parsing
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure JSON header is always sent
header('Content-Type: application/json; charset=utf-8');

// Catch fatal errors and return JSON
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) { http_response_code(500); }
        if (ob_get_length() === 0 || strpos(ob_get_contents(), '{') === false) { ob_clean(); echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e['message']]); }
    }
});


if (!defined('ROOT_PATH')) {
    if (is_dir(__DIR__ . '/app')) {
        define('ROOT_PATH', __DIR__);
    } else {
        define('ROOT_PATH', dirname(__DIR__));
    }
}

require_once ROOT_PATH . '/config/database.php'; // Moved after shutdown function
require_once ROOT_PATH . '/app/controllers/AdminPackageController.php'; // Moved after shutdown function

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

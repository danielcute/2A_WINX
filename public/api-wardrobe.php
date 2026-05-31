<?php
/**
 * API - Wardrobe Data
 * Handles wardrobe searches and retrieval
 * 
 * Usage:
 * - Get all: /api-wardrobe.php?action=getAll
 * - By category: /api-wardrobe.php?action=getByCategory&category=Wedding
 * - Search: /api-wardrobe.php?action=search&q=dress
 * - Categories: /api-wardrobe.php?action=getCategories
 */

if (!defined('ROOT_PATH')) {
    // Check if app folder exists at current level (production) or parent level (local)
    if (is_dir(__DIR__ . '/app')) {
        define('ROOT_PATH', __DIR__);
    } else {
        define('ROOT_PATH', dirname(__DIR__));
    }
}

require_once ROOT_PATH . '/app/models/Wardrobe.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Encode image data to base64 for JSON transmission
 */
function encodeImageToBase64(&$wardrobe) {
    if (!empty($wardrobe['image']) && !empty($wardrobe['image_type'])) {
        $wardrobe['image'] = base64_encode($wardrobe['image']);
    }
    return $wardrobe;
}

/**
 * Encode images in array of wardrobes to base64
 */
function encodeImagesToBase64(&$wardrobes) {
    if (is_array($wardrobes)) {
        foreach ($wardrobes as &$wardrobe) {
            if (!empty($wardrobe['image']) && !empty($wardrobe['image_type'])) {
                $wardrobe['image'] = base64_encode($wardrobe['image']);
            }
        }
    }
    return $wardrobes;
}

try {
    $wardrobe = new Wardrobe();
    $action = $_GET['action'] ?? 'getAll';
    
    switch ($action) {
        case 'getAll':
            $data = $wardrobe->getAll();
            $data = encodeImagesToBase64($data);
            echo json_encode(['success' => true, 'data' => $data]);
            break;
            
        case 'getByCategory':
            $category = isset($_GET['category']) ? trim($_GET['category']) : '';
            if (empty($category)) {
                echo json_encode(['success' => false, 'message' => 'Category parameter required']);
                break;
            }
            $data = $wardrobe->getByCategory($category);
            $data = encodeImagesToBase64($data);
            echo json_encode(['success' => true, 'data' => $data]);
            break;
            
        case 'search':
            $query = isset($_GET['q']) ? trim($_GET['q']) : '';
            if (strlen($query) < 2) {
                echo json_encode(['success' => true, 'data' => []]);
                break;
            }
            $data = $wardrobe->search($query);
            $data = encodeImagesToBase64($data);
            echo json_encode(['success' => true, 'data' => $data]);
            break;
            
        case 'getCategories':
            $data = $wardrobe->getCategories();
            echo json_encode(['success' => true, 'data' => $data]);
            break;

            
        case 'get':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id === 0) {
                echo json_encode(['success' => false, 'message' => 'ID parameter required']);
                break;
            }
            $data = $wardrobe->getById($id);
            if ($data) {
                $data = encodeImageToBase64($data);
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Wardrobe not found']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log('API Wardrobe error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>

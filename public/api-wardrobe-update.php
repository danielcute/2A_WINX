<?php
/**
 * API - Update Wardrobe
 * Handles wardrobe updates via AJAX
 */

// Prevent PHP from outputting HTML errors/warnings that break JSON parsing
ini_set('display_errors', 0);
error_reporting(0);

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
    // Check if app folder exists at current level (production) or parent level (local)
    if (is_dir(__DIR__ . '/app')) {
        define('ROOT_PATH', __DIR__);
    } else {
        define('ROOT_PATH', dirname(__DIR__));
    }
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once ROOT_PATH . '/config/database.php'; // Moved after shutdown function
require_once ROOT_PATH . '/app/models/Wardrobe.php'; // Moved after shutdown function

try {
    // Check authentication (match WardrobeController::requireAdmin)
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }


    require_once ROOT_PATH . '/app/models/User.php';
    $userModel = new User();
    $admin = $userModel->findById((int)$_SESSION['user_id']);

    if (!$admin || ($admin['role'] ?? null) !== 'admin') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }


    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
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

    $wardrobe = new Wardrobe();
    $success = $wardrobe->update($wardrobeId, $data);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Wardrobe updated successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update wardrobe']);
    }
} catch (Exception $e) {
    error_log('API Wardrobe Update error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
exit;
?>

<?php
/**
 * User Avatar API
 * Handles profile photo upload and retrieval
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once ROOT_PATH . '/config/database.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// Prevent errors from breaking JSON
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) { http_response_code(500); }
        if (ob_get_length() === 0 || strpos(ob_get_contents(), '{') === false) { 
            ob_clean(); 
            echo json_encode(['success' => false, 'message' => 'Fatal error']); 
        }
    }
});

try {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if (!$userId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    if ($action === 'upload') {
        // Handle avatar upload
        if (!isset($_FILES['avatar'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No file provided']);
            exit;
        }

        $file = $_FILES['avatar'];
        $upload_dir = ROOT_PATH . '/public/assets/img/avatars/';

        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Upload failed']);
            exit;
        }

        if (!in_array($file['type'], $allowed_types)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            exit;
        }

        if ($file['size'] > $max_size) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File too large']);
            exit;
        }

        // Generate unique filename
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
        $target_file = $upload_dir . $filename;
        $relative_path = '/assets/img/avatars/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $target_file)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to save file']);
            exit;
        }

        // Update database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users_tbl SET image = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->bind_param('si', $relative_path, $userId);

        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['user_avatar'] = $relative_path;
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'avatar_path' => $relative_path
            ]);
        } else {
            $stmt->close();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
        exit;

    } elseif ($action === 'get') {
        // Get user avatar
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT image FROM users_tbl WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $avatar_path = $row['image'] ?? null;
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'avatar_path' => $avatar_path,
            'avatar_url' => $avatar_path ? (strpos($avatar_path, 'http') === 0 ? $avatar_path : '/' . ltrim($avatar_path, '/')) : null
        ]);
        exit;

    } elseif ($action === 'delete') {
        // Delete avatar
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users_tbl SET image = NULL WHERE user_id = ?");
        $stmt->bind_param('i', $userId);

        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['user_avatar'] = null;
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Avatar deleted']);
        } else {
            $stmt->close();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete avatar']);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);

} catch (Exception $e) {
    error_log("api-avatar error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

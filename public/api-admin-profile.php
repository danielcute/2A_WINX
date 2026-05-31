<?php
/**
 * Admin Profile API
 * Handles admin profile picture uploads with automatic cropping/resizing
 * 
 * Same functionality as api-user-profile.php but for admin users
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

session_start();

// Define constants
if (!defined('ROOT_PATH')) {
    // Check if app folder exists at current level (production) or parent level (local)
    if (is_dir(__DIR__ . '/app')) {
        define('ROOT_PATH', __DIR__);
    } else {
        define('ROOT_PATH', dirname(__DIR__));
    }
}

require_once ROOT_PATH . '/config/database.php'; // Moved after shutdown function
require_once ROOT_PATH . '/app/models/User.php'; // Moved after shutdown function

// Require admin authentication
// Accept both role-based and admin_logged_in-based session flags.
if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin' && empty($_SESSION['admin_logged_in']))) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Admin access required.',
        'code' => 'not_authenticated'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$userModel = new User();

// GET request - fetch admin profile
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $userModel->findById($userId);
    
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin profile not found'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'admin' => [
            'user_id' => $user['user_id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
            'image' => $user['image'] ?? null,
            'created_at' => $user['created_at'] ?? null
        ]
    ]);
    exit;
}

// POST request - handle admin profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    
    // ===== UPLOAD ADMIN AVATAR WITH CROPPING =====
    if ($action === 'upload_avatar' && isset($_FILES['avatar'])) {
        $file = $_FILES['avatar'];
        $upload_dir = ROOT_PATH . '/public/uploads/avatars/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Validation
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB max
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds maximum upload size',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form maximum size',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
                UPLOAD_ERR_EXTENSION => 'PHP extension blocked the upload'
            ];
            
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $error_messages[$file['error']] ?? 'Unknown upload error',
                'code' => 'upload_error'
            ]);
            exit;
        }
        
        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Only JPG, PNG, GIF, and WEBP images are allowed',
                'code' => 'invalid_file_type'
            ]);
            exit;
        }
        
        // Validate file size
        if ($file['size'] > $max_size) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Image size must be less than 5MB',
                'code' => 'file_too_large'
            ]);
            exit;
        }
        
        try {
            // Load image
            $image_path = $file['tmp_name'];
            $image_info = getimagesize($image_path);
            
            if (!$image_info) {
                throw new Exception('Invalid image file');
            }
            
            $original_width = $image_info[0];
            $original_height = $image_info[1];
            $mime_type = $image_info['mime'];
            
            // Create image resource based on type
            switch ($mime_type) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($image_path);
                    $ext = 'jpg';
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($image_path);
                    $ext = 'png';
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($image_path);
                    $ext = 'gif';
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($image_path);
                    $ext = 'webp';
                    break;
                default:
                    throw new Exception('Unsupported image type');
            }
            
            if (!$image) {
                throw new Exception('Failed to load image');
            }
            
            // Crop to square (profile picture should be square)
            $size = min($original_width, $original_height);
            $x = ($original_width - $size) / 2;
            $y = ($original_height - $size) / 2;
            
            // Create cropped image (square format) - 400x400 for admin avatars
            $cropped_image = imagecreatetruecolor(400, 400);
            
            // Handle transparency for PNG
            if ($ext === 'png') {
                imagealphablending($cropped_image, false);
                imagesavealpha($cropped_image, true);
                $transparent = imagecolorallocatealpha($cropped_image, 255, 255, 255, 127);
                imagefilledrectangle($cropped_image, 0, 0, 400, 400, $transparent);
            }
            
            imagecopyresampled(
                $cropped_image, $image,
                0, 0, $x, $y,
                400, 400,
                $size, $size
            );
            
            // Generate unique filename
            $filename = 'avatar_admin_' . $userId . '_' . time() . '.' . $ext;
            $file_path = $upload_dir . $filename;
            $relative_path = '/public/uploads/avatars/' . $filename;
            
            // Save cropped image
            switch ($ext) {
                case 'jpg':
                    imagejpeg($cropped_image, $file_path, 90);
                    break;
                case 'png':
                    imagepng($cropped_image, $file_path, 9);
                    break;
                case 'gif':
                    imagegif($cropped_image, $file_path);
                    break;
                case 'webp':
                    imagewebp($cropped_image, $file_path, 90);
                    break;
            }
            
            imagedestroy($image);
            imagedestroy($cropped_image);
            
            // Delete old avatar if it exists
            $user = $userModel->findById($userId);
            if ($user && !empty($user['image'])) {
                $old_image_path = ROOT_PATH . $user['image'];
                if (file_exists($old_image_path)) {
                    @unlink($old_image_path);
                }
            }
            
            // Update database
            $update_result = $userModel->update($userId, ['image' => $relative_path]);
            
            if (!$update_result) {
                unlink($file_path);
                throw new Exception('Failed to update admin profile picture in database');
            }
            
            // Update session
            $_SESSION['user_avatar'] = $relative_path;
            
            echo json_encode([
                'success' => true,
                'message' => 'Admin profile picture updated successfully',
                'image_url' => $relative_path,
                'code' => 'avatar_updated'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => 'image_processing_error'
            ]);
        }
        exit;
    }
    
    // ===== UPDATE ADMIN PROFILE =====
    elseif ($action === 'update_profile') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        $errors = [];
        
        if (empty($first_name)) {
            $errors[] = 'First name is required';
        }
        if (empty($last_name)) {
            $errors[] = 'Last name is required';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors),
                'code' => 'validation_error'
            ]);
            exit;
        }
        
        // Update admin
        $update_data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone
        ];
        
        $update_result = $userModel->update($userId, $update_data);
        
        if ($update_result) {
            // Update session
            $_SESSION['user_name'] = $first_name;
            $_SESSION['user_last_name'] = $last_name;
            $_SESSION['user_phone'] = $phone;
            
            echo json_encode([
                'success' => true,
                'message' => 'Admin profile updated successfully',
                'code' => 'profile_updated'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update admin profile',
                'code' => 'database_error'
            ]);
        }
        exit;
    }
    
    else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action',
            'code' => 'invalid_action'
        ]);
        exit;
    }
}

// Invalid request method
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Method not allowed',
    'code' => 'method_not_allowed'
]);
?>

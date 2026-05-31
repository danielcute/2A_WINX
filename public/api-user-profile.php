<?php
/**
 * User Profile API
 * Handles profile picture uploads with automatic cropping/resizing
 * 
 * POST /api-user-profile.php
 *   - upload_avatar: Upload and crop profile picture
 *   - update_profile: Update user profile information
 * 
 * GET /api-user-profile.php
 *   - Get current user profile data
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

// Require authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in first.',
        'code' => 'not_authenticated'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$userModel = new User();

// GET request - fetch user profile
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $userModel->findById($userId);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'user' => [
            'user_id' => $user['user_id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'birthday' => $user['birthday'],
            'address' => $user['address'],
            'city' => $user['city'] ?? '',
            'image' => $user['image'] ?? null,
            'created_at' => $user['created_at'] ?? null
        ]
    ]);
    exit;
}

// POST request - handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    
    // ===== UPLOAD AVATAR WITH CROPPING =====
    if ($action === 'upload_avatar' && isset($_FILES['avatar'])) {
        $file = $_FILES['avatar'];
        $upload_dir = ROOT_PATH . '/public/uploads/avatars/'; // Ensure path matches web root
        
        // Create upload directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Validation
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB max
        $max_width = 2000;
        $max_height = 2000;
        
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
            
            // Validate image dimensions
            if ($original_width > $max_width || $original_height > $max_height) {
                throw new Exception("Image dimensions exceed maximum ({$max_width}x{$max_height}px)");
            }
            
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
            
            // Create cropped image (square format)
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
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $file_path = $upload_dir . $filename;
            $relative_path = '/uploads/avatars/' . $filename;
            
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
                    unlink($old_image_path);
                }
            }
            
            // Update database
            $update_result = $userModel->update($userId, ['image' => $relative_path]);
            
            if (!$update_result) {
                unlink($file_path);
                throw new Exception('Failed to update profile picture in database');
            }
            
            // Update session
            $_SESSION['user_avatar'] = $relative_path;
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile picture updated successfully',
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
    
    // ===== UPDATE PROFILE INFORMATION =====
    elseif ($action === 'update_profile') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $birthday = trim($_POST['birthday'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        
        $errors = [];
        
        // Validation
        if (empty($first_name)) {
            $errors[] = 'First name is required';
        }
        if (empty($last_name)) {
            $errors[] = 'Last name is required';
        }
        if (empty($phone)) {
            $errors[] = 'Phone number is required';
        }
        if (empty($birthday)) {
            $errors[] = 'Birthday is required';
        }
        if (empty($address)) {
            $errors[] = 'Address is required';
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
        
        // Update user
        $update_data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'birthday' => $birthday,
            'address' => $address,
            'city' => $city
        ];
        
        $update_result = $userModel->update($userId, $update_data);
        
        if ($update_result) {
            // Update session
            $_SESSION['user_name'] = $first_name;
            $_SESSION['user_last_name'] = $last_name;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_birthday'] = $birthday;
            $_SESSION['user_address'] = $address;
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'code' => 'profile_updated'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update profile',
                'code' => 'database_error'
            ]);
        }
        exit;
    }
    
    // ===== UPLOAD DEFAULT AVATAR =====
    elseif ($action === 'upload_avatar_default') {
        $avatar_path = $_POST['avatar_path'] ?? null;

        if (empty($avatar_path)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Default avatar path is required.']);
            exit;
        }

        // Validate path to prevent directory traversal
        if (strpos($avatar_path, '..') !== false || strpos($avatar_path, '/assets/images/avatars/') === false) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Invalid default avatar path.']);
             exit;
        }

        // Update database
        $update_result = $userModel->update($userId, ['image' => $avatar_path]);

        if ($update_result) {
            $_SESSION['user_avatar'] = $avatar_path;
            echo json_encode([
                'success' => true,
                'message' => 'Default avatar updated successfully',
                'image_url' => $avatar_path,
                'code' => 'default_avatar_updated'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to set default avatar in database.']);
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

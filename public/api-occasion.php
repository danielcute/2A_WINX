<?php
/**
 * Occasions API Endpoint - Unified
 * Handles all occasions operations including image uploads
 * GET    /api-occasion.php?id=1        - Get occasion data with image as base64
 * GET    /api-occasion.php?image=1     - Get just the image as base64
 * POST   /api-occasion.php              - Create or update occasion with image
 * DELETE /api-occasion.php              - Delete occasion
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

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php'; // Moved after shutdown function
require_once ROOT_PATH . '/app/models/Notification.php'; // Moved after shutdown function

$method = $_SERVER['REQUEST_METHOD']; // Moved after shutdown function
$db = Database::getInstance()->getConnection(); // Moved after shutdown function

try {
    if ($method === 'GET') {
        // Get image only (for lazy loading)
        if (isset($_GET['image'])) {
            $id = intval($_GET['image']);
            $stmt = $db->prepare("SELECT image, image_name FROM occasions_tbl WHERE occasion_id = ?");
            if (!$stmt) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Database error']);
                exit;
            }
            
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $occasion = $result->fetch_assoc();
            $stmt->close();
            
            if (!$occasion || !$occasion['image']) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Image not found']);
                exit;
            }
            
            // Convert to base64 data URL
            $base64 = base64_encode($occasion['image']);
            $mime_type = 'image/jpeg';
            if ($occasion['image_name']) {
                $ext = strtolower(pathinfo($occasion['image_name'], PATHINFO_EXTENSION));
                $mime_types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
                $mime_type = $mime_types[$ext] ?? 'image/jpeg';
            }
            
            echo json_encode(['success' => true, 'image' => 'data:' . $mime_type . ';base64,' . $base64]);
            exit;
        }
        
        // Get occasion data
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $db->prepare("SELECT * FROM occasions_tbl WHERE occasion_id = ?");
            if (!$stmt) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Database error']);
                exit;
            }
            
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $occasion = $result->fetch_assoc();
            $stmt->close();
            
            if (!$occasion) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Occasion not found']);
                exit;
            }
            
            // Get packages count
            $pkg_result = $db->query("SELECT COUNT(*) as count FROM packages_tbl WHERE occasion_id = $id");
            $pkg_count = $pkg_result->fetch_assoc()['count'] ?? 0;
            $occasion['packages_count'] = $pkg_count;
            
            // Don't send full image binary - send flag if it exists
            $occasion['has_image'] = !empty($occasion['image']);
            unset($occasion['image']);
            
            echo json_encode(['success' => true, 'data' => $occasion]);
            exit;
        }
        
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID parameter required']);
    } 
    
    elseif ($method === 'POST') {
    // Check admin session (match WardrobeController::requireAdmin)
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        require_once ROOT_PATH . '/app/models/User.php';
        $userModel = new User();
        $admin = $userModel->findById((int)$_SESSION['user_id']);

        if (!$admin || (($admin['role'] ?? null) !== 'admin')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        $action = $_GET['action'] ?? 'create';
        
        // Handle multipart/form-data (FormData with image)
        if (strpos($content_type, 'multipart/form-data') !== false || isset($_FILES['image'])) {
            $events = $_POST['events'] ?? '';
            $descriptions = $_POST['descriptions'] ?? '';
            $occasion_id = intval($_POST['occasion_id'] ?? 0);
            
            if (empty($events)) {
                echo json_encode(['success' => false, 'message' => 'Occasion name is required']);
                exit;
            }
            
            $image_data = null;
            $image_name = null;
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_data = file_get_contents($_FILES['image']['tmp_name']);
                $image_name = basename($_FILES['image']['name']);
            }
            
            $events = $db->real_escape_string($events);
            $descriptions = $db->real_escape_string($descriptions);
            
            // Update existing occasion
            if ($action === 'update' && $occasion_id) {
                if ($image_data !== null) {
                    $stmt = $db->prepare("UPDATE occasions_tbl SET events = ?, descriptions = ?, image = ?, image_name = ? WHERE occasion_id = ?");
                    $stmt->bind_param("ssssi", $events, $descriptions, $image_data, $image_name, $occasion_id);
                } else {
                    $stmt = $db->prepare("UPDATE occasions_tbl SET events = ?, descriptions = ? WHERE occasion_id = ?");
                    $stmt->bind_param("ssi", $events, $descriptions, $occasion_id);
                }
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Occasion updated successfully!';
                    echo json_encode(['success' => true, 'message' => 'Occasion updated successfully']);
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update: ' . $stmt->error]);
                    $stmt->close();
                }
            } 
            // Create new occasion
            else {
                $stmt = $db->prepare("INSERT INTO occasions_tbl (events, descriptions, image, image_name) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $events, $descriptions, $image_data, $image_name);
                
                if ($stmt->execute()) {
                    $newOccasionId = $db->insert_id;
                    $_SESSION['success_message'] = 'Occasion created successfully!';
                    
                    // Create notifications for all users about the new occasion
                    try {
                        $notificationModel = new Notification();
                        
                        // Get all user IDs
                        $userResult = $db->query("SELECT user_id FROM users_tbl WHERE role = 'user'");
                        if ($userResult) {
                            while ($userRow = $userResult->fetch_assoc()) {
                                $notificationModel->create([
                                    'user_id' => $userRow['user_id'],
                                    'type' => 'system_update',
                                    'title' => 'New Occasion Available',
                                    'message' => 'A new occasion "' . htmlspecialchars($events) . '" has been added to the system!',
                                    'related_type' => 'occasion',
                                    'related_id' => $newOccasionId
                                ]);
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Failed to create occasion notifications: " . $e->getMessage());
                    }
                    
                    echo json_encode(['success' => true, 'message' => 'Occasion created successfully']);
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create: ' . $stmt->error]);
                    $stmt->close();
                }
            }
        } 
        // Handle JSON request (backward compatibility)
        else {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['events'])) {
                echo json_encode(['success' => false, 'message' => 'Occasion name is required']);
                exit;
            }
            
            $events = $db->real_escape_string($data['events']);
            $descriptions = $db->real_escape_string($data['descriptions'] ?? '');
            
            $query = "INSERT INTO occasions_tbl (events, descriptions) VALUES ('$events', '$descriptions')";
            
            if ($db->query($query)) {
                $_SESSION['success_message'] = 'Occasion created successfully!';
                echo json_encode(['success' => true, 'message' => 'Occasion created successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed: ' . $db->error]);
            }
        }
    } 
    
    elseif ($method === 'DELETE') {
        // Check admin session (match WardrobeController::requireAdmin)
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        require_once ROOT_PATH . '/app/models/User.php';
        $userModel = new User();
        $admin = $userModel->findById((int)$_SESSION['user_id']);

        if (!$admin || (($admin['role'] ?? null) !== 'admin')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        
        $data = json_decode(file_get_contents('php://input'), true);
        $occasion_id = intval($data['occasion_id'] ?? 0);
        
        if (!$occasion_id) {
            echo json_encode(['success' => false, 'message' => 'Occasion ID required']);
            exit;
        }
        
        // Check if occasion has packages
        $check = $db->query("SELECT COUNT(*) as count FROM packages_tbl WHERE occasion_id = $occasion_id");
        $count = $check->fetch_assoc()['count'];
        
        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => "Cannot delete occasion with $count associated packages"]);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM occasions_tbl WHERE occasion_id = ?");
        $stmt->bind_param("i", $occasion_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Occasion deleted successfully!';
            echo json_encode(['success' => true, 'message' => 'Occasion deleted successfully']);
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . $stmt->error]);
            $stmt->close();
        }
    } 
    
    else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

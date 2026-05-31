<?php
/**
 * API Endpoint for Organizer Color Choices
 * POST: Save organizer color choices for a booking
 * GET: Retrieve organizer color choices
 */

session_start();

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Customization.php';

header('Content-Type: application/json');

// Check authentication
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$db = Database::getInstance()->getConnection();

try {
    if ($action === 'get') {
        // GET organizer color choices for a booking
        $bookingId = (int)($_GET['booking_id'] ?? 0);
        $planId = (int)($_GET['plan_id'] ?? 0);
        
        if (!$bookingId && !$planId) {
            http_response_code(400);
            echo json_encode(['error' => 'booking_id or plan_id required']);
            exit;
        }
        
        if ($bookingId) {
            $stmt = $db->prepare("SELECT colors_json, description FROM organizer_color_choices WHERE booking_id = ?");
            $stmt->bind_param('i', $bookingId);
        } else {
            $stmt = $db->prepare("SELECT colors_json, description FROM organizer_color_choices WHERE plan_id = ?");
            $stmt->bind_param('i', $planId);
        }
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            
            if ($data) {
                $response = [
                    'colors' => json_decode($data['colors_json'], true),
                    'description' => $data['description']
                ];
            } else {
                $response = ['colors' => [], 'description' => null];
            }
            
            echo json_encode($response);
        }
        $stmt->close();
        
    } elseif ($action === 'save') {
        // SAVE organizer color choices
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $planId = (int)($_POST['plan_id'] ?? 0);
        $colorsJson = $_POST['colors_json'] ?? '[]';
        $description = $_POST['description'] ?? '';
        
        if (!$bookingId || !$planId) {
            http_response_code(400);
            echo json_encode(['error' => 'booking_id and plan_id required']);
            exit;
        }
        
        // Validate JSON
        if (!is_array(json_decode($colorsJson, true))) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid colors_json format']);
            exit;
        }
        
        $stmt = $db->prepare("
            INSERT INTO organizer_color_choices (booking_id, plan_id, colors_json, description)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            colors_json = VALUES(colors_json),
            description = VALUES(description)
        ");
        
        $stmt->bind_param('iiss', $bookingId, $planId, $colorsJson, $description);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Colors saved successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save colors']);
        }
        $stmt->close();
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>

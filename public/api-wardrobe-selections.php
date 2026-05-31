<?php
/**
 * Wardrobe Selections API
 * Handles wardrobe selection operations for bookings
 */
if (!defined('ROOT_PATH')) {
    // Check if app folder exists at current level (production) or parent level (local)
    if (is_dir(__DIR__ . '/app')) {
        define('ROOT_PATH', __DIR__);
    } else {
        define('ROOT_PATH', dirname(__DIR__));
    }
}

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

require_once ROOT_PATH . '/app/models/Wardrobe.php'; // Moved after shutdown function

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$wardrobe = new Wardrobe();
$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    switch ($action) {
        case 'save':
            // Save a wardrobe selection
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not authenticated');
            }
            
            $data = [
                'plan_id' => $_POST['plan_id'] ?? null,
                'user_id' => $_SESSION['user_id'],
                'wardrobe_id' => $_POST['wardrobe_id'] ?? null,
                'quantity' => $_POST['quantity'] ?? 1,
                'size' => $_POST['size'] ?? 'Standard',
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'subtotal' => $_POST['subtotal'] ?? 0,
                'notes' => $_POST['notes'] ?? ''
            ];
            
            if (empty($data['plan_id']) || empty($data['wardrobe_id'])) {
                throw new Exception('Missing required fields');
            }
            
            $selection_id = $wardrobe->saveSelection($data);
            
            if ($selection_id) {
                echo json_encode([
                    'success' => true,
                    'selection_id' => $selection_id,
                    'message' => 'Wardrobe selection saved successfully'
                ]);
            } else {
                throw new Exception('Failed to save wardrobe selection');
            }
            break;
        
        case 'getByPlan':
            // Get all selections for a plan
            $plan_id = $_GET['plan_id'] ?? null;
            
            if (!$plan_id) {
                throw new Exception('Missing plan_id');
            }
            
            $selections = $wardrobe->getSelectionsByPlan($plan_id);
            
            echo json_encode([
                'success' => true,
                'selections' => $selections,
                'total_cost' => $wardrobe->getTotalCost($plan_id)
            ]);
            break;
        
        case 'getByUser':
            // Get all selections for a user
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not authenticated');
            }
            
            $selections = $wardrobe->getSelectionsByUser($_SESSION['user_id']);
            
            echo json_encode([
                'success' => true,
                'selections' => $selections
            ]);
            break;
        
        case 'update':
            // Update a wardrobe selection
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $selection_id = $_POST['selection_id'] ?? null;
            
            if (!$selection_id) {
                throw new Exception('Missing selection_id');
            }
            
            $data = [
                'quantity' => $_POST['quantity'] ?? 1,
                'size' => $_POST['size'] ?? 'Standard',
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'subtotal' => $_POST['subtotal'] ?? 0,
                'notes' => $_POST['notes'] ?? '',
                'status' => $_POST['status'] ?? 'pending'
            ];
            
            $success = $wardrobe->updateSelection($selection_id, $data);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Wardrobe selection updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update wardrobe selection');
            }
            break;
        
        case 'delete':
            // Delete a wardrobe selection
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $selection_id = $_POST['selection_id'] ?? null;
            
            if (!$selection_id) {
                throw new Exception('Missing selection_id');
            }
            
            $success = $wardrobe->deleteSelection($selection_id);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Wardrobe selection deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete wardrobe selection');
            }
            break;
        
        case 'checkAvailability':
            // Check availability for a wardrobe
            $wardrobe_id = $_GET['wardrobe_id'] ?? null;
            $start_date = $_GET['start_date'] ?? null;
            $end_date = $_GET['end_date'] ?? null;
            $quantity = $_GET['quantity'] ?? 1;
            
            if (!$wardrobe_id || !$start_date || !$end_date) {
                throw new Exception('Missing required parameters');
            }
            
            $available = $wardrobe->checkAvailability($wardrobe_id, $start_date, $end_date, $quantity);
            
            echo json_encode([
                'success' => true,
                'available' => $available,
                'wardrobe_id' => $wardrobe_id
            ]);
            break;
        
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

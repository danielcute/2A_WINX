<?php

/**
 * Receipt API Endpoint
 * Handles: Receipt retrieval, formatting
 * 
 * Available Actions:
 * - get_latest_receipt: Get the most recent receipt for a plan
 * - get_receipts: Get all receipts for a plan
 */

header('Content-Type: application/json; charset=utf-8');

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

// Initialize database using singleton
try {
    $db = Database::getInstance()->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => $e->getMessage()]));
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$response = [];

try {
    switch ($action) {
        case 'get_latest_receipt':
            $response = getLatestReceipt($db);
            break;
            
        case 'get_receipts':
            $response = getReceipts($db);
            break;

        default:
            $response = [
                'success' => false,
                'error' => 'Invalid action: ' . htmlspecialchars($action ?? 'none')
            ];
    }

} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'debug' => $_ENV['DEBUG'] ? $e->getMessage() : null
    ];
}

echo json_encode($response);
exit;

/**
 * Handler Functions
 */

function getLatestReceipt($db) {
    // Check user authentication
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }

    $planId = (int)($_GET['plan_id'] ?? $_POST['plan_id'] ?? 0);
    $userId = (int)$_SESSION['user_id'];
    
    if (!$planId) {
        return ['success' => false, 'error' => 'Missing plan_id'];
    }

    // Get the most recent receipt for this plan
    $stmt = $db->prepare("
        SELECT 
            r.*,
            p.event_name,
            p.total_price
        FROM payment_receipts_tbl r
        JOIN plans_tbl p ON r.plan_id = p.plan_id
        WHERE r.plan_id = ? AND r.user_id = ? AND p.user_id = ?
        ORDER BY r.paid_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("iii", $planId, $userId, $userId);
    $stmt->execute();
    $receipt = $stmt->get_result()->fetch_assoc();

    if (!$receipt) {
        return ['success' => false, 'error' => 'No receipts found for this plan'];
    }

    // Parse items_purchased JSON if present
    if ($receipt['items_purchased']) {
        $receipt['items_purchased'] = json_decode($receipt['items_purchased'], true);
    }

    return [
        'success' => true,
        'receipt' => $receipt
    ];
}

function getReceipts($db) {
    // Check user authentication
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }

    $planId = (int)($_GET['plan_id'] ?? $_POST['plan_id'] ?? 0);
    $userId = (int)$_SESSION['user_id'];
    
    if (!$planId) {
        return ['success' => false, 'error' => 'Missing plan_id'];
    }

    // Get all receipts for this plan
    $stmt = $db->prepare("
        SELECT 
            r.*,
            p.event_name,
            p.total_price
        FROM payment_receipts_tbl r
        JOIN plans_tbl p ON r.plan_id = p.plan_id
        WHERE r.plan_id = ? AND r.user_id = ? AND p.user_id = ?
        ORDER BY r.paid_at DESC
    ");
    $stmt->bind_param("iii", $planId, $userId, $userId);
    $stmt->execute();
    $receipts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($receipts)) {
        return ['success' => false, 'error' => 'No receipts found for this plan'];
    }

    // Parse items_purchased JSON if present
    foreach ($receipts as &$receipt) {
        if ($receipt['items_purchased']) {
            $receipt['items_purchased'] = json_decode($receipt['items_purchased'], true);
        }
    }

    return [
        'success' => true,
        'receipts' => $receipts,
        'count' => count($receipts)
    ];
}

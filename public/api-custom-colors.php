<?php
/**
 * API endpoint for custom color selections
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

session_start();
require_once ROOT_PATH . '/app/models/Customization.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    $planId = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;

    $customization = new Customization();

    switch ($action) {
        case 'get':
            if (!$planId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Plan ID required']);
                exit;
            }

            $customColors = $customization->getCustomColors($planId);
            
            if ($customColors) {
                echo json_encode([
                    'success' => true,
                    'data' => $customColors,
                    'colors_display' => self::formatColorsForDisplay($customColors['colors'] ?? [])
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No custom colors found']);
            }
            break;

        case 'store':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }

            if (!$planId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Plan ID required']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $customColors = $input['colors'] ?? [];
            $description = $input['description'] ?? 'Custom color combination';

            if (empty($customColors)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Colors required']);
                exit;
            }

            if ($customization->storeCustomColors($planId, $customColors, $description)) {
                echo json_encode(['success' => true, 'message' => 'Custom colors saved']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save custom colors']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }

} catch (Exception $e) {
    error_log('Custom colors API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
exit;

function formatColorsForDisplay($colors) {
    $formatted = [];
    foreach ($colors as $color) {
        $formatted[] = [
            'name' => $color['name'] ?? 'Unknown',
            'hex' => $color['hex'] ?? '#000000'
        ];
    }
    return $formatted;
}
?>

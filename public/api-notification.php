<?php
/**
 * Notification API
 * Handles notification operations
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

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

session_start();
require_once ROOT_PATH . '/config/database.php'; // Moved after shutdown function
require_once ROOT_PATH . '/app/models/Notification.php'; // Moved after shutdown function


$response = ['success' => false, 'message' => 'Invalid request'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$notificationId = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : (isset($_GET['notification_id']) ? (int)$_GET['notification_id'] : 0);
$userId = $_SESSION['user_id'] ?? 0;

// Normalize auth for both admin + user areas.
// If you're admin-logged-in but user_id isn't set, try common variants.
if ($userId <= 0) {
    if (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
        $userId = (int)$_SESSION['user_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $notificationModel = new Notification();
    
    switch ($action) {
        case 'get_unread':
            // Get unread notification count and list
            if ($userId > 0) {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $unreadCount = $notificationModel->getUnreadCount($userId);
                $unreadNotifications = $notificationModel->getUnreadNotifications($userId, $limit);
                
                $response = [
                    'success' => true,
                    'unread_count' => $unreadCount,
                    'notifications' => $unreadNotifications
                ];
            } else {
                http_response_code(401);
                $response = ['success' => false, 'message' => 'Not authenticated'];
            }
            break;
            
        case 'get_all':

            // Get all notifications with pagination
            if ($userId > 0) {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                $notifications = $notificationModel->getUserNotifications($userId, $limit, $offset);
                
                $response = [
                    'success' => true,
                    'notifications' => $notifications
                ];
            } else {
                $response = ['success' => false, 'message' => 'Not authenticated'];
            }
            break;
            
        case 'mark_as_read':
            // Mark a single notification as read
            if ($notificationId > 0) {
                $result = $notificationModel->markAsRead($notificationId);
                $response = [
                    'success' => $result,
                    'message' => $result ? 'Notification marked as read' : 'Failed to mark notification as read'
                ];
            } else {
                $response = ['success' => false, 'message' => 'Invalid notification ID'];
            }
            break;
            
        case 'mark_all_as_read':
            // Mark all notifications as read for the user
            if ($userId > 0) {
                $result = $notificationModel->markAllAsRead($userId);
                $response = [
                    'success' => $result,
                    'message' => $result ? 'All notifications marked as read' : 'Failed to mark all notifications as read'
                ];
            } else {
                $response = ['success' => false, 'message' => 'Not authenticated'];
            }
            break;
            
        case 'delete':
            // Delete a notification
            if ($notificationId > 0) {
                $result = $notificationModel->delete($notificationId);
                $response = [
                    'success' => $result,
                    'message' => $result ? 'Notification deleted' : 'Failed to delete notification'
                ];
            } else {
                $response = ['success' => false, 'message' => 'Invalid notification ID'];
            }
            break;
            
        case 'get_unread_count':
            // Get just the unread count
            if ($userId > 0) {
                $unreadCount = $notificationModel->getUnreadCount($userId);
                $response = [
                    'success' => true,
                    'unread_count' => $unreadCount
                ];
            } else {
                $response = ['success' => false, 'message' => 'Not authenticated'];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
}

echo json_encode($response);

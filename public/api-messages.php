<?php
/**
 * Real-time Messaging API
 * Provides JSON responses for real-time message updates
 * Access: /SINTA/public/api-messages.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

if (!isset($_SESSION)) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/controllers/MessagingController.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$action = $_GET['action'] ?? 'get-count';
$messagingController = new MessagingController();
$response = [];

try {
    switch ($action) {
        case 'get-count':
            // Get message count and unread count for admin
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                $admin_id = $_SESSION['user_id'];
                $messages = $messagingController->getAdminMessages($admin_id, 'all');
                $unread_count = $messagingController->getUnreadCount($admin_id);
                
                $response = [
                    'success' => true,
                    'messageCount' => count($messages),
                    'unreadCount' => $unread_count,
                    'messages' => array_map(function($msg) {
                        return [
                            'id' => $msg['message_id'],
                            'subject' => $msg['subject'],
                            'sender_id' => $msg['sender_id'],
                            'status' => $msg['status'],
                            'created_at' => $msg['created_at']
                        ];
                    }, $messages)
                ];
            } else {
                http_response_code(403);
                $response = ['error' => 'Not an admin'];
            }
            break;
            
        case 'get-message':
            // Get single message with replies
            $message_id = intval($_GET['message_id'] ?? 0);
            if ($message_id > 0) {
                $data = $messagingController->getMessageWithReplies($message_id);
                
                // Mark as read if admin
                if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                    $messagingController->markAsRead($message_id);
                }
                
                $response = [
                    'success' => true,
                    'message' => $data['message'],
                    'replies' => $data['replies']
                ];
            } else {
                http_response_code(400);
                $response = ['error' => 'Invalid message ID'];
            }
            break;
            
        case 'send-reply':
            // Send reply to message
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                $message_id = intval($_POST['message_id'] ?? 0);
                $reply_text = trim($_POST['reply_text'] ?? '');
                $admin_id = $_SESSION['user_id'];
                
                if (!$message_id || !$reply_text) {
                    http_response_code(400);
                    $response = ['error' => 'Missing required fields'];
                } else {
                    $result = $messagingController->replyToMessage($message_id, $admin_id, $reply_text);
                    if ($result['success']) {
                        $response = [
                            'success' => true,
                            'reply_id' => $result['reply_id'],
                            'message' => 'Reply sent successfully'
                        ];
                    } else {
                        http_response_code(500);
                        $response = ['error' => $result['error'] ?? 'Failed to send reply'];
                    }
                }
            } else {
                http_response_code(403);
                $response = ['error' => 'Not authorized'];
            }
            break;
            
        case 'user-unread':
            // Get unread messages for current user
            $user_id = $_SESSION['user_id'];
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM messages_tbl WHERE sender_id = ? AND status = 'replied'");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            $response = [
                'success' => true,
                'unreadCount' => $result['count'] ?? 0
            ];
            break;
            
        default:
            http_response_code(400);
            $response = ['error' => 'Unknown action'];
    }
    
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

echo json_encode($response);
?>

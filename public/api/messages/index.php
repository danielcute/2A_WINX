<?php
/**
 * MESSAGE API ENDPOINT
 * Location: public/api/messages/index.php
 * Handles sending and receiving messages between user and admin
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

require_once dirname(dirname(dirname(__DIR__))) . '/config/database.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/models/Message.php';

session_start();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$messageModel = new Message();

// HANDLE GET REQUESTS
if ($method === 'GET') {
    if ($action === 'check') {
        // Check for new messages
        $userId = (int)($_GET['user_id'] ?? 0);
        
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID required']);
            exit;
        }

        $messages = $messageModel->getUserMessages($userId);
        $unreadCount = count(array_filter($messages, fn($m) => $m['status'] === 'unread' && $m['is_admin_reply'] === 1));

        echo json_encode([
            'success' => true,
            'hasNewMessages' => $unreadCount > 0,
            'unreadCount' => $unreadCount,
            'totalMessages' => count($messages)
        ]);
        exit;
    }

    if ($action === 'getAll') {
        // Get all messages for a user
        $userId = (int)($_GET['user_id'] ?? 0);
        
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID required']);
            exit;
        }

        $messages = $messageModel->getUserMessages($userId);
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
        exit;
    }
}

// HANDLE POST REQUESTS
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'send') {
        // Send a message from user to admin
        $userId = (int)($input['user_id'] ?? 0);
        $content = trim($input['content'] ?? '');

        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            exit;
        }

        if (empty($content)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Message content required']);
            exit;
        }

        // Create message
        $messageId = $messageModel->create([
            'sender_id' => $userId,
            'recipient_id' => 1,
            'content' => $content,
            'status' => 'unread'
        ]);

        if ($messageId) {
            echo json_encode([
                'success' => true,
                'message_id' => $messageId,
                'message' => 'Message sent successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error saving message']);
        }
        exit;
    }

    if ($action === 'adminReply') {
        // Admin sends a reply to user
        // Admin ID could be stored as user_id when admin is logged in
        $adminId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $userId = (int)($input['user_id'] ?? 0);
        $content = trim($input['content'] ?? '');

        if ($adminId <= 0 || $userId <= 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized - Admin ID: ' . $adminId]);
            exit;
        }

        if (empty($content)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Message content required']);
            exit;
        }

        // Create reply message
        $messageId = $messageModel->create([
            'sender_id' => $adminId,
            'recipient_id' => $userId,
            'content' => $content,
            'status' => 'unread'
        ]);

        if ($messageId) {
            echo json_encode([
                'success' => true,
                'message_id' => $messageId,
                'message' => 'Reply sent successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error saving reply']);
        }
        exit;
    }
}

// DEFAULT RESPONSE
http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
exit;
?>

<?php
/**
 * Message Model
 * Handles user messages and admin-user communication
 */
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';

class Message {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new message
     */
    public function create($data) {
        $senderId = (int)$data['sender_id'];
        $recipientId = (int)$data['recipient_id'];
        $subject = $this->db->real_escape_string($data['subject'] ?? '');
        $messageText = $this->db->real_escape_string($data['message_text']);
        $messageType = $data['message_type'] ?? 'inquiry';
        
        $stmt = $this->db->prepare("
            INSERT INTO messages_tbl 
            (sender_id, recipient_id, subject, message_text, message_type, status) 
            VALUES (?, ?, ?, ?, ?, 'unread')
        ");
        
        if (!$stmt) {
            error_log("Message prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("iisss", $senderId, $recipientId, $subject, $messageText, $messageType);
        
        if ($stmt->execute()) {
            $result = $this->db->insert_id;
            $stmt->close();
            return $result;
        } else {
            error_log("Message execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    /**
     * Get user's sent messages
     */
    public function getUserMessages($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   u.first_name, u.last_name, u.email
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.recipient_id = u.user_id
            WHERE m.sender_id = ?
            ORDER BY m.created_at DESC
        ");
        
        if (!$stmt) {
            error_log("getUserMessages prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $messages;
    }
    
    /**
     * Get messages received by admin/recipient
     */
    public function getReceivedMessages($recipientId) {
        $recipientId = (int)$recipientId;
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   u.first_name, u.last_name, u.email
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.sender_id = u.user_id
            WHERE m.recipient_id = ?
            ORDER BY m.created_at DESC
        ");
        
        if (!$stmt) {
            error_log("getReceivedMessages prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("i", $recipientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $messages;
    }
    
    /**
     * Get all conversations (admin view)
     */
    public function getAllConversations($recipientId) {
        $recipientId = (int)$recipientId;
        $sql = "
            SELECT 
                m.sender_id,
                u.first_name,
                u.last_name,
                u.email,
                COUNT(CASE WHEN m.status = 'unread' THEN 1 END) as unread_count,
                MAX(m.created_at) as last_message_time,
                (SELECT message_text FROM messages_tbl WHERE recipient_id = ? AND sender_id = u.user_id ORDER BY created_at DESC LIMIT 1) as last_message
            FROM messages_tbl m
            JOIN users_tbl u ON m.sender_id = u.user_id
            WHERE m.recipient_id = ?
            GROUP BY m.sender_id
            ORDER BY last_message_time DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("getAllConversations prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("ii", $recipientId, $recipientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversations = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $conversations;
    }
    
    /**
     * Get conversation between two users
     */
    public function getConversation($userId1, $userId2) {
        $userId1 = (int)$userId1;
        $userId2 = (int)$userId2;
        
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   u.first_name, u.last_name
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.sender_id = u.user_id
            WHERE (m.sender_id = ? AND m.recipient_id = ?) 
               OR (m.sender_id = ? AND m.recipient_id = ?)
            ORDER BY m.created_at ASC
        ");
        
        if (!$stmt) {
            error_log("getConversation prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $messages;
    }
    
    /**
     * Get single message by ID
     */
    public function findById($messageId) {
        $messageId = (int)$messageId;
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   u.first_name, u.last_name, u.email
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.sender_id = u.user_id
            WHERE m.message_id = ?
        ");
        
        if (!$stmt) {
            error_log("findById prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $message = $result->fetch_assoc();
        $stmt->close();
        return $message;
    }
    
    /**
     * Mark message as read
     */
    public function markAsRead($messageId) {
        $messageId = (int)$messageId;
        $stmt = $this->db->prepare("UPDATE messages_tbl SET status = 'read' WHERE message_id = ?");
        
        if (!$stmt) {
            error_log("markAsRead prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $messageId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    /**
     * Get unread message count for a user
     */
    public function getUnreadCount($recipientId) {
        $recipientId = (int)$recipientId;
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM messages_tbl 
            WHERE recipient_id = ? AND status = 'unread'
        ");
        
        if (!$stmt) {
            error_log("getUnreadCount prepare failed: " . $this->db->error);
            return 0;
        }
        
        $stmt->bind_param("i", $recipientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'] ?? 0;
    }
    
    /**
     * Reply to a message
     */
    public function addReply($messageId, $senderId, $replyText) {
        $messageId = (int)$messageId;
        $senderId = (int)$senderId;
        $replyText = $this->db->real_escape_string($replyText);
        
        // Insert reply
        $stmt = $this->db->prepare("
            INSERT INTO message_replies_tbl 
            (message_id, sender_id, reply_text)
            VALUES (?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("addReply prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("iis", $messageId, $senderId, $replyText);
        
        if ($stmt->execute()) {
            $replyId = $this->db->insert_id;
            
            // Update original message status to 'replied'
            $update = $this->db->prepare("UPDATE messages_tbl SET status = 'replied' WHERE message_id = ?");
            $update->bind_param("i", $messageId);
            $update->execute();
            $update->close();
            
            $stmt->close();
            return $replyId;
        } else {
            error_log("addReply execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    /**
     * Get all replies for a message
     */
    public function getReplies($messageId) {
        $messageId = (int)$messageId;
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   u.first_name, u.last_name, u.role
            FROM message_replies_tbl r
            LEFT JOIN users_tbl u ON r.sender_id = u.user_id
            WHERE r.message_id = ?
            ORDER BY r.created_at ASC
        ");
        
        if (!$stmt) {
            error_log("getReplies prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $replies = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $replies;
    }
    
    /**
     * Delete a message
     */
    public function delete($messageId) {
        $messageId = (int)$messageId;
        $stmt = $this->db->prepare("DELETE FROM messages_tbl WHERE message_id = ?");
        
        if (!$stmt) {
            error_log("delete prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $messageId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>
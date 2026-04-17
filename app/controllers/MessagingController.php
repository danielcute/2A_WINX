<?php
/**
 * Messaging Controller
 * Handles real user-to-admin messaging system
 */

define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';

class MessagingController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Send message from user to admin
     */
    public function sendMessage($sender_id, $subject, $message_text, $message_type = 'inquiry') {
        // Get admin user ID (first admin in database)
        $admin_stmt = $this->db->prepare("SELECT user_id FROM users_tbl WHERE role = 'admin' LIMIT 1");
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        $admin = $admin_result->fetch_assoc();
        
        if (!$admin) {
            return ['success' => false, 'error' => 'No admin found'];
        }
        
        $recipient_id = $admin['user_id'];
        
        $stmt = $this->db->prepare("
            INSERT INTO messages_tbl 
            (sender_id, recipient_id, subject, message_text, message_type, status) 
            VALUES (?, ?, ?, ?, ?, 'unread')
        ");
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        $stmt->bind_param("iisss", $sender_id, $recipient_id, $subject, $message_text, $message_type);
        
        if ($stmt->execute()) {
            $message_id = $this->db->insert_id;
            // Mark as read if admin is reading it
            return ['success' => true, 'message_id' => $message_id];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Get all messages for admin
     */
    public function getAdminMessages($admin_id, $filter = 'all') {
        $query = "SELECT m.*, 
                  u.first_name, u.last_name, u.email
                  FROM messages_tbl m
                  LEFT JOIN users_tbl u ON m.sender_id = u.user_id
                  WHERE m.recipient_id = ?";
        
        $params = [$admin_id];
        $types = "i";
        
        if ($filter === 'unread') {
            $query .= " AND m.status = 'unread'";
        } elseif ($filter === 'replied') {
            $query .= " AND m.status = 'replied'";
        }
        
        $query .= " ORDER BY m.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get single message with all replies
     */
    public function getMessageWithReplies($message_id) {
        // Get main message
        $msg_stmt = $this->db->prepare("
            SELECT m.*, u.first_name, u.last_name, u.email
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.sender_id = u.user_id
            WHERE m.message_id = ?
        ");
        $msg_stmt->bind_param("i", $message_id);
        $msg_stmt->execute();
        $message = $msg_stmt->get_result()->fetch_assoc();
        
        // Get all replies
        $replies_stmt = $this->db->prepare("
            SELECT r.*, u.first_name, u.last_name, u.email, u.role
            FROM message_replies_tbl r
            LEFT JOIN users_tbl u ON r.sender_id = u.user_id
            WHERE r.message_id = ?
            ORDER BY r.created_at ASC
        ");
        $replies_stmt->bind_param("i", $message_id);
        $replies_stmt->execute();
        $replies = $replies_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return [
            'message' => $message,
            'replies' => $replies
        ];
    }
    
    /**
     * Reply to message
     */
    public function replyToMessage($message_id, $sender_id, $reply_text) {
        // Insert reply
        $stmt = $this->db->prepare("
            INSERT INTO message_replies_tbl 
            (message_id, sender_id, reply_text)
            VALUES (?, ?, ?)
        ");
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        $stmt->bind_param("iis", $message_id, $sender_id, $reply_text);
        
        if ($stmt->execute()) {
            // Update original message status to 'replied'
            $update = $this->db->prepare("UPDATE messages_tbl SET status = 'replied' WHERE message_id = ?");
            $update->bind_param("i", $message_id);
            $update->execute();
            
            return ['success' => true, 'reply_id' => $this->db->insert_id];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Mark message as read
     */
    public function markAsRead($message_id) {
        $stmt = $this->db->prepare("UPDATE messages_tbl SET status = 'read' WHERE message_id = ? AND status = 'unread'");
        $stmt->bind_param("i", $message_id);
        return $stmt->execute();
    }
    
    /**
     * Get user's sent messages (for user to see their conversation)
     */
    public function getUserMessages($user_id) {
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   u.first_name, u.last_name
                   FROM messages_tbl m
                   LEFT JOIN users_tbl u ON m.recipient_id = u.user_id
                   WHERE m.sender_id = ?
                   ORDER BY m.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get unread message count for admin
     */
    public function getUnreadCount($admin_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM messages_tbl WHERE recipient_id = ? AND status = 'unread'");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
}

?>

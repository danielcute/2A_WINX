<?php
require_once ROOT_PATH . '/config/database.php';

class Message {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $senderId = (int)($data['sender_id'] ?? $data['user_id']);
        $recipientId = (int)($data['recipient_id'] ?? 1);
        $content = $this->db->real_escape_string($data['content']);
        $status = $data['status'] ?? 'unread';
        
        $stmt = $this->db->prepare("INSERT INTO messages_tbl (sender_id, recipient_id, content, status, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiss", $senderId, $recipientId, $content, $status);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        error_log("Message create error: " . $stmt->error);
        return false;
    }
    
    public function getUserMessages($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("
            SELECT m.*, 
            CASE WHEN m.sender_id != ? THEN 'Admin' ELSE u.first_name END as sender_name
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.sender_id = u.user_id
            WHERE m.sender_id = ? OR m.recipient_id = ?
            ORDER BY m.timestamp ASC
        ");
        $stmt->bind_param("iii", $userId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }
    
    public function getAllConversations() {
        $adminId = 1;
        $stmt = $this->db->prepare("
            SELECT 
                m.sender_id,
                CONCAT(u.first_name, ' ', u.last_name) as user_name,
                u.email,
                u.phone,
                COUNT(*) as total_messages,
                (SELECT content FROM messages_tbl WHERE sender_id = m.sender_id AND recipient_id = ? ORDER BY timestamp DESC LIMIT 1) as last_message,
                MAX(m.timestamp) as last_message_time,
                COUNT(CASE WHEN m.status = 'unread' AND m.recipient_id = ? THEN 1 END) as unread_count
            FROM messages_tbl m
            JOIN users_tbl u ON m.sender_id = u.user_id
            WHERE m.recipient_id = ?
            GROUP BY m.sender_id
            ORDER BY last_message_time DESC
        ");
        $stmt->bind_param("iii", $adminId, $adminId, $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        return $conversations;
    }
    
    public function getConversation($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("
            SELECT m.message_id, m.sender_id, m.recipient_id, m.content, m.status, m.timestamp,
            CASE WHEN m.sender_id != ? THEN 'Admin' ELSE u.first_name END as sender_name
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.sender_id = u.user_id
            WHERE m.sender_id = ? OR m.recipient_id = ?
            ORDER BY m.timestamp ASC
        ");
        $stmt->bind_param("iii", $userId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            error_log("Message getConversation Error: " . $this->db->error);
            return [];
        }
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $row['is_admin_reply'] = ($row['sender_id'] != $userId) ? 1 : 0;
            $messages[] = $row;
        }
        return $messages;
    }
    
    public function markAsRead($messageId) {
        $messageId = (int)$messageId;
        $stmt = $this->db->prepare("UPDATE messages_tbl SET status = 'read' WHERE message_id = ?");
        $stmt->bind_param("i", $messageId);
        return $stmt->execute();
    }
    
    public function markUserMessagesAsRead($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("UPDATE messages_tbl SET status = 'read' WHERE recipient_id = ? AND status = 'unread'");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    public function getUnreadCount($userId = null) {
        if ($userId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM messages_tbl WHERE recipient_id = ? AND status = 'unread'");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $adminId = 1;
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM messages_tbl WHERE recipient_id = ? AND status = 'unread'");
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
?>

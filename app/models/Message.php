<?php
require_once ROOT_PATH . '/config/database.php';

class Message {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $userId = (int)$data['user_id'];
        $adminId = isset($data['admin_id']) ? (int)$data['admin_id'] : 'NULL';
        $parentId = isset($data['parent_id']) ? (int)$data['parent_id'] : 'NULL';
        $content = $this->db->real_escape_string($data['content']);
        $isAdminReply = isset($data['is_admin_reply']) ? (int)$data['is_admin_reply'] : 0;
        $status = $data['status'] ?? 'unread';
        
        $sql = "INSERT INTO tbl_messages (user_id, admin_id, parent_id, content, is_admin_reply, status) 
                VALUES ($userId, $adminId, $parentId, '$content', $isAdminReply, '$status')";
        
        if ($this->db->query($sql)) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    public function getUserMessages($userId) {
        $userId = (int)$userId;
        $sql = "SELECT m.*, 
                CASE WHEN m.is_admin_reply = 1 THEN 'Admin' ELSE u.first_name END as sender_name
                FROM tbl_messages m
                LEFT JOIN tbl_users u ON m.user_id = u.user_id
                WHERE m.user_id = $userId
                ORDER BY m.timestamp ASC";
        
        $result = $this->db->query($sql);
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }
    
    public function getAllConversations() {
        $sql = "SELECT 
                    m.user_id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    COUNT(CASE WHEN m.status = 'unread' AND m.is_admin_reply = 0 THEN 1 END) as unread_count,
                    MAX(m.timestamp) as last_message
                FROM tbl_messages m
                JOIN tbl_users u ON m.user_id = u.user_id
                GROUP BY m.user_id
                ORDER BY last_message DESC";
        
        $result = $this->db->query($sql);
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        return $conversations;
    }
    
    public function getConversation($userId) {
        $userId = (int)$userId;
        $sql = "SELECT m.*, 
                CASE WHEN m.is_admin_reply = 1 THEN 'Admin' ELSE u.first_name END as sender_name
                FROM tbl_messages m
                LEFT JOIN tbl_users u ON m.user_id = u.user_id
                WHERE m.user_id = $userId
                ORDER BY m.timestamp ASC";
        
        $result = $this->db->query($sql);
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }
    
    public function markAsRead($messageId) {
        $messageId = (int)$messageId;
        return $this->db->query("UPDATE tbl_messages SET status = 'read' WHERE message_id = $messageId");
    }
    
    public function markUserMessagesAsRead($userId) {
        $userId = (int)$userId;
        return $this->db->query("UPDATE tbl_messages SET status = 'read' WHERE user_id = $userId AND is_admin_reply = 0");
    }
    
    public function getUnreadCount() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM tbl_messages WHERE status = 'unread' AND is_admin_reply = 0");
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
?>
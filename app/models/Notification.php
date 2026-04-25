<?php
/**
 * Notification Model
 * Handles notification creation, retrieval, and management
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a notification
     * @param array $data - notification data
     * @return int|false - notification_id on success, false on failure
     */
    public function create($data) {
        $userId = (int)$data['user_id'];
        $type = $this->db->real_escape_string($data['type'] ?? 'system_update');
        $title = isset($data['title']) ? $this->db->real_escape_string($data['title']) : null;
        $message = isset($data['message']) ? $this->db->real_escape_string($data['message']) : null;
        $relatedType = isset($data['related_type']) ? $this->db->real_escape_string($data['related_type']) : null;
        $relatedId = isset($data['related_id']) ? (int)$data['related_id'] : null;
        
        $stmt = $this->db->prepare("INSERT INTO notifications_tbl 
        (user_id, type, title, message, related_type, related_id) 
        VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Notification create prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("issssi", $userId, $type, $title, $message, $relatedType, $relatedId);
        
        if ($stmt->execute()) {
            $result = $this->db->insert_id;
            $stmt->close();
            return $result;
        } else {
            error_log("Notification create execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    /**
     * Create notifications for multiple users
     * @param array $userIds - array of user IDs
     * @param array $data - notification data (type, title, message, etc.)
     * @return int - count of notifications created
     */
    public function createForMultipleUsers($userIds, $data) {
        $count = 0;
        foreach ($userIds as $userId) {
            $notificationData = array_merge($data, ['user_id' => $userId]);
            if ($this->create($notificationData)) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Get unread notifications for a user
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUnreadNotifications($userId, $limit = 10) {
        $userId = (int)$userId;
        $limit = (int)$limit;
        
        $sql = "SELECT * FROM notifications_tbl 
                WHERE user_id = $userId AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT $limit";
        
        $result = $this->db->query($sql);
        $notifications = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
        }
        
        return $notifications;
    }
    
    /**
     * Get all notifications for a user
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUserNotifications($userId, $limit = 20, $offset = 0) {
        $userId = (int)$userId;
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT * FROM notifications_tbl 
                WHERE user_id = $userId 
                ORDER BY created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $result = $this->db->query($sql);
        $notifications = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
        }
        
        return $notifications;
    }
    
    /**
     * Get unread notification count for a user
     * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId) {
        $userId = (int)$userId;
        
        $sql = "SELECT COUNT(*) as count FROM notifications_tbl 
                WHERE user_id = $userId AND is_read = 0";
        
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['count'] ?? 0;
    }
    
    /**
     * Mark notification as read
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead($notificationId) {
        $notificationId = (int)$notificationId;
        
        $stmt = $this->db->prepare("UPDATE notifications_tbl 
                                    SET is_read = 1, read_at = NOW() 
                                    WHERE notification_id = ?");
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $notificationId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Mark all notifications as read for a user
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead($userId) {
        $userId = (int)$userId;
        
        $stmt = $this->db->prepare("UPDATE notifications_tbl 
                                    SET is_read = 1, read_at = NOW() 
                                    WHERE user_id = ? AND is_read = 0");
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $userId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Delete a notification
     * @param int $notificationId
     * @return bool
     */
    public function delete($notificationId) {
        $notificationId = (int)$notificationId;
        
        $stmt = $this->db->prepare("DELETE FROM notifications_tbl WHERE notification_id = ?");
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $notificationId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Delete old read notifications (older than X days)
     * @param int $days
     * @return int - count of deleted notifications
     */
    public function deleteOldReadNotifications($days = 30) {
        $days = (int)$days;
        
        $stmt = $this->db->prepare("DELETE FROM notifications_tbl 
                                    WHERE is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        
        if (!$stmt) {
            return 0;
        }
        
        $stmt->bind_param("i", $days);
        $stmt->execute();
        
        $deletedCount = $this->db->affected_rows;
        $stmt->close();
        
        return $deletedCount;
    }
    
    /**
     * Get notifications by type
     * @param int $userId
     * @param string $type
     * @param int $limit
     * @return array
     */
    public function getNotificationsByType($userId, $type, $limit = 10) {
        $userId = (int)$userId;
        $type = $this->db->real_escape_string($type);
        $limit = (int)$limit;
        
        $sql = "SELECT * FROM notifications_tbl 
                WHERE user_id = $userId AND type = '$type' 
                ORDER BY created_at DESC 
                LIMIT $limit";
        
        $result = $this->db->query($sql);
        $notifications = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
        }
        
        return $notifications;
    }
}

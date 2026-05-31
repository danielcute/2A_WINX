<?php
/**
 * Feedback Model
 * Handles user feedback and admin replies
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Notification.php';

class Feedback {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all feedback with user details
     */
    public function getAll($status = null) {
        $sql = "SELECT f.*, u.first_name, u.last_name, u.email,
                COUNT(fr.reply_id) as reply_count
                FROM feedback_tbl f
                LEFT JOIN users_tbl u ON f.user_id = u.user_id
                LEFT JOIN feedback_replies_tbl fr ON f.feedback_id = fr.feedback_id";
        
        if ($status) {
            $status = $this->db->real_escape_string($status);
            $sql .= " WHERE f.status = '$status'";
        }
        
        $sql .= " GROUP BY f.feedback_id ORDER BY f.created_at DESC";
        
        $result = $this->db->query($sql);
        $feedbacks = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $feedbacks[] = $row;
            }
        }
        
        return $feedbacks;
    }
    
    /**
     * Get feedback by ID with replies
     */
    public function findById($id) {
        $id = (int)$id;
        $sql = "SELECT f.*, u.first_name, u.last_name, u.email
                FROM feedback_tbl f
                LEFT JOIN users_tbl u ON f.user_id = u.user_id
                WHERE f.feedback_id = $id";
        
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }
    
    /**
     * Get feedback replies
     */
    public function getReplies($feedbackId) {
        $feedbackId = (int)$feedbackId;
        $sql = "SELECT fr.*, 
                CASE 
                    WHEN fr.reply_type = 'admin' THEN CONCAT(u.first_name, ' ', u.last_name, ' (Admin)')
                    ELSE CONCAT(u.first_name, ' ', u.last_name)
                END as sender_name,
                u.image as sender_avatar
                FROM feedback_replies_tbl fr
                LEFT JOIN users_tbl u ON (fr.admin_id = u.user_id OR fr.user_id = u.user_id)
                WHERE fr.feedback_id = $feedbackId
                ORDER BY fr.created_at ASC";
        
        $result = $this->db->query($sql);
        $replies = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $replies[] = $row;
            }
        }
        
        return $replies;
    }
    
    /**
     * Create new feedback
     */
    public function create($data) {
        $userId = (int)$data['user_id'];
        $subject = $this->db->real_escape_string($data['subject']);
        $message = $this->db->real_escape_string($data['message']);
        $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
        
        $sql = "INSERT INTO feedback_tbl (user_id, subject, message, rating, status) 
                VALUES ($userId, '$subject', '$message', $rating, 'open')";
        
        if ($this->db->query($sql)) {
            return $this->db->insert_id;
        }
        
        error_log("Feedback create error: " . $this->db->error);
        return false;
    }
    
    /**
     * Update feedback status
     */
    public function updateStatus($id, $status) {
        $id = (int)$id;
        $status = $this->db->real_escape_string($status);
        
        $sql = "UPDATE feedback_tbl SET status = '$status', updated_at = CURRENT_TIMESTAMP 
                WHERE feedback_id = $id";
        
        return $this->db->query($sql);
    }
    
    /**
     * Add admin reply to feedback
     */
    public function addAdminReply($feedbackId, $adminId, $message) {
        $feedbackId = (int)$feedbackId;
        $adminId = (int)$adminId;
        $message = $this->db->real_escape_string($message);
        
        $sql = "INSERT INTO feedback_replies_tbl (feedback_id, admin_id, message, reply_type) 
                VALUES ($feedbackId, $adminId, '$message', 'admin')";
        
        if ($this->db->query($sql)) {
            // Update feedback status to in_progress
            $this->updateStatus($feedbackId, 'in_progress');
            
            // Create notification for the feedback submitter
            try {
                $feedback = $this->findById($feedbackId);
                if ($feedback) {
                    $notificationModel = new Notification();
                    $notificationModel->create([
                        'user_id' => $feedback['user_id'],
                        'type' => 'feedback_reply',
                        'title' => 'New Reply to Your Feedback',
                        'message' => 'Admin has replied to your feedback: "' . htmlspecialchars(substr($feedback['subject'], 0, 50)) . '"',
                        'related_type' => 'feedback',
                        'related_id' => $feedbackId
                    ]);
                }
            } catch (Exception $e) {
                error_log("Failed to create notification for feedback reply: " . $e->getMessage());
            }
            
            return $this->db->insert_id;
        }
        
        error_log("Admin reply error: " . $this->db->error);
        return false;
    }
    
    /**
     * Add user reply to feedback
     */
    public function addUserReply($feedbackId, $userId, $message) {
        $feedbackId = (int)$feedbackId;
        $userId = (int)$userId;
        $message = $this->db->real_escape_string($message);
        
        $sql = "INSERT INTO feedback_replies_tbl (feedback_id, user_id, message, reply_type) 
                VALUES ($feedbackId, $userId, '$message', 'user')";
        
        if ($this->db->query($sql)) {
            return $this->db->insert_id;
        }
        
        error_log("User reply error: " . $this->db->error);
        return false;
    }
    
    /**
     * Delete feedback
     */
    public function delete($id) {
        $id = (int)$id;
        
        // Delete replies first
        $this->db->query("DELETE FROM feedback_replies_tbl WHERE feedback_id = $id");
        
        // Delete feedback
        return $this->db->query("DELETE FROM feedback_tbl WHERE feedback_id = $id");
    }
    
    /**
     * Get user's feedback
     */
    public function getUserFeedback($userId) {
        $userId = (int)$userId;
        $sql = "SELECT f.*, 
                COUNT(fr.reply_id) as reply_count
                FROM feedback_tbl f
                LEFT JOIN feedback_replies_tbl fr ON f.feedback_id = fr.feedback_id
                WHERE f.user_id = $userId
                GROUP BY f.feedback_id
                ORDER BY f.created_at DESC";
        
        $result = $this->db->query($sql);
        $feedbacks = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $feedbacks[] = $row;
            }
        }
        
        return $feedbacks;
    }
    
    /**
     * Get feedback statistics
     */
    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                FROM feedback_tbl";
        
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }
}
?>

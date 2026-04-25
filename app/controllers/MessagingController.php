<?php
/**
 * Messaging Controller
 * Handles real user-to-admin messaging system
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Notification.php';

class MessagingController {
    private $db;
    private $admin_id_cache = null;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get the first admin user ID (cached)
     */
    private function getAdminId() {
        if ($this->admin_id_cache !== null) {
            return $this->admin_id_cache;
        }
        $stmt = $this->db->prepare("SELECT user_id FROM users_tbl WHERE role = 'admin' LIMIT 1");
        if (!$stmt) return null;
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();
        $this->admin_id_cache = $admin ? $admin['user_id'] : null;
        return $this->admin_id_cache;
    }
    
    /**
     * Send message from user to admin
     */
    public function sendMessage($sender_id, $subject, $message_text, $message_type = 'inquiry') {
        $recipient_id = $this->getAdminId();
        if (!$recipient_id) {
            return ['success' => false, 'error' => 'No admin found'];
        }
        
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
            $stmt->close();
            return ['success' => true, 'message_id' => $message_id];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'error' => $error];
        }
    }
    
    /**
     * Admin sends a new message to a user (not a reply)
     */
    public function sendAdminMessage($admin_id, $user_id, $subject, $message_text, $message_type = 'inquiry') {
        // Verify admin role
        $check = $this->db->prepare("SELECT role FROM users_tbl WHERE user_id = ?");
        $check->bind_param("i", $admin_id);
        $check->execute();
        $res = $check->get_result();
        $admin = $res->fetch_assoc();
        $check->close();
        
        if (!$admin || $admin['role'] !== 'admin') {
            return ['success' => false, 'error' => 'Only admins can use this method'];
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO messages_tbl 
            (sender_id, recipient_id, subject, message_text, message_type, status) 
            VALUES (?, ?, ?, ?, ?, 'unread')
        ");
        if (!$stmt) return ['success' => false, 'error' => $this->db->error];
        
        $stmt->bind_param("iisss", $admin_id, $user_id, $subject, $message_text, $message_type);
        if ($stmt->execute()) {
            $id = $this->db->insert_id;
            $stmt->close();
            return ['success' => true, 'message_id' => $id];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'error' => $error];
        }
    }
    
    /**
     * Get all messages for admin
     */
    public function getAdminMessages($admin_id, $filter = 'all') {
        $sql = "SELECT m.*, 
                u.user_id, u.first_name, u.last_name, u.email
                FROM messages_tbl m
                LEFT JOIN users_tbl u ON m.sender_id = u.user_id
                WHERE m.recipient_id = ?";
        
        if ($filter === 'unread') {
            $sql .= " AND m.status = 'unread'";
        } elseif ($filter === 'replied') {
            $sql .= " AND m.status = 'replied'";
        } elseif ($filter === 'archived') {
            $sql .= " AND m.status = 'archived'";
        } elseif ($filter === 'spam') {
            $sql .= " AND m.status = 'spam'";
        }
        
        $sql .= " ORDER BY m.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("MessagingController::getAdminMessages() prepare failed: " . $this->db->error);
            return [];
        }
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $messages;
    }
    
    /**
     * Get single message with all replies
     */
    public function getMessageWithReplies($message_id) {
        // Get main message
        $msg_stmt = $this->db->prepare("
            SELECT m.*, u.user_id, u.first_name, u.last_name, u.email
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.sender_id = u.user_id
            WHERE m.message_id = ?
        ");
        if (!$msg_stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return ['message' => null, 'replies' => []];
        }
        $msg_stmt->bind_param("i", $message_id);
        $msg_stmt->execute();
        $message = $msg_stmt->get_result()->fetch_assoc();
        $msg_stmt->close();
        
        if (!$message) {
            return ['message' => null, 'replies' => []];
        }
        
        // Get all replies
        $replies_stmt = $this->db->prepare("
            SELECT r.*, u.user_id, u.first_name, u.last_name, u.email, u.role
            FROM message_replies_tbl r
            LEFT JOIN users_tbl u ON r.sender_id = u.user_id
            WHERE r.message_id = ?
            ORDER BY r.created_at ASC
        ");
        if (!$replies_stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return ['message' => $message, 'replies' => []];
        }
        $replies_stmt->bind_param("i", $message_id);
        $replies_stmt->execute();
        $replies = $replies_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $replies_stmt->close();
        
        return [
            'message' => $message,
            'replies' => $replies
        ];
    }
    
    /**
     * Get a single conversation thread (message + all replies) by message ID
     * Used for admin or user detail view
     */
    public function getConversationByMessageId($message_id, $user_id = null, $is_admin = false) {
        // Get main message with sender/recipient names
        $msg_stmt = $this->db->prepare("
            SELECT m.*, 
                   sender.first_name as sender_first, sender.last_name as sender_last, sender.role as sender_role,
                   recipient.first_name as recipient_first, recipient.last_name as recipient_last
            FROM messages_tbl m
            LEFT JOIN users_tbl sender ON m.sender_id = sender.user_id
            LEFT JOIN users_tbl recipient ON m.recipient_id = recipient.user_id
            WHERE m.message_id = ?
        ");
        $msg_stmt->bind_param("i", $message_id);
        $msg_stmt->execute();
        $message = $msg_stmt->get_result()->fetch_assoc();
        $msg_stmt->close();
        
        if (!$message) return null;
        
        // Permission check
        if (!$is_admin && $user_id) {
            if ($message['sender_id'] != $user_id && $message['recipient_id'] != $user_id) {
                return null; // user not involved in this conversation
            }
        }
        
        // Get replies
        $reply_stmt = $this->db->prepare("
            SELECT r.*, u.first_name, u.last_name, u.role
            FROM message_replies_tbl r
            LEFT JOIN users_tbl u ON r.sender_id = u.user_id
            WHERE r.message_id = ?
            ORDER BY r.created_at ASC
        ");
        $reply_stmt->bind_param("i", $message_id);
        $reply_stmt->execute();
        $replies = $reply_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $reply_stmt->close();
        
        $message['replies'] = $replies;
        return $message;
    }
    
    /**
     * Get full conversation for a user (both sent messages and admin replies)
     * Returns an array of "threads" – each thread is a message with its replies
     */
    public function getUserFullConversation($user_id) {
        // Get all messages sent by this user
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   u.first_name, u.last_name, u.email
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.recipient_id = u.user_id
            WHERE m.sender_id = ?
            ORDER BY m.created_at DESC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // For each message, attach its replies
        foreach ($messages as &$msg) {
            $reply_stmt = $this->db->prepare("
                SELECT r.*, u.first_name, u.last_name, u.role
                FROM message_replies_tbl r
                LEFT JOIN users_tbl u ON r.sender_id = u.user_id
                WHERE r.message_id = ?
                ORDER BY r.created_at ASC
            ");
            $reply_stmt->bind_param("i", $msg['message_id']);
            $reply_stmt->execute();
            $msg['replies'] = $reply_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $reply_stmt->close();
        }
        
        return $messages;
    }
    
    /**
     * Reply to message (admin only)
     */
    public function replyToMessage($message_id, $sender_id, $reply_text) {
        // First, check if message exists
        $check_stmt = $this->db->prepare("SELECT message_id, sender_id FROM messages_tbl WHERE message_id = ?");
        if (!$check_stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        $check_stmt->bind_param("i", $message_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $message = $result->fetch_assoc();
        $check_stmt->close();
        
        if (!$message) {
            return ['success' => false, 'error' => 'Message not found'];
        }
        
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
            $reply_id = $this->db->insert_id;
            $stmt->close();
            
            // Update original message status to 'replied'
            $update_stmt = $this->db->prepare("UPDATE messages_tbl SET status = 'replied' WHERE message_id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("i", $message_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            // Create notification for the original message sender
            try {
                $notificationModel = new Notification();
                $senderUserId = $message['sender_id'];
                
                // Get sender's name and message subject for notification
                $senderStmt = $this->db->prepare("SELECT first_name, last_name FROM users_tbl WHERE user_id = ?");
                if ($senderStmt) {
                    $senderStmt->bind_param("i", $sender_id);
                    $senderStmt->execute();
                    $senderResult = $senderStmt->get_result();
                    $senderInfo = $senderResult->fetch_assoc();
                    $senderStmt->close();
                    
                    $senderName = $senderInfo ? ($senderInfo['first_name'] . ' ' . $senderInfo['last_name']) : 'Admin';
                    
                    $notificationModel->create([
                        'user_id' => $senderUserId,
                        'type' => 'message_reply',
                        'title' => 'New Reply from ' . $senderName,
                        'message' => 'You have a new reply to your message',
                        'related_type' => 'message',
                        'related_id' => $message_id
                    ]);
                }
            } catch (Exception $e) {
                error_log("Failed to create notification for message reply: " . $e->getMessage());
            }
            
            return ['success' => true, 'reply_id' => $reply_id];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'error' => $error];
        }
    }
    
    /**
     * Mark message as read
     */
    public function markAsRead($message_id) {
        $stmt = $this->db->prepare("UPDATE messages_tbl SET status = 'read' WHERE message_id = ? AND status = 'unread'");
        if (!$stmt) return false;
        $stmt->bind_param("i", $message_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    /**
     * Get user's sent messages (basic, without replies)
     */
    public function getUserMessages($user_id) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.user_id, u.first_name, u.last_name
            FROM messages_tbl m
            LEFT JOIN users_tbl u ON m.recipient_id = u.user_id
            WHERE m.sender_id = ?
            ORDER BY m.created_at DESC
        ");
        if (!$stmt) {
            error_log("MessagingController::getUserMessages() prepare failed: " . $this->db->error);
            return [];
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $messages;
    }
    
    /**
     * Get unread message count for admin
     */
    public function getUnreadCount($admin_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM messages_tbl WHERE recipient_id = ? AND status = 'unread'");
        if (!$stmt) {
            error_log("MessagingController::getUnreadCount() failed: " . $this->db->error);
            return 0;
        }
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'] ?? 0;
    }
    
    /**
     * Delete message (admin or owner)
     */
    public function deleteMessage($message_id, $user_id, $is_admin = false) {
        if ($is_admin) {
            $stmt = $this->db->prepare("DELETE FROM messages_tbl WHERE message_id = ?");
        } else {
            $stmt = $this->db->prepare("DELETE FROM messages_tbl WHERE message_id = ? AND sender_id = ?");
        }
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        if ($is_admin) {
            $stmt->bind_param("i", $message_id);
        } else {
            $stmt->bind_param("ii", $message_id, $user_id);
        }
        $result = $stmt->execute();
        $stmt->close();
        return ['success' => $result, 'error' => $result ? null : 'Failed to delete message'];
    }
    
    /**
     * Get all users (for admin to send messages to)
     */
    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT user_id, first_name, last_name, email FROM users_tbl ORDER BY first_name ASC, last_name ASC");
        if (!$stmt) {
            error_log("MessagingController::getAllUsers() prepare failed: " . $this->db->error);
            return [];
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $users;
    }
    
    /**
     * Archive message (soft delete)
     */
    public function archiveMessage($message_id) {
        $stmt = $this->db->prepare("UPDATE messages_tbl SET status = 'archived' WHERE message_id = ?");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        $stmt->bind_param("i", $message_id);
        $result = $stmt->execute();
        $stmt->close();
        return ['success' => $result, 'error' => $result ? null : 'Failed to archive message'];
    }
    
    /**
     * Mark message as spam
     */
    public function markAsSpam($message_id) {
        $stmt = $this->db->prepare("UPDATE messages_tbl SET status = 'spam' WHERE message_id = ?");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        $stmt->bind_param("i", $message_id);
        $result = $stmt->execute();
        $stmt->close();
        return ['success' => $result, 'error' => $result ? null : 'Failed to mark as spam'];
    }
    
    /**
     * Restore message from archive
     */
    public function restoreMessage($message_id) {
        $stmt = $this->db->prepare("UPDATE messages_tbl SET status = 'unread' WHERE message_id = ?");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        $stmt->bind_param("i", $message_id);
        $result = $stmt->execute();
        $stmt->close();
        return ['success' => $result, 'error' => $result ? null : 'Failed to restore message'];
    }
}
?>
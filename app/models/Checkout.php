<?php
/**
 * Checkout Model
 * Handles payment checkout operations
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';

class Checkout {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $userId = (int)$data['user_id'];
        $planId = isset($data['plan_id']) ? (int)$data['plan_id'] : null;
        $totalAmount = (float)$data['total_amount'];
        $paymentMethod = $data['payment_method'] ?? null;
        $depositAmount = isset($data['deposit_amount']) ? (float)$data['deposit_amount'] : null;
        $status = $data['status'] ?? 'pending';
        $transactionId = $data['transaction_id'] ?? null;
        
        $stmt = $this->db->prepare("
            INSERT INTO checkout_tbl 
            (user_id, plan_id, total_amount, payment_method, deposit_amount, status, transaction_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("Checkout create prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("iiddsss", $userId, $planId, $totalAmount, $paymentMethod, $depositAmount, $status, $transactionId);
        
        if ($stmt->execute()) {
            $result = $this->db->insert_id;
            $stmt->close();
            return $result;
        } else {
            error_log("Checkout create execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    public function getByCheckoutId($checkoutId) {
        $checkoutId = (int)$checkoutId;
        $stmt = $this->db->prepare("SELECT * FROM checkout_tbl WHERE checkout_id = ?");
        
        if (!$stmt) {
            error_log("getByCheckoutId prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("i", $checkoutId);
        $stmt->execute();
        $result = $stmt->get_result();
        $checkout = $result->fetch_assoc();
        $stmt->close();
        return $checkout;
    }
    
    public function getByPlanId($planId) {
        $planId = (int)$planId;
        $stmt = $this->db->prepare("SELECT * FROM checkout_tbl WHERE plan_id = ? ORDER BY created_at DESC");
        
        if (!$stmt) {
            error_log("getByPlanId prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        $checkout = $result->fetch_assoc();
        $stmt->close();
        return $checkout;
    }
    
    public function updateStatus($checkoutId, $status, $transactionId = null) {
        $checkoutId = (int)$checkoutId;
        
        if ($transactionId) {
            $stmt = $this->db->prepare("
                UPDATE checkout_tbl 
                SET status = ?, transaction_id = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE checkout_id = ?
            ");
            
            if (!$stmt) {
                error_log("updateStatus prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("ssi", $status, $transactionId, $checkoutId);
        } else {
            $stmt = $this->db->prepare("
                UPDATE checkout_tbl 
                SET status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE checkout_id = ?
            ");
            
            if (!$stmt) {
                error_log("updateStatus prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("si", $status, $checkoutId);
        }
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function getByUserId($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("
            SELECT c.*, p.event_name, p.event_date 
            FROM checkout_tbl c
            LEFT JOIN plans_tbl p ON c.plan_id = p.plan_id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        
        if (!$stmt) {
            error_log("getByUserId prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $checkouts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $checkouts;
    }
    
    public function delete($checkoutId) {
        $checkoutId = (int)$checkoutId;
        $stmt = $this->db->prepare("DELETE FROM checkout_tbl WHERE checkout_id = ?");
        
        if (!$stmt) {
            error_log("delete prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $checkoutId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>
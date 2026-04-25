in the<?php
/**
 * Booking Model
 * Handles booking/checkout operations
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';

class Booking {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        // Fetch all plans as bookings (from plans_tbl)
        $sql = "SELECT p.plan_id as checkout_id, p.user_id, p.event_name, p.event_date, p.venue, 
                p.guest_count, p.total_price as total_amount, p.status, p.theme, p.event_time,
                u.first_name, u.last_name, u.email, u.phone
                FROM plans_tbl p
                LEFT JOIN users_tbl u ON p.user_id = u.user_id
                ORDER BY p.plan_id DESC";
        
        $result = $this->db->query($sql);
        
        if (!$result) {
            error_log("Booking::getAll() error: " . $this->db->error);
            return [];
        }
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }
    
    public function findById($id) {
        $id = (int)$id;
        $result = $this->db->query("SELECT p.*, u.first_name, u.last_name, u.email 
                                    FROM plans_tbl p 
                                    LEFT JOIN users_tbl u ON p.user_id = u.user_id 
                                    WHERE p.plan_id = $id");
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $userId = (int)$data['user_id'];
        $planId = isset($data['plan_id']) ? (int)$data['plan_id'] : 'NULL';
        $totalAmount = (float)$data['total_amount'];
        $paymentMethod = isset($data['payment_method']) ? "'" . $this->db->real_escape_string($data['payment_method']) . "'" : 'NULL';
        $depositAmount = isset($data['deposit_amount']) ? (float)$data['deposit_amount'] : 'NULL';
        $status = $data['status'] ?? 'pending';
        $transactionId = isset($data['transaction_id']) ? "'" . $this->db->real_escape_string($data['transaction_id']) . "'" : 'NULL';
        
        $sql = "INSERT INTO checkout_tbl (user_id, plan_id, total_amount, payment_method, deposit_amount, status, transaction_id) 
                VALUES ($userId, $planId, $totalAmount, $paymentMethod, $depositAmount, '$status', $transactionId)";
        
        if ($this->db->query($sql)) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    public function canUpdateStatus($planId) {
        $planId = (int)$planId;
        $result = $this->db->query("SELECT status FROM plans_tbl WHERE plan_id = $planId");
        $booking = $result->fetch_assoc();
        
        if (!$booking) {
            return ['can_update' => false, 'reason' => 'Booking not found'];
        }
        
        // Cannot update if status is 'completed'
        if ($booking['status'] === 'completed') {
            return ['can_update' => false, 'reason' => 'Cannot modify completed bookings'];
        }
        
        return ['can_update' => true];
    }
    
    public function updateStatus($planId, $status, $transactionId = null) {
        $planId = (int)$planId;
        $status = $this->db->real_escape_string($status);
        
        // Check if booking can be updated
        $canUpdate = $this->canUpdateStatus($planId);
        if (!$canUpdate['can_update']) {
            return false;
        }
        
        $sql = "UPDATE plans_tbl SET status = '$status'";
        if ($transactionId) {
            $sql .= ", transaction_id = '" . $this->db->real_escape_string($transactionId) . "'";
        }
        $sql .= " WHERE plan_id = $planId";
        
        return $this->db->query($sql);
    }
    
    public function canDelete($planId) {
        $planId = (int)$planId;
        $result = $this->db->query("SELECT status FROM plans_tbl WHERE plan_id = $planId");
        $booking = $result->fetch_assoc();
        
        if (!$booking) {
            return ['can_delete' => false, 'reason' => 'Booking not found'];
        }
        
        // Cannot delete if status is 'completed'
        if ($booking['status'] === 'completed') {
            return ['can_delete' => false, 'reason' => 'Cannot delete completed bookings'];
        }
        
        return ['can_delete' => true];
    }
    
    public function delete($id) {
        $id = (int)$id;
        
        // Check if booking can be deleted
        $canDelete = $this->canDelete($id);
        if (!$canDelete['can_delete']) {
            return false;
        }
        
        return $this->db->query("DELETE FROM plans_tbl WHERE plan_id = $id");
    }
    
    public function getStats() {
        $result = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(total_price) as total_revenue
            FROM plans_tbl
        ");
        return $result->fetch_assoc();
    }
}
?>
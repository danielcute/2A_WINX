<?php
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
        $sql = "SELECT b.*, u.first_name, u.last_name, u.email, u.phone, p.name as package_name
                FROM checkout_tbl b
                LEFT JOIN users_tbl u ON b.user_id = u.user_id
                LEFT JOIN packages_tbl p ON b.package_id = p.package_id
                ORDER BY b.date DESC";
        
        $result = $this->db->query($sql);
        if (!$result) {
            error_log('Booking getAll Error: ' . $this->db->error);
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
        $result = $this->db->query("SELECT * FROM checkout_tbl WHERE checkout_id = $id");
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
    
    public function updateStatus($checkoutId, $status, $transactionId = null) {
        $checkoutId = (int)$checkoutId;
        $status = $this->db->real_escape_string($status);
        
        $sql = "UPDATE checkout_tbl SET status = '$status'";
        if ($transactionId) {
            $sql .= ", transaction_id = '" . $this->db->real_escape_string($transactionId) . "'";
        }
        $sql .= " WHERE checkout_id = $checkoutId";
        
        return $this->db->query($sql);
    }
    
    public function delete($id) {
        $id = (int)$id;
        return $this->db->query("DELETE FROM checkout_tbl WHERE checkout_id = $id");
    }
    
    public function getStats() {
        $result = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(total_amount) as total_revenue
            FROM checkout_tbl
        ");
        return $result->fetch_assoc();
    }
}
?>
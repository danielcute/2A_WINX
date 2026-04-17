<?php
require_once ROOT_PATH . '/config/database.php';

class Checkout {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $userId = (int)$data['user_id'];
        $planId = (int)$data['plan_id'];
        $totalAmount = (float)$data['total_amount'];
        $paymentMethod = isset($data['payment_method']) ? "'" . $this->db->real_escape_string($data['payment_method']) . "'" : 'NULL';
        $depositAmount = isset($data['deposit_amount']) ? (float)$data['deposit_amount'] : 'NULL';
        $status = $data['status'] ?? 'pending';
        $transactionId = isset($data['transaction_id']) ? "'" . $this->db->real_escape_string($data['transaction_id']) . "'" : 'NULL';
        
        $sql = "INSERT INTO tbl_checkout (user_id, plan_id, total_amount, payment_method, deposit_amount, status, transaction_id) 
                VALUES ($userId, $planId, $totalAmount, $paymentMethod, $depositAmount, '$status', $transactionId)";
        
        if ($this->db->query($sql)) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    public function getByPlanId($planId) {
        $planId = (int)$planId;
        $result = $this->db->query("SELECT * FROM tbl_checkout WHERE plan_id = $planId ORDER BY created_at DESC");
        return $result->fetch_assoc();
    }
    
    public function updateStatus($checkoutId, $status, $transactionId = null) {
        $checkoutId = (int)$checkoutId;
        $status = $this->db->real_escape_string($status);
        
        $sql = "UPDATE tbl_checkout SET status = '$status'";
        if ($transactionId) {
            $sql .= ", transaction_id = '" . $this->db->real_escape_string($transactionId) . "'";
            $sql .= ", payment_date = NOW()";
        }
        $sql .= " WHERE checkout_id = $checkoutId";
        
        return $this->db->query($sql);
    }
}
?>
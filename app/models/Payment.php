<?php

namespace Models;

class Payment {
    private $db;
    
    public function __construct($db = null) {
        if (!$db) {
            require_once __DIR__ . '/../config/database.php';
            $this->db = \Database::getInstance()->getConnection();
        } else {
            $this->db = $db;
        }
    }

    /**
     * Create a new payment transaction
     */
    public function createPayment($planId, $userId, $paymentType, $amount, $paymentMethod, $paymentDetails = null) {
        $stmt = $this->db->prepare("
            INSERT INTO payments_tbl (plan_id, user_id, payment_type, amount, payment_method, payment_status, payment_details)
            VALUES (?, ?, ?, ?, ?, 'pending', ?)
        ");
        
        $detailsJson = $paymentDetails ? json_encode($paymentDetails) : null;
        $stmt->bind_param("iisdsss", $planId, $userId, $paymentType, $amount, $paymentMethod, $detailsJson);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Update payment status with gateway response
     */
    public function updatePaymentStatus($paymentId, $status, $transactionId = null, $gatewayResponse = null, $referenceNumber = null) {
        $stmt = $this->db->prepare("
            UPDATE payments_tbl 
            SET payment_status = ?, 
                transaction_id = COALESCE(?, transaction_id),
                reference_number = COALESCE(?, reference_number),
                gateway_response = COALESCE(?, gateway_response),
                paid_at = IF(? = 'completed', NOW(), paid_at)
            WHERE payment_id = ?
        ");
        
        $responseJson = $gatewayResponse ? json_encode($gatewayResponse) : null;
        $stmt->bind_param("ssssssi", $status, $transactionId, $referenceNumber, $responseJson, $status, $paymentId);
        
        return $stmt->execute();
    }

    /**
     * Get payment by ID
     */
    public function getPaymentById($paymentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM payments_tbl WHERE payment_id = ?
        ");
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get all payments for a plan
     */
    public function getPaymentsByPlanId($planId) {
        $stmt = $this->db->prepare("
            SELECT * FROM payments_tbl 
            WHERE plan_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get payment summary for a plan
     */
    public function getPaymentSummary($planId) {
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_paid,
                COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed_payments,
                COUNT(CASE WHEN payment_status IN ('pending', 'processing') THEN 1 END) as pending_payments
            FROM payments_tbl 
            WHERE plan_id = ?
        ");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get all payments for a user
     */
    public function getPaymentsByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT p.*, pl.event_name, pl.event_date 
            FROM payments_tbl p
            JOIN plans_tbl pl ON p.plan_id = pl.plan_id
            WHERE p.user_id = ? 
            ORDER BY p.created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if deposit is paid for a plan
     */
    public function isDepositPaid($planId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM payments_tbl 
            WHERE plan_id = ? 
            AND payment_type = 'deposit' 
            AND payment_status = 'completed'
        ");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    /**
     * Check if full payment is completed
     */
    public function isFullPaymentCompleted($planId) {
        $stmt = $this->db->prepare("
            SELECT SUM(amount) as total_paid FROM payments_tbl 
            WHERE plan_id = ? AND payment_status = 'completed'
        ");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Get plan total
        $planStmt = $this->db->prepare("SELECT total_price FROM plans_tbl WHERE plan_id = ?");
        $planStmt->bind_param("i", $planId);
        $planStmt->execute();
        $planResult = $planStmt->get_result();
        $plan = $planResult->fetch_assoc();
        
        if (!$plan) return false;
        
        $totalPaid = $row['total_paid'] ?? 0;
        return $totalPaid >= $plan['total_price'];
    }

    /**
     * Get balance remaining for a plan
     */
    public function getBalanceRemaining($planId) {
        $stmt = $this->db->prepare("
            SELECT (pl.total_price - COALESCE(SUM(CASE WHEN p.payment_status = 'completed' THEN p.amount ELSE 0 END), 0)) as balance
            FROM plans_tbl pl
            LEFT JOIN payments_tbl p ON pl.plan_id = p.plan_id
            WHERE pl.plan_id = ?
            GROUP BY pl.plan_id
        ");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return max(0, $row['balance'] ?? 0);
    }
}
?>

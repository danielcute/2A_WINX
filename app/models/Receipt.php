<?php

namespace Models;

class Receipt {
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
     * Generate a unique receipt number
     */
    private function generateReceiptNumber() {
        // Format: RCP-YYYYMMDD-XXXXX
        return 'RCP-' . date('Ymd') . '-' . strtoupper(substr(md5(microtime()), 0, 5));
    }

    /**
     * Create a payment receipt
     */
    public function createReceipt($planId, $userId, $paymentId, $receiptType, $amount, $paymentMethod, $paidBy, $itemsPurchased = null, $referenceNumber = null) {
        // Get plan details
        $planStmt = $this->db->prepare("
            SELECT total_price FROM plans_tbl WHERE plan_id = ?
        ");
        $planStmt->bind_param("i", $planId);
        $planStmt->execute();
        $plan = $planStmt->get_result()->fetch_assoc();

        if (!$plan) {
            return false;
        }

        // Calculate totals
        $subtotal = $amount; // Amount paid
        $serviceFee = 0;
        $totalAmount = $amount;
        
        // Get user info
        $userStmt = $this->db->prepare("
            SELECT first_name, last_name, email, phone FROM users_tbl WHERE user_id = ?
        ");
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $user = $userStmt->get_result()->fetch_assoc();

        // Get current balance
        $balanceStmt = $this->db->prepare("
            SELECT (? - COALESCE(SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END), 0)) as balance
            FROM payments_tbl 
            WHERE plan_id = ?
        ");
        $balanceStmt->bind_param("di", $plan['total_price'], $planId);
        $balanceStmt->execute();
        $balanceResult = $balanceStmt->get_result()->fetch_assoc();
        $balanceRemaining = $balanceResult['balance'] ?? $plan['total_price'];

        $receiptNumber = $this->generateReceiptNumber();
        $itemsJson = $itemsPurchased ? json_encode($itemsPurchased) : null;

        $stmt = $this->db->prepare("
            INSERT INTO payment_receipts_tbl (
                plan_id, user_id, payment_id, receipt_number, receipt_type,
                subtotal, service_fee, total_amount, amount_paid, balance_remaining,
                payment_method, paid_by, paid_at, reference_number, items_purchased,
                recipient_email, recipient_phone
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiiissddddssssss",
            $planId,
            $userId,
            $paymentId,
            $receiptNumber,
            $receiptType,
            $subtotal,
            $serviceFee,
            $totalAmount,
            $amount,
            $balanceRemaining,
            $paymentMethod,
            $paidBy,
            $referenceNumber,
            $itemsJson,
            $user['email'],
            $user['phone']
        );

        if ($stmt->execute()) {
            $receiptId = $this->db->insert_id;
            
            // Update plan with payment progress
            $this->updatePlanPaymentProgress($planId, $amount, $plan['total_price']);
            
            return $receiptId;
        }
        return false;
    }

    /**
     * Update plan payment progress
     */
    private function updatePlanPaymentProgress($planId, $amountPaid, $totalPrice) {
        $stmt = $this->db->prepare("
            UPDATE plans_tbl 
            SET total_paid = (
                    SELECT COALESCE(SUM(amount), 0) 
                    FROM payments_tbl 
                    WHERE plan_id = ? AND payment_status = 'completed'
                ),
                balance_remaining = (
                    ? - COALESCE((
                        SELECT SUM(amount) 
                        FROM payments_tbl 
                        WHERE plan_id = ? AND payment_status = 'completed'
                    ), 0)
                ),
                payment_count = (
                    SELECT COUNT(*) 
                    FROM payments_tbl 
                    WHERE plan_id = ? AND payment_status = 'completed'
                )
            WHERE plan_id = ?
        ");
        
        $stmt->bind_param("iiiii", $planId, $totalPrice, $planId, $planId, $planId);
        return $stmt->execute();
    }

    /**
     * Get receipt by ID
     */
    public function getReceiptById($receiptId) {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   u.first_name, u.last_name, u.email, u.phone,
                   p.event_name, p.event_date, p.total_price, p.events
            FROM payment_receipts_tbl r
            JOIN users_tbl u ON r.user_id = u.user_id
            JOIN plans_tbl p ON r.plan_id = p.plan_id
            WHERE r.receipt_id = ?
        ");
        $stmt->bind_param("i", $receiptId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get receipt by receipt number
     */
    public function getReceiptByNumber($receiptNumber) {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   u.first_name, u.last_name, u.email, u.phone,
                   p.event_name, p.event_date, p.total_price, p.events
            FROM payment_receipts_tbl r
            JOIN users_tbl u ON r.user_id = u.user_id
            JOIN plans_tbl p ON r.plan_id = p.plan_id
            WHERE r.receipt_number = ?
        ");
        $stmt->bind_param("s", $receiptNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get all receipts for a plan
     */
    public function getReceiptsByPlanId($planId) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.first_name, u.last_name
            FROM payment_receipts_tbl r
            JOIN users_tbl u ON r.user_id = u.user_id
            WHERE r.plan_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all receipts for a user
     */
    public function getReceiptsByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT r.*, p.event_name, p.event_date
            FROM payment_receipts_tbl r
            JOIN plans_tbl p ON r.plan_id = p.plan_id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Format receipt data for display
     */
    public function formatReceiptData($receipt) {
        if (!$receipt) return null;

        return [
            'receipt_id' => $receipt['receipt_id'],
            'receipt_number' => $receipt['receipt_number'],
            'receipt_type' => $receipt['receipt_type'],
            'customer_name' => $receipt['first_name'] . ' ' . $receipt['last_name'],
            'customer_email' => $receipt['email'],
            'customer_phone' => $receipt['phone'],
            'event_name' => $receipt['event_name'],
            'event_date' => $receipt['event_date'],
            'payment_method' => $receipt['payment_method'],
            'amount_paid' => number_format($receipt['amount_paid'], 2),
            'balance_remaining' => number_format($receipt['balance_remaining'], 2),
            'total_event_price' => number_format($receipt['total_amount'] + $receipt['balance_remaining'], 2),
            'paid_at' => $receipt['paid_at'],
            'paid_by' => $receipt['paid_by'],
            'reference_number' => $receipt['reference_number'],
            'items_purchased' => $receipt['items_purchased'] ? json_decode($receipt['items_purchased'], true) : [],
            'created_at' => $receipt['created_at']
        ];
    }
}
?>

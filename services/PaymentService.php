<?php

namespace Services;

use Models\Payment;
use Models\Receipt;
use PaymentGateways\GCashGateway;
use PaymentGateways\PayMayaGateway;
use PaymentGateways\BankTransferGateway;
use PaymentGateways\ATMCardGateway;

class PaymentService {
    private $payment;
    private $receipt;
    private $db;

    public function __construct($db = null) {
        if (!$db) {
            require_once __DIR__ . '/../config/database.php';
            $this->db = \Database::getInstance()->getConnection();
        } else {
            $this->db = $db;
        }
        
        $this->payment = new Payment($this->db);
        $this->receipt = new Receipt($this->db);
    }

    /**
     * Get appropriate payment gateway based on method
     */
    private function getGateway($paymentMethod) {
        switch (strtolower($paymentMethod)) {
            case 'gcash':
                return new GCashGateway(
                    getenv('GCASH_MERCHANT_ID'),
                    getenv('APP_ENV') === 'production'
                );
            
            case 'paymaya':
                return new PayMayaGateway(
                    getenv('PAYMAYA_PUBLIC_KEY'),
                    getenv('PAYMAYA_SECRET_KEY'),
                    getenv('APP_ENV') === 'production'
                );
            
            case 'bank_transfer':
                return new BankTransferGateway();
            
            case 'atm_card':
                return new ATMCardGateway(
                    getenv('ATM_MERCHANT_ID'),
                    getenv('APP_ENV') === 'production'
                );
            
            default:
                return null;
        }
    }

    /**
     * Process a payment request
     */
    public function processPayment($planId, $userId, $paymentMethod, $paymentDetails, $paymentType = 'deposit') {
        // Create payment record
        $paymentId = $this->payment->createPayment(
            $planId,
            $userId,
            $paymentType,
            $paymentDetails['amount'],
            $paymentMethod,
            $paymentDetails
        );

        if (!$paymentId) {
            return [
                'success' => false,
                'error' => 'Failed to create payment record'
            ];
        }

        // Get payment gateway
        $gateway = $this->getGateway($paymentMethod);
        if (!$gateway) {
            return [
                'success' => false,
                'error' => 'Payment method not supported'
            ];
        }

        // Initiate payment with gateway
        $gatewayResponse = $gateway->initiatePayment($planId, $userId, $paymentDetails['amount'], $paymentDetails);

        if (!$gatewayResponse['success'] ?? false) {
            $this->payment->updatePaymentStatus($paymentId, 'failed');
            return [
                'success' => false,
                'error' => $gatewayResponse['error'] ?? 'Payment gateway error',
                'payment_id' => $paymentId
            ];
        }

        // Update payment with gateway response
        $this->payment->updatePaymentStatus(
            $paymentId,
            'processing',
            $gatewayResponse['transaction_id'] ?? $gatewayResponse['checkoutId'] ?? null,
            $gatewayResponse,
            $gatewayResponse['reference_number'] ?? null
        );

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'transaction_id' => $gatewayResponse['transaction_id'] ?? $gatewayResponse['checkoutId'],
            'checkout_url' => $gateway->generateCheckoutUrl($gatewayResponse),
            'reference_number' => $gatewayResponse['reference_number'] ?? null,
            'gateway_response' => $gatewayResponse
        ];
    }

    /**
     * Complete a payment (called from webhook/callback or manual confirmation)
     */
    public function completePayment($paymentId, $transactionId = null, $referenceNumber = null) {
        // Update payment status
        $this->payment->updatePaymentStatus(
            $paymentId,
            'completed',
            $transactionId,
            null,
            $referenceNumber
        );

        // Get payment details
        $paymentRecord = $this->payment->getPaymentById($paymentId);
        
        if (!$paymentRecord) {
            return [
                'success' => false,
                'error' => 'Payment not found'
            ];
        }

        // Get plan details
        $planStmt = $this->db->prepare("SELECT * FROM plans_tbl WHERE plan_id = ?");
        $planStmt->bind_param("i", $paymentRecord['plan_id']);
        $planStmt->execute();
        $plan = $planStmt->get_result()->fetch_assoc();

        // Get user details
        $userStmt = $this->db->prepare("SELECT first_name, last_name FROM users_tbl WHERE user_id = ?");
        $userStmt->bind_param("i", $paymentRecord['user_id']);
        $userStmt->execute();
        $user = $userStmt->get_result()->fetch_assoc();

        // Create receipt
        $itemsPurchased = $plan['events'] ? json_decode($plan['events'], true) : [];
        
        $receiptType = $this->isFullPayment($paymentRecord['plan_id']) ? 'full' : 'partial';
        
        $receiptId = $this->receipt->createReceipt(
            $paymentRecord['plan_id'],
            $paymentRecord['user_id'],
            $paymentId,
            $receiptType,
            $paymentRecord['amount'],
            $paymentRecord['payment_method'],
            $user['first_name'] . ' ' . $user['last_name'],
            $itemsPurchased,
            $transactionId
        );

        if (!$receiptId) {
            return [
                'success' => false,
                'error' => 'Failed to create receipt'
            ];
        }

        // Update plan payment status if deposit is paid
        if ($this->payment->isDepositPaid($paymentRecord['plan_id'])) {
            $this->db->prepare("UPDATE plans_tbl SET payment_status = 'paid' WHERE plan_id = ?")->bind_param("i", $paymentRecord['plan_id'])->execute();
        }

        // Check if full payment is completed
        $isFullPaymentComplete = $this->isFullPayment($paymentRecord['plan_id']);
        if ($isFullPaymentComplete) {
            $this->db->prepare("UPDATE plans_tbl SET status = 'completed' WHERE plan_id = ?")->bind_param("i", $paymentRecord['plan_id'])->execute();
        }

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'receipt_id' => $receiptId,
            'receipt_type' => $receiptType,
            'full_payment_completed' => $isFullPaymentComplete,
            'message' => $isFullPaymentComplete 
                ? 'Full payment completed! Event booking is confirmed.'
                : 'Deposit payment received. Balance payment is due ' . date('M d, Y', strtotime('+2 weeks'))
        ];
    }

    /**
     * Check if full payment is completed
     */
    private function isFullPayment($planId) {
        return $this->payment->isFullPaymentCompleted($planId);
    }

    /**
     * Get payment summary for a plan
     */
    public function getPaymentSummary($planId) {
        $summary = $this->payment->getPaymentSummary($planId);
        $balance = $this->payment->getBalanceRemaining($planId);

        return [
            'total_paid' => $summary['total_paid'] ?? 0,
            'balance_remaining' => $balance,
            'completed_payments' => $summary['completed_payments'] ?? 0,
            'pending_payments' => $summary['pending_payments'] ?? 0
        ];
    }

    /**
     * Get receipts for a plan
     */
    public function getReceiptsByPlanId($planId) {
        $receipts = $this->receipt->getReceiptsByPlanId($planId);
        return array_map([$this->receipt, 'formatReceiptData'], $receipts);
    }

    /**
     * Get receipts for a user
     */
    public function getReceiptsByUserId($userId) {
        $receipts = $this->receipt->getReceiptsByUserId($userId);
        return array_map([$this->receipt, 'formatReceiptData'], $receipts);
    }

    /**
     * Generate receipt HTML for display/PDF
     */
    public function generateReceiptHTML($receiptId) {
        $receipt = $this->receipt->getReceiptById($receiptId);
        
        if (!$receipt) {
            return null;
        }

        $formatted = $this->receipt->formatReceiptData($receipt);
        
        return $this->renderReceiptTemplate($formatted);
    }

    /**
     * Render receipt HTML template
     */
    private function renderReceiptTemplate($data) {
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?php echo $data['receipt_number']; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        .receipt-container {
            border: 1px solid #ddd;
            padding: 40px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #8B7355;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #8B7355;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .receipt-type {
            display: inline-block;
            background: <?php echo $data['receipt_type'] === 'full' ? '#28a745' : '#ffc107'; ?>;
            color: <?php echo $data['receipt_type'] === 'full' ? 'white' : '#333'; ?>;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 0;
        }
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .section {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .section h3 {
            margin-top: 0;
            color: #8B7355;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .section p {
            margin: 8px 0;
            line-height: 1.6;
        }
        .label {
            font-weight: bold;
            color: #666;
            width: 140px;
            display: inline-block;
        }
        .items-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .items-table th {
            background: #8B7355;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .items-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: #f0f0f0;
            border-radius: 4px;
            grid-column: 1 / -1;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 18px;
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #ddd;
            color: #999;
            font-size: 12px;
        }
        @media print {
            body { margin: 0; padding: 0; }
            .receipt-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>SINTA Event Planning</h1>
            <p>Official Payment Receipt</p>
            <div class="receipt-type">
                <?php echo strtoupper($data['receipt_type']); ?> PAYMENT
            </div>
            <p style="color: #999; font-size: 12px;">Receipt #<?php echo $data['receipt_number']; ?></p>
        </div>

        <div class="content">
            <div class="section">
                <h3>🧑 Customer Information</h3>
                <p><span class="label">Name:</span><?php echo $data['customer_name']; ?></p>
                <p><span class="label">Email:</span><?php echo $data['customer_email']; ?></p>
                <p><span class="label">Phone:</span><?php echo $data['customer_phone']; ?></p>
            </div>

            <div class="section">
                <h3>📅 Event Details</h3>
                <p><span class="label">Event:</span><?php echo $data['event_name']; ?></p>
                <p><span class="label">Date:</span><?php echo date('F d, Y', strtotime($data['event_date'])); ?></p>
                <p><span class="label">Payment Date:</span><?php echo date('F d, Y g:i A', strtotime($data['paid_at'])); ?></p>
            </div>

            <div class="section" style="grid-column: 1 / -1;">
                <h3>💳 Payment Information</h3>
                <p><span class="label">Payment Method:</span><?php echo strtoupper(str_replace('_', ' ', $data['payment_method'])); ?></p>
                <p><span class="label">Reference:</span><?php echo $data['reference_number'] ?: 'N/A'; ?></p>
                <p><span class="label">Paid By:</span><?php echo $data['paid_by']; ?></p>
            </div>

            <div class="summary">
                <h3 style="margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Payment Summary</h3>
                <div class="summary-row">
                    <span>Amount Paid:</span>
                    <strong>₱<?php echo $data['amount_paid']; ?></strong>
                </div>
                <div class="summary-row">
                    <span>Balance Remaining:</span>
                    <strong style="color: <?php echo $data['balance_remaining'] > 0 ? '#dc3545' : '#28a745'; ?>">
                        ₱<?php echo $data['balance_remaining']; ?>
                    </strong>
                </div>
                <div class="summary-row total">
                    <span>Total Event Price:</span>
                    <span>₱<?php echo $data['total_event_price']; ?></span>
                </div>
                <?php if ($data['balance_remaining'] > 0): ?>
                <div style="padding: 15px; background: #fff3cd; border-radius: 4px; margin-top: 10px; font-size: 13px;">
                    ⚠️ Balance payment of <strong>₱<?php echo $data['balance_remaining']; ?></strong> is due 2 weeks before your event.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business! For inquiries, contact SINTA Event Planning.</p>
            <p>This receipt was generated on <?php echo date('F d, Y \a\t g:i A'); ?></p>
            <p style="margin-top: 20px;">📞 Contact: +63 XXX XXX XXXX | 📧 info@sintaevents.com</p>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
?>

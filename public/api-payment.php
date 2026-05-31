<?php

/**
 * Payment API Endpoint
 * Handles: Payment initiation, callbacks, receipt generation, payment tracking
 * 
 * Available Actions:
 * - process_payment: Initiate payment process
 */

// Prevent PHP from outputting HTML errors/warnings that break JSON parsing
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure JSON header is always sent
header('Content-Type: application/json; charset=utf-8');

// Catch fatal errors and return JSON
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) { http_response_code(500); }
        if (ob_get_length() === 0 || strpos(ob_get_contents(), '{') === false) { ob_clean(); echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e['message']]); }
    }
});

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
require_once ROOT_PATH . '/config/database.php'; // Moved after shutdown function

// Initialize database using singleton
try {
    $db = Database::getInstance()->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => $e->getMessage()]));
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$response = [];

try {
    switch ($action) {
        case 'process_payment':
            $response = handleProcessPayment($db);
            break;

        default:
            $response = [
                'success' => false,
                'error' => 'Invalid action: ' . htmlspecialchars($action ?? 'none')
            ];
    }

} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'debug' => $_ENV['DEBUG'] ? $e->getMessage() : null
    ];
}

echo json_encode($response);
exit;

/**
 * Handler Functions
 */

function handleProcessPayment($db) {
    // Check user authentication
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'error' => 'User not authenticated'];
    }

    $planId = (int)($_POST['plan_id'] ?? 0);
    $userId = (int)$_SESSION['user_id'];
    $paymentMethod = $_POST['payment_method'] ?? null;
    $paymentType = $_POST['payment_type'] ?? 'deposit'; // deposit or final
    
    if (!$planId || !$paymentMethod) {
        return ['success' => false, 'error' => 'Missing required fields'];
    }

    // Get plan details including current payment status
    $planStmt = $db->prepare("SELECT total_price, payment_status, total_paid, events FROM plans_tbl WHERE plan_id = ? AND user_id = ?");
    $planStmt->bind_param("ii", $planId, $userId);
    $planStmt->execute();
    $plan = $planStmt->get_result()->fetch_assoc();

    if (!$plan) {
        return ['success' => false, 'error' => 'Plan not found or unauthorized'];
    }

    // Calculate payment amount based on type
    $totalPrice = (float)$plan['total_price'];
    $currentlyPaid = (float)($plan['total_paid'] ?? 0);
    
    if ($paymentType === 'deposit') {
        $amount = round($totalPrice * 0.5, 2); // 50% deposit
    } else {
        // Balance payment - pay remaining amount
        $amount = round($totalPrice - $currentlyPaid, 2);
    }

    // Generate transaction ID
    $transactionId = 'TXN-' . $planId . '-' . time() . '-' . rand(1000, 9999);
    $referenceNumber = 'REF-' . $planId . '-' . date('Ymd') . '-' . rand(100000, 999999);

    // Prepare payment details JSON
    $paymentDetails = [
        'amount' => $amount,
        'plan_id' => $planId,
        'user_id' => $userId,
        'payment_method' => $paymentMethod,
        'payment_type' => $paymentType,
        'transaction_id' => $transactionId,
        'reference_number' => $referenceNumber,
        'processed_at' => date('Y-m-d H:i:s')
    ];

    // Add method-specific details
    switch (strtolower($paymentMethod)) {
        case 'gcash':
            $paymentDetails['mobile_number'] = $_POST['mobile_number'] ?? 'N/A';
            $paymentDetails['gateway'] = 'GCash';
            $paymentDetails['status_note'] = 'Awaiting GCash payment confirmation';
            break;
            
        case 'paymaya':
            $paymentDetails['mobile_number'] = $_POST['mobile_number'] ?? 'N/A';
            $paymentDetails['gateway'] = 'PayMaya';
            $paymentDetails['status_note'] = 'Awaiting PayMaya payment confirmation';
            break;

        case 'bank_transfer':
            $paymentDetails['gateway'] = 'Bank Transfer';
            $paymentDetails['bank_details'] = 'To be provided to user';
            $paymentDetails['status_note'] = 'Pending bank transfer - awaiting proof of transfer';
            break;

        case 'debit_card':
            $paymentDetails['gateway'] = 'Debit Card';
            $paymentDetails['status_note'] = 'Processing debit card payment';
            break;
    }

    // Store payment details in plans_tbl for tracking
    $paymentDetailsJson = json_encode($paymentDetails);
    
    // Calculate new payment totals
    $newTotalPaid = round($currentlyPaid + $amount, 2);
    $balanceRemaining = round($totalPrice - $newTotalPaid, 2);
    
    // Determine payment status based on type and total paid
    if ($newTotalPaid >= $totalPrice) {
        $newPaymentStatus = 'fully_paid';
        // Auto-complete the event when fully paid
        $newEventStatus = 'completed';
    } else {
        $newPaymentStatus = 'paid';
        $newEventStatus = NULL; // Don't change event status
    }
    
    // Update plan with payment method, details, status, and payment tracking
    if ($newEventStatus === 'completed') {
        $updateStmt = $db->prepare("UPDATE plans_tbl SET payment_method = ?, payment_details = ?, payment_status = ?, status = ?, total_paid = ?, balance_remaining = ? WHERE plan_id = ? AND user_id = ?");
        $updateStmt->bind_param("ssssddii", $paymentMethod, $paymentDetailsJson, $newPaymentStatus, $newEventStatus, $newTotalPaid, $balanceRemaining, $planId, $userId);
    } else {
        $updateStmt = $db->prepare("UPDATE plans_tbl SET payment_method = ?, payment_details = ?, payment_status = ?, total_paid = ?, balance_remaining = ? WHERE plan_id = ? AND user_id = ?");
        $updateStmt->bind_param("sssddii", $paymentMethod, $paymentDetailsJson, $newPaymentStatus, $newTotalPaid, $balanceRemaining, $planId, $userId);
    }
    
    if ($updateStmt->execute()) {
        // Create payment record in payments_tbl - simplified insert
        $paymentDetailsJson2 = json_encode($paymentDetails);
        
        // Map payment method to database enum values
        $paymentMethodEnum = strtolower(str_replace(' ', '_', $paymentMethod));
        if ($paymentMethodEnum === 'debit_card' && stripos($paymentMethod, 'atm') === false) {
            $paymentMethodEnum = 'atm_card'; // If it's debit card that isn't ATM, map appropriately
        }
        if (in_array($paymentMethodEnum, ['bank_transfer', 'atm_card', 'debit_card'])) {
            // These are valid enum values
        } else {
            // Map to closest enum value
            $paymentMethodMap = [
                'gcash' => 'gcash',
                'paymaya' => 'paymaya',
                'bank_transfer' => 'bank_transfer',
                'debit_card' => 'atm_card',
                'credit_card' => 'credit_card',
                'atm_card' => 'atm_card'
            ];
            $paymentMethodEnum = $paymentMethodMap[$paymentMethodEnum] ?? 'bank_transfer';
        }
        
        $insertPaymentQuery = "
            INSERT INTO payments_tbl (plan_id, user_id, payment_type, amount, payment_method, payment_status, payment_details, transaction_id, reference_number, created_at)
            VALUES ($planId, $userId, '$paymentType', $amount, '$paymentMethodEnum', 'completed', '" . $db->real_escape_string($paymentDetailsJson2) . "', '$transactionId', '$referenceNumber', NOW())
        ";
        
        if (!$db->query($insertPaymentQuery)) {
            // Log error but continue - payment was already recorded in plans_tbl
            error_log("Payment record insert failed: " . $db->error);
        } else {
            $paymentId = $db->insert_id;
            
            // Try to create receipt
            try {
                // Determine receipt type based on payment status
                $receiptType = ($newPaymentStatus === 'fully_paid') ? 'full' : 'partial';
                $receiptNumber = 'RCP-' . date('Ymd') . '-' . strtoupper(substr(md5(microtime()), 0, 5));
                
                // Get plan details including items for receipt
                $planStmt2 = $db->query("SELECT total_price, events FROM plans_tbl WHERE plan_id = $planId");
                $planData = ($planStmt2) ? $planStmt2->fetch_assoc() : [];
                
                // Extract items from events JSON for receipt - get ALL items with full details
                $itemsPurchased = [];
                if (!empty($planData['events'])) {
                    $eventData = json_decode(stripslashes($planData['events']), true);
                    if (is_array($eventData)) {
                        // Get items array
                        if (isset($eventData['items']) && is_array($eventData['items'])) {
                            foreach ($eventData['items'] as $item) {
                                if (!empty($item['name'])) {
                                    $itemsPurchased[] = [
                                        'category' => $item['category'] ?? 'Item',
                                        'name' => $item['name'],
                                        'price' => isset($item['price']) ? (float)$item['price'] : 0
                                    ];
                                }
                            }
                        }
                        
                        // Also capture package name if available
                        if (!empty($eventData['packageName']) && !in_array($eventData['packageName'], array_column($itemsPurchased, 'name'))) {
                            array_unshift($itemsPurchased, [
                                'category' => 'Package',
                                'name' => $eventData['packageName'],
                                'price' => 0 // Package is included in items
                            ]);
                        }
                    }
                }
                $itemsPurchasedJson = json_encode($itemsPurchased);
                
                // Get user info
                $userStmt = $db->query("SELECT email, phone FROM users_tbl WHERE user_id = $userId");
                $userData = ($userStmt) ? $userStmt->fetch_assoc() : ['email' => '', 'phone' => ''];
                
                // Calculate subtotal and service fee for receipt
                $subtotal = 0;
                foreach ($itemsPurchased as $item) {
                    $subtotal += $item['price'];
                }
                $serviceFee = round($subtotal * 0.03, 2); // 3% service fee
                
                // Use actual total from database
                $receiptTotal = (float)($planData['total_price'] ?? $totalPrice);
                
                // Insert receipt
                $receiptQuery = "
                    INSERT INTO payment_receipts_tbl (
                        plan_id, user_id, payment_id, receipt_number, receipt_type,
                        subtotal, service_fee, total_amount, amount_paid, balance_remaining,
                        payment_method, paid_by, reference_number, items_purchased,
                        recipient_email, recipient_phone, paid_at
                    ) VALUES (
                        $planId, $userId, $paymentId, '$receiptNumber', '$receiptType',
                        $subtotal, $serviceFee, $receiptTotal, $newTotalPaid, $balanceRemaining,
                        '$paymentMethod', 'online_payment', '$referenceNumber', '" . $db->real_escape_string($itemsPurchasedJson) . "',
                        '" . $db->real_escape_string($userData['email'] ?? '') . "', 
                        '" . $db->real_escape_string($userData['phone'] ?? '') . "', 
                        NOW()
                    )
                ";
                
                if (!$db->query($receiptQuery)) {
                    error_log("Receipt insert failed: " . $db->error);
                }
            } catch (Exception $e) {
                error_log("Receipt creation error: " . $e->getMessage());
            }
        }
        
        return [
            'success' => true,
            'message' => 'Payment processed successfully',
            'plan_id' => $planId,
            'transaction_id' => $transactionId,
            'reference_number' => $referenceNumber,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'payment_type' => $paymentType,
            'status' => 'completed'
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Failed to process payment: ' . $db->error,
            'debug_info' => $db->error
        ];
    }
}

function handleCompletePayment($db, $paymentService) {
    $paymentId = (int)($_POST['payment_id'] ?? 0);
    $transactionId = $_POST['transaction_id'] ?? null;
    $referenceNumber = $_POST['reference_number'] ?? null;

    if (!$paymentId) {
        return ['success' => false, 'error' => 'Payment ID is required'];
    }

    return $paymentService->completePayment($paymentId, $transactionId, $referenceNumber);
}

function handleGCashCallback($db, $paymentService) {
    // Handle webhook from GCash
    $paymentId = $_POST['payment_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $transactionId = $_POST['transaction_id'] ?? null;

    if (!$paymentId || !$status) {
        return ['success' => false, 'error' => 'Missing required callback fields'];
    }

    if ($status === 'success') {
        return $paymentService->completePayment($paymentId, $transactionId);
    } else {
        // Payment failed
        return ['success' => false, 'error' => 'Payment was not completed'];
    }
}

function handleGCashReturn($db, $paymentService) {
    // Handle GCash return page
    $planId = (int)($_GET['plan_id'] ?? 0);
    $referenceNumber = $_GET['reference_number'] ?? null;

    if (!$planId || !$referenceNumber) {
        return ['success' => false, 'error' => 'Missing plan or reference'];
    }

    // Find payment by reference and verify
    $stmt = $db->prepare("
        SELECT p.payment_id, p.payment_status 
        FROM payments_tbl p 
        WHERE p.plan_id = ? AND p.reference_number = ? 
        LIMIT 1
    ");
    $stmt->bind_param("is", $planId, $referenceNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();

    if (!$payment) {
        return ['success' => false, 'error' => 'Payment not found'];
    }

    if ($payment['payment_status'] === 'completed') {
        return [
            'success' => true,
            'status' => 'completed',
            'message' => 'Payment has been received and processed',
            'payment_id' => $payment['payment_id']
        ];
    } else {
        return [
            'success' => false,
            'status' => 'pending',
            'message' => 'Payment is still processing. Please wait or check back later.',
            'payment_id' => $payment['payment_id']
        ];
    }
}

function handlePayMayaReturn($db, $paymentService) {
    // Similar to GCash return
    $planId = (int)($_GET['plan_id'] ?? 0);
    $checkoutId = $_GET['checkout_id'] ?? null;

    if (!$planId) {
        return ['success' => false, 'error' => 'Missing plan ID'];
    }

    $stmt = $db->prepare("
        SELECT p.payment_id, p.payment_status 
        FROM payments_tbl p 
        WHERE p.plan_id = ? 
        ORDER BY p.created_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();

    if (!$payment) {
        return ['success' => false, 'error' => 'Payment not found'];
    }

    return [
        'success' => $payment['payment_status'] === 'completed',
        'status' => $payment['payment_status'],
        'message' => $payment['payment_status'] === 'completed' 
            ? 'Payment successful!' 
            : 'Payment is processing',
        'payment_id' => $payment['payment_id']
    ];
}

function handleATMCallback($db, $paymentService) {
    // Similar to GCash callback
    return handleGCashCallback($db, $paymentService);
}

function handleATMReturn($db, $paymentService) {
    // Similar to GCash return
    return handleGCashReturn($db, $paymentService);
}

function handleGetReceipts($db, $paymentService) {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'error' => 'Not authenticated'];
    }

    $userId = (int)$_SESSION['user_id'];
    $planId = (int)($_GET['plan_id'] ?? 0);

    if ($planId) {
        $receipts = $paymentService->getReceiptsByPlanId($planId);
    } else {
        $receipts = $paymentService->getReceiptsByUserId($userId);
    }

    return [
        'success' => true,
        'receipts' => $receipts,
        'count' => count($receipts)
    ];
}

function handleGetReceiptByNumber($db, $paymentService) {
    $receiptNumber = $_GET['receipt_number'] ?? null;

    if (!$receiptNumber) {
        return ['success' => false, 'error' => 'Receipt number is required'];
    }

    $receipt = new Receipt($db);
    $receiptData = $receipt->getReceiptByNumber($receiptNumber);

    if (!$receiptData) {
        return ['success' => false, 'error' => 'Receipt not found'];
    }

    return [
        'success' => true,
        'receipt' => $receipt->formatReceiptData($receiptData)
    ];
}

function handleDownloadReceipt($db, $paymentService) {
    $receiptId = (int)($_GET['receipt_id'] ?? 0);

    if (!$receiptId) {
        return ['success' => false, 'error' => 'Receipt ID is required'];
    }

    $receipt = new Receipt($db);
    $receiptData = $receipt->getReceiptById($receiptId);

    if (!$receiptData) {
        return ['success' => false, 'error' => 'Receipt not found'];
    }

    // Generate HTML
    $html = $paymentService->generateReceiptHTML($receiptId);

    // Return as downloadable content
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="Receipt-' . $receiptData['receipt_number'] . '.html"');
    echo $html;
    exit;
}

function handleGetPaymentStatus($db, $paymentService) {
    $paymentId = (int)($_GET['payment_id'] ?? 0);

    if (!$paymentId) {
        return ['success' => false, 'error' => 'Payment ID is required'];
    }

    $payment = new Payment($db);
    $paymentRecord = $payment->getPaymentById($paymentId);

    if (!$paymentRecord) {
        return ['success' => false, 'error' => 'Payment not found'];
    }

    return [
        'success' => true,
        'payment_id' => $paymentRecord['payment_id'],
        'status' => $paymentRecord['payment_status'],
        'amount' => (float)$paymentRecord['amount'],
        'payment_type' => $paymentRecord['payment_type'],
        'reference_number' => $paymentRecord['reference_number'],
        'paid_at' => $paymentRecord['paid_at']
    ];
}

function handleGetPaymentSummary($db, $paymentService) {
    $planId = (int)($_GET['plan_id'] ?? 0);

    if (!$planId) {
        return ['success' => false, 'error' => 'Plan ID is required'];
    }

    $summary = $paymentService->getPaymentSummary($planId);

    return [
        'success' => true,
        'summary' => $summary
    ];
}

?>

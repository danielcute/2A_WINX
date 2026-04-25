<?php
/**
 * Plan Management API
 * Handles plan operations like cancellation and status checking
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

session_start();
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Notification.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request'];

// Handle both GET and POST requests
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$planId = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : (isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $conn = Database::getInstance()->getConnection();
    
    switch ($action) {
        case 'check_cancellation':
            // Check if a plan can be cancelled and get remaining time
            if ($planId > 0) {
                $query = "SELECT 
                            plan_id,
                            event_name,
                            status,
                            created_at,
                            TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes_elapsed
                          FROM plans_tbl
                          WHERE plan_id = ?";
                
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
                } else {
                    $stmt->bind_param("i", $planId);
                    $stmt->execute();
                    $plan = $stmt->get_result()->fetch_assoc();
                    
                    if ($plan) {
                        $can_cancel = false;
                        $minutes_remaining = 0;
                        
                        if ($plan['status'] === 'pending' && (int)$plan['minutes_elapsed'] <= 30) {
                            $can_cancel = true;
                            $minutes_remaining = 30 - (int)$plan['minutes_elapsed'];
                        }
                        
                        $response = [
                            'success' => true,
                            'plan_id' => $plan['plan_id'],
                            'event_name' => $plan['event_name'],
                            'status' => $plan['status'],
                            'can_cancel' => $can_cancel,
                            'minutes_remaining' => $minutes_remaining,
                            'minutes_elapsed' => (int)$plan['minutes_elapsed']
                        ];
                    } else {
                        $response = ['success' => false, 'message' => 'Plan not found'];
                    }
                    $stmt->close();
                }
            }
            break;
            
        case 'cancel_plan':
            // Cancel a pending plan (within 30 minutes)
            if (!isset($_SESSION['user_id'])) {
                $response = ['success' => false, 'message' => 'Not authenticated'];
            } elseif ($planId > 0) {
                // Verify the plan belongs to the user
                $stmt = $conn->prepare("SELECT user_id FROM plans_tbl WHERE plan_id = ?");
                if (!$stmt) {
                    $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
                } else {
                    $stmt->bind_param("i", $planId);
                    $stmt->execute();
                    $plan = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    
                    if (!$plan) {
                        $response = ['success' => false, 'message' => 'Plan not found'];
                    } elseif ((int)$plan['user_id'] != (int)$_SESSION['user_id']) {
                        $response = ['success' => false, 'message' => 'Unauthorized'];
                    } else {
                        // Check if still within 30 minutes
                        $checkStmt = $conn->prepare("SELECT TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes_elapsed, status FROM plans_tbl WHERE plan_id = ?");
                        $checkStmt->bind_param("i", $planId);
                        $checkStmt->execute();
                        $planCheck = $checkStmt->get_result()->fetch_assoc();
                        $checkStmt->close();
                        
                        if ((int)$planCheck['minutes_elapsed'] > 30) {
                            // Auto-confirm the plan first
                            $conn->query("UPDATE plans_tbl SET status = 'confirmed' WHERE plan_id = " . (int)$planId);
                            $response = ['success' => false, 'message' => 'Cancellation period expired (30 minutes). Plan has been auto-confirmed.'];
                        } elseif ($planCheck['status'] !== 'pending') {
                            $response = ['success' => false, 'message' => 'Only pending plans can be cancelled'];
                        } else {
                            // Update status to canceled
                            $updateStmt = $conn->prepare("UPDATE plans_tbl SET status = 'canceled' WHERE plan_id = ?");
                            $updateStmt->bind_param("i", $planId);
                            
                            if ($updateStmt->execute()) {
                                $response = ['success' => true, 'message' => 'Plan cancelled successfully'];
                            } else {
                                $response = ['success' => false, 'message' => 'Failed to cancel plan: ' . $updateStmt->error];
                            }
                            $updateStmt->close();
                        }
                    }
                }
            }
            break;
            
        case 'auto_confirm':
            // Run auto-confirmation (for cron job or manual trigger)
            // First, get all plans that need to be auto-confirmed
            $selectQuery = $conn->prepare("SELECT plan_id, user_id, event_name, total_price 
                                           FROM plans_tbl 
                                           WHERE status = 'pending' 
                                           AND created_at <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
            
            if (!$selectQuery) {
                $response = ['success' => false, 'message' => 'Query preparation failed: ' . $conn->error];
            } else {
                $selectQuery->execute();
                $result = $selectQuery->get_result();
                $autoConfirmedPlans = [];
                
                while ($plan = $result->fetch_assoc()) {
                    $autoConfirmedPlans[] = $plan;
                }
                $selectQuery->close();
                
                // Update plans to confirmed
                $updateResult = $conn->query("UPDATE plans_tbl 
                                            SET status = 'confirmed',
                                                admin_confirmed_at = NOW()
                                            WHERE status = 'pending' 
                                            AND created_at <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
                
                if ($updateResult) {
                    // Create notifications for auto-confirmed plans
                    try {
                        $notificationModel = new Notification();
                        $depositAmount = 0;
                        
                        foreach ($autoConfirmedPlans as $plan) {
                            $depositAmount = round($plan['total_price'] * 0.5);
                            $notificationModel->create([
                                'user_id' => $plan['user_id'],
                                'type' => 'payment_due',
                                'title' => '30-Minute Confirmation: Payment Due',
                                'message' => 'Your booking for "' . htmlspecialchars($plan['event_name']) . '" has been auto-confirmed. A 50% deposit (₱' . number_format($depositAmount, 0) . ') is now due for payment.',
                                'related_type' => 'plan',
                                'related_id' => $plan['plan_id']
                            ]);
                        }
                    } catch (Exception $e) {
                        error_log("Failed to create auto-confirmation notifications: " . $e->getMessage());
                    }
                    
                    $response = ['success' => true, 'message' => 'Auto-confirmation completed', 'count' => count($autoConfirmedPlans)];
                } else {
                    $response = ['success' => false, 'message' => 'Auto-confirmation failed: ' . $conn->error];
                }
            }
            break;
            
        case 'admin_confirm':
            // Admin manually confirms a plan
            if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                $response = ['success' => false, 'message' => 'Not authorized'];
            } elseif ($planId > 0) {
                $updateStmt = $conn->prepare("UPDATE plans_tbl SET status = 'confirmed', admin_confirmed_at = NOW() WHERE plan_id = ?");
                $updateStmt->bind_param("i", $planId);
                
                if ($updateStmt->execute()) {
                    $response = ['success' => true, 'message' => 'Plan confirmed by admin. User must pay 50% deposit within 24 hours.'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to confirm plan: ' . $updateStmt->error];
                }
                $updateStmt->close();
            }
            break;
    }
}

echo json_encode($response);


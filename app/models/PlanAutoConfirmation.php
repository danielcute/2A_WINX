<?php
/**
 * Auto-confirmation system for pending plans
 * After 30 minutes of creation, pending plans are automatically confirmed
 * Users can only cancel within the first 30 minutes
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Plan.php';

class PlanAutoConfirmation {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    /**
     * Auto-confirm pending plans that are older than 30 minutes
     */
    public function autoConfirmExpiredPlans() {
        $query = "UPDATE plans_tbl 
                  SET status = 'confirmed'
                  WHERE status = 'pending' 
                  AND created_at <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        
        return $this->conn->query($query);
    }
    
    /**
     * Check if a plan can be cancelled (within 30 minutes of creation)
     */
    public function canCancelPlan($planId) {
        $query = "SELECT 
                    TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes_elapsed,
                    status
                  FROM plans_tbl
                  WHERE plan_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Check if prepare failed
        if (!$stmt) {
            error_log("PlanAutoConfirmation::canCancelPlan() - Prepare failed: " . $this->conn->error);
            return ['can_cancel' => false, 'reason' => 'Database error'];
        }
        
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            return ['can_cancel' => false, 'reason' => 'Plan not found'];
        }
        
        if ($result['status'] !== 'pending') {
            return ['can_cancel' => false, 'reason' => 'Only pending plans can be cancelled'];
        }
        
        $minutesElapsed = (int)$result['minutes_elapsed'];
        
        if ($minutesElapsed > 30) {
            // Auto-confirm this plan since 30 mins have passed
            $this->conn->query("UPDATE plans_tbl SET status = 'confirmed' WHERE plan_id = " . intval($planId));
            return ['can_cancel' => false, 'reason' => 'Cancellation period expired (30 minutes)'];
        }
        
        return [
            'can_cancel' => true,
            'minutes_remaining' => 30 - $minutesElapsed,
            'minutes_elapsed' => $minutesElapsed
        ];
    }
    
    /**
     * Cancel a plan (only if within 30 minutes)
     */
    public function cancelPlan($planId) {
        $cancellation = $this->canCancelPlan($planId);
        
        if (!$cancellation['can_cancel']) {
            return ['success' => false, 'message' => $cancellation['reason']];
        }
        
        $query = "UPDATE plans_tbl SET status = 'canceled' WHERE plan_id = ?";
        $stmt = $this->conn->prepare($query);
        
        // Check if prepare failed
        if (!$stmt) {
            error_log("PlanAutoConfirmation::cancelPlan() - Prepare failed: " . $this->conn->error);
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $planId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Plan cancelled successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to cancel plan'];
        }
    }
    
    /**
     * Get plan status with cancellation info
     */
    public function getPlanStatusInfo($planId) {
        $query = "SELECT 
                    plan_id,
                    event_name,
                    status,
                    created_at,
                    TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes_elapsed
                  FROM plans_tbl
                  WHERE plan_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Check if prepare failed
        if (!$stmt) {
            error_log("PlanAutoConfirmation::getPlanStatusInfo() - Prepare failed: " . $this->conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $plan = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$plan) {
            return null;
        }
        
        $plan['can_cancel'] = false;
        $plan['minutes_remaining'] = 0;
        
        // Check if plan is pending and within 30 minutes
        if ($plan['status'] === 'pending') {
            $minutesElapsed = (int)$plan['minutes_elapsed'];
            
            if ($minutesElapsed <= 30) {
                // Still within cancellation window
                $plan['can_cancel'] = true;
                $plan['minutes_remaining'] = 30 - $minutesElapsed;
            } else {
                // Auto-confirm the plan since 30 minutes have passed
                $updateStmt = $this->conn->prepare("UPDATE plans_tbl SET status = 'confirmed' WHERE plan_id = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("i", $planId);
                    $updateStmt->execute();
                    $updateStmt->close();
                    // Update the status in the returned array
                    $plan['status'] = 'confirmed';
                    $plan['can_cancel'] = false;
                    $plan['minutes_remaining'] = 0;
                }
            }
        }
        // Don't auto-confirm if status is 'canceled', 'completed', 'rejected', or 'approved'
        // Only auto-confirm pending plans
        
        return $plan;
    }
}

// Run auto-confirmation if called directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $autoConfirm = new PlanAutoConfirmation();
    $result = $autoConfirm->autoConfirmExpiredPlans();
    echo json_encode(['success' => true, 'message' => 'Auto-confirmation check completed']);
}
?>

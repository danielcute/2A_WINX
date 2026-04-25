<?php
class PlanController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $page = 'plans';
        $plans = [];

        if (!empty($_SESSION['user_id'])) {
            require_once ROOT_PATH . '/app/models/Plan.php';
            require_once ROOT_PATH . '/app/models/PlanAutoConfirmation.php';
            $planModel = new Plan();
            $autoConfirm = new PlanAutoConfirmation();
            $plans = $planModel->getUserPlans($_SESSION['user_id']);
            
            // Update plan statuses with auto-confirmation and cancellation info
            foreach ($plans as &$plan) {
                $planStatusInfo = $autoConfirm->getPlanStatusInfo($plan['plan_id']);
                if ($planStatusInfo) {
                    $plan['status'] = $planStatusInfo['status'];
                    $plan['can_cancel'] = $planStatusInfo['can_cancel'] ?? false;
                    $plan['minutes_remaining'] = $planStatusInfo['minutes_remaining'] ?? 0;
                } else {
                    $plan['can_cancel'] = false;
                    $plan['minutes_remaining'] = 0;
                }
            }
            unset($plan); // CRITICAL: Break the reference to prevent array corruption
        }

        include VIEW_PATH . '/user/plans.php';
    }
    
    public function delete() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['plan_id'])) {
            echo json_encode(['success' => false, 'message' => 'Plan ID is required']);
            exit;
        }
        
        require_once ROOT_PATH . '/app/models/Plan.php';
        $planModel = new Plan();
        
        // Get the plan to verify it belongs to the user
        $plan = $planModel->findById($data['plan_id']);
        
        if (!$plan) {
            echo json_encode(['success' => false, 'message' => 'Plan not found']);
            exit;
        }
        
        if ($plan['user_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        // Only allow deletion of completed plans
        if ($plan['status'] !== 'completed') {
            echo json_encode(['success' => false, 'message' => 'Only completed plans can be deleted']);
            exit;
        }
        
        if ($planModel->delete($data['plan_id'])) {
            echo json_encode(['success' => true, 'message' => 'Plan deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete plan']);
        }
        exit;
    }
}
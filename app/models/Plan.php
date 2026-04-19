<?php
/**
 * Plan Model
 * Handles event plans and planning
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';

class Plan {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $userId = (int)$data['user_id'];
        $occasionId = isset($data['occasion_id']) ? (int)$data['occasion_id'] : null;
        $packageId = isset($data['package_id']) ? (int)$data['package_id'] : null;
        $customizeId = isset($data['customize_id']) ? (int)$data['customize_id'] : null;
        $eventName = $this->db->real_escape_string($data['event_name']);
        $eventDate = $this->db->real_escape_string($data['event_date']);
        $eventTime = isset($data['event_time']) ? $data['event_time'] : null;
        $guestCount = isset($data['guest_count']) ? (int)$data['guest_count'] : 0;
        $venue = isset($data['venue']) ? $this->db->real_escape_string($data['venue']) : null;
        $theme = isset($data['theme']) ? $this->db->real_escape_string($data['theme']) : null;
        $totalPrice = (float)$data['total_price'];
        $status = $data['status'] ?? 'pending';
        $events = isset($data['events']) ? $this->db->real_escape_string($data['events']) : null;
        
        $stmt = $this->db->prepare("INSERT INTO plans_tbl 
        (user_id, occasion_id, package_id, customize_id, event_name, event_date, event_time, guest_count, venue, theme, total_price, status, events) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Plan create prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("iiiisssisiiss", $userId, $occasionId, $packageId, $customizeId, $eventName, $eventDate, $eventTime, $guestCount, $venue, $theme, $totalPrice, $status, $events);
        
        if ($stmt->execute()) {
            $result = $this->db->insert_id;
            $stmt->close();
            return $result;
        } else {
            error_log("Plan create execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    public function getUserPlans($userId) {
        $userId = (int)$userId;
        $sql = "SELECT p.*, o.events as occasion_name, pk.name as package_name,
                (SELECT SUM(total_amount) FROM checkout_tbl WHERE plan_id = p.plan_id AND status = 'paid') as paid_amount
                FROM plans_tbl p
                LEFT JOIN occasions_tbl o ON p.occasion_id = o.occasion_id
                LEFT JOIN packages_tbl pk ON p.package_id = pk.package_id
                WHERE p.user_id = $userId
                ORDER BY p.event_date ASC";
        
        $result = $this->db->query($sql);
        $plans = [];
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
        return $plans;
    }
    
    public function findById($id) {
        $id = (int)$id;
        $sql = "SELECT p.*, o.events as occasion_name, pk.name as package_name,
                u.first_name, u.last_name, u.email, u.phone
                FROM plans_tbl p
                LEFT JOIN occasions_tbl o ON p.occasion_id = o.occasion_id
                LEFT JOIN packages_tbl pk ON p.package_id = pk.package_id
                LEFT JOIN users_tbl u ON p.user_id = u.user_id
                WHERE p.plan_id = $id";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }
    
    public function update($id, $data) {
        $id = (int)$id;
        $sets = [];
        
        if (isset($data['status'])) {
            $sets[] = "status = '" . $this->db->real_escape_string($data['status']) . "'";
        }
        if (isset($data['event_name'])) {
            $sets[] = "event_name = '" . $this->db->real_escape_string($data['event_name']) . "'";
        }
        if (isset($data['event_date'])) {
            $sets[] = "event_date = '" . $this->db->real_escape_string($data['event_date']) . "'";
        }
        if (isset($data['venue'])) {
            $sets[] = "venue = '" . $this->db->real_escape_string($data['venue']) . "'";
        }
        
        if (empty($sets)) return false;
        
        $sql = "UPDATE plans_tbl SET " . implode(', ', $sets) . " WHERE plan_id = $id";
        return $this->db->query($sql);
    }
    
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT p.*, o.name as occasion_name, u.first_name, u.last_name, u.email
                FROM tbl_plans p
                LEFT JOIN tbl_occasions o ON p.occasion_id = o.occasion_id
                LEFT JOIN tbl_users u ON p.user_id = u.user_id
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $offset, $limit";
        }
        
        $result = $this->db->query($sql);
        $plans = [];
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
        return $plans;
    }
    
    public function getCount() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM tbl_plans");
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    public function getStats() {
        $result = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(total_price) as total_revenue
            FROM tbl_plans
        ");
        return $result->fetch_assoc();
    }
}
?>
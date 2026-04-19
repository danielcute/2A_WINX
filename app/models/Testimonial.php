<?php
/**
 * Testimonial Model
 * Handles user testimonials and reviews
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';

class Testimonial {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll($approvedOnly = true) {
        $sql = "SELECT t.*, u.first_name, u.last_name, u.image as user_image
                FROM testimonials_tbl t
                LEFT JOIN users_tbl u ON t.user_id = u.user_id";
        if ($approvedOnly) {
            $sql .= " WHERE t.status = 'approved'";
        }
        $sql .= " ORDER BY t.created_at DESC";
        
        $result = $this->db->query($sql);
        $testimonials = [];
        while ($row = $result->fetch_assoc()) {
            $testimonials[] = $row;
        }
        return $testimonials;
    }
    
    public function findById($id) {
        $id = (int)$id;
        $result = $this->db->query("SELECT * FROM testimonials_tbl WHERE testimonial_id = $id");
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $userId = (int)$data['user_id'];
        $rating = (int)($data['rating'] ?? 5);
        $comment = $this->db->real_escape_string($data['comment']);
        $status = $data['status'] ?? 'pending';
        
        $stmt = $this->db->prepare("
            INSERT INTO testimonials_tbl 
            (user_id, rating, comment, status) 
            VALUES (?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("Testimonial create prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("iiss", $userId, $rating, $comment, $status);
        
        if ($stmt->execute()) {
            $result = $this->db->insert_id;
            $stmt->close();
            return $result;
        } else {
            error_log("Testimonial create execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    public function update($id, $data) {
        $id = (int)$id;
        $sets = [];
        $types = "";
        $values = [];
        
        if (isset($data['status'])) {
            $sets[] = "status = ?";
            $types .= "s";
            $values[] = $data['status'];
        }
        if (isset($data['rating'])) {
            $sets[] = "rating = ?";
            $types .= "i";
            $values[] = (int)$data['rating'];
        }
        if (isset($data['comment'])) {
            $sets[] = "comment = ?";
            $types .= "s";
            $values[] = $data['comment'];
        }
        
        if (empty($sets)) return false;
        
        $types .= "i";
        $values[] = $id;
        
        $sql = "UPDATE testimonials_tbl SET " . implode(', ', $sets) . " WHERE testimonial_id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Testimonial update prepare failed: " . $this->db->error);
            return false;
        }
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $values));
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function delete($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("DELETE FROM testimonials_tbl WHERE testimonial_id = ?");
        
        if (!$stmt) {
            error_log("Testimonial delete prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function getPendingCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM testimonials_tbl WHERE status = 'pending'");
        
        if (!$stmt) {
            error_log("getPendingCount prepare failed: " . $this->db->error);
            return 0;
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'] ?? 0;
    }
    
    public function approve($id) {
        return $this->update($id, ['status' => 'approved']);
    }
    
    public function reject($id) {
        return $this->update($id, ['status' => 'rejected']);
    }
    
    public function getApprovedCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM testimonials_tbl WHERE status = 'approved'");
        
        if (!$stmt) {
            error_log("getApprovedCount prepare failed: " . $this->db->error);
            return 0;
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'] ?? 0;
    }
}
?>
}
?>
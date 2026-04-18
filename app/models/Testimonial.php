<?php
require_once ROOT_PATH . '/config/database.php';

class Testimonial {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll($approvedOnly = true) {
        $sql = "SELECT t.*, u.first_name, u.last_name, u.image as user_image, p.name as package_name
                FROM testimonials_tbl t
                LEFT JOIN users_tbl u ON t.user_id = u.user_id
                LEFT JOIN packages_tbl p ON t.package_id = p.package_id";
        if ($approvedOnly) {
            $sql .= " WHERE t.status = 'approved'";
        }
        $sql .= " ORDER BY t.created_at DESC";
        
        $result = $this->db->query($sql);
        if (!$result) {
            error_log('Testimonial getAll Error: ' . $this->db->error);
            return [];
        }
        
        $testimonials = [];
        while ($row = $result->fetch_assoc()) {
            $testimonials[] = $row;
        }
        return $testimonials;
    }
    
    public function findById($id) {
        $id = (int)$id;
        $result = $this->db->query("SELECT * FROM reviews_tbl WHERE review_id = $id");
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $userId = isset($data['user_id']) ? (int)$data['user_id'] : 'NULL';
        $packageId = isset($data['package_id']) ? (int)$data['package_id'] : 'NULL';
        $rating = (int)$data['rating'];
        $comment = $this->db->real_escape_string($data['comment']);
        $status = isset($data['status']) ? "'" . $this->db->real_escape_string($data['status']) . "'" : "'pending'";
        
        $sql = "INSERT INTO reviews_tbl (user_id, package_id, rating, comment, status) 
                VALUES ($userId, $packageId, $rating, '$comment', $status)";
        
        if ($this->db->query($sql)) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    public function update($id, $data) {
        $id = (int)$id;
        $sets = [];
        
        if (isset($data['status'])) {
            $sets[] = "status = '" . $this->db->real_escape_string($data['status']) . "'";
        }
        if (isset($data['rating'])) {
            $sets[] = "rating = " . (int)$data['rating'];
        }
        if (isset($data['comment'])) {
            $sets[] = "comment = '" . $this->db->real_escape_string($data['comment']) . "'";
        }
        
        if (empty($sets)) return false;
        
        $sql = "UPDATE reviews_tbl SET " . implode(', ', $sets) . " WHERE review_id = $id";
        return $this->db->query($sql);
    }
    
    public function delete($id) {
        $id = (int)$id;
        return $this->db->query("DELETE FROM reviews_tbl WHERE review_id = $id");
    }
    
    public function getPendingCount() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM testimonials_tbl WHERE status = 'pending'");
        if (!$result) {
            error_log('Testimonial getPendingCount Error: ' . $this->db->error);
            return 0;
        }
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
?>
<?php
require_once ROOT_PATH . '/config/database.php';

class Occasion {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        $result = $this->db->query("SELECT * FROM occasions_tbl ORDER BY occasion_id");
        $occasions = [];
        while ($row = $result->fetch_assoc()) {
            $occasions[] = $row;
        }
        return $occasions;
    }
    
    public function findById($id) {
        $id = (int)$id;
        $result = $this->db->query("SELECT * FROM occasions_tbl WHERE occasion_id = $id");
        return $result->fetch_assoc();
    }
    
    public function findByName($name) {
        $name = $this->db->real_escape_string($name);
        $result = $this->db->query("SELECT * FROM occasions_tbl WHERE LOWER(events) = LOWER('$name')");
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $events = $this->db->real_escape_string($data['events']);
        $descriptions = $this->db->real_escape_string($data['descriptions'] ?? '');
        
        $sql = "INSERT INTO occasions_tbl (events, descriptions) VALUES ('$events', '$descriptions')";
        
        if ($this->db->query($sql)) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    public function update($id, $data) {
        $id = (int)$id;
        $events = $this->db->real_escape_string($data['events']);
        $descriptions = $this->db->real_escape_string($data['descriptions'] ?? '');
        
        $sql = "UPDATE occasions_tbl SET events = '$events', descriptions = '$descriptions' WHERE occasion_id = $id";
        return $this->db->query($sql);
    }
    
    public function delete($id) {
        $id = (int)$id;
        return $this->db->query("DELETE FROM occasions_tbl WHERE occasion_id = $id");
    }
}
?>
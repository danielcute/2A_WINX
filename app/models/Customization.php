<?php
require_once ROOT_PATH . '/config/database.php';

class Customization {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getOptionsByCategory($category) {
        $category = $this->db->real_escape_string($category);
        $result = $this->db->query("SELECT * FROM tbl_customization_options WHERE category = '$category' AND is_active = 1");
        
        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
        return $options;
    }
    
    public function getAllOptions() {
        $result = $this->db->query("SELECT * FROM tbl_customization_options WHERE is_active = 1 ORDER BY category, price");
        
        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
        return $options;
    }
    
    public function getOptionById($id) {
        $id = (int)$id;
        $result = $this->db->query("SELECT * FROM tbl_customization_options WHERE option_id = $id");
        return $result->fetch_assoc();
    }
    
    public function calculateTotal($selectedOptions) {
        $total = 0;
        if (is_array($selectedOptions)) {
            foreach ($selectedOptions as $optionId) {
                $option = $this->getOptionById($optionId);
                if ($option) {
                    $total += $option['price'];
                }
            }
        }
        return $total;
    }
}
?>
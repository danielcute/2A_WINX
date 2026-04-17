<?php
require_once MODEL_PATH . 'Occasion.php';

class OccasionController {
    private $occasionModel;
    
    public function __construct() {
        $this->occasionModel = new Occasion();
    }
    
    public function index() {
        $page = 'occasions';
        $occasions = $this->occasionModel->getAll();
        include VIEW_PATH . '/user/occasions.php';
    }
}
?>
<?php
require_once MODEL_PATH . 'Package.php';
require_once MODEL_PATH . 'Occasion.php';

class PackageController {
    private $packageModel;
    private $occasionModel;
    
    public function __construct() {
        $this->packageModel = new Package();
        $this->occasionModel = new Occasion();
    }
    
    public function index() {
        $page = 'packages';
        $occasionName = $_GET['occasion'] ?? 'wedding';
        
        $occasion = $this->occasionModel->findByName($occasionName);
        $occasionId = $occasion ? $occasion['occasion_id'] : null;
        
        $packages = $this->packageModel->getOccasionPackages($occasionId);
        $occasionLabel = $occasion ? $occasion['name'] : ucfirst($occasionName);
        
        include VIEW_PATH . '/user/packages.php';
    }
}
?>
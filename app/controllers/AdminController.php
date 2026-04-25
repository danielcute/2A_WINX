<?php
class AdminController {
    private function checkAuth() {
        // Proper auth check
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: /SINTA/public/index.php?route=signin');
            exit;
        }
    }
    
    public function dashboard() {
        $this->checkAuth();
        
        // Get stats from database
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getInstance()->getConnection();
        
        // Get counts
        $packages_result = $db->query("SELECT COUNT(*) as count FROM packages_tbl");
        $packages_count = $packages_result->fetch_assoc()['count'] ?? 0;
        
        $bookings_result = $db->query("SELECT COUNT(*) as count FROM plans_tbl");
        $bookings_count = $bookings_result->fetch_assoc()['count'] ?? 0;
        
        $testimonials_result = $db->query("SELECT COUNT(*) as count FROM testimonials_tbl");
        $testimonials_count = $testimonials_result->fetch_assoc()['count'] ?? 0;
        
        $messages_result = $db->query("SELECT COUNT(*) as count FROM messages_tbl WHERE status = 'unread'");
        $unread_count = $messages_result->fetch_assoc()['count'] ?? 0;
        
        $page_title = 'Admin Dashboard';
        include ROOT_PATH . '/app/views/admin/admin-dashboard.php';
    }
    
    public function bookings() {
        $this->checkAuth();
        $page_title = 'Booking Management';
        include ROOT_PATH . '/app/views/admin/admin-bookings.php';
    }
    
    public function packages() {
        $this->checkAuth();
        $page_title = 'Package Management';
        include ROOT_PATH . '/app/views/admin/admin-packages.php';
    }
    
    public function testimonials() {
        $this->checkAuth();
        $page_title = 'Testimonial Management';
        include ROOT_PATH . '/app/views/admin/admin-testimonials.php';
    }
    
    public function messages() {
        $this->checkAuth();
        $page_title = 'Message Management';
        
        // Get unread count for display
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getInstance()->getConnection();
        $result = $db->query("SELECT COUNT(*) as count FROM messages_tbl WHERE recipient_id = " . ($_SESSION['user_id'] ?? 0) . " AND status = 'unread'");
        $unread_count = $result->fetch_assoc()['count'] ?? 0;
        $_SESSION['admin_unread_count'] = $unread_count;
        
        include ROOT_PATH . '/app/views/admin/admin-messages.php';
    }
    
    public function customize() {
        $this->checkAuth();
        $page_title = 'Customization Management';
        
        require_once ROOT_PATH . '/app/models/Customization.php';
        $customization = new Customization();
        $options = $customization->getAllOptions();
        
        include ROOT_PATH . '/app/views/admin/admin-customize.php';
    }
    
    public function logout() {
        session_destroy();
        header('Location: /SINTA/public/index.php?route=signin');
        exit;
    }
}
?>
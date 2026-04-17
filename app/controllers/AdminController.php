<?php
class AdminController {
    private function checkAuth() {
        // Simple check for testing
        if (!isset($_SESSION['admin_logged_in'])) {
            $_SESSION['admin_logged_in'] = true; // Auto-login for testing
        }
    }
    
    public function dashboard() {
        $this->checkAuth();
        echo "<h1>Admin Dashboard</h1>";
        echo "<p>Welcome to Admin Panel</p>";
        echo "<ul>";
        echo "<li><a href='/sinta/public/admin-bookings'>Bookings</a></li>";
        echo "<li><a href='/sinta/public/admin-packages'>Packages</a></li>";
        echo "<li><a href='/sinta/public/admin-testimonials'>Testimonials</a></li>";
        echo "<li><a href='/sinta/public/admin-messages'>Messages</a></li>";
        echo "<li><a href='/sinta/public/admin-logout'>Logout</a></li>";
        echo "</ul>";
    }
    
    public function bookings() {
        $this->checkAuth();
        echo "<h1>Booking Management</h1>";
        echo "<p><a href='/sinta/public/admin-dashboard'>Back to Dashboard</a></p>";
    }
    
    public function packages() {
        $this->checkAuth();
        echo "<h1>Package Management</h1>";
        echo "<p><a href='/sinta/public/admin-dashboard'>Back to Dashboard</a></p>";
    }
    
    public function testimonials() {
        $this->checkAuth();
        echo "<h1>Testimonial Management</h1>";
        echo "<p><a href='/sinta/public/admin-dashboard'>Back to Dashboard</a></p>";
    }
    
    public function messages() {
        $this->checkAuth();
        echo "<h1>Message Management</h1>";
        echo "<p><a href='/sinta/public/admin-dashboard'>Back to Dashboard</a></p>";
    }
    
    public function logout() {
        session_destroy();
        header('Location: /sinta/public/landing');
        exit;
    }
}
?>
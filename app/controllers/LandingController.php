<?php
class LandingController {
    public function index() {
        echo "<h1>Welcome to Sinta</h1>";
        echo "<p>Landing page is working!</p>";
        echo "<hr>";
        echo "<h3>Test Links:</h3>";
        echo "<ul>";
        echo "<li><a href='/sinta/public/signin'>Sign In</a></li>";
        echo "<li><a href='/sinta/public/signup'>Sign Up</a></li>";
        echo "<li><a href='/sinta/public/homepage'>Homepage</a></li>";
        echo "<li><a href='/sinta/public/admin-dashboard'>Admin Dashboard</a></li>";
        echo "</ul>";
    }
}
?>
<?php
class HomeController {
    public function index() {
        echo "<h1>User Homepage</h1>";
        echo "<p>Welcome to your event planning dashboard!</p>";
        echo "<ul>";
        echo "<li><a href='/sinta/public/occasions'>Browse Occasions</a></li>";
        echo "<li><a href='/sinta/public/plans'>My Plans</a></li>";
        echo "<li><a href='/sinta/public/messages'>Messages</a></li>";
        echo "<li><a href='/sinta/public/profile'>Profile</a></li>";
        echo "<li><a href='/sinta/public/landing'>Logout</a></li>";
        echo "</ul>";
    }
}
?>
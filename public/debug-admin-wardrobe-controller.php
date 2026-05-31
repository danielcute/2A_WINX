<?php
// Debug the AdminWardrobeController listAll function
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Set up paths like in public/index.php
define('ROOT_PATH', dirname(__DIR__));
define('VIEW_PATH', ROOT_PATH . '/app/views');
define('APP_URL', '/index.php');

// Include required files
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Wardrobe.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/controllers/AdminWardrobeController.php';

// Simulate admin session
$_SESSION['user_id'] = 1;
$_SESSION['admin_logged_in'] = true;

// Run the controller
$controller = new AdminWardrobeController();

// Create output buffer to capture the view
ob_start();
$controller->listAll();
$output = ob_get_clean();

// Check if wardrobes are in the output
$hasCategories = strpos($output, 'category-section') !== false;
$hasWedding = strpos($output, 'Wedding') !== false;

echo "<h1>Debug Output</h1>";
echo "<p>Has category sections: " . ($hasCategories ? "YES" : "NO") . "</p>";
echo "<p>Has Wedding category: " . ($hasWedding ? "YES" : "NO") . "</p>";
echo "<p>Output length: " . strlen($output) . " characters</p>";
echo "<hr>";
echo "<h2>First 2000 characters of output:</h2>";
echo "<pre>";
echo htmlspecialchars(substr($output, 0, 2000));
echo "</pre>";
?>

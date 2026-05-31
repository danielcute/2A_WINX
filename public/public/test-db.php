<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/config/database.php';

echo "<h1>Database Connection Test</h1>";

try {
    $db = Database::getInstance()->getConnection();
    echo "<p style='color:green'>✓ Database connected successfully!</p>";
    
    // Test query
    $result = $db->query("SHOW TABLES");
    echo "<h2>Tables in database:</h2>";
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}
?>
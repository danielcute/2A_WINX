<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Check wardrobes table
echo "<h2>Wardrobe Database Check</h2>";

// Count all wardrobes
$allCount = $db->query("SELECT COUNT(*) as count FROM wardrobes_tbl");
$allData = $allCount->fetch_assoc();
echo "<p><strong>Total wardrobes in database:</strong> " . $allData['count'] . "</p>";

// Count active wardrobes
$activeCount = $db->query("SELECT COUNT(*) as count FROM wardrobes_tbl WHERE is_active = 1");
$activeData = $activeCount->fetch_assoc();
echo "<p><strong>Active wardrobes:</strong> " . $activeData['count'] . "</p>";

// List all wardrobes
echo "<h3>All Wardrobes:</h3>";
$result = $db->query("SELECT wardrobe_id, category, name, rental_price, is_active FROM wardrobes_tbl ORDER BY category, name");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Category</th><th>Name</th><th>Price</th><th>Active</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $active = $row['is_active'] ? 'Yes' : 'No';
        echo "<tr>";
        echo "<td>" . $row['wardrobe_id'] . "</td>";
        echo "<td>" . $row['category'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>₱" . $row['rental_price'] . "</td>";
        echo "<td>" . $active . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No wardrobes found in database!</p>";
}

// Check database structure
echo "<h3>Wardrobe Table Structure:</h3>";
$fields = $db->query("DESCRIBE wardrobes_tbl");
if ($fields && $fields->num_rows > 0) {
    echo "<pre>";
    while ($field = $fields->fetch_assoc()) {
        print_r($field);
    }
    echo "</pre>";
}

// Try to activate all wardrobes
echo "<h3>Activating all wardrobes...</h3>";
$update = $db->query("UPDATE wardrobes_tbl SET is_active = 1");
echo "<p>Update result: " . ($update ? "Success" : "Failed") . "</p>";

// Check again
$activeCount = $db->query("SELECT COUNT(*) as count FROM wardrobes_tbl WHERE is_active = 1");
$activeData = $activeCount->fetch_assoc();
echo "<p><strong>Active wardrobes after update:</strong> " . $activeData['count'] . "</p>";

?>

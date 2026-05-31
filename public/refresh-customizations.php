<?php
/**
 * Database Refresh Script
 * Clears old customization data and reseeds with new categories
 * Access: http://localhost/refresh-customizations.php
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Customization.php';

// Get database connection
$db = Database::getInstance()->getConnection();

// Clear existing data
echo "<h2>Refreshing Customization Database...</h2>";
echo "<p>Clearing existing customization options...</p>";
$db->query("DELETE FROM customization_options_tbl");
echo "✓ Old data cleared<br>";

// Now instantiate Customization which will trigger seeding
echo "<p>Seeding new customization options...</p>";
$customization = new Customization();
echo "✓ Database refreshed successfully<br>";

// Verify the data
$result = $db->query("SELECT category, COUNT(*) as count FROM customization_options_tbl GROUP BY category ORDER BY category");
if ($result) {
    echo "<h3>Seeded Categories:</h3>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li><strong>" . htmlspecialchars($row['category']) . "</strong>: " . $row['count'] . " items</li>";
    }
    echo "</ul>";
}

// Count total items
$totalResult = $db->query("SELECT COUNT(*) as total FROM customization_options_tbl");
$totalRow = $totalResult->fetch_assoc();
echo "<p><strong>Total items: " . $totalRow['total'] . "</strong></p>";

echo "<p><a href='/index.php?route=admin-customize'><button style='padding: 10px 20px; background: #8A7650; color: white; border: none; border-radius: 5px; cursor: pointer;'>View Admin Customize Page</button></a></p>";
?>

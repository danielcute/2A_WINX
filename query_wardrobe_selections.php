<?php
require_once 'config/database.php';

$db = Database::getInstance();
$connection = $db->getConnection();

// Get count
$result = $connection->query('SELECT COUNT(*) as count FROM wardrobe_selections_tbl');
$count_row = $result->fetch_assoc();
echo 'Total wardrobe selections: ' . $count_row['count'] . PHP_EOL;

// Get sample data if exists
if ($count_row['count'] > 0) {
    $samples = $connection->query('SELECT * FROM wardrobe_selections_tbl LIMIT 5');
    echo PHP_EOL . 'Sample data (first 5 rows):' . PHP_EOL;
    while ($row = $samples->fetch_assoc()) {
        echo '- ID: ' . $row['id'] . ', Wardrobe ID: ' . $row['wardrobe_id'] . ', Selection: ' . $row['selection'] . ', Date: ' . $row['created_at'] . PHP_EOL;
    }
}
?>

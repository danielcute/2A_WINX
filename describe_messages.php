<?php
require_once 'config/database.php';
$db = Database::getInstance()->getConnection();
$result = $db->query("DESCRIBE messages_tbl");
echo "messages_tbl schema:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ') ' . ($row['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . "\n";
}
echo "\nusers_tbl users:\n";
$result = $db->query("DESCRIBE users_tbl");
while ($row = $result->fetch_assoc()) {
    if (strpos($row['Field'], 'user_id') !== false || strpos($row['Field'], 'first_name') !== false) {
        echo $row['Field'] . ' (' . $row['Type'] . ")\n";
    }
}
?>

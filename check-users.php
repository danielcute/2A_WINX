<?php
// Use local database for checking users
$db = new mysqli('localhost', 'root', '', 'sinta_db', 3306);

// Check all users
$result = $db->query("SELECT user_id, email, full_name, role FROM users LIMIT 20");
if ($result && $result->num_rows > 0) {
    echo "Users in database:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- Email: " . $row['email'] . " | Name: " . $row['full_name'] . " | Role: " . $row['role'] . "\n";
    }
} else {
    echo "No users found\n";
}
?>

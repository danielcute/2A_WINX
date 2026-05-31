<?php
// Determine root path for local access
if (is_dir(__DIR__ . '/public')) {
    $root = __DIR__;
} else {
    $root = dirname(__DIR__);
}

// Direct database connection (no index.php dependency)
$db = new mysqli('localhost', 'root', '', 'sinta_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "=== Admin Account Check ===\n";

// Check if account exists
$email = 'sinta2026@gmail.com';
$result = $db->query("SELECT user_id, email, role, first_name FROM users_tbl WHERE LOWER(email) = '" . $db->real_escape_string(strtolower($email)) . "'");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "✓ Account found:\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Role: " . $user['role'] . "\n";
    echo "  Name: " . $user['first_name'] . "\n";
    exit;
}

echo "✗ Account not found. Creating...\n";

// Create admin account with hashed password
$password_hash = password_hash('Sinta2026', PASSWORD_DEFAULT);
$first_name = 'Sinta';
$last_name = 'Admin';
$email = 'sinta2026@gmail.com';
$role = 'admin';

$sql = "INSERT INTO users_tbl (first_name, last_name, email, password, role) 
        VALUES (
            '" . $db->real_escape_string($first_name) . "',
            '" . $db->real_escape_string($last_name) . "',
            '" . $db->real_escape_string($email) . "',
            '" . $db->real_escape_string($password_hash) . "',
            '" . $db->real_escape_string($role) . "'
        )";

if ($db->query($sql)) {
    echo "✓ Admin account created!\n";
    echo "  Email: " . $email . "\n";
    echo "  Password: Sinta2026\n";
    echo "  Role: admin\n";
} else {
    echo "✗ Creation failed: " . $db->error . "\n";
}

$db->close();
?>

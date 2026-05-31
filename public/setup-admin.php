<?php
// Prevent any output buffering issues
if (ob_get_level()) ob_end_clean();

// Direct database connection (no index.php dependency)
$db = new mysqli('localhost', 'root', '', 'sinta_db');

if ($db->connect_error) {
    http_response_code(500);
    header('Content-Type: text/plain');
    die("Connection failed: " . $db->connect_error);
}

// Set content type
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Admin Account Setup</h1>";
echo "<pre>";

// Check if account exists
$email = 'sinta2026@gmail.com';
$result = $db->query("SELECT user_id, email, role, first_name FROM users_tbl WHERE LOWER(email) = '" . $db->real_escape_string(strtolower($email)) . "'");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "✓ Account already exists:\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Role: " . $user['role'] . "\n";
    echo "  Name: " . $user['first_name'] . "\n";
    echo "\n\nYou can log in with:\n";
    echo "  Email: sinta2026@gmail.com\n";
    echo "  Password: Sinta2026\n";
} else {
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
        echo "✓ Admin account created successfully!\n";
        echo "  Email: " . $email . "\n";
        echo "  Password: Sinta2026\n";
        echo "  Role: admin\n";
        echo "\n\nYou can now log in with these credentials.\n";
    } else {
        echo "✗ Creation failed: " . $db->error . "\n";
    }
}

echo "</pre>";

$db->close();
?>

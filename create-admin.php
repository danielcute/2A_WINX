<?php
require 'public/index.php';

// This will establish DB connection through the index.php initialization
$db = Database::getInstance()->getConnection();

echo "=== Checking Admin Account ===\n";

// Check if account exists
$result = $db->query("SELECT user_id, email, role, first_name FROM users_tbl WHERE LOWER(email) = 'sinta2026@gmail.com'");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "✓ Account found!\n";
    echo "  User ID: " . $user['user_id'] . "\n";
    echo "  Name: " . $user['first_name'] . "\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Role: " . $user['role'] . "\n";
} else {
    echo "✗ Account NOT found. Creating admin account...\n";
    
    $email = 'sinta2026@gmail.com';
    $password = password_hash('Sinta2026', PASSWORD_DEFAULT);
    $first_name = 'Sinta';
    $last_name = 'Admin';
    $role = 'admin';
    
    $stmt = $db->prepare("INSERT INTO users_tbl (first_name, last_name, email, password, role, phone, birthday, address) 
                          VALUES (?, ?, ?, ?, ?, '', NULL, '')");
    
    if ($stmt) {
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $role);
        if ($stmt->execute()) {
            echo "✓ Admin account created successfully!\n";
            echo "  Email: " . $email . "\n";
            echo "  Password: Sinta2026\n";
            echo "  Role: admin\n";
        } else {
            echo "✗ Error creating account: " . $stmt->error . "\n";
        }
        $stmt->close();
    } else {
        echo "✗ Prepare error: " . $db->error . "\n";
    }
}
?>

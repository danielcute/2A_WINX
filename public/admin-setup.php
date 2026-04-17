<?php
/**
 * Admin Account Setup & Verification
 * Use this to create or update admin account
 * Access via: http://localhost/SINTA/public/admin-setup.php
 */

define('ROOT_PATH', dirname(__DIR__));

// Admin credentials
$admin_email = 'sinta@gmail.com';
$admin_password = 'sintaadmin';
$admin_first_name = 'Sinta';
$admin_last_name = 'Admin';

require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Admin Account Setup</h2>";
echo "<hr>";

// Check if admin already exists (case-insensitive)
$check_stmt = $db->prepare("SELECT user_id, email, role FROM users_tbl WHERE LOWER(email) = LOWER(?)");
$check_stmt->bind_param("s", $admin_email);
$check_stmt->execute();
$result = $check_stmt->get_result();
$admin_user = $result->fetch_assoc();

if ($admin_user) {
    echo "<h3>Admin Account Found</h3>";
    echo "✓ Email: " . htmlspecialchars($admin_user['email']) . "<br>";
    echo "✓ Current Role: " . htmlspecialchars($admin_user['role']) . "<br>";
    echo "✓ User ID: " . $admin_user['user_id'] . "<br>";
    
    // Update admin role if needed
    if ($admin_user['role'] !== 'admin') {
        $role_update = $db->prepare("UPDATE users_tbl SET role = 'admin' WHERE LOWER(email) = LOWER(?)");
        $role_update->bind_param("s", $admin_email);
        if ($role_update->execute()) {
            echo "<p style='color: green;'><strong>✓ Updated role to 'admin'</strong></p>";
        }
    }
    
    // Check and fix password
    echo "<h3>Password Verification</h3>";
    $pwd_stmt = $db->prepare("SELECT password FROM users_tbl WHERE LOWER(email) = LOWER(?)");
    $pwd_stmt->bind_param("s", $admin_email);
    $pwd_stmt->execute();
    $pwd_result = $pwd_stmt->get_result();
    $pwd_row = $pwd_result->fetch_assoc();
    
    if ($pwd_row && password_verify($admin_password, $pwd_row['password'])) {
        echo "<p style='color: green;'>✓ Password verified successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Password does NOT match. Updating password...</p>";
        $new_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        $pwd_update = $db->prepare("UPDATE users_tbl SET password = ? WHERE LOWER(email) = LOWER(?)");
        $pwd_update->bind_param("ss", $new_password_hash, $admin_email);
        if ($pwd_update->execute()) {
            echo "<p style='color: green;'><strong>✓ Password hash updated successfully!</strong></p>";
            echo "<p>Now try logging in with:</p>";
            echo "<ul>";
            echo "<li>Email: <code>sinta@gmail.com</code></li>";
            echo "<li>Password: <code>sintaadmin</code></li>";
            echo "</ul>";
        }
    }
    
} else {
    echo "<h3>Admin Account NOT Found - Creating...</h3>";
    
    // Create new admin account
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    $role = 'admin';
    $phone = '09000000000';
    $birthday = '2000-01-01';
    $address = 'Admin Address';
    
    $insert_stmt = $db->prepare("INSERT INTO users_tbl (first_name, last_name, email, phone, birthday, address, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$insert_stmt) {
        echo "<p style='color: red;'>✗ Error: " . $db->error . "</p>";
    } else {
        $insert_stmt->bind_param("ssssssss", $admin_first_name, $admin_last_name, $admin_email, $phone, $birthday, $address, $password_hash, $role);
        
        if ($insert_stmt->execute()) {
            $new_user_id = $db->insert_id;
            echo "<p style='color: green;'><strong>✓ Admin account created successfully!</strong></p>";
            echo "✓ Email: " . htmlspecialchars($admin_email) . "<br>";
            echo "✓ Password: " . htmlspecialchars($admin_password) . "<br>";
            echo "✓ User ID: " . $new_user_id . "<br>";
            echo "✓ Role: admin<br>";
        } else {
            echo "<p style='color: red;'>✗ Error creating admin: " . $insert_stmt->error . "</p>";
        }
    }
}

echo "<hr>";
echo "<h3>✓ Admin Setup Complete!</h3>";
echo "<p><strong>Login Credentials:</strong></p>";
echo "<ul>";
echo "<li>Email: <code>sinta@gmail.com</code></li>";
echo "<li>Password: <code>sintaadmin</code></li>";
echo "</ul>";

echo "<p><a href='/SINTA/public/index.php?route=signin'>Go to Sign In →</a></p>";
echo "<p style='color: #666; margin-top: 2rem;'><small>After login, you should be redirected to the Admin Dashboard.</small></p>";

$db->close();
?>

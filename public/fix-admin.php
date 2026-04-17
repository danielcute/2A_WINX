<?php
/**
 * Admin Login Fix Tool
 * This script will diagnose and fix your admin login issue
 * Access via: http://localhost/SINTA/public/fix-admin.php
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/User.php';

$db = Database::getInstance()->getConnection();

// Admin credentials
$admin_email = 'sinta@gmail.com';
$admin_password = 'sintaadmin';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Login Fix</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1, h2 { color: #333; }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #1976d2; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .section { margin: 30px 0; padding: 15px; border-left: 4px solid #8A7650; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔧 Admin Login Fix Tool</h1>
    <hr>";

// Step 1: Check if admin exists
echo "<div class='section'>";
echo "<h2>Step 1: Check if admin exists</h2>";

$check_stmt = $db->prepare("SELECT user_id, email, role, password FROM users_tbl WHERE LOWER(email) = LOWER(?)");
$check_email = strtolower(trim($admin_email));
$check_stmt->bind_param("s", $check_email);
$check_stmt->execute();
$result = $check_stmt->get_result();
$admin_user = $result->fetch_assoc();

if ($admin_user) {
    echo "<div class='success'>✅ Admin account found!</div>";
    echo "<table>";
    echo "<tr><td><strong>User ID:</strong></td><td>" . $admin_user['user_id'] . "</td></tr>";
    echo "<tr><td><strong>Email:</strong></td><td>" . htmlspecialchars($admin_user['email']) . "</td></tr>";
    echo "<tr><td><strong>Role:</strong></td><td>" . htmlspecialchars($admin_user['role']) . "</td></tr>";
    echo "<tr><td><strong>Password Hash:</strong></td><td><code>" . substr($admin_user['password'], 0, 30) . "...</code></td></tr>";
    echo "</table>";
} else {
    echo "<div class='error'>❌ Admin account NOT found in database!</div>";
    echo "<div class='info'>ℹ️ Searching for any admin accounts...</div>";
    
    $admins_stmt = $db->prepare("SELECT user_id, email, role FROM users_tbl WHERE role = 'admin'");
    $admins_stmt->execute();
    $admins_result = $admins_stmt->get_result();
    
    if ($admins_result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Email</th><th>Role</th></tr>";
        while ($row = $admins_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>No admin accounts found!</div>";
    }
}

echo "</div>";

// Step 2: Test password hash
if ($admin_user) {
    echo "<div class='section'>";
    echo "<h2>Step 2: Check Password Hash</h2>";
    
    $is_bcrypt = (substr($admin_user['password'], 0, 4) === '$2y$' || substr($admin_user['password'], 0, 4) === '$2a$' || substr($admin_user['password'], 0, 4) === '$2b$');
    
    if ($is_bcrypt) {
        echo "<div class='info'>ℹ️ Password is properly bcrypt hashed</div>";
        
        $verify_result = password_verify($admin_password, $admin_user['password']);
        if ($verify_result) {
            echo "<div class='success'>✅ Password verification PASSED! Login should work.</div>";
        } else {
            echo "<div class='error'>❌ Password verification FAILED!</div>";
            echo "<div class='info'>The stored password hash doesn't match 'sintaadmin'</div>";
        }
    } else {
        echo "<div class='error'>❌ Password is NOT properly hashed!</div>";
        echo "<div class='info'>Password hash format: <code>" . substr($admin_user['password'], 0, 20) . "...</code></div>";
        echo "<p>This is the issue! The password needs to be bcrypt hashed.</p>";
    }
    
    echo "</div>";
    
    // Step 3: Check role
    echo "<div class='section'>";
    echo "<h2>Step 3: Check Admin Role</h2>";
    
    if ($admin_user['role'] === 'admin') {
        echo "<div class='success'>✅ Role is set to 'admin'</div>";
    } else {
        echo "<div class='error'>❌ Role is set to '" . htmlspecialchars($admin_user['role']) . "' instead of 'admin'</div>";
    }
    
    echo "</div>";
}

// Step 4: Show all users for reference
echo "<div class='section'>";
echo "<h2>Step 4: All Users in Database</h2>";

$all_stmt = $db->prepare("SELECT user_id, first_name, last_name, email, role FROM users_tbl LIMIT 20");
$all_stmt->execute();
$all_result = $all_stmt->get_result();

if ($all_result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    while ($row = $all_result->fetch_assoc()) {
        $highlight = (strtolower($row['email']) === strtolower($admin_email)) ? " style='background: #fffacd;'" : "";
        echo "<tr$highlight>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['role']) . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>No users found in database!</div>";
}

echo "</div>";

// Step 5: Auto-fix button
echo "<div class='section' style='background: #fff3cd; border-left-color: #ff9800;'>";
echo "<h2>Step 5: Auto-Fix Admin Account</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_admin'])) {
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Update or insert admin
    if ($admin_user) {
        // Update existing admin
        $update_stmt = $db->prepare("UPDATE users_tbl SET role = 'admin', password = ? WHERE LOWER(email) = LOWER(?)");
        $update_stmt->bind_param("ss", $password_hash, $admin_email);
        
        if ($update_stmt->execute()) {
            echo "<div class='success'>✅ Admin account FIXED!</div>";
            echo "<p><strong>Changes made:</strong></p>";
            echo "<ul>";
            echo "<li>Role updated to 'admin'</li>";
            echo "<li>Password updated and properly bcrypt hashed</li>";
            echo "</ul>";
            echo "<p><strong>Now try logging in with:</strong></p>";
            echo "<ul>";
            echo "<li>Email: <code>sinta@gmail.com</code></li>";
            echo "<li>Password: <code>sintaadmin</code></li>";
            echo "</ul>";
        } else {
            echo "<div class='error'>❌ Failed to update admin: " . $db->error . "</div>";
        }
    } else {
        // Create new admin
        $first_name = 'Sinta';
        $last_name = 'Admin';
        $phone = '09000000000';
        $birthday = '2000-01-01';
        $address = 'Admin Address';
        $role = 'admin';
        
        $insert_stmt = $db->prepare("INSERT INTO users_tbl (first_name, last_name, email, phone, birthday, address, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssssss", $first_name, $last_name, $admin_email, $phone, $birthday, $address, $password_hash, $role);
        
        if ($insert_stmt->execute()) {
            echo "<div class='success'>✅ Admin account CREATED!</div>";
            echo "<p><strong>Login credentials:</strong></p>";
            echo "<ul>";
            echo "<li>Email: <code>sinta@gmail.com</code></li>";
            echo "<li>Password: <code>sintaadmin</code></li>";
            echo "</ul>";
        } else {
            echo "<div class='error'>❌ Failed to create admin: " . $db->error . "</div>";
        }
    }
} else {
    echo "<form method='POST'>";
    echo "<button type='submit' name='fix_admin' style='background: #8A7650; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
    echo "🔧 Fix / Update Admin Account Now</button>";
    echo "</form>";
    echo "<p><small>This will ensure password is properly bcrypt hashed and role is set to admin.</small></p>";
}

echo "</div>";

echo "</div>
</body>
</html>";
?>

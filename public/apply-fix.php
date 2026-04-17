<?php
/**
 * Fix Admin Account Script
 * Fixes the existing admin account with correct credentials
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

// The ACTUAL credentials in your database
$admin_email = 'sinta2026@gmail.com';
$admin_password = 'sintaAdmins2026';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Fix</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: white; background: #4caf50; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .error { color: white; background: #f44336; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info { color: #1976d2; background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f0f0f0; }
        code { background: #f5f5f5; padding: 5px 10px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔧 Admin Account Fix</h1>
    <hr>";

// Hash the password properly
$password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

echo "<div class='info'>";
echo "<h3>Fixing admin account with:</h3>";
echo "<ul>";
echo "<li><strong>Email:</strong> <code>$admin_email</code></li>";
echo "<li><strong>Password:</strong> <code>$admin_password</code></li>";
echo "<li><strong>Role:</strong> admin</li>";
echo "</ul>";
echo "</div>";

// Update the admin account
$update_stmt = $db->prepare("UPDATE users_tbl SET role = 'admin', password = ? WHERE email = ?");
$update_stmt->bind_param("ss", $password_hash, $admin_email);

if ($update_stmt->execute()) {
    echo "<div class='success'>";
    echo "<h2>✅ Admin Account Fixed Successfully!</h2>";
    echo "<p>Your admin account is now ready to use.</p>";
    echo "<p><strong>Login with these credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Email: <code>sinta2026@gmail.com</code></li>";
    echo "<li>Password: <code>sintaAdmins2026</code></li>";
    echo "</ul>";
    echo "<p><a href='/SINTA/public/index.php?route=signin' style='color: white; text-decoration: underline;'>Go to Login →</a></p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h2>❌ Failed to update admin account</h2>";
    echo "<p>Error: " . htmlspecialchars($db->error) . "</p>";
    echo "</div>";
}

// Show current admin status
echo "<hr>";
echo "<h2>Current Admin Account Status:</h2>";

$verify_stmt = $db->prepare("SELECT user_id, first_name, last_name, email, role FROM users_tbl WHERE email = ?");
$verify_stmt->bind_param("s", $admin_email);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();
$admin_user = $verify_result->fetch_assoc();

if ($admin_user) {
    echo "<table>";
    echo "<tr><td><strong>User ID:</strong></td><td>" . $admin_user['user_id'] . "</td></tr>";
    echo "<tr><td><strong>Name:</strong></td><td>" . htmlspecialchars($admin_user['first_name'] . ' ' . $admin_user['last_name']) . "</td></tr>";
    echo "<tr><td><strong>Email:</strong></td><td>" . htmlspecialchars($admin_user['email']) . "</td></tr>";
    echo "<tr><td><strong>Role:</strong></td><td><strong>" . htmlspecialchars($admin_user['role']) . "</strong></td></tr>";
    echo "</table>";
    
    if ($admin_user['role'] === 'admin') {
        echo "<div class='success'>✅ Role is correctly set to 'admin'</div>";
    } else {
        echo "<div class='error'>❌ Role is still not 'admin'</div>";
    }
}

echo "</div>
</body>
</html>";
?>

<?php
/**
 * Complete Login Debugging Tool
 * Traces the entire admin login process step by step
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/User.php';

$db = Database::getInstance()->getConnection();

// Admin credentials from your database
$admin_email = 'sinta2026@gmail.com';
$admin_password = 'sintaAdmins2026';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Complete Admin Login Debug</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1, h2 { color: #333; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #8A7650; background: #fafafa; }
        .success { border-left-color: #4caf50; background: #e8f5e9; }
        .error { border-left-color: #f44336; background: #ffebee; }
        .warning { border-left-color: #ff9800; background: #fff3e0; }
        code { background: #f5f5f5; padding: 3px 6px; border-radius: 3px; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f0f0f0; }
        .checkmark { color: #4caf50; font-weight: bold; }
        .cross { color: #f44336; font-weight: bold; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Complete Admin Login Debug</h1>
    <hr>";

// STEP 1: Check if admin exists in database
echo "<div class='step success'>";
echo "<h2>STEP 1: Check if admin exists in database</h2>";

$check_stmt = $db->prepare("SELECT user_id, first_name, last_name, email, role, password FROM users_tbl WHERE LOWER(email) = LOWER(?)");
$check_email = strtolower(trim($admin_email));
$check_stmt->bind_param("s", $check_email);
$check_stmt->execute();
$result = $check_stmt->get_result();
$admin_user = $result->fetch_assoc();

if ($admin_user) {
    echo "<p class='checkmark'>✅ Admin account found in database</p>";
    echo "<table>";
    echo "<tr><td><strong>User ID:</strong></td><td>" . $admin_user['user_id'] . "</td></tr>";
    echo "<tr><td><strong>Email:</strong></td><td>" . htmlspecialchars($admin_user['email']) . "</td></tr>";
    echo "<tr><td><strong>First Name:</strong></td><td>" . htmlspecialchars($admin_user['first_name']) . "</td></tr>";
    echo "<tr><td><strong>Last Name:</strong></td><td>" . htmlspecialchars($admin_user['last_name']) . "</td></tr>";
    echo "<tr><td><strong>Role:</strong></td><td><strong>" . htmlspecialchars($admin_user['role']) . "</strong></td></tr>";
    echo "<tr><td><strong>Password Hash (first 40 chars):</strong></td><td><code>" . substr($admin_user['password'], 0, 40) . "...</code></td></tr>";
    echo "</table>";
} else {
    echo "<p class='cross'>❌ Admin account NOT found in database!</p>";
    echo "<p>Searched for: <code>" . htmlspecialchars($admin_email) . "</code></p>";
}

echo "</div>";

// STEP 2: Check password hash format
if ($admin_user) {
    echo "<div class='step'>";
    echo "<h2>STEP 2: Check password hash format</h2>";
    
    $hash_start = substr($admin_user['password'], 0, 4);
    $is_bcrypt = ($hash_start === '$2y$' || $hash_start === '$2a$' || $hash_start === '$2b$');
    
    if ($is_bcrypt) {
        echo "<p class='checkmark'>✅ Password is bcrypt hashed (format: <code>$hash_start</code>)</p>";
    } else {
        echo "<p class='cross'>❌ Password is NOT properly hashed!</p>";
        echo "<p>Hash starts with: <code>" . htmlspecialchars($hash_start) . "</code></p>";
        echo "<p>This is a PLAIN TEXT password, not bcrypt!</p>";
    }
    
    echo "</div>";
    
    // STEP 3: Test password_verify
    echo "<div class='step'>";
    echo "<h2>STEP 3: Test password_verify()</h2>";
    echo "<p>Testing: <code>password_verify('$admin_password', hash)</code></p>";
    
    $password_check = password_verify($admin_password, $admin_user['password']);
    
    if ($password_check) {
        echo "<p class='checkmark'>✅ Password verification PASSED!</p>";
        echo "<p>The password <code>$admin_password</code> matches the hash.</p>";
    } else {
        echo "<p class='cross'>❌ Password verification FAILED!</p>";
        echo "<p>The password <code>$admin_password</code> does NOT match the stored hash.</p>";
    }
    
    echo "</div>";
    
    // STEP 4: Simulate login process
    echo "<div class='step'>";
    echo "<h2>STEP 4: Simulate User Model authenticate()</h2>";
    
    $userModel = new User();
    $authenticated_user = $userModel->authenticate($admin_email, $admin_password);
    
    if ($authenticated_user) {
        echo "<p class='checkmark'>✅ User model authenticate() PASSED!</p>";
        echo "<p>Authentication will succeed.</p>";
    } else {
        echo "<p class='cross'>❌ User model authenticate() FAILED!</p>";
        echo "<p>Login will show 'Invalid email or password'</p>";
    }
    
    echo "</div>";
    
    // STEP 5: Check admin role
    echo "<div class='step'>";
    echo "<h2>STEP 5: Check admin role for routing</h2>";
    
    if ($admin_user['role'] === 'admin') {
        echo "<p class='checkmark'>✅ Role is set to 'admin'</p>";
        echo "<p>User will be redirected to: <code>/SINTA/public/index.php?route=admin-dashboard</code></p>";
    } else {
        echo "<p class='cross'>❌ Role is set to: <code>" . htmlspecialchars($admin_user['role']) . "</code></p>";
        echo "<p>User will be redirected to HOMEPAGE, not admin dashboard!</p>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h2>🔧 What needs to be fixed:</h2>";

$issues = [];
if (!$admin_user) {
    $issues[] = "✗ Admin account doesn't exist";
} else {
    if ($admin_user['role'] !== 'admin') {
        $issues[] = "✗ Role is not 'admin' (currently: " . htmlspecialchars($admin_user['role']) . ")";
    }
    
    $hash_start = substr($admin_user['password'], 0, 4);
    $is_bcrypt = ($hash_start === '$2y$' || $hash_start === '$2a$' || $hash_start === '$2b$');
    if (!$is_bcrypt) {
        $issues[] = "✗ Password is not properly bcrypt hashed";
    }
    
    $password_check = password_verify($admin_password, $admin_user['password']);
    if (!$password_check && $is_bcrypt) {
        $issues[] = "✗ Password hash doesn't match the provided password";
    }
}

if (empty($issues)) {
    echo "<div class='step success'>";
    echo "<h2>✅ All systems GO!</h2>";
    echo "<p>Everything is configured correctly. You should be able to login now.</p>";
    echo "<p><strong>Login credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Email: <code>$admin_email</code></li>";
    echo "<li>Password: <code>$admin_password</code></li>";
    echo "</ul>";
    echo "<p><a href='/SINTA/public/index.php?route=signin'>🔐 Go to Login Page</a></p>";
    echo "</div>";
} else {
    echo "<div class='step error'>";
    echo "<h2>❌ Issues Found:</h2>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "<p><a href='update-admin.php'>🔧 Click here to auto-fix these issues</a></p>";
    echo "</div>";
}

echo "</div>
</body>
</html>";
?>

<?php
/**
 * Authentication Test Script  
 * Test admin credentials directly
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/User.php';

// Test credentials
$test_email = 'sinta@gmail.com';
$test_password = 'sintaadmin';

$userModel = new User();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Auth Test</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .result { padding: 20px; margin: 20px 0; border-radius: 5px; }
        .pass { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .fail { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        code { background: #f5f5f5; padding: 5px; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
<h1>Authentication Test</h1>";

// Step 1: Test authenticate method
echo "<h2>Test: userModel->authenticate()</h2>";
$result = $userModel->authenticate($test_email, $test_password);

if ($result) {
    echo "<div class='result pass'>";
    echo "<h3>✅ Authentication PASSED!</h3>";
    echo "<p>Returned user data:</p>";
    echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='result fail'>";
    echo "<h3>❌ Authentication FAILED!</h3>";
    echo "<p>The authenticate function returned FALSE.</p>";
    echo "</div>";
}

// Step 2: Test findByEmail
echo "<h2>Test: userModel->findByEmail()</h2>";
$user = $userModel->findByEmail($test_email);

if ($user) {
    echo "<div class='result pass'>";
    echo "<h3>✅ Email found in database!</h3>";
    echo "<table>";
    echo "<tr><td><b>User ID:</b></td><td>" . $user['user_id'] . "</td></tr>";
    echo "<tr><td><b>Email:</b></td><td>" . htmlspecialchars($user['email']) . "</td></tr>";
    echo "<tr><td><b>Role:</b></td><td>" . htmlspecialchars($user['role']) . "</td></tr>";
    echo "<tr><td><b>First Name:</b></td><td>" . htmlspecialchars($user['first_name']) . "</td></tr>";
    echo "<tr><td><b>Password Hash (first 50 chars):</b></td><td><code>" . substr($user['password'], 0, 50) . "...</code></td></tr>";
    echo "</table>";
    echo "</div>";
    
    // Step 3: Test password_verify
    echo "<h2>Test: password_verify()</h2>";
    $password_check = password_verify($test_password, $user['password']);
    
    if ($password_check) {
        echo "<div class='result pass'>";
        echo "<h3>✅ Password verification PASSED!</h3>";
        echo "<p>The password '<code>sintaadmin</code>' correctly hashes to the stored hash.</p>";
        echo "</div>";
    } else {
        echo "<div class='result fail'>";
        echo "<h3>❌ Password verification FAILED!</h3>";
        echo "<p>The password '<code>sintaadmin</code>' does NOT match the stored hash.</p>";
        echo "<p><strong>This is the problem!</strong></p>";
        echo "<p>The stored password was probably inserted as plain text instead of bcrypt hash.</p>";
        echo "</div>";
    }
} else {
    echo "<div class='result fail'>";
    echo "<h3>❌ Email not found in database!</h3>";
    echo "<p>No user with email '<code>" . htmlspecialchars($test_email) . "</code>' exists.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If authentication PASSED: The login should work. Try again.</li>";
echo "<li>If findByEmail FAILED: The admin account doesn't exist. <a href='fix-admin.php'>Click here to create it</a></li>";
echo "<li>If password_verify FAILED: The password is wrong. <a href='fix-admin.php'>Click here to fix it</a></li>";
echo "</ol>";

echo "</body>
</html>";
?>

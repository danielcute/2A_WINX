<?php
/**
 * Admin Login Diagnostic Tool
 * Check admin account status and password verification
 * Access via: http://localhost/SINTA/public/admin-diagnose.php
 */

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/User.php';

echo "<h2>🔍 Admin Login Diagnostic</h2>";
echo "<hr>";

// Credentials to test
$admin_email = 'sinta@gmail.com';
$admin_password = 'sintaadmin';

$db = Database::getInstance()->getConnection();

echo "<h3>1. Check if admin exists in database</h3>";
$check_stmt = $db->prepare("SELECT user_id, email, role, password FROM users_tbl WHERE LOWER(email) = LOWER(?)");
$check_email = strtolower(trim($admin_email));
$check_stmt->bind_param("s", $check_email);
$check_stmt->execute();
$result = $check_stmt->get_result();
$admin_user = $result->fetch_assoc();

if ($admin_user) {
    echo "✅ <strong>Admin found in database!</strong><br>";
    echo "Email: " . htmlspecialchars($admin_user['email']) . "<br>";
    echo "Role: " . htmlspecialchars($admin_user['role']) . "<br>";
    echo "User ID: " . $admin_user['user_id'] . "<br>";
    echo "Password Hash: " . substr($admin_user['password'], 0, 20) . "...<br>";
} else {
    echo "❌ <strong>Admin NOT found!</strong> Email doesn't exist in database.<br>";
    echo "Expected email: " . htmlspecialchars($admin_email) . "<br>";
}

echo "<hr>";
echo "<h3>2. Test Authentication</h3>";

if ($admin_user) {
    $userModel = new User();
    $authenticated = $userModel->authenticate($admin_email, $admin_password);
    
    if ($authenticated) {
        echo "✅ <strong>Authentication SUCCESSFUL!</strong><br>";
        echo "User data returned: " . json_encode($authenticated, JSON_PRETTY_PRINT) . "<br>";
    } else {
        echo "❌ <strong>Authentication FAILED!</strong><br>";
        echo "Testing password_verify()...<br>";
        
        $password_verify_result = password_verify($admin_password, $admin_user['password']);
        if ($password_verify_result) {
            echo "✅ password_verify() returned TRUE<br>";
        } else {
            echo "❌ password_verify() returned FALSE<br>";
            echo "This means the password hash doesn't match the provided password.<br>";
            echo "<strong>Issue Found:</strong> Password mismatch!<br>";
        }
    }
} else {
    echo "⚠️ Cannot test authentication - admin not found in database.<br>";
}

echo "<hr>";
echo "<h3>3. All Users in Database</h3>";
$all_users = $db->prepare("SELECT user_id, first_name, last_name, email, role FROM users_tbl");
$all_users->execute();
$all_result = $all_users->get_result();

if ($all_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; padding: 8px;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    while ($row = $all_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['role']) . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No users found!<br>";
}

echo "<hr>";
echo "<h3>4. Fix Options</h3>";
echo "If the admin account was manually inserted into the database, ";
echo "the password might not be hashed correctly.<br>";
echo "<a href='admin-setup.php' style='padding: 10px 20px; background: #8A7650; color: white; text-decoration: none; border-radius: 5px;'>";
echo "Click here to run Admin Setup & Fix Password</a>";
?>

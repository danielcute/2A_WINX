<?php
/**
 * Direct Database Update for Admin Account
 * Sets admin role and hashes password
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Admin credentials from your database
$admin_email = 'sinta2026@gmail.com';
$admin_password = 'sintaAdmins2026';

// Hash the password
$password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

// Update the admin account
$sql = "UPDATE users_tbl SET role = 'admin', password = ? WHERE email = ?";
$stmt = $db->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $db->error);
}

$stmt->bind_param("ss", $password_hash, $admin_email);

if ($stmt->execute()) {
    echo "✅ Admin account updated successfully!<br>";
    echo "Email: " . htmlspecialchars($admin_email) . "<br>";
    echo "Role: admin<br>";
    echo "Password: Hash updated<br><br>";
    
    // Verify the update
    $verify = $db->prepare("SELECT email, role FROM users_tbl WHERE email = ?");
    $verify->bind_param("s", $admin_email);
    $verify->execute();
    $result = $verify->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && $user['role'] === 'admin') {
        echo "✅ Verified: Admin role is now ACTIVE<br>";
        echo "<br>You can now login with:<br>";
        echo "Email: sinta2026@gmail.com<br>";
        echo "Password: sintaAdmins2026<br>";
    }
} else {
    die("Execute failed: " . $stmt->error);
}
?>

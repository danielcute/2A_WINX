<?php
/**
 * COMPLETE ADMIN ACCOUNT FIX
 * This script fixes everything and prepares database for admin features
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Complete Setup</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #bee5eb; }
        h1, h2 { color: #333; }
        code { background: #f5f5f5; padding: 5px 10px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>✅ Complete Admin Setup & Database Preparation</h1>
    <hr>";

// Admin credentials
$admin_email = 'sinta2026@gmail.com';
$admin_password = 'sintaAdmins2026';
$password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

// ===== STEP 1: Fix Admin Account =====
echo "<h2>STEP 1: Fix Admin Account</h2>";

$update_admin = $db->prepare("UPDATE users_tbl SET role = 'admin', password = ? WHERE email = ?");
$update_admin->bind_param("ss", $password_hash, $admin_email);

if ($update_admin->execute()) {
    echo "<div class='success'>✅ Admin account updated with proper password hash and admin role</div>";
} else {
    echo "<div class='error'>❌ Failed to update admin: " . $db->error . "</div>";
}

// ===== STEP 2: Create/Update Packages Table =====
echo "<h2>STEP 2: Create/Update Packages Table</h2>";

$packages_table = "ALTER TABLE packages_tbl ADD COLUMN IF NOT EXISTS 
    image VARCHAR(255),
    ADD COLUMN IF NOT EXISTS category VARCHAR(100),
    ADD COLUMN IF NOT EXISTS features TEXT,
    ADD COLUMN IF NOT EXISTS max_guests INT DEFAULT 100,
    ADD COLUMN IF NOT EXISTS duration_hours INT DEFAULT 4,
    ADD COLUMN IF NOT EXISTS venue_type VARCHAR(100),
    ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active'";

if ($db->query($packages_table)) {
    echo "<div class='success'>✅ Packages table updated with new columns</div>";
} else {
    echo "<div class='info'>ℹ️ Packages table already has required columns or doesn't need updates</div>";
}

// ===== STEP 3: Create Customizations Table =====
echo "<h2>STEP 3: Create Customizations Table</h2>";

$customizations_table = "CREATE TABLE IF NOT EXISTS customizations_tbl (
    customization_id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    user_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    price DECIMAL(10, 2),
    category VARCHAR(100),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages_tbl(package_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users_tbl(user_id) ON DELETE SET NULL,
    INDEX idx_package_id (package_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($db->query($customizations_table)) {
    echo "<div class='success'>✅ Customizations table created successfully</div>";
} else {
    echo "<div class='info'>ℹ️ Customizations table already exists</div>";
}

// ===== STEP 4: Create Messages Table =====
echo "<h2>STEP 4: Create Messages Table</h2>";

$messages_table = "CREATE TABLE IF NOT EXISTS messages_tbl (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_id INT,
    subject VARCHAR(200),
    message_text LONGTEXT NOT NULL,
    message_type ENUM('inquiry', 'booking_question', 'general', 'support') DEFAULT 'inquiry',
    status ENUM('unread', 'read', 'replied', 'archived') DEFAULT 'unread',
    attachment_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users_tbl(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users_tbl(user_id) ON DELETE SET NULL,
    INDEX idx_sender (sender_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($db->query($messages_table)) {
    echo "<div class='success'>✅ Messages table created successfully</div>";
} else {
    echo "<div class='info'>ℹ️ Messages table already exists</div>";
}

// ===== STEP 5: Create Message Replies Table =====
echo "<h2>STEP 5: Create Message Replies Table</h2>";

$replies_table = "CREATE TABLE IF NOT EXISTS message_replies_tbl (
    reply_id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    sender_id INT NOT NULL,
    reply_text LONGTEXT NOT NULL,
    attachment_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages_tbl(message_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users_tbl(user_id) ON DELETE CASCADE,
    INDEX idx_message_id (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($db->query($replies_table)) {
    echo "<div class='success'>✅ Message Replies table created successfully</div>";
} else {
    echo "<div class='info'>ℹ️ Message Replies table already exists</div>";
}

// ===== STEP 6: Verify Admin Login =====
echo "<h2>STEP 6: Verify Admin Account</h2>";

$verify = $db->prepare("SELECT user_id, email, role, first_name, last_name FROM users_tbl WHERE email = ?");
$verify->bind_param("s", $admin_email);
$verify->execute();
$verify_result = $verify->get_result();
$admin = $verify_result->fetch_assoc();

if ($admin && $admin['role'] === 'admin') {
    echo "<div class='success'>✅ Admin account verified and ready!</div>";
    echo "<p><strong>Admin Details:</strong></p>";
    echo "<ul>";
    echo "<li>ID: " . $admin['user_id'] . "</li>";
    echo "<li>Name: " . htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($admin['email']) . "</li>";
    echo "<li>Role: " . htmlspecialchars($admin['role']) . "</li>";
    echo "</ul>";
} else {
    echo "<div class='error'>❌ Admin account not properly configured</div>";
}

echo "<hr>";
echo "<div class='info'>";
echo "<h3>✅ Setup Complete!</h3>";
echo "<p><strong>You can now login as admin with:</strong></p>";
echo "<ul>";
echo "<li>Email: <code>sinta2026@gmail.com</code></li>";
echo "<li>Password: <code>sintaAdmins2026</code></li>";
echo "</ul>";
echo "<p><a href='/SINTA/public/index.php?route=signin' style='background: #8A7650; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 10px;'>🔐 Go to Login</a></p>";
echo "</div>";

echo "</div>
</body>
</html>";
?>

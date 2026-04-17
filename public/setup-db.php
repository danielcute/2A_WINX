<?php
/**
 * Database Setup Script
 * Run this file once to initialize the SINTA database and tables
 * Access via: http://localhost/SINTA/public/setup-db.php
 */

define('ROOT_PATH', dirname(__DIR__));

// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = ''; // Leave empty for XAMPP default
$database = 'sinta_db';

// Create connection without selecting database first
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>SINTA Database Setup</h2>";

// Create database
$create_db = "CREATE DATABASE IF NOT EXISTS `$database`";
if ($conn->query($create_db) === TRUE) {
    echo "✓ Database created successfully or already exists.<br>";
} else {
    die("✗ Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);
$conn->set_charset("utf8mb4");

// Create users table
$create_users_table = "CREATE TABLE IF NOT EXISTS `users_tbl` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `birthday` DATE,
    `address` VARCHAR(255),
    `city` VARCHAR(100),
    `birthday` DATE,
    `image` VARCHAR(255),
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($create_users_table) === TRUE) {
    echo "✓ Users table created successfully or already exists.<br>";
} else {
    die("✗ Error creating users table: " . $conn->error);
}

// Create bookings table
$create_bookings_table = "CREATE TABLE IF NOT EXISTS `bookings_tbl` (
    `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `event_type` VARCHAR(100),
    `event_date` DATE,
    `guest_count` INT,
    `package_id` INT,
    `total_price` DECIMAL(10, 2),
    `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users_tbl`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($create_bookings_table) === TRUE) {
    echo "✓ Bookings table created successfully or already exists.<br>";
} else {
    die("✗ Error creating bookings table: " . $conn->error);
}

// Create packages table
$create_packages_table = "CREATE TABLE IF NOT EXISTS `packages_tbl` (
    `package_id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_name` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10, 2) NOT NULL,
    `event_type` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($create_packages_table) === TRUE) {
    echo "✓ Packages table created successfully or already exists.<br>";
} else {
    die("✗ Error creating packages table: " . $conn->error);
}

// Create messages table
$create_messages_table = "CREATE TABLE IF NOT EXISTS `messages_tbl` (
    `message_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `subject` VARCHAR(200),
    `message` TEXT NOT NULL,
    `status` ENUM('unread', 'read') DEFAULT 'unread',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users_tbl`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($create_messages_table) === TRUE) {
    echo "✓ Messages table created successfully or already exists.<br>";
} else {
    die("✗ Error creating messages table: " . $conn->error);
}

echo "<br><h3 style='color: green;'>✓ Database setup completed successfully!</h3>";
echo "<p>You can now proceed to <a href='/SINTA/public/index.php?route=landing'>Sinta Application</a></p>";
echo "<p><strong>Admin Login:</strong><br>";
echo "Email: admin@sinta.com<br>";
echo "Password: sinta2024</p>";

$conn->close();
?>

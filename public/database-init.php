<?php
/**
 * Database Initialization Script
 * Creates/Updates all necessary database tables with proper schemas
 * Run once to initialize the database
 */

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$database = 'sinta_db';
$port = 3307;

// Create connection
$mysqli = new mysqli($host, $user, $pass, $database, $port);

if ($mysqli->connect_error) {
    die('Connection Error: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

// Track results
$results = [];

// 1. USERS TABLE
$sql = "CREATE TABLE IF NOT EXISTS users_tbl (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    $results[] = '✓ users_tbl created/verified';
} else {
    $results[] = '✗ users_tbl: ' . $mysqli->error;
}

// 2. PACKAGES TABLE
$sql = "CREATE TABLE IF NOT EXISTS packages_tbl (
    package_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description LONGTEXT,
    price DECIMAL(10, 2),
    image VARCHAR(255),
    category VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    $results[] = '✓ packages_tbl created/verified';
} else {
    $results[] = '✗ packages_tbl: ' . $mysqli->error;
}

// 3. CHECKOUT TABLE (Bookings)
$sql = "CREATE TABLE IF NOT EXISTS checkout_tbl (
    checkout_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    package_id INT,
    event_date DATE,
    guest_count INT,
    venue_location VARCHAR(255),
    special_requests LONGTEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    total_price DECIMAL(10, 2),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users_tbl(user_id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages_tbl(package_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    $results[] = '✓ checkout_tbl created/verified';
} else {
    $results[] = '✗ checkout_tbl: ' . $mysqli->error;
}

// 4. CUSTOMIZATIONS TABLE
$sql = "CREATE TABLE IF NOT EXISTS customizations_tbl (
    customization_id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description LONGTEXT,
    price DECIMAL(10, 2),
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages_tbl(package_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    $results[] = '✓ customizations_tbl created/verified';
} else {
    $results[] = '✗ customizations_tbl: ' . $mysqli->error;
}

// 5. MESSAGES TABLE
$sql = "CREATE TABLE IF NOT EXISTS messages_tbl (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    recipient_id INT,
    subject VARCHAR(255),
    message LONGTEXT,
    read_status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users_tbl(user_id) ON DELETE SET NULL,
    FOREIGN KEY (recipient_id) REFERENCES users_tbl(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    $results[] = '✓ messages_tbl created/verified';
} else {
    $results[] = '✗ messages_tbl: ' . $mysqli->error;
}

// 6. TESTIMONIALS TABLE
$sql = "CREATE TABLE IF NOT EXISTS testimonials_tbl (
    testimonial_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    package_id INT,
    rating INT,
    comment LONGTEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users_tbl(user_id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages_tbl(package_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    $results[] = '✓ testimonials_tbl created/verified';
} else {
    $results[] = '✗ testimonials_tbl: ' . $mysqli->error;
}

// 7. OCCASIONS TABLE
$sql = "CREATE TABLE IF NOT EXISTS occasions_tbl (
    occasion_id INT PRIMARY KEY AUTO_INCREMENT,
    occasion_name VARCHAR(255) NOT NULL,
    description LONGTEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    $results[] = '✓ occasions_tbl created/verified';
} else {
    $results[] = '✗ occasions_tbl: ' . $mysqli->error;
}

// 8. PLANS TABLE (if needed)
$sql = "CREATE TABLE IF NOT EXISTS plans_tbl (
    plan_id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT,
    plan_name VARCHAR(255),
    plan_details LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages_tbl(package_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    $results[] = '✓ plans_tbl created/verified';
} else {
    $results[] = '✗ plans_tbl: ' . $mysqli->error;
}

// Create indexes for performance
$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_user_email ON users_tbl(email)",
    "CREATE INDEX IF NOT EXISTS idx_checkout_user ON checkout_tbl(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_checkout_package ON checkout_tbl(package_id)",
    "CREATE INDEX IF NOT EXISTS idx_checkout_status ON checkout_tbl(status)",
    "CREATE INDEX IF NOT EXISTS idx_messages_recipient ON messages_tbl(recipient_id)",
    "CREATE INDEX IF NOT EXISTS idx_testimonials_status ON testimonials_tbl(status)",
    "CREATE INDEX IF NOT EXISTS idx_customization_package ON customizations_tbl(package_id)"
];

foreach ($indexes as $index) {
    if ($mysqli->query($index)) {
        $results[] = '✓ Index created';
    }
}

$mysqli->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Initialization</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .result { padding: 10px; margin: 10px 0; border-radius: 4px; font-family: monospace; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .summary { text-align: center; margin-top: 30px; font-size: 18px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Initialization</h1>
        <hr>
        <?php foreach ($results as $result): ?>
            <div class="result <?php echo strpos($result, '✓') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($result); ?>
            </div>
        <?php endforeach; ?>
        <div class="summary">
            ✓ Database Setup Complete!
        </div>
    </div>
</body>
</html>

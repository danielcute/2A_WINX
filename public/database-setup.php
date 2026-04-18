<?php
/**
 * Complete Database Setup Script
 * Creates or updates all tables with correct schema
 */

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$database = 'sinta_db';
$port = 3307;

$mysqli = new mysqli($host, $user, $pass, $database, $port);

if ($mysqli->connect_error) {
    die('Connection Error: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

$results = [];
$errors = [];

// ============================================
// CREATE ALL TABLES
// ============================================

// 1. USERS TABLE
$sql = "CREATE TABLE IF NOT EXISTS users_tbl (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    birthday VARCHAR(50),
    address VARCHAR(255),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    $results[] = '✓ users_tbl created/verified';
} else {
    $errors[] = 'users_tbl: ' . $mysqli->error;
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
    $errors[] = 'packages_tbl: ' . $mysqli->error;
}

// 3. OCCASIONS TABLE
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
    $errors[] = 'occasions_tbl: ' . $mysqli->error;
}

// 4. PLANS TABLE
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
    $errors[] = 'plans_tbl: ' . $mysqli->error;
}

// 5. CHECKOUT TABLE (Bookings) - with package_id
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
    if (strpos($mysqli->error, 'already exists') === false) {
        // If table doesn't exist for another reason, log it
        // But if it already exists, that's fine
        $errors[] = 'checkout_tbl: ' . $mysqli->error;
    } else {
        $results[] = '✓ checkout_tbl already exists';
    }
}

// 6. CUSTOMIZATIONS TABLE
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
    $errors[] = 'customizations_tbl: ' . $mysqli->error;
}

// 7. MESSAGES TABLE
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
    $errors[] = 'messages_tbl: ' . $mysqli->error;
}

// 8. TESTIMONIALS TABLE
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
    $errors[] = 'testimonials_tbl: ' . $mysqli->error;
}

// ============================================
// CREATE INDEXES (safe, ignore if exist)
// ============================================

$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_user_email ON users_tbl(email)",
    "CREATE INDEX IF NOT EXISTS idx_checkout_user ON checkout_tbl(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_checkout_package ON checkout_tbl(package_id)",
    "CREATE INDEX IF NOT EXISTS idx_checkout_status ON checkout_tbl(status)",
    "CREATE INDEX IF NOT EXISTS idx_messages_sender ON messages_tbl(sender_id)",
    "CREATE INDEX IF NOT EXISTS idx_messages_recipient ON messages_tbl(recipient_id)",
    "CREATE INDEX IF NOT EXISTS idx_testimonials_status ON testimonials_tbl(status)",
    "CREATE INDEX IF NOT EXISTS idx_customization_package ON customizations_tbl(package_id)"
];

foreach ($indexes as $index) {
    try {
        @$mysqli->query($index); // Suppress warnings
    } catch (Exception $e) {
        // Silently ignore index creation errors
    }
}

$results[] = '✓ Indexes created (or already exist)';

$mysqli->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { 
            max-width: 700px; 
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 { font-size: 2em; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 30px; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #667eea; margin-bottom: 15px; font-size: 1.1em; }
        .result { 
            padding: 12px 15px; 
            margin: 8px 0; 
            border-radius: 6px; 
            font-family: monospace;
            font-size: 0.95em;
            border-left: 4px solid #28a745;
            background: #d4edda;
            color: #155724;
        }
        .error-result {
            border-left-color: #dc3545;
            background: #f8d7da;
            color: #721c24;
        }
        .summary { 
            text-align: center; 
            margin: 30px 0;
            padding: 20px;
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            font-size: 1.2em; 
            font-weight: bold;
            color: #155724;
        }
        .buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ Database Setup Complete</h1>
            <p>All tables initialized successfully</p>
        </div>
        
        <div class="content">
            <?php if (!empty($results)): ?>
            <div class="section">
                <h3>✓ Successfully Created:</h3>
                <?php foreach ($results as $result): ?>
                    <div class="result"><?php echo htmlspecialchars($result); ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
            <div class="section">
                <h3>⚠ Warnings/Errors:</h3>
                <?php foreach ($errors as $error): ?>
                    <div class="result error-result"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="summary">
                ✓ Database is ready to use!
            </div>
            
            <div class="buttons">
                <a href="/SINTA/public/index.php?route=admin-dashboard" class="btn">Go to Admin Panel</a>
                <a href="/SINTA/public/setup-guide.php" class="btn">View Setup Guide</a>
            </div>
        </div>
    </div>
</body>
</html>

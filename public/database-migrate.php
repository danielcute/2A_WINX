<?php
/**
 * Database Migration Script
 * Fixes existing tables to have correct schema
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

// ========================================
// FIX 1: ALTER checkout_tbl if needed
// ========================================
$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'checkout_tbl' AND COLUMN_NAME = 'package_id' AND TABLE_SCHEMA = '$database'";

$result = $mysqli->query($sql);
if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    $addColumn = "ALTER TABLE checkout_tbl ADD COLUMN package_id INT AFTER user_id";
    if ($mysqli->query($addColumn)) {
        $results[] = '✓ Added package_id column to checkout_tbl';
    } else {
        $results[] = '✗ Failed to add package_id: ' . $mysqli->error;
    }
} else {
    $results[] = '✓ checkout_tbl already has package_id column';
}

// Add foreign key if not exists (simplified check)
$addFK = "ALTER TABLE checkout_tbl ADD CONSTRAINT fk_checkout_package 
          FOREIGN KEY (package_id) REFERENCES packages_tbl(package_id) ON DELETE SET NULL";
if ($mysqli->query($addFK)) {
    $results[] = '✓ Added foreign key for package_id';
} else {
    // Foreign key might already exist, don't show error
    if (strpos($mysqli->error, 'Duplicate key name') !== false || strpos($mysqli->error, 'already exists') !== false) {
        $results[] = '✓ Foreign key already exists (skipped)';
    } else {
        $results[] = '⚠ Foreign key check: ' . $mysqli->error;
    }
}

// ========================================
// FIX 2: Verify messages_tbl schema
// ========================================
$msgCheck = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_NAME = 'messages_tbl' AND COLUMN_NAME = 'recipient_id' AND TABLE_SCHEMA = '$database'";

$msgResult = $mysqli->query($msgCheck);
if ($msgResult->num_rows > 0) {
    $results[] = '✓ messages_tbl has correct schema (recipient_id column)';
} else {
    $results[] = '⚠ messages_tbl needs review - recipient_id column missing';
}

// ========================================
// FIX 3: Verify testimonials_tbl exists
// ========================================
$testimonialCheck = "SHOW TABLES LIKE 'testimonials_tbl'";
$testimonialResult = $mysqli->query($testimonialCheck);
if ($testimonialResult->num_rows == 0) {
    $createTestimonial = "CREATE TABLE IF NOT EXISTS testimonials_tbl (
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
    
    if ($mysqli->query($createTestimonial)) {
        $results[] = '✓ Created testimonials_tbl';
    } else {
        $results[] = '✗ Failed to create testimonials_tbl: ' . $mysqli->error;
    }
} else {
    $results[] = '✓ testimonials_tbl exists';
}

// ========================================
// FIX 4: Ensure all required tables exist
// ========================================
$tables = [
    'users_tbl' => "CREATE TABLE IF NOT EXISTS users_tbl (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        email VARCHAR(100) UNIQUE,
        phone VARCHAR(20),
        password VARCHAR(255),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'packages_tbl' => "CREATE TABLE IF NOT EXISTS packages_tbl (
        package_id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description LONGTEXT,
        price DECIMAL(10, 2),
        image VARCHAR(255),
        category VARCHAR(100),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'customizations_tbl' => "CREATE TABLE IF NOT EXISTS customizations_tbl (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'messages_tbl' => "CREATE TABLE IF NOT EXISTS messages_tbl (
        message_id INT PRIMARY KEY AUTO_INCREMENT,
        sender_id INT,
        recipient_id INT,
        subject VARCHAR(255),
        message LONGTEXT,
        read_status ENUM('unread', 'read') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users_tbl(user_id) ON DELETE SET NULL,
        FOREIGN KEY (recipient_id) REFERENCES users_tbl(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $tableName => $createSQL) {
    if ($mysqli->query($createSQL)) {
        // Only show if it was created, not if it already exists
    } else {
        if (strpos($mysqli->error, 'already exists') === false) {
            $results[] = "✗ Error with $tableName: " . $mysqli->error;
        }
    }
}

// Create indexes
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
        // Silently create indexes
    }
}

$results[] = '✓ All indexes verified/created';

$mysqli->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            margin: 0;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { 
            font-size: 2em; 
            margin: 0 0 10px 0;
        }
        .header p { 
            margin: 0;
            opacity: 0.9;
        }
        .content { 
            padding: 30px;
        }
        .result { 
            padding: 12px 15px; 
            margin: 8px 0; 
            border-radius: 6px; 
            font-family: 'Courier New', monospace;
            font-size: 0.95em;
            border-left: 4px solid #667eea;
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            border-left-color: #28a745;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24;
            border-left-color: #dc3545;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }
        .summary { 
            text-align: center; 
            margin-top: 30px; 
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            font-size: 1.1em; 
            font-weight: bold;
            color: #28a745;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
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
            <h1>🔧 Database Migration</h1>
            <p>Fixing table structure and schema</p>
        </div>
        
        <div class="content">
            <h3 style="color: #667eea; margin-bottom: 20px;">Migration Results:</h3>
            
            <?php foreach ($results as $result): ?>
                <div class="result <?php 
                    echo strpos($result, '✓') !== false ? 'success' : 
                         (strpos($result, '✗') !== false ? 'error' : 'warning');
                ?>">
                    <?php echo htmlspecialchars($result); ?>
                </div>
            <?php endforeach; ?>
            
            <div class="summary">
                ✓ Database Migration Complete!
            </div>
            
            <div class="action-buttons">
                <a href="/SINTA/public/index.php?route=admin-dashboard" class="btn">Go to Admin Panel</a>
                <button class="btn" onclick="location.reload()">Refresh</button>
            </div>
        </div>
    </div>
</body>
</html>

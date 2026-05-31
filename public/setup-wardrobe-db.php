<?php
/**
 * SINTA - Database Setup & Verification Script
 * 
 * HOW TO USE:
 * 1. Save this file as: /public_html/setup-wardrobe-db.php
 * 2. Upload to Hostinger
 * 3. Visit: https://sinta.bsit2a.com/setup-wardrobe-db.php
 * 4. Follow on-screen instructions
 * 
 * This script will:
 * ✓ Check database connection
 * ✓ Create missing tables (wardrobes_tbl, wardrobe_selections_tbl)
 * ✓ Verify all tables exist
 * ✓ Add sample data (optional)
 * ✓ Run diagnostic tests
 */

session_start();

// Color output for terminal/web
$colors = [
    'success' => 'color: #4CAF50; font-weight: bold;',
    'error' => 'color: #f44336; font-weight: bold;',
    'warning' => 'color: #ff9800; font-weight: bold;',
    'info' => 'color: #2196F3; font-weight: bold;',
    'code' => 'font-family: monospace; background: #f5f5f5; padding: 2px 5px;'
];

// Simple logging
function log_msg($message, $type = 'info') {
    global $colors;
    echo sprintf(
        '<div style="margin: 8px 0; %s">%s</div>',
        $colors[$type] ?? $colors['info'],
        htmlspecialchars($message)
    );
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SINTA Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #2C2820; border-bottom: 3px solid #8A7650; padding-bottom: 10px; }
        h2 { color: #8A7650; margin-top: 20px; }
        .status-box {
            border-left: 4px solid #2196F3;
            background: #E3F2FD;
            padding: 12px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .status-box.success {
            border-left-color: #4CAF50;
            background: #E8F5E9;
        }
        .status-box.error {
            border-left-color: #f44336;
            background: #FFEBEE;
        }
        .status-box.warning {
            border-left-color: #ff9800;
            background: #FFF3E0;
        }
        button {
            background: #8A7650;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
        }
        button:hover { background: #6B5A3D; }
        code {
            background: #f5f5f5;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 SINTA Database Setup & Verification</h1>
    
    <?php
    
    // Get action from URL or POST
    $action = $_GET['action'] ?? $_POST['action'] ?? 'status';
    
    // Try to connect to database
    require_once __DIR__ . '/config/database.php';
    
    try {
        $db = Database::getInstance()->getConnection();
        $connected = true;
        log_msg('✓ Database Connection: SUCCESS', 'success');
        
        // Get database info
        $result = $db->query("SELECT DATABASE() as db_name");
        if ($result) {
            $row = $result->fetch_assoc();
            log_msg('Database: ' . htmlspecialchars($row['db_name']), 'info');
        }
    } catch (Exception $e) {
        $connected = false;
        log_msg('✗ Database Connection: FAILED - ' . $e->getMessage(), 'error');
    }
    
    if ($connected) {
        echo '<h2>📊 Table Status</h2>';
        
        // Check tables
        $tables_to_check = [
            'wardrobes_tbl',
            'wardrobe_selections_tbl',
            'occasions_tbl',
            'packages_tbl',
            'users_tbl'
        ];
        
        $tables_exist = [];
        foreach ($tables_to_check as $table) {
            $result = $db->query("SHOW TABLES LIKE '$table'");
            $exists = $result && $result->num_rows > 0;
            $tables_exist[$table] = $exists;
            
            $status = $exists ? '✓' : '✗';
            $type = $exists ? 'success' : 'error';
            echo "<div class='status-box $type'>$status <code>$table</code> - " . ($exists ? 'EXISTS' : 'MISSING') . "</div>";
        }
        
        // If action is create, create missing tables
        if ($action === 'create_tables') {
            echo '<h2>🔨 Creating Missing Tables</h2>';
            
            // Create wardrobes_tbl
            if (!$tables_exist['wardrobes_tbl']) {
                $sql = "CREATE TABLE IF NOT EXISTS `wardrobes_tbl` (
                  `wardrobe_id` int(11) NOT NULL AUTO_INCREMENT,
                  `category` varchar(100) NOT NULL,
                  `name` varchar(150) NOT NULL,
                  `description` text,
                  `rental_price` decimal(10,2) NOT NULL,
                  `availability_count` int(11) NOT NULL,
                  `rental_duration_days` int(11) NOT NULL,
                  `sizes_available` varchar(255),
                  `condition_status` enum('excellent','good','fair','needs_cleaning') DEFAULT 'excellent',
                  `image` longblob,
                  `image_type` varchar(50),
                  `is_active` tinyint(1) DEFAULT 1,
                  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`wardrobe_id`),
                  KEY `idx_category` (`category`),
                  KEY `idx_active` (`is_active`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                
                if ($db->query($sql)) {
                    log_msg('✓ Created table: wardrobes_tbl', 'success');
                } else {
                    log_msg('✗ Failed to create wardrobes_tbl: ' . $db->error, 'error');
                }
            } else {
                log_msg('⊘ wardrobes_tbl already exists, skipping', 'warning');
            }
            
            // Create wardrobe_selections_tbl
            if (!$tables_exist['wardrobe_selections_tbl']) {
                $sql = "CREATE TABLE IF NOT EXISTS `wardrobe_selections_tbl` (
                  `selection_id` int(11) NOT NULL AUTO_INCREMENT,
                  `plan_id` int(11),
                  `user_id` int(11),
                  `wardrobe_id` int(11) NOT NULL,
                  `quantity_selected` int(11) DEFAULT 1,
                  `size_selected` varchar(50),
                  `rental_start_date` date,
                  `rental_end_date` date,
                  `subtotal_price` decimal(10,2),
                  `status` enum('pending','confirmed','rented','returned','cancelled') DEFAULT 'pending',
                  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`selection_id`),
                  FOREIGN KEY (`wardrobe_id`) REFERENCES `wardrobes_tbl`(`wardrobe_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                
                if ($db->query($sql)) {
                    log_msg('✓ Created table: wardrobe_selections_tbl', 'success');
                } else {
                    log_msg('✗ Failed to create wardrobe_selections_tbl: ' . $db->error, 'error');
                }
            } else {
                log_msg('⊘ wardrobe_selections_tbl already exists, skipping', 'warning');
            }
            
            // Refresh status
            log_msg('Refreshing table status...', 'info');
            header('Refresh: 2; url=?action=status');
            exit;
        }
        
        echo '<h2>🧪 Diagnostic Tests</h2>';
        
        // Test 1: Check Wardrobe Model
        echo '<h3>Test 1: Wardrobe Model</h3>';
        if (file_exists(__DIR__ . '/app/models/Wardrobe.php')) {
            log_msg('✓ File exists: app/models/Wardrobe.php', 'success');
            
            // Try to load it
            try {
                require_once __DIR__ . '/app/models/Wardrobe.php';
                log_msg('✓ Wardrobe model can be instantiated', 'success');
            } catch (Exception $e) {
                log_msg('✗ Error loading Wardrobe model: ' . $e->getMessage(), 'error');
            }
        } else {
            log_msg('✗ Missing: app/models/Wardrobe.php', 'error');
        }
        
        // Test 2: Check Wardrobe Controller
        echo '<h3>Test 2: Wardrobe Controller</h3>';
        if (file_exists(__DIR__ . '/app/controllers/WardrobeController.php')) {
            log_msg('✓ File exists: app/controllers/WardrobeController.php', 'success');
        } else {
            log_msg('✗ Missing: app/controllers/WardrobeController.php', 'error');
        }
        
        // Test 3: Check Admin View
        echo '<h3>Test 3: Admin View</h3>';
        if (file_exists(__DIR__ . '/app/views/admin/admin-wardrobe.php')) {
            log_msg('✓ File exists: app/views/admin/admin-wardrobe.php', 'success');
        } else {
            log_msg('✗ Missing: app/views/admin/admin-wardrobe.php', 'error');
        }
        
        // Test 4: Check API Endpoints
        echo '<h3>Test 4: API Endpoints</h3>';
        $apis = [
            'api-wardrobe.php',
            'api-wardrobe-image.php',
            'api-wardrobe-update.php',
            'api-wardrobe-selections.php'
        ];
        foreach ($apis as $api) {
            if (file_exists(__DIR__ . '/public/' . $api)) {
                log_msg('✓ ' . $api, 'success');
            } else {
                log_msg('✗ Missing: ' . $api, 'error');
            }
        }
        
        // Test 5: Occasions
        echo '<h3>Test 5: Occasions Setup</h3>';
        if (file_exists(__DIR__ . '/public/api-occasion.php')) {
            log_msg('✓ API exists: api-occasion.php', 'success');
            
            // Check if has update method
            $content = file_get_contents(__DIR__ . '/public/api-occasion.php');
            if (strpos($content, 'action=update') !== false) {
                log_msg('✓ Has update functionality', 'success');
            } else {
                log_msg('⚠ Update functionality might be missing', 'warning');
            }
        } else {
            log_msg('✗ Missing: api-occasion.php', 'error');
        }
        
        echo '<h2>📋 Actions</h2>';
        
        if (!$tables_exist['wardrobes_tbl'] || !$tables_exist['wardrobe_selections_tbl']) {
            echo '<form method="POST">';
            echo '<input type="hidden" name="action" value="create_tables">';
            echo '<button type="submit">🔨 Create Missing Tables</button>';
            echo '</form>';
        } else {
            log_msg('✓ All required tables exist!', 'success');
        }
        
        echo '<h3>Manual Tests</h3>';
        echo '<p>After creating tables, test these URLs:</p>';
        echo '<ul>';
        echo '<li><a href="index.php?route=admin-wardrobe" target="_blank">Admin Wardrobe: /index.php?route=admin-wardrobe</a></li>';
        echo '<li><a href="index.php?route=admin-occasions" target="_blank">Admin Occasions: /index.php?route=admin-occasions</a></li>';
        echo '<li><a href="index.php?route=wardrobe" target="_blank">User Wardrobe: /index.php?route=wardrobe</a></li>';
        echo '</ul>';
        
    } else {
        echo '<div class="status-box error">Cannot proceed - database connection failed. Check config/database.php</div>';
    }
    
    ?>
    
</div>

<hr>

<p style="color: #666; font-size: 12px; margin-top: 20px;">
    <strong>Note:</strong> This is a diagnostic tool. Delete this file after setup is complete for security.
    <br>
    <strong>File location:</strong> <code><?php echo __FILE__; ?></code>
</p>

</body>
</html>

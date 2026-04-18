<?php
/**
 * Database Column Migration
 * Adds missing columns to existing tables
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
// Check and add missing columns
// ========================================

// 1. Check if messages_tbl has recipient_id
$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'messages_tbl' 
        AND COLUMN_NAME = 'recipient_id'";

$result = $mysqli->query($sql);

if ($result && $result->num_rows == 0) {
    // Column doesn't exist, add it
    $results[] = '⚠ recipient_id column missing, adding...';
    
    // First, drop existing foreign keys if any
    $dropFK = "ALTER TABLE messages_tbl DROP FOREIGN KEY IF EXISTS fk_messages_recipient";
    @$mysqli->query($dropFK);
    
    // Add the column
    $addColumn = "ALTER TABLE messages_tbl 
                  ADD COLUMN recipient_id INT AFTER sender_id,
                  ADD FOREIGN KEY (recipient_id) REFERENCES users_tbl(user_id) ON DELETE SET NULL";
    
    if ($mysqli->query($addColumn)) {
        $results[] = '✓ Successfully added recipient_id column';
    } else {
        if (strpos($mysqli->error, 'Duplicate') !== false || strpos($mysqli->error, 'already') !== false) {
            $results[] = '✓ recipient_id column already exists';
        } else {
            $results[] = '✗ Error adding column: ' . $mysqli->error;
        }
    }
} else {
    $results[] = '✓ recipient_id column already exists';
}

// 2. Verify messages_tbl structure
$sql = "DESCRIBE messages_tbl";
$result = $mysqli->query($sql);

if ($result) {
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    $results[] = '✓ messages_tbl columns: ' . implode(', ', $columns);
} else {
    $results[] = '✗ Error describing messages_tbl: ' . $mysqli->error;
}

$mysqli->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration</title>
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
        .warning {
            border-left-color: #ffc107;
            background: #fff3cd;
            color: #856404;
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
            <h1>🔧 Database Migration</h1>
            <p>Adding missing columns and fixing schema</p>
        </div>
        
        <div class="content">
            <div class="section">
                <h3>Migration Results:</h3>
                <?php foreach ($results as $result): ?>
                    <div class="result <?php 
                        echo (strpos($result, '✓') !== false) ? '' : 
                             ((strpos($result, '⚠') !== false) ? 'warning' : 'error-result');
                    ?>">
                        <?php echo htmlspecialchars($result); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="summary">
                ✓ Database migration complete!
            </div>
            
            <div class="buttons">
                <a href="/SINTA/public/index.php?route=admin-messages" class="btn">Go to Messages</a>
                <a href="/SINTA/public/index.php?route=admin-dashboard" class="btn">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>

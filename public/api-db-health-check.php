<?php
/**
 * Database Health Check API
 * Checks connectivity and status of all critical database tables
 */

session_start();

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, max-age=60'); // Cache for 60 seconds

try {
    $db = Database::getInstance()->getConnection();
    
    if (!$db) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'status' => 'offline',
            'message' => 'Database connection failed',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    // Test basic connection
    if (!$db->ping()) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'status' => 'offline',
            'message' => 'Database ping failed',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    // Check critical tables
    $criticalTables = [
        'users_tbl' => 'Users Database',
        'wardrobes_tbl' => 'Wardrobes Database',
        'plans_tbl' => 'Bookings/Events Database',
        'packages_tbl' => 'Packages Database',
        'customizations_tbl' => 'Customizations Database',
        'occasions_tbl' => 'Occasions Database',
        'messages_tbl' => 'Messages Database',
        'payments_tbl' => 'Payments Database'
    ];
    
    $tablesStatus = [];
    $allTablesHealthy = true;
    
    foreach ($criticalTables as $table => $label) {
        $result = $db->query("SELECT COUNT(*) as count FROM `$table`");
        
        if ($result) {
            $row = $result->fetch_assoc();
            $tablesStatus[$table] = [
                'name' => $label,
                'status' => 'healthy',
                'records' => (int)$row['count'],
                'accessible' => true
            ];
        } else {
            $allTablesHealthy = false;
            $tablesStatus[$table] = [
                'name' => $label,
                'status' => 'error',
                'error' => $db->error,
                'accessible' => false
            ];
        }
    }
    
    // Get database statistics
    $dbStats = [];
    $result = $db->query("SELECT 
        table_name, 
        table_rows,
        data_length,
        index_length
        FROM information_schema.TABLES 
        WHERE table_schema = DATABASE()");
    
    $totalSize = 0;
    $totalRows = 0;
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $totalSize += ($row['data_length'] + $row['index_length']);
            $totalRows += $row['table_rows'];
        }
    }
    
    // Build response
    $response = [
        'success' => true,
        'status' => $allTablesHealthy ? 'online' : 'degraded',
        'database' => [
            'connected' => true,
            'server_version' => $db->server_info,
            'database_name' => $db->select_db(substr($db->select_db(''), 1)) ? $db->select_db('') : 'unknown'
        ],
        'tables' => $tablesStatus,
        'statistics' => [
            'total_records' => $totalRows,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'check_timestamp' => date('Y-m-d H:i:s'),
            'all_tables_healthy' => $allTablesHealthy
        ]
    ];
    
    http_response_code(200);
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>

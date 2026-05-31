<?php
/**
 * Database Configuration — Sinta
 * Environment-aware configuration for local development and production deployment
 * 
 * AUTO-DETECTED:
 * - Local: localhost:3306, root user (no password)
 * - Production: Hostinger remote database with credentials
 */

// Detect environment
// Force LOCAL when running under XAMPP / localhost.
// (Your current app requests are being treated as PRODUCTION, causing DB port 3307 failures.)
$host = strtolower(trim($_SERVER['HTTP_HOST'] ?? ''));
$is_localhost = (
    $host === 'localhost' ||
    $host === '127.0.0.1' ||
    $host === 'localhost:8080' ||
    $host === 'localhost:80' ||
    str_starts_with($host, 'localhost') ||
    str_starts_with($host, '127.0.0.1') ||
    // CLI / internal script runs
    PHP_SAPI === 'cli' ||
    // Common local XAMPP variables
    getenv('XAMPP') !== false ||
    getenv('ENV') === 'local'
);

// Environment-based configuration
if ($is_localhost || gethostname() === 'DESKTOP' || getenv('ENV') === 'local') {
    // LOCAL DEVELOPMENT (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_PORT', 3306);              // Standard XAMPP MySQL port
    define('DB_USER', 'root');            // XAMPP default user
    define('DB_PASS', '');                // XAMPP default (no password)
    define('DB_NAME', 'sinta_db');        // Local database name
    define('ENVIRONMENT', 'LOCAL');
} else {
    // PRODUCTION (Hostinger)
    define('DB_HOST', 'localhost');       // Hostinger uses localhost from their server
    define('DB_PORT', 3307);              // Hostinger custom port
    define('DB_USER', 'u536627044_sinta');
    define('DB_PASS', 'Sinta2026');
    define('DB_NAME', 'u536627044_sinta');
    define('ENVIRONMENT', 'PRODUCTION');
}

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

        if ($this->connection->connect_error) {
            // Return JSON only (no warnings/HTML) for API consumers
            if (PHP_SAPI !== 'cli') {
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
            }
            error_log('Database connection failed: ' . $this->connection->connect_error);
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'Database connection failed. Please check your configuration.'
            ], JSON_UNESCAPED_SLASHES);
            exit;
        }

        $this->connection->set_charset('utf8mb4');
    }

    /**
     * Returns the singleton instance of Database.
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the raw MySQLi connection.
     */
    public function getConnection(): mysqli {
        return $this->connection;
    }u

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}
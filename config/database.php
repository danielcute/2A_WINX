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
$is_localhost = (
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
    $_SERVER['HTTP_HOST'] === 'localhost:8080' ||
    $_SERVER['HTTP_HOST'] === 'localhost:80' ||
    strpos($_SERVER['HTTP_HOST'], 'localhost') === 0 ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === 0
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
            error_log('Database connection failed: ' . $this->connection->connect_error);
            die(json_encode([
                'error' => true,
                'message' => 'Database connection failed. Please check your configuration.'
            ]));
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
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}
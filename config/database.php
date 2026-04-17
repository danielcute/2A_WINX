<?php
/**
 * Database Configuration — Sinta
 * Place this file at: /SINTA/config/database.php
 *
 * Matches your sinta_db running on XAMPP (MariaDB port 3307).
 * Change DB_HOST, DB_PORT, DB_USER, DB_PASS if your setup differs.
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3307);          // Change to 3306 if using default MySQL port
define('DB_USER', 'root');        // Change to your MySQL username
define('DB_PASS', '');            // Change to your MySQL password (blank by default in XAMPP)
define('DB_NAME', 'sinta_db');

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
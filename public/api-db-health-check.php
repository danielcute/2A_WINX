<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

$response = [
    'success' => false,
    'environment' => getenv('ENV') ?: null,
    'php_sapi' => PHP_SAPI,
    'db' => [
        'host' => defined('DB_HOST') ? DB_HOST : null,
        'port' => defined('DB_PORT') ? DB_PORT : null,
        'name' => defined('DB_NAME') ? DB_NAME : null,
        'user' => defined('DB_USER') ? DB_USER : null,
    ],
    'result' => null,
];

try {
    // Attempt connection
    $mysqli = Database::getInstance()->getConnection();

    if ($mysqli instanceof mysqli) {
        $response['success'] = true;
        $response['result'] = [
            'connected' => true,
            'server_info' => $mysqli->server_info ?? null,
            'server_version' => $mysqli->server_version ?? null,
        ];
    } else {
        $response['result'] = [
            'connected' => false,
            'error' => 'Database connection did not return mysqli instance',
        ];
    }
} catch (Throwable $e) {
    $response['result'] = [
        'connected' => false,
        'error' => $e->getMessage(),
    ];
}

echo json_encode($response);


<?php
// Define ROOT_PATH if not already defined (to ensure BASE_URL is available)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}

// Include index.php to set up BASE_URL if not defined
if (!defined('BASE_URL')) {
    // Calculate BASE_URL manually if not defined
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    $base_url = $protocol . $host . ($script_name === '/' || $script_name === '\\' ? '' : $script_name);
    if (strpos($base_url, '/public') !== false) {
        $base_url = str_replace('/public', '', $base_url);
    }
    define('BASE_URL', rtrim($base_url, '/'));
}

session_start();
session_destroy();
header('Location: ' . BASE_URL . '/index.php?route=signin');
exit;
<?php
session_start();

// Destroy session
session_destroy();

// Redirect to landing page
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$base_url = $protocol . $host . ($script_name === '/' || $script_name === '\\' ? '' : $script_name);
if (strpos($base_url, '/public') !== false) {
    $base_url = str_replace('/public', '', $base_url);
}
$base_url = rtrim($base_url, '/');

header('Location: ' . $base_url . '/index.php?route=landing');
exit;
?>

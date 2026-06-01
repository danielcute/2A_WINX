<?php
/**
 * Occasion Image Endpoint - serves occasion images as binary
 * GET /api-occasion-image.php?id=1
 */
ini_set('display_errors', 0);
error_reporting(0);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once ROOT_PATH . '/config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(404);
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT image, image_name FROM occasions_tbl WHERE occasion_id = ?");
if (!$stmt) {
    http_response_code(500);
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row || empty($row['image'])) {
    http_response_code(404);
    exit;
}

// Determine MIME type from filename
$mime = 'image/jpeg';
if (!empty($row['image_name'])) {
    $ext = strtolower(pathinfo($row['image_name'], PATHINFO_EXTENSION));
    $mimeMap = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
    ];
    $mime = $mimeMap[$ext] ?? 'image/jpeg';
}

// Cache headers
header('Content-Type: ' . $mime);
header('Cache-Control: public, max-age=86400');
header('Content-Length: ' . strlen($row['image']));

echo $row['image'];
exit;

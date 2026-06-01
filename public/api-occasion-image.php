<?php
/**
 * Occasion Image Endpoint - serves occasion images as binary
 * GET /api-occasion-image.php?id=1
 */
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure JSON header is always sent first to prevent HTML errors breaking frontend
header('Cache-Control: public, max-age=86400');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        http_response_code(404);
        exit;
    }

    $db = Database::getInstance()->getConnection();
    if (!$db) {
        http_response_code(500);
        exit;
    }

    $stmt = $db->prepare("SELECT image, image_name FROM occasions_tbl WHERE occasion_id = ?");
    if (!$stmt) {
        http_response_code(500);
        exit;
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        http_response_code(500);
        $stmt->close();
        exit;
    }

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

    // Send headers
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($row['image']));
    header('Pragma: public');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

    echo $row['image'];
    exit;

} catch (Exception $e) {
    error_log("api-occasion-image error: " . $e->getMessage());
    http_response_code(500);
    exit;
}

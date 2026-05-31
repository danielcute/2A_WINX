<?php
/**
 * API: Serve wardrobe image
 * File: /api-wardrobe-image.php
 *
 * GET  ?id=<wardrobe_id>   → streams image bytes with correct Content-Type
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Bootstrap ────────────────────────────────────────────────────────────────
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Wardrobe.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Invalid ID');
}

$wardrobeModel = new Wardrobe();
$img           = $wardrobeModel->getImage($id);

if (!$img) {
    // Return a 1×1 transparent GIF as placeholder
    http_response_code(404);
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

// Cache for 1 hour
header('Cache-Control: public, max-age=3600');
header('Content-Type: ' . $img['image_type']);
header('Content-Length: ' . strlen($img['image']));
echo $img['image'];
<?php
/**
 * Event Images Management Dashboard
 * Quick management tool for occasion images
 * 
 * Usage: Open in browser
 * URL: http://localhost/sinta/public/manage-event-images.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Require admin authentication
if (!isset($_SESSION['admin_logged_in']) && !isset($_GET['setup'])) {
    header('HTTP/1.1 403 Forbidden');
    die('Admin access required');
}

define('ROOT_PATH', dirname(dirname(__FILE__)));
require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? null;

// Handle AJAX requests for image management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $post_action = $_POST['action'] ?? null;
    
    if ($post_action === 'upload_image') {
        if (!isset($_POST['occasion_id']) || !isset($_FILES['image'])) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }
        
        $occasion_id = intval($_POST['occasion_id']);
        $file = $_FILES['image'];
        
        // Validate
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large']);
            exit;
        }
        
        // Read file
        $image_data = file_get_contents($file['tmp_name']);
        $image_name = basename($file['name']);
        
        // Update database
        $stmt = $db->prepare("UPDATE occasions_tbl SET image = ?, image_name = ? WHERE occasion_id = ?");
        if ($stmt) {
            $stmt->bind_param("bsi", $image_data, $image_name, $occasion_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Image uploaded']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            $stmt->close();
        }
        exit;
    }
    
    if ($post_action === 'delete_image') {
        $occasion_id = intval($_POST['occasion_id']);
        
        if ($db->query("UPDATE occasions_tbl SET image = NULL, image_name = NULL WHERE occasion_id = $occasion_id")) {
            echo json_encode(['success' => true, 'message' => 'Image deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting image']);
        }
        exit;
    }
}

// Get all occasions
$result = $db->query("SELECT occasion_id, events, descriptions, image_name, IF(image IS NOT NULL AND image != '', 1, 0) as has_image FROM occasions_tbl ORDER BY occasion_id");
$occasions = [];
while ($row = $result->fetch_assoc()) {
    $occasions[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Images Manager — Sinta Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            padding: 2rem;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        .header p {
            color: #666;
        }
        .occasions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .occasion-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .occasion-image {
            width: 100%;
            height: 150px;
            background: #e8e8e8;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .occasion-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .occasion-image.no-image {
            background: linear-gradient(135deg, #e8d5c4 0%, #d4c7b1 100%);
            color: #8a7650;
            font-size: 2rem;
        }
        .occasion-info {
            padding: 1rem;
        }
        .occasion-info h3 {
            margin-bottom: 0.25rem;
            color: #333;
        }
        .occasion-info p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        .occasion-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }
        .status-has-image {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-no-image {
            background: #fff3e0;
            color: #e65100;
        }
        .occasion-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .btn-upload {
            background: #8a7650;
            color: white;
        }
        .btn-upload:hover {
            background: #6b5a3e;
        }
        .btn-delete {
            background: #ff6b6b;
            color: white;
        }
        .btn-delete:hover {
            background: #ff5252;
        }
        .btn-delete:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        input[type="file"] {
            display: none;
        }
        .upload-input {
            display: none;
        }
        .setup-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .setup-section h2 {
            margin-bottom: 1rem;
            color: #333;
        }
        .setup-section p {
            margin-bottom: 1rem;
            color: #666;
        }
        .setup-code {
            background: #f5f5f5;
            padding: 1rem;
            border-radius: 4px;
            font-family: monospace;
            overflow-x: auto;
            margin-bottom: 1rem;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-box {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-box .number {
            font-size: 2rem;
            font-weight: bold;
            color: #8a7650;
        }
        .stat-box .label {
            color: #666;
            font-size: 0.9rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #2196f3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🖼️ Event Images Manager</h1>
            <p>Upload and manage occasion images for your event selection page</p>
        </div>
        
        <div class="setup-section">
            <h2>📊 Quick Stats</h2>
            <div class="stats">
                <div class="stat-box">
                    <div class="number"><?= count($occasions) ?></div>
                    <div class="label">Total Occasions</div>
                </div>
                <div class="stat-box">
                    <div class="number"><?= count(array_filter($occasions, fn($o) => $o['has_image'])) ?></div>
                    <div class="label">With Images</div>
                </div>
                <div class="stat-box">
                    <div class="number"><?= count(array_filter($occasions, fn($o) => !$o['has_image'])) ?></div>
                    <div class="label">Missing Images</div>
                </div>
            </div>
            
            <?php if (count(array_filter($occasions, fn($o) => !$o['has_image'])) > 0): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Tip:</strong> Some occasions are missing images. Run the setup script to generate placeholder images:
                    <div class="setup-code">php setup-event-images.php</div>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Great!</strong> All occasions have images. Users will see beautiful event previews!
                </div>
            <?php endif; ?>
        </div>
        
        <h2 style="margin-bottom: 1.5rem; color: #333;">📸 Manage Occasion Images</h2>
        
        <div class="occasions-grid">
            <?php foreach ($occasions as $occ): ?>
                <div class="occasion-card">
                    <div class="occasion-image <?= !$occ['has_image'] ? 'no-image' : '' ?>" id="preview_<?= $occ['occasion_id'] ?>">
                        <?php if (!$occ['has_image']): ?>
                            <i class="fas fa-image"></i>
                        <?php else: ?>
                            <img src="/api-occasion.php?image=<?= $occ['occasion_id'] ?>" alt="<?= htmlspecialchars($occ['events']) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="occasion-info">
                        <h3><?= htmlspecialchars($occ['events']) ?></h3>
                        <p><?= htmlspecialchars(substr($occ['descriptions'] ?? '', 0, 60)) ?></p>
                        <span class="occasion-status <?= $occ['has_image'] ? 'status-has-image' : 'status-no-image' ?>">
                            <?= $occ['has_image'] ? '✅ Has Image' : '⚠️ No Image' ?>
                        </span>
                        <div class="occasion-actions">
                            <button class="btn btn-upload" onclick="document.getElementById('file_<?= $occ['occasion_id'] ?>').click()">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                            <button class="btn btn-delete" <?= !$occ['has_image'] ? 'disabled' : '' ?> onclick="deleteImage(<?= $occ['occasion_id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                        <input type="file" id="file_<?= $occ['occasion_id'] ?>" class="upload-input" accept="image/*" onchange="uploadImage(<?= $occ['occasion_id'] ?>, this)">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        function uploadImage(occasionId, input) {
            if (!input.files[0]) return;
            
            const formData = new FormData();
            formData.append('action', 'upload_image');
            formData.append('occasion_id', occasionId);
            formData.append('image', input.files[0]);
            
            fetch('/manage-event-images.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Image uploaded successfully!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(err => {
                alert('❌ Upload failed: ' + err.message);
            });
        }
        
        function deleteImage(occasionId) {
            if (!confirm('Delete this image?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete_image');
            formData.append('occasion_id', occasionId);
            
            fetch('/manage-event-images.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Image deleted');
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>

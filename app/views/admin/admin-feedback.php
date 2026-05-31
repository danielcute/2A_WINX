<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define ROOT_PATH if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}

require_once ROOT_PATH . '/app/models/Feedback.php';

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin' && empty($_SESSION['admin_logged_in']))) {
    header('Location: ' . BASE_URL . '/index.php?route=signin');
    exit;
}

$feedbackModel = new Feedback();
$adminId = $_SESSION['user_id'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $action = $_POST['action'];
        $response = ['success' => false, 'message' => 'Invalid action'];
        
        if ($action === 'add_reply') {
            $feedbackId = (int)($_POST['feedback_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            
            if (!$feedbackId || empty($message)) {
                $response = ['success' => false, 'message' => 'Invalid data'];
            } else {
                $replyId = $feedbackModel->addAdminReply($feedbackId, $adminId, $message);
                if ($replyId) {
                    $response = ['success' => true, 'message' => 'Reply sent successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to send reply'];
                }
            }
        } elseif ($action === 'update_status') {
            $feedbackId = (int)($_POST['feedback_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            if (!$feedbackId || empty($status)) {
                $response = ['success' => false, 'message' => 'Invalid data'];
            } else {
                if ($feedbackModel->updateStatus($feedbackId, $status)) {
                    $response = ['success' => true, 'message' => 'Status updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to update status'];
                }
            }
        } elseif ($action === 'delete') {
            $feedbackId = (int)($_POST['feedback_id'] ?? 0);
            
            if (!$feedbackId) {
                $response = ['success' => false, 'message' => 'Invalid feedback ID'];
            } else {
                if ($feedbackModel->delete($feedbackId)) {
                    $response = ['success' => true, 'message' => 'Feedback deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete feedback'];
                }
            }
        }
        
        if (ob_get_level()) ob_clean();
        echo json_encode($response);
    } catch (Exception $e) {
        if (ob_get_level()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Page title for admin-nav
$page = 'admin-feedback';
$page_title = 'Feedback Management';

// Get filter and data
$status_filter = $_GET['status'] ?? null;
$feedbacks = $feedbackModel->getAll($status_filter);
$stats = $feedbackModel->getStats();

// Ensure stats array has all keys
$stats = array_merge([
    'total' => 0,
    'open' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'closed' => 0
], $stats);

// Get selected feedback details
$currentFeedback = null;
$currentReplies = [];
if (!empty($_GET['id'])) {
    $feedbackId = (int)$_GET['id'];
    $currentFeedback = $feedbackModel->findById($feedbackId);
    if ($currentFeedback) {
        $currentReplies = $feedbackModel->getReplies($currentFeedback['feedback_id']) ?: [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Feedback Management | Sinta</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Your existing styles (same as previous improved version) */
        body { background: #F5F0E8; }
        .admin-container { width: 100%; margin: 0; padding: 1.5rem; }
        .page-header { margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; background: white; padding: 1.5rem 2rem; border-radius: 24px; border: 1px solid var(--border); }
        .page-header h1 { font-family: var(--serif); font-size: 2.2rem; color: var(--dark); margin: 0; font-weight: 700; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 24px; border: 2px solid var(--border); text-align: center; transition: all 0.3s; }
        .stat-card:hover { border-color: var(--primary); transform: translateY(-4px); }
        .stat-card h3 { font-size: 2rem; color: var(--primary); margin: 0; font-weight: 800; }
        .stat-card p { color: var(--text-secondary); margin: 0.5rem 0 0; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }

        /* Unified Content Container */
        .content-wrapper { 
            display: grid; 
            grid-template-columns: 380px 1fr; 
            gap: 0; 
            background: white; 
            border-radius: 24px; 
            overflow: hidden; 
            border: 1px solid var(--border); 
            min-height: 70vh;
            box-shadow: var(--shadow-md);
        }

        .feedback-list { border-right: 1px solid var(--border); background: #FCFAF7; }
        .filter-tabs { display: flex; gap: 0.5rem; padding: 1rem; border-bottom: 1px solid #E2D9C8; overflow-x: auto; scrollbar-width: none; }
        .filter-btn { padding: 0.4rem 1rem; background: white; border: 1px solid var(--border); border-radius: 20px; cursor: pointer; color: var(--text-secondary); font-weight: 600; font-size: 0.8rem; transition: all 0.2s; text-decoration: none; white-space: nowrap; }
        .filter-btn.active { background: #8A7650; color: white; }

        .feedback-items { max-height: calc(70vh - 60px); overflow-y: auto; }
        .feedback-item { 
            padding: 1.25rem 1rem; 
            border-bottom: 1px solid #F0EBE3; 
            cursor: pointer; 
            transition: all 0.2s; 
            text-decoration: none; 
            display: flex; 
            gap: 0.8rem; 
            color: inherit; 
        }
        .feedback-item:hover { background: white; box-shadow: inset 4px 0 0 var(--primary); }
        .feedback-item.active { background: #F5F0E8; box-shadow: inset 4px 0 0 var(--primary); }
        
        .item-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary-pale); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
        .item-content { flex: 1; min-width: 0; }
        .feedback-subject { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .feedback-meta { font-size: 0.8rem; color: #8B7355; display: flex; justify-content: space-between; }

        .detail-panel { background: white; padding: 2rem; display: flex; flex-direction: column; }
        .detail-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #E2D9C8; }
        .detail-title { flex: 1; }
        .detail-subject { font-size: 1.6rem; font-weight: 700; color: #2C2820; margin-bottom: 0.75rem; font-family: var(--serif); }
        .detail-meta { font-size: 0.85rem; color: #8B7355; margin: 0.25rem 0; }
        
        /* Mobile 'Back' Navigation */
        .back-to-list { display: none; margin-bottom: 1.5rem; text-decoration: none; color: var(--primary); font-weight: 600; align-items: center; gap: 0.5rem; }

        .status-badge { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 30px; font-size: 0.75rem; font-weight: 600; }
        .status-open { background: #FFF3CD; color: #856404; }
        .status-in_progress { background: #D1ECF1; color: #0c5460; }
        .status-resolved { background: #D4EDDA; color: #155724; }
        .status-closed { background: #F8D7DA; color: #721c24; }
        .rating-stars { display: inline-flex; gap: 0.2rem; margin-left: 0.5rem; color: #FFC107; }
        .feedback-content { background: #F5F0E8; padding: 1.2rem; border-radius: 16px; margin: 1rem 0; line-height: 1.6; color: #2C2820; }
        .replies-container { margin: 1.5rem 0; }
        .replies-header { font-weight: 700; margin-bottom: 1rem; color: #2C2820; }
        .reply-item { background: #F9F7F5; border-left: 4px solid #8A7650; padding: 1rem; margin-bottom: 1rem; border-radius: 12px; }
        .reply-item.admin { border-left-color: #1976d2; background: #EFF7FF; }
        .reply-sender { font-weight: 700; color: #2C2820; }
        .reply-time { font-size: 0.7rem; color: #8B7355; margin-left: 1rem; }
        .reply-content { margin-top: 0.5rem; color: #4A443E; }
        .action-buttons { display: flex; flex-wrap: wrap; gap: 1rem; margin: 1.5rem 0 1rem; }
        .btn-action { padding: 0.7rem 1.5rem; border: none; border-radius: 40px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; }
        .btn-primary { background: #8A7650; color: white; }
        .btn-primary:hover { background: #6B5A40; transform: translateY(-2px); }
        .btn-danger { background: #f44336; color: white; }
        .btn-danger:hover { background: #d32f2f; transform: translateY(-2px); }
        .status-select { padding: 0.6rem 1rem; border-radius: 40px; border: 2px solid #E2D9C8; background: white; font-weight: 600; cursor: pointer; }
        .reply-form textarea { width: 100%; padding: 0.8rem; border: 2px solid #E2D9C8; border-radius: 16px; font-family: inherit; resize: vertical; min-height: 100px; }
        
        @media (max-width: 900px) { 
            .content-wrapper { grid-template-columns: 1fr; } 
            .feedback-list { display: <?= !empty($_GET['id']) ? 'none' : 'block' ?>; border-right: none; }
            .detail-panel { display: <?= !empty($_GET['id']) ? 'flex' : 'none' ?>; }
            .back-to-list { display: inline-flex; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

<div class="admin-container">
    <div class="page-header">
        <h1><i class="fas fa-comments"></i> Feedback Management</h1>
    </div>
    
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card"><h3><?php echo (int)($stats['total'] ?? 0); ?></h3><p>Total Feedback</p></div>
        <div class="stat-card"><h3><?php echo (int)($stats['open'] ?? 0); ?></h3><p>Open</p></div>
        <div class="stat-card"><h3><?php echo (int)($stats['in_progress'] ?? 0); ?></h3><p>In Progress</p></div>
        <div class="stat-card"><h3><?php echo (int)($stats['resolved'] ?? 0); ?></h3><p>Resolved</p></div>
    </div>
    
    <div class="content-wrapper">
        <!-- Left column: feedback list -->
        <div>
            <div class="filter-tabs">
                <a href="?route=admin-feedback" class="filter-btn <?php echo $status_filter === null ? 'active' : ''; ?>">All</a>
                <a href="?route=admin-feedback&status=open" class="filter-btn <?php echo $status_filter === 'open' ? 'active' : ''; ?>">Open</a>
                <a href="?route=admin-feedback&status=in_progress" class="filter-btn <?php echo $status_filter === 'in_progress' ? 'active' : ''; ?>">In Progress</a>
                <a href="?route=admin-feedback&status=resolved" class="filter-btn <?php echo $status_filter === 'resolved' ? 'active' : ''; ?>">Resolved</a>
                <a href="?route=admin-feedback&status=closed" class="filter-btn <?php echo $status_filter === 'closed' ? 'active' : ''; ?>">Closed</a>
            </div>
            <div class="feedback-list">
                <div class="feedback-items">
                    <?php if (empty($feedbacks)): ?>
                        <div class="empty-state"><i class="fas fa-inbox"></i><p>No feedback found</p></div>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb): ?>
                            <a href="?route=admin-feedback&id=<?php echo $fb['feedback_id']; ?>" class="feedback-item <?php echo (!empty($_GET['id']) && (int)$_GET['id'] === $fb['feedback_id']) ? 'active' : ''; ?>">
                                <div class="item-avatar"><?= substr($fb['first_name'], 0, 1) ?></div>
                                <div class="item-content">
                                    <div class="feedback-subject"><?php echo htmlspecialchars($fb['subject']); ?></div>
                                    <div class="feedback-meta">
                                        <span><?php echo htmlspecialchars($fb['first_name']); ?></span>
                                        <span><?php echo date('M d', strtotime($fb['created_at'])); ?></span>
                                    </div>
                                    <div style="margin-top: 0.25rem;"><span class="status-badge status-<?php echo $fb['status']; ?>" style="padding: 0.1rem 0.5rem; font-size: 0.65rem;"><?php echo ucfirst(str_replace('_', ' ', $fb['status'])); ?></span></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Right column: detail view -->
        <div>
            <?php if ($currentFeedback): ?>
                <div class="detail-panel">
                    <a href="?route=admin-feedback" class="back-to-list"><i class="fas fa-arrow-left"></i> Back to Inbox</a>
                    <div class="detail-header">
                        <div class="detail-title">
                            <div class="detail-subject"><?php echo htmlspecialchars($currentFeedback['subject']); ?></div>
                            <div class="detail-meta">
                                From: <strong><?php echo htmlspecialchars($currentFeedback['first_name'] . ' ' . ($currentFeedback['last_name'] ?? '')); ?></strong><br>
                                Email: <strong><?php echo htmlspecialchars($currentFeedback['email']); ?></strong><br>
                                Submitted: <?php echo date('M d, Y H:i', strtotime($currentFeedback['created_at'])); ?>
                            </div>
                            <div style="margin-top: 0.5rem;">
                                <span class="status-badge status-<?php echo $currentFeedback['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $currentFeedback['status'])); ?></span>
                                <span class="rating-stars">
                                    <?php 
                                    $rating = (int)($currentFeedback['rating'] ?? 0);
                                    for ($i = 0; $i < 5; $i++) {
                                        echo ($i < $rating) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div>
                            <select id="statusSelect" class="status-select" onchange="updateStatus(<?php echo $currentFeedback['feedback_id']; ?>, this.value)">
                                <option value="open" <?php echo $currentFeedback['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="in_progress" <?php echo $currentFeedback['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $currentFeedback['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $currentFeedback['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="feedback-content">
                        <?php echo nl2br(htmlspecialchars($currentFeedback['message'])); ?>
                    </div>
                    
                    <?php if (!empty($currentReplies)): ?>
                        <div class="replies-container">
                            <div class="replies-header"><i class="fas fa-reply-all"></i> Conversation</div>
                            <?php foreach ($currentReplies as $reply): ?>
                                <div class="reply-item <?php echo ($reply['is_admin'] ?? false) ? 'admin' : ''; ?>">
                                    <div>
                                        <span class="reply-sender"><?php echo htmlspecialchars($reply['sender_name'] ?? ($reply['is_admin'] ? 'Admin' : 'Customer')); ?></span>
                                        <span class="reply-time"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></span>
                                    </div>
                                    <div class="reply-content"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <button class="btn-action btn-primary" onclick="sendReply(<?php echo $currentFeedback['feedback_id']; ?>)">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                        <button class="btn-action btn-danger" onclick="deleteFeedback(<?php echo $currentFeedback['feedback_id']; ?>)">
                            <i class="fas fa-trash"></i> Delete Feedback
                        </button>
                    </div>
                    
                    <div class="reply-form">
                        <textarea id="replyMessage" placeholder="Type your response here..."></textarea>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comments" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p>Select feedback from the list to view and respond</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function sendReply(feedbackId) {
    const message = document.getElementById('replyMessage').value.trim();
    if (!message) {
        showToast('Please enter a reply', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'add_reply');
    formData.append('feedback_id', feedbackId);
    formData.append('message', message);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            document.getElementById('replyMessage').value = '';
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

function updateStatus(feedbackId, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('feedback_id', feedbackId);
    formData.append('status', status);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

function deleteFeedback(feedbackId) {
    if (!confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('feedback_id', feedbackId);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => location.href = '?route=admin-feedback', 1500);
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php include 'admin-footer.php'; ?>
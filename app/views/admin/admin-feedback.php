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

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /SINTA/public/index.php?route=signin');
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
            $message = $_POST['message'] ?? '';
            
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

$page_title = 'Feedback Management';
$status = $_GET['status'] ?? null;
$feedbacks = $feedbackModel->getAll($status);
$stats = $feedbackModel->getStats();

// Get current feedback if viewing details
$currentFeedback = null;
$currentReplies = null;
if (!empty($_GET['id'])) {
    $currentFeedback = $feedbackModel->findById((int)$_GET['id']);
    if ($currentFeedback) {
        $currentReplies = $feedbackModel->getReplies($currentFeedback['feedback_id']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Feedback Management | Sinta</title>
    <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #F5F0E8; }
        .admin-container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #2C2820; margin: 0; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center; }
        .stat-card h3 { font-size: 2rem; color: #8A7650; margin: 0; }
        .stat-card p { color: #8B7355; margin: 0.5rem 0 0; }
        
        .content-wrapper { display: grid; grid-template-columns: 350px 1fr; gap: 2rem; }
        
        .feedback-list { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .feedback-item { padding: 1rem; border-bottom: 1px solid #E2D9C8; cursor: pointer; transition: background 0.2s; }
        .feedback-item:hover { background: #F5F0E8; }
        .feedback-item.active { background: #8A7650; color: white; }
        .feedback-item.active .subject, .feedback-item.active .sender { color: white; }
        .feedback-item.active .meta { color: rgba(255,255,255,0.8); }
        .feedback-subject { font-weight: 600; color: #2C2820; margin-bottom: 0.25rem; }
        .feedback-item.active .subject { color: white; }
        .feedback-meta { font-size: 0.8rem; color: #8B7355; }
        .feedback-item.active .meta { color: rgba(255,255,255,0.8); }
        
        .detail-panel { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .detail-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem; }
        .detail-subject { font-size: 1.4rem; font-weight: 600; color: #2C2820; }
        .detail-meta { color: #8B7355; font-size: 0.95rem; margin: 0.5rem 0; }
        
        .status-badge { display: inline-block; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-open { background: #FFF3CD; color: #856404; }
        .status-in_progress { background: #D1ECF1; color: #0c5460; }
        .status-resolved { background: #D4EDDA; color: #155724; }
        .status-closed { background: #F8D7DA; color: #721c24; }
        
        .priority-badge { display: inline-block; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem; }
        .priority-low { background: #E8F5E9; color: #2E7D32; }
        .priority-medium { background: #FFF3E0; color: #E65100; }
        .priority-high { background: #FFEBEE; color: #C62828; }
        
        .feedback-content { background: #F5F0E8; padding: 1rem; border-radius: 8px; margin: 1rem 0; line-height: 1.6; color: #555; }
        
        .replies-container { margin: 2rem 0; }
        .replies-header { font-size: 1.1rem; font-weight: 600; color: #2C2820; margin-bottom: 1rem; }
        .reply-item { margin-bottom: 1rem; padding: 1rem; background: #F5F0E8; border-left: 4px solid #8A7650; border-radius: 4px; }
        .reply-sender { font-weight: 600; color: #2C2820; }
        .reply-time { font-size: 0.8rem; color: #8B7355; margin-left: 1rem; }
        .reply-content { margin-top: 0.5rem; color: #555; line-height: 1.5; }
        
        .reply-form { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #E2D9C8; }
        .reply-form textarea { width: 100%; padding: 1rem; border: 2px solid #E2D9C8; border-radius: 8px; font-family: inherit; min-height: 100px; }
        .reply-form textarea:focus { outline: none; border-color: #8A7650; }
        
        .action-buttons { display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap; }
        .btn-action { padding: 0.6rem 1.2rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background: #8A7650; color: white; }
        .btn-primary:hover { background: #6d5e40; }
        .btn-secondary { background: #E2D9C8; color: #2C2820; }
        .btn-secondary:hover { background: #CFC3B5; }
        .btn-danger { background: #f44336; color: white; }
        .btn-danger:hover { background: #d32f2f; }
        
        .empty-state { text-align: center; padding: 3rem; color: #8B7355; }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
        
        .toast { position: fixed; bottom: 2rem; right: 2rem; padding: 1rem 1.5rem; border-radius: 8px; color: white; font-weight: 600; z-index: 1000; }
        .toast.success { background: #2e7d32; }
        .toast.error { background: #c62828; }
        
        .filter-tabs { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
        .filter-btn { padding: 0.5rem 1rem; background: #E2D9C8; border: none; border-radius: 6px; cursor: pointer; color: #2C2820; font-weight: 600; transition: all 0.2s; }
        .filter-btn.active { background: #8A7650; color: white; }
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
        <div class="stat-card">
            <h3><?php echo $stats['total'] ?? 0; ?></h3>
            <p>Total Feedback</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['open'] ?? 0; ?></h3>
            <p>Open</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['in_progress'] ?? 0; ?></h3>
            <p>In Progress</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['resolved'] ?? 0; ?></h3>
            <p>Resolved</p>
        </div>
    </div>
    
    <!-- Content -->
    <div class="content-wrapper">
        <!-- Feedback List -->
        <div>
            <div class="filter-tabs">
                <a href="?route=admin-feedback" class="filter-btn <?php echo !$status ? 'active' : ''; ?>">All</a>
                <a href="?route=admin-feedback&status=open" class="filter-btn <?php echo $status === 'open' ? 'active' : ''; ?>">Open</a>
                <a href="?route=admin-feedback&status=in_progress" class="filter-btn <?php echo $status === 'in_progress' ? 'active' : ''; ?>">In Progress</a>
                <a href="?route=admin-feedback&status=resolved" class="filter-btn <?php echo $status === 'resolved' ? 'active' : ''; ?>">Resolved</a>
                <a href="?route=admin-feedback&status=closed" class="filter-btn <?php echo $status === 'closed' ? 'active' : ''; ?>">Closed</a>
            </div>
            
            <div class="feedback-list">
                <?php if (empty($feedbacks)): ?>
                    <div style="padding: 2rem; text-align: center; color: #8B7355;">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>No feedback found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($feedbacks as $fb): ?>
                        <a href="?route=admin-feedback&id=<?php echo $fb['feedback_id']; ?>" style="text-decoration: none; color: inherit;">
                            <div class="feedback-item <?php echo (!empty($_GET['id']) && $_GET['id'] == $fb['feedback_id']) ? 'active' : ''; ?>" onclick="selectFeedback(event, <?php echo $fb['feedback_id']; ?>)">
                                <div class="feedback-subject"><?php echo htmlspecialchars(substr($fb['subject'], 0, 30)); ?></div>
                                <div class="feedback-meta">
                                    <div><?php echo htmlspecialchars($fb['first_name'] . ' ' . $fb['last_name']); ?></div>
                                    <div><?php echo date('M d, Y', strtotime($fb['created_at'])); ?></div>
                                    <div><span class="status-badge status-<?php echo $fb['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $fb['status'])); ?></span></div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Detail Panel -->
        <div>
            <?php if ($currentFeedback): ?>
                <div class="detail-panel">
                    <div class="detail-header">
                        <div>
                            <div class="detail-subject"><?php echo htmlspecialchars($currentFeedback['subject']); ?></div>
                            <div class="detail-meta">
                                From: <strong><?php echo htmlspecialchars($currentFeedback['first_name'] . ' ' . $currentFeedback['last_name']); ?></strong>
                            </div>
                            <div class="detail-meta">
                                Email: <strong><?php echo htmlspecialchars($currentFeedback['email']); ?></strong>
                            </div>
                            <div class="detail-meta">
                                Submitted: <?php echo date('M d, Y H:i', strtotime($currentFeedback['created_at'])); ?>
                            </div>
                            <div style="margin-top: 0.5rem;">
                                <span class="status-badge status-<?php echo $currentFeedback['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $currentFeedback['status'])); ?></span>
                                <span style="display: inline-block; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: #FFF3E0; color: #E65100; margin-left: 0.5rem;">
                                    <?php 
                                    $rating = $currentFeedback['rating'] ?? 0;
                                    for ($i = 0; $i < 5; $i++) {
                                        echo ($i < $rating) ? '★' : '☆';
                                    }
                                    echo " Rating";
                                    ?>
                                </span>
                            </div>
                        </div>
                        <select id="statusSelect" onchange="updateStatus(<?php echo $currentFeedback['feedback_id']; ?>, this.value)" style="padding: 0.5rem; border: 2px solid #E2D9C8; border-radius: 6px; font-weight: 600;">
                            <option value="open" <?php echo $currentFeedback['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo $currentFeedback['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $currentFeedback['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?php echo $currentFeedback['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    
                    <div class="feedback-content">
                        <?php echo nl2br(htmlspecialchars($currentFeedback['message'])); ?>
                    </div>
                    
                    <?php if (!empty($currentReplies)): ?>
                        <div class="replies-container">
                            <div class="replies-header">Conversation</div>
                            <?php foreach ($currentReplies as $reply): ?>
                                <div class="reply-item">
                                    <div>
                                        <span class="reply-sender"><?php echo htmlspecialchars($reply['sender_name']); ?></span>
                                        <span class="reply-time"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></span>
                                    </div>
                                    <div class="reply-content">
                                        <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="reply-form">
                        <h4 style="color: #2C2820; margin-bottom: 1rem;">Add Response</h4>
                        <textarea id="replyMessage" placeholder="Type your response here..."></textarea>
                        <div class="action-buttons">
                            <button class="btn-action btn-primary" onclick="sendReply(<?php echo $currentFeedback['feedback_id']; ?>)">
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                            <button class="btn-action btn-danger" onclick="deleteFeedback(<?php echo $currentFeedback['feedback_id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="background: white; border-radius: 12px; padding: 3rem; text-align: center; color: #8B7355;">
                    <i class="fas fa-comments" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
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
    
    fetch('/SINTA/public/index.php?route=admin-feedback', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(r => r.json())
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
    
    fetch('/SINTA/public/index.php?route=admin-feedback', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

function deleteFeedback(feedbackId) {
    if (!confirm('Are you sure you want to delete this feedback?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('feedback_id', feedbackId);
    
    fetch('/SINTA/public/index.php?route=admin-feedback', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => location.href = '?route=admin-feedback', 1500);
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

function selectFeedback(e, feedbackId) {
    e.preventDefault();
    window.location.href = '?route=admin-feedback&id=' + feedbackId;
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
</body>
</html>

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

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?route=signin');
    exit;
}

$feedbackModel = new Feedback();
$userId = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'submit_feedback') {
            $data = [
                'user_id' => $userId,
                'subject' => $_POST['subject'] ?? '',
                'message' => $_POST['message'] ?? '',
                'priority' => $_POST['priority'] ?? 'medium'
            ];
            
            if (empty($data['subject']) || empty($data['message'])) {
                echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
            } else {
                $feedbackId = $feedbackModel->create($data);
                if ($feedbackId) {
                    echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully', 'feedback_id' => $feedbackId]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to submit feedback']);
                }
            }
        } elseif ($action === 'add_reply') {
            $feedbackId = (int)($_POST['feedback_id'] ?? 0);
            $message = $_POST['message'] ?? '';
            
            if (!$feedbackId || empty($message)) {
                echo json_encode(['success' => false, 'message' => 'Invalid reply data']);
            } else {
                // Check if feedback belongs to user
                $feedback = $feedbackModel->findById($feedbackId);
                if (!$feedback || $feedback['user_id'] != $userId) {
                    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                } else {
                    $replyId = $feedbackModel->addUserReply($feedbackId, $userId, $message);
                    if ($replyId) {
                        echo json_encode(['success' => true, 'message' => 'Reply added successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to add reply']);
                    }
                }
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

$page_title = 'Feedback & Support';
$userFeedbacks = $feedbackModel->getUserFeedback($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | Sinta</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .feedback-container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .feedback-header { margin-bottom: 2rem; }
        .feedback-header h1 { font-size: 2rem; color: #2C2820; margin-bottom: 0.5rem; font-family: 'Cormorant Garamond', serif; }
        .feedback-header p { color: #8B7355; font-size: 0.95rem; }
        
        .feedback-tabs { display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid #E2D9C8; }
        .feedback-tabs button { background: none; border: none; padding: 1rem 0; font-size: 1rem; color: #8B7355; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.3s; }
        .feedback-tabs button.active { color: #8A7650; border-bottom-color: #8A7650; }
        
        .form-section, .list-section { display: none; }
        .form-section.active, .list-section.active { display: block; }
        
        .form-section { background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2C2820; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem; border: 2px solid #E2D9C8; border-radius: 8px; font-family: inherit; font-size: 1rem; }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #8A7650; box-shadow: 0 0 0 3px rgba(138, 118, 80, 0.1); }
        
        .btn-submit { background: #8A7650; color: white; border: none; padding: 0.875rem 2rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-submit:hover { background: #6d5e40; }
        
        .feedback-list { display: flex; flex-direction: column; gap: 1.5rem; }
        .feedback-item { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid #8A7650; }
        .feedback-header-item { display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; }
        .feedback-subject { font-size: 1.1rem; font-weight: 600; color: #2C2820; margin-bottom: 0.25rem; }
        .feedback-meta { font-size: 0.85rem; color: #8B7355; }
        .feedback-status { display: inline-block; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-open { background: #FFF3CD; color: #856404; }
        .status-in_progress { background: #D1ECF1; color: #0c5460; }
        .status-resolved { background: #D4EDDA; color: #155724; }
        .status-closed { background: #F8D7DA; color: #721c24; }
        
        .priority { display: inline-block; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem; }
        .priority-low { background: #E8F5E9; color: #2E7D32; }
        .priority-medium { background: #FFF3E0; color: #E65100; }
        .priority-high { background: #FFEBEE; color: #C62828; }
        
        .feedback-rating { display: inline-block; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: #FFF3E0; color: #E65100; margin-left: 0.5rem; }
        
        .feedback-content { margin: 1rem 0; color: #555; line-height: 1.6; }
        
        .replies-section { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #E2D9C8; }
        .replies-section h4 { color: #2C2820; margin-bottom: 1rem; }
        
        .reply-item { margin-bottom: 1rem; padding: 1rem; background: #F5F0E8; border-radius: 8px; }
        .reply-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
        .reply-sender { font-weight: 600; color: #2C2820; }
        .reply-time { font-size: 0.8rem; color: #8B7355; }
        .reply-content { color: #555; line-height: 1.5; }
        
        .add-reply { margin-top: 1rem; }
        .add-reply textarea { width: 100%; padding: 0.75rem; border: 2px solid #E2D9C8; border-radius: 8px; font-family: inherit; font-size: 0.95rem; min-height: 80px; }
        .add-reply textarea:focus { outline: none; border-color: #8A7650; }
        .btn-reply { background: #8A7650; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-size: 0.9rem; font-weight: 600; cursor: pointer; margin-top: 0.5rem; }
        .btn-reply:hover { background: #6d5e40; }
        
        .empty-state { text-align: center; padding: 3rem 1rem; color: #8B7355; }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
        
        .toast { position: fixed; bottom: 2rem; right: 2rem; padding: 1rem 1.5rem; border-radius: 8px; color: white; font-weight: 600; z-index: 1000; animation: slideIn 0.3s ease; }
        .toast.success { background: #2e7d32; }
        .toast.error { background: #c62828; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body>
<?php include ROOT_PATH . '/app/views/user/nav.php'; ?>

<div class="feedback-container">
    <div class="feedback-header">
        <h1><i class="fas fa-comments" style="color: #8A7650;"></i> Feedback & Support</h1>
        <p>Share your feedback or report issues. Our team will respond to your message.</p>
    </div>
    
    <div class="feedback-tabs">
        <button class="tab-btn active" data-tab="submit">Submit Feedback</button>
        <button class="tab-btn" data-tab="history">Your Feedback</button>
    </div>
    
    <!-- Submit Feedback Form -->
    <div class="form-section active" id="submit-tab">
        <form id="feedbackForm">
            <div class="form-group">
                <label for="subject">Subject *</label>
                <input type="text" id="subject" name="subject" placeholder="Brief summary of your feedback" required>
            </div>
            
            <div class="form-group">
                <label for="rating">Rating</label>
                <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;" id="feedback-rating">
                  <button type="button" class="star-rating-btn" data-rating="1" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s; padding: 0; margin: 0;">★</button>
                  <button type="button" class="star-rating-btn" data-rating="2" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s; padding: 0; margin: 0;">★</button>
                  <button type="button" class="star-rating-btn" data-rating="3" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s; padding: 0; margin: 0;">★</button>
                  <button type="button" class="star-rating-btn" data-rating="4" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s; padding: 0; margin: 0;">★</button>
                  <button type="button" class="star-rating-btn" data-rating="5" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s; padding: 0; margin: 0;">★</button>
                </div>
                <input type="hidden" id="rating-value" name="rating" value="0">
            </div>
            
            <div class="form-group">
                <label for="message">Message *</label>
                <textarea id="message" name="message" placeholder="Describe your feedback in detail..." required></textarea>
            </div>
            
            <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Submit Feedback</button>
        </form>
    </div>
    
    <!-- Feedback History -->
    <div class="list-section" id="history-tab">
        <?php if (empty($userFeedbacks)): ?>
            <div class="empty-state">
                <div><i class="fas fa-inbox"></i></div>
                <p>No feedback yet. Start by <button class="tab-btn" data-tab="submit" style="background: none; color: #8A7650; text-decoration: underline; padding: 0;">submitting feedback</button></p>
            </div>
        <?php else: ?>
            <div class="feedback-list">
                <?php foreach ($userFeedbacks as $feedback): ?>
                    <div class="feedback-item" id="feedback-<?php echo $feedback['feedback_id']; ?>">
                        <div class="feedback-header-item">
                            <div>
                                <div class="feedback-subject"><?php echo htmlspecialchars($feedback['subject']); ?></div>
                                <div class="feedback-meta">
                                    Posted on <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                                    <span class="feedback-status status-<?php echo $feedback['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $feedback['status'])); ?></span>
                                    <span class="feedback-rating">
                                        <?php 
                                        $rating = $feedback['rating'] ?? 0;
                                        for ($i = 0; $i < 5; $i++) {
                                            echo ($i < $rating) ? '★' : '☆';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="feedback-content">
                            <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
                        </div>
                        
                        <?php if ($feedback['reply_count'] > 0): ?>
                            <div class="replies-section">
                                <h4><i class="fas fa-reply"></i> Responses (<?php echo $feedback['reply_count']; ?>)</h4>
                                <?php
                                $replies = $feedbackModel->getReplies($feedback['feedback_id']);
                                foreach ($replies as $reply):
                                ?>
                                    <div class="reply-item">
                                        <div class="reply-header">
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
                        
                        <?php if ($feedback['status'] !== 'closed'): ?>
                            <div class="add-reply">
                                <textarea class="reply-input" placeholder="Add your response..." data-feedback-id="<?php echo $feedback['feedback_id']; ?>"></textarea>
                                <button class="btn-reply" onclick="addReply(<?php echo $feedback['feedback_id']; ?>)"><i class="fas fa-comment"></i> Reply</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tabName = this.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('[id$="-tab"]').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(tabName + '-tab').classList.add('active');
    });
});

// Star rating for feedback page
const feedbackStarButtons = document.querySelectorAll('#feedback-rating .star-rating-btn');
feedbackStarButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const rating = this.dataset.rating;
        document.getElementById('rating-value').value = rating;
        feedbackStarButtons.forEach(b => b.style.color = '#ddd');
        for (let i = 0; i < rating; i++) {
            feedbackStarButtons[i].style.color = '#8A7650';
        }
    });
});

// Real-time polling for new replies every 30 seconds
setInterval(() => {
    const historyTab = document.getElementById('history-tab');
    if (historyTab && historyTab.classList.contains('active')) {
        // We only reload if the user is looking at their history
        // Alternatively, fetch just the items via a specific API
        // For now, we'll keep it simple
    }
}, 30000);

// Form submission
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'submit_feedback');
    formData.append('subject', document.getElementById('subject').value);
    formData.append('message', document.getElementById('message').value);
    formData.append('rating', document.getElementById('rating-value').value);
    
    fetch('index.php?route=feedback', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            document.getElementById('feedbackForm').reset();
            // Real-time update: Refresh only the history tab content
            if(window.loadClientFeedback) loadClientFeedback(); 
            setTimeout(() => {
                location.reload(); // Fallback to ensure state sync
            }, 1000);
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
});

function addReply(feedbackId) {
    const textarea = document.querySelector(`textarea[data-feedback-id="${feedbackId}"]`);
    const message = textarea.value.trim();
    
    if (!message) {
        showToast('Please enter your reply', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'add_reply');
    formData.append('feedback_id', feedbackId);
    formData.append('message', message);
    
    fetch('/index.php?route=feedback', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            textarea.value = '';
            setTimeout(() => location.reload(), 1500);
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

</body>
</html>

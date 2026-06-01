<?php
/**
 * Admin Messages Management Page (Real)
 */
$page = 'admin-messages';
$page_title = 'Messages';

if (!isset($_SESSION['user_id']) || empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . BASE_URL . '/index.php?route=signin');
    exit;
}

require_once ROOT_PATH . '/app/controllers/MessagingController.php';

$messagingController = new MessagingController();
$admin_id = $_SESSION['user_id'];

$message = '';
$error = '';

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $message_id = intval($_POST['message_id'] ?? 0);
    $reply_text = trim($_POST['reply_text'] ?? '');
    if ($message_id && $reply_text) {
        $result = $messagingController->replyToMessage($message_id, $admin_id, $reply_text);
        if ($result['success']) {
            $message = 'Reply sent successfully!';
        } else {
            $error = 'Failed to send reply: ' . $result['error'];
        }
    }
}

// Handle admin new message to user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_message') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message_text'] ?? '');
    if ($user_id && $subject && $message_text) {
        $result = $messagingController->sendAdminMessage($admin_id, $user_id, $subject, $message_text);
        if ($result['success']) {
            $message = 'Message sent to user!';
        } else {
            $error = 'Failed: ' . $result['error'];
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$messages = $messagingController->getAdminMessages($admin_id, $filter);
$unread_count = $messagingController->getUnreadCount($admin_id);

// Get selected message if viewing
$selected_message = null;
if (isset($_GET['message_id'])) {
    $message_id = intval($_GET['message_id']);
    $selected_message = $messagingController->getMessageWithReplies($message_id);
    $messagingController->markAsRead($message_id);
}

// Get list of users for "new message" dropdown
// Fetch users for the "new message" dropdown.
// MessagingController::$db is private, so do not access it directly here.
if (method_exists($messagingController, 'getUsersForNewMessage')) {
    $users = $messagingController->getUsersForNewMessage();
} else {
    // Fallback: attempt to use a public method that may exist.
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Messages | Sinta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <style>
        body { background: #f5f5f5; font-family: 'DM Sans', sans-serif; }
        .admin-container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 20px; 
            box-sizing: border-box;
            padding-top: 2rem;
        }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .page-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 1.9rem; color: #2C2820; margin: 0; font-weight: 700; letter-spacing: -0.03em; display: inline-flex; align-items: center; gap: 0.75rem; }
        .badge { display: inline-block; background: #f44336; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; margin-left: 10px; }
        .messages-layout { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
        .messages-list { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; height: 70vh; }
        .messages-header { padding: 15px; border-bottom: 1px solid #eee; }
        .filter-buttons { display: flex; gap: 5px; margin-bottom: 10px; }
        .filter-btn { padding: 8px 12px; border: 1px solid #ddd; background: white; border-radius: 5px; cursor: pointer; font-size: 0.85rem; }
        .filter-btn.active { background: #8A7650; color: white; border-color: #8A7650; }
        .messages-scroll { flex: 1; overflow-y: auto; }
        .message-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s; }
        .message-item:hover { background: #f9f9f9; }
        .message-item.active { background: #f0e8df; }
        .message-item.unread { background: #fffacd; font-weight: 600; }
        .message-from { font-weight: 600; color: #333; font-size: 0.95rem; }
        .message-subject { color: #666; font-size: 0.9rem; margin: 5px 0; }
        .message-preview { color: #999; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .message-date { color: #999; font-size: 0.8rem; margin-top: 5px; }
        .message-detail { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; }
        .msg-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .msg-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .detail-header { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .detail-from { font-size: 0.9rem; color: #666; margin-bottom: 10px; }
        .detail-subject { font-size: 1.5rem; font-weight: 700; color: #333; margin-bottom: 10px; }
        .detail-meta { font-size: 0.85rem; color: #999; }
        .detail-body { background: #fafafa; padding: 15px; border-radius: 5px; margin-bottom: 20px; line-height: 1.6; }
        .conversation { margin-bottom: 20px; }
        .conversation-item { margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-left: 4px solid #8A7650; border-radius: 3px; }
        .conversation-item.admin { background: #e3f2fd; border-left-color: #1976d2; }
        .conversation-sender { font-weight: 600; color: #333; margin-bottom: 5px; font-size: 0.9rem; }
        .conversation-text { color: #333; line-height: 1.5; }
        .conversation-date { font-size: 0.8rem; color: #999; margin-top: 5px; }
        .reply-form { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .reply-form label { display: block; font-weight: 600; margin-bottom: 10px; color: #333; }
        .reply-form textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; min-height: 120px; }
        .reply-form button { background: #8A7650; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; margin-top: 10px; }
        .reply-form button:hover { background: #6B5A3E; }

        @media (max-width: 1024px) {
            .admin-container {
                padding: 1.2rem;
            }

            .page-header {
                margin-bottom: 1.5rem;
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }

            .page-header h1 {
                font-size: 1.6rem;
            }

            .page-header .btn {
                width: 100%;
            }

            .messages-layout {
                grid-template-columns: 1fr;
                gap: 1.2rem;
            }

            .messages-list {
                max-height: 500px;
            }

            .message-detail {
                padding: 1.2rem;
            }
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }

            .page-header {
                gap: 0.75rem;
                margin-bottom: 1.2rem;
            }

            .page-header h1 {
                font-size: 1.3rem;
                gap: 0.5rem;
            }

            .page-header .btn {
                width: 100%;
                padding: 1rem;
                font-size: 0.9rem;
            }

            .messages-layout {
                gap: 1rem;
            }

            .messages-list {
                max-height: 400px;
                border-radius: 12px;
            }

            .messages-header {
                padding: 1rem;
            }

            .filter-buttons {
                gap: 0.4rem;
            }

            .filter-btn {
                padding: 0.6rem 1rem;
                font-size: 0.8rem;
                border-radius: 8px;
            }

            .messages-scroll {
                max-height: 350px;
            }

            .message-item {
                padding: 1rem 0.75rem;
            }

            .message-from {
                font-size: 0.9rem;
            }

            .message-subject {
                font-size: 0.85rem;
            }

            .message-preview {
                font-size: 0.8rem;
            }

            .message-detail {
                padding: 1rem;
                border-radius: 12px;
            }

            .detail-header {
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }

            .detail-subject {
                font-size: 1.2rem;
            }

            .detail-body {
                padding: 1rem;
                font-size: 0.9rem;
            }

            .conversation-item {
                padding: 1rem;
                margin-bottom: 0.75rem;
            }

            .conversation-sender {
                font-size: 0.85rem;
            }

            .conversation-text {
                font-size: 0.9rem;
            }

            .reply-form {
                margin-top: 1.2rem;
                padding-top: 1rem;
            }

            .reply-form label {
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
            }

            .reply-form textarea {
                padding: 0.75rem;
                font-size: 0.9rem;
                min-height: 100px;
                border-radius: 8px;
            }

            .reply-form button {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
                border-radius: 8px;
            }

            .msg-success,
            .msg-error {
                font-size: 0.9rem;
                padding: 1rem;
                border-radius: 8px;
            }

            .badge {
                margin-left: 0.5rem;
                padding: 3px 8px;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 600px) {
            .admin-container {
                padding: 0.75rem;
            }

            .admin-page-header {
                margin-bottom: 1rem;
            }

            .page-header {
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .page-header h1 {
                font-size: 1.1rem;
                gap: 0.4rem;
            }

            .page-header h1 i {
                font-size: 1.2rem;
            }

            .page-header .btn {
                padding: 0.8rem 1rem;
                font-size: 0.8rem;
                border-radius: 12px;
            }

            .page-header .btn i {
                font-size: 0.9rem;
            }

            .messages-layout {
                gap: 0.75rem;
            }

            .messages-list {
                max-height: 350px;
                border-radius: 10px;
            }

            .messages-header {
                padding: 0.75rem;
            }

            .filter-buttons {
                gap: 0.3rem;
            }

            .filter-btn {
                padding: 0.5rem 0.8rem;
                font-size: 0.75rem;
                border-radius: 6px;
            }

            .messages-scroll {
                max-height: 300px;
            }

            .message-item {
                padding: 0.75rem 0.5rem;
            }

            .message-from {
                font-size: 0.85rem;
            }

            .message-subject {
                font-size: 0.8rem;
                margin: 3px 0;
            }

            .message-preview {
                font-size: 0.75rem;
            }

            .message-date {
                font-size: 0.7rem;
            }

            .message-detail {
                padding: 0.75rem;
                border-radius: 10px;
            }

            .msg-success,
            .msg-error {
                font-size: 0.8rem;
                padding: 0.75rem;
                border-radius: 6px;
                margin-bottom: 1rem;
            }

            .detail-header {
                padding-bottom: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .detail-from {
                font-size: 0.8rem;
            }

            .detail-subject {
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }

            .detail-meta {
                font-size: 0.75rem;
            }

            .detail-body {
                padding: 0.75rem;
                font-size: 0.85rem;
                line-height: 1.4;
                margin-bottom: 1rem;
            }

            .conversation {
                margin-bottom: 1rem;
            }

            .conversation-item {
                padding: 0.75rem;
                margin-bottom: 0.5rem;
                border-radius: 8px;
            }

            .conversation-sender {
                font-size: 0.8rem;
                margin-bottom: 0.3rem;
            }

            .conversation-text {
                font-size: 0.8rem;
                line-height: 1.4;
            }

            .conversation-date {
                font-size: 0.65rem;
                margin-top: 0.3rem;
            }

            .reply-form {
                margin-top: 1rem;
                padding-top: 0.75rem;
            }

            .reply-form label {
                font-size: 0.8rem;
                margin-bottom: 0.4rem;
            }

            .reply-form textarea {
                padding: 0.6rem;
                font-size: 0.8rem;
                min-height: 80px;
                border-radius: 6px;
            }

            .reply-form button {
                padding: 0.6rem 1.2rem;
                font-size: 0.8rem;
                border-radius: 6px;
                width: 100%;
            }

            .badge {
                margin-left: 0.3rem;
                padding: 2px 6px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/admin-nav.php'; ?>
<div class="admin-container">
    <div class="admin-page-header page-header messages-header">
        <h1 class="admin-page-title"><i class="fas fa-envelope animated-icon"></i> Messages <?php if ($unread_count > 0): ?><span class="badge"><?= $unread_count ?> New</span><?php endif; ?></h1>
        <button class="btn btn--primary btn--sm" id="adminNewMsgBtn"><i class="fas fa-plus"></i> New Message</button>
    </div>
    
    <?php if ($message): ?>
        <div class="msg-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="messages-layout">
        <!-- Messages List -->
        <div class="messages-list">
            <div class="messages-header">
                <div class="filter-buttons">
                    <button class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>" onclick="window.location.href='?route=admin-messages&filter=all'">All</button>
                    <button class="filter-btn <?= $filter === 'unread' ? 'active' : '' ?>" onclick="window.location.href='?route=admin-messages&filter=unread'">Unread</button>
                    <button class="filter-btn <?= $filter === 'replied' ? 'active' : '' ?>" onclick="window.location.href='?route=admin-messages&filter=replied'">Replied</button>
                </div>
                <div style="font-size: 0.85rem; color: #666;">Total: <?= count($messages) ?></div>
            </div>
            <div class="messages-scroll">
                <?php if (empty($messages)): ?>
                    <div style="padding: 20px; text-align: center; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                        No messages
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <a href="?route=admin-messages&message_id=<?= $msg['message_id'] ?>" style="text-decoration: none; color: inherit;">
                            <div class="message-item <?= ($selected_message && $selected_message['message']['message_id'] === $msg['message_id']) ? 'active' : '' ?> <?= $msg['status'] === 'unread' ? 'unread' : '' ?>">
                                <div class="message-from">
                                    <i class="fas fa-user-circle"></i> 
                                    <?= htmlspecialchars($msg['first_name'] ?? 'Customer') ?> #<?= $msg['sender_id'] ?>
                                </div>
                                <div class="message-subject"><?= htmlspecialchars($msg['subject']) ?></div>
                                <div class="message-preview"><?= htmlspecialchars(substr($msg['message_text'], 0, 60)) ?></div>
                                <div class="message-date"><?= date('M d, Y H:i', strtotime($msg['created_at'] ?? 'now')) ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Message Detail -->
        <div class="message-detail">
            <?php if ($selected_message): ?>
                <div class="detail-header">
                    <div class="detail-from">
                        <i class="fas fa-user-circle"></i>
                        <?= htmlspecialchars($selected_message['message']['first_name'] ?? 'Customer') ?> #<?= $selected_message['message']['sender_id'] ?>
                        (<?= htmlspecialchars($selected_message['message']['email'] ?? 'No email') ?>)
                    </div>
                    <div class="detail-subject"><?= htmlspecialchars($selected_message['message']['subject']) ?></div>
                    <div class="detail-meta">
                        <i class="fas fa-clock"></i> <?= date('M d, Y H:i A', strtotime($selected_message['message']['created_at'] ?? 'now')) ?>
                        | <span style="background: #f0f0f0; padding: 3px 8px; border-radius: 3px; font-size: 0.8rem;">
                            <i class="fas fa-tag"></i> <?= ucfirst($selected_message['message']['message_type'] ?? 'inquiry') ?>
                        </span>
                    </div>
                </div>
                <div class="detail-body">
                    <?= nl2br(htmlspecialchars($selected_message['message']['message_text'])) ?>
                </div>
                
                <!-- Replies -->
                <?php if (!empty($selected_message['replies'])): ?>
                    <div style="margin-bottom: 20px; border-top: 2px solid #eee; padding-top: 20px;">
                        <h3 style="color: #333; margin-bottom: 15px;">Conversation History</h3>
                        <div class="conversation">
                            <?php foreach ($selected_message['replies'] as $reply): ?>
                                <div class="conversation-item <?= $reply['role'] === 'admin' ? 'admin' : '' ?>">
                                    <div class="conversation-sender">
                                        <i class="fas fa-user-circle"></i>
                                        <?php if ($reply['role'] === 'admin'): ?>
                                            Admin Support
                                            <span style="background: #1976d2; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.75rem; margin-left: 5px;">Staff</span>
                                        <?php else: ?>
                                            Customer #<?= $reply['sender_id'] ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="conversation-text">
                                        <?= nl2br(htmlspecialchars($reply['reply_text'] ?? '')) ?>
                                    </div>
                                    <div class="conversation-date">
                                        <?= date('M d, Y H:i A', strtotime($reply['created_at'] ?? 'now')) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Reply Form -->
                <form method="POST" class="reply-form">
                    <input type="hidden" name="action" value="reply">
                    <input type="hidden" name="message_id" value="<?= $selected_message['message']['message_id'] ?>">
                    <label for="reply_text"><i class="fas fa-reply"></i> Send Reply</label>
                    <textarea name="reply_text" id="reply_text" placeholder="Type your reply here..." required></textarea>
                    <button type="submit"><i class="fas fa-paper-plane"></i> Send Reply</button>
                </form>
            <?php else: ?>
                <div style="text-align: center; color: #999; padding: 40px;">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                    <p>Select a message to view details and reply</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- New Message Modal (Admin to User) -->
<div id="newMsgModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
    <div style="background:white; max-width:500px; width:90%; border-radius:12px; padding:1.5rem;">
        <h3>New Message to User</h3>
        <form method="POST">
            <input type="hidden" name="action" value="new_message">
            <label>Select User:</label>
            <select name="user_id" required style="width:100%; margin-bottom:1rem; padding:0.5rem;">
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?> (<?= $u['email'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="subject" placeholder="Subject" required style="width:100%; margin-bottom:1rem; padding:0.5rem;">
            <textarea name="message_text" placeholder="Message" rows="5" required style="width:100%; margin-bottom:1rem;"></textarea>
            <div style="display:flex; justify-content:flex-end; gap:1rem;">
                <button type="button" id="closeModalBtn" class="btn btn--ghost">Cancel</button>
                <button type="submit" class="btn btn--primary">Send</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal handling
const modal = document.getElementById('newMsgModal');
document.getElementById('adminNewMsgBtn')?.addEventListener('click', () => modal.style.display = 'flex');
document.getElementById('closeModalBtn')?.addEventListener('click', () => modal.style.display = 'none');
window.onclick = function(e) { if (e.target === modal) modal.style.display = 'none'; }

// Auto-refresh (optional, keep from original)
let lastMessageCount = <?= count($messages) ?>;
setInterval(() => {
    fetch('<?= BASE_URL ?>/api-messages.php?action=get-count', {
      credentials: 'same-origin'
    })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.messageCount > lastMessageCount) {
                location.reload();
            }
        }).catch(console.log);
}, 10000);
</script>
<?php include 'admin-footer.php'; ?></script>
<?php include "admin-footer.php"; ?>

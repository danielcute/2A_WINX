<?php 
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
$page_title = 'Message Management';

// Initialize messages session storage (simulating database)
if (!isset($_SESSION['admin_messages'])) {
    $_SESSION['admin_messages'] = [
        [
            'id' => 1,
            'user_id' => 1,
            'user_name' => 'Maria Santos',
            'user_email' => 'maria@email.com',
            'message' => 'Hi! I have a question about the wedding package. Can I customize the floral arrangements?',
            'is_admin_reply' => 0,
            'parent_id' => null,
            'status' => 'unread',
            'created_at' => '2026-04-14 09:30:00'
        ],
        [
            'id' => 2,
            'user_id' => 1,
            'user_name' => 'Maria Santos',
            'user_email' => 'maria@email.com',
            'message' => 'Also, what\'s the payment schedule like?',
            'is_admin_reply' => 0,
            'parent_id' => null,
            'status' => 'unread',
            'created_at' => '2026-04-14 09:31:00'
        ],
        [
            'id' => 3,
            'user_id' => 2,
            'user_name' => 'John Reyes',
            'user_email' => 'john@email.com',
            'message' => 'Is the Classic Birthday package available for December?',
            'is_admin_reply' => 0,
            'parent_id' => null,
            'status' => 'read',
            'created_at' => '2026-04-13 15:20:00'
        ]
    ];
}

// Initialize replies storage
if (!isset($_SESSION['admin_replies'])) {
    $_SESSION['admin_replies'] = [
        [
            'id' => 1,
            'message_id' => 3,
            'user_id' => 2,
            'reply' => 'Hi John! Yes, the Classic Birthday package is available in December. I\'d recommend booking early to secure your date!',
            'created_at' => '2026-04-13 16:00:00'
        ]
    ];
}

// Handle reply submission
$reply_success = '';
$reply_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_reply') {
        $message_id = (int)$_POST['message_id'];
        $user_id = (int)$_POST['user_id'];
        $user_name = $_POST['user_name'];
        $user_email = $_POST['user_email'];
        $reply_text = trim($_POST['reply_text']);
        
        if (empty($reply_text)) {
            $reply_error = 'Please enter a reply message.';
        } else {
            // Store reply
            $new_reply_id = count($_SESSION['admin_replies']) + 1;
            $_SESSION['admin_replies'][] = [
                'id' => $new_reply_id,
                'message_id' => $message_id,
                'user_id' => $user_id,
                'reply' => $reply_text,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Mark original message as replied
            foreach ($_SESSION['admin_messages'] as &$msg) {
                if ($msg['id'] == $message_id) {
                    $msg['status'] = 'replied';
                    break;
                }
            }
            
            // Update unread count (decrease)
            if (!isset($_SESSION['admin_unread_count'])) {
                $_SESSION['admin_unread_count'] = 0;
            }
            
            $reply_success = 'Reply sent successfully to ' . htmlspecialchars($user_name);
        }
    }
}

// Mark message as read when viewing conversation
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    foreach ($_SESSION['admin_messages'] as &$msg) {
        if ($msg['id'] == $view_id && $msg['status'] == 'unread') {
            $msg['status'] = 'read';
            // Decrease unread count
            if (isset($_SESSION['admin_unread_count']) && $_SESSION['admin_unread_count'] > 0) {
                $_SESSION['admin_unread_count']--;
            }
            break;
        }
    }
}

// Get all conversations grouped by user
$conversations = [];
foreach ($_SESSION['admin_messages'] as $msg) {
    $user_id = $msg['user_id'];
    if (!isset($conversations[$user_id])) {
        $conversations[$user_id] = [
            'user_id' => $user_id,
            'user_name' => $msg['user_name'],
            'user_email' => $msg['user_email'],
            'messages' => [],
            'last_message' => $msg['created_at'],
            'unread_count' => 0
        ];
    }
    $conversations[$user_id]['messages'][] = $msg;
    if ($msg['status'] == 'unread') {
        $conversations[$user_id]['unread_count']++;
    }
    if ($msg['created_at'] > $conversations[$user_id]['last_message']) {
        $conversations[$user_id]['last_message'] = $msg['created_at'];
    }
}

// Sort conversations by last message time (newest first)
usort($conversations, function($a, $b) {
    return strtotime($b['last_message']) - strtotime($a['last_message']);
});

// Get selected conversation
$selected_user = null;
$selected_messages = [];
$selected_user_id = null;

if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $selected_user_id = (int)$_GET['user'];
    if (isset($conversations[$selected_user_id])) {
        $selected_user = $conversations[$selected_user_id];
        $selected_messages = $selected_user['messages'];
    }
}

// Calculate total unread count
$total_unread = 0;
foreach ($conversations as $conv) {
    $total_unread += $conv['unread_count'];
}
$_SESSION['admin_unread_count'] = $total_unread;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Messages | Sinta</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .messages-container {
            display: flex;
            gap: 1.5rem;
            min-height: calc(100vh - 160px);
            background: white;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            overflow: hidden;
        }
        
        /* Conversations Sidebar */
        .conversations-sidebar {
            width: 320px;
            border-right: 1px solid var(--border);
            background: var(--bg-primary);
            display: flex;
            flex-direction: column;
        }
        
        .conversations-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border);
            background: var(--cream);
        }
        
        .conversations-header h3 {
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .conversations-header h3 i {
            color: var(--primary);
        }
        
        .conversations-search {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .conversations-search input {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 1px solid var(--border);
            border-radius: 60px;
            font-size: 0.8rem;
            outline: none;
        }
        
        .conversations-search input:focus {
            border-color: var(--primary);
        }
        
        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .conversation-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all var(--t-fast);
            text-decoration: none;
            color: inherit;
        }
        
        .conversation-item:hover {
            background: var(--cream);
        }
        
        .conversation-item.active {
            background: var(--primary-pale);
            border-left: 3px solid var(--primary);
        }
        
        .conversation-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary-pale);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
            flex-shrink: 0;
        }
        
        .conversation-info {
            flex: 1;
            min-width: 0;
        }
        
        .conversation-name {
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .conversation-name span:first-child {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .conversation-time {
            font-size: 0.65rem;
            color: var(--gray-light);
        }
        
        .conversation-preview {
            font-size: 0.75rem;
            color: var(--gray);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 0.2rem;
        }
        
        .unread-badge {
            background: var(--primary);
            color: white;
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        /* Chat Area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--bg-secondary);
        }
        
        .chat-header {
            padding: 1rem 1.5rem;
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .chat-user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary-pale);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
        }
        
        .chat-user-name h4 {
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }
        
        .chat-user-name p {
            font-size: 0.7rem;
            color: var(--gray);
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .message-group {
            display: flex;
            flex-direction: column;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 20px;
            margin-bottom: 0.25rem;
        }
        
        .message-bubble.user {
            background: white;
            border: 1px solid var(--border);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        
        .message-bubble.admin {
            background: var(--primary);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        
        .message-bubble.admin p {
            color: white;
        }
        
        .message-bubble p {
            font-size: 0.85rem;
            line-height: 1.5;
        }
        
        .message-time {
            font-size: 0.6rem;
            color: var(--gray-light);
            margin-top: 0.25rem;
        }
        
        .message-bubble.admin .message-time {
            color: rgba(255,255,255,0.7);
            text-align: right;
        }
        
        .message-bubble.user .message-time {
            margin-left: 0.5rem;
        }
        
        .reply-divider {
            margin: 0.5rem 0;
            font-size: 0.7rem;
            color: var(--gray-light);
            text-align: center;
            position: relative;
        }
        
        .reply-divider::before,
        .reply-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: var(--border);
        }
        
        .reply-divider::before {
            left: 0;
        }
        
        .reply-divider::after {
            right: 0;
        }
        
        /* Reply Form */
        .reply-form {
            padding: 1rem 1.5rem;
            background: white;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .reply-form textarea {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 20px;
            font-family: var(--sans);
            font-size: 0.85rem;
            resize: vertical;
            min-height: 60px;
            outline: none;
        }
        
        .reply-form textarea:focus {
            border-color: var(--primary);
        }
        
        .reply-form button {
            padding: 0.6rem 1.2rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 60px;
            cursor: pointer;
            transition: all var(--t-fast);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .reply-form button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .empty-chat {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }
        
        .empty-chat i {
            font-size: 3rem;
            color: var(--border);
            margin-bottom: 1rem;
        }
        
        /* Alert Messages */
        .alert-msg {
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 3px solid #2e7d32;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 3px solid #c62828;
        }
        
        @media (max-width: 768px) {
            .conversations-sidebar {
                width: 280px;
            }
            .message-bubble {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>

<?php include '../public/admin-nav.php'; ?>

<div style="padding: 0 2rem 2rem;">
    <!-- Success/Error Messages -->
    <?php if ($reply_success): ?>
        <div class="alert-msg alert-success" style="margin-bottom: 1rem;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($reply_success) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($reply_error): ?>
        <div class="alert-msg alert-error" style="margin-bottom: 1rem;">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($reply_error) ?>
        </div>
    <?php endif; ?>
    
    <div class="messages-container">
        <!-- Conversations Sidebar -->
        <div class="conversations-sidebar">
            <div class="conversations-header">
                <h3><i class="fas fa-envelope"></i> Customer Conversations</h3>
            </div>
            <div class="conversations-search">
                <input type="text" id="searchConv" placeholder="Search customers..." onkeyup="filterConversations()">
            </div>
            <div class="conversations-list" id="conversationsList">
                <?php if (empty($conversations)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--gray);">
                        <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                        <p style="margin-top: 0.5rem;">No messages yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <a href="admin-messages.php?user=<?= $conv['user_id'] ?>" 
                           class="conversation-item <?= ($selected_user_id == $conv['user_id']) ? 'active' : '' ?>"
                           data-name="<?= strtolower(htmlspecialchars($conv['user_name'])) ?>"
                           data-email="<?= strtolower(htmlspecialchars($conv['user_email'])) ?>">
                            <div class="conversation-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-name">
                                    <span><?= htmlspecialchars($conv['user_name']) ?></span>
                                    <?php if ($conv['unread_count'] > 0): ?>
                                        <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="conversation-time">
                                    <?= date('M d, g:i A', strtotime($conv['last_message'])) ?>
                                </div>
                                <div class="conversation-preview">
                                    <?php 
                                    $last_msg = end($conv['messages']);
                                    echo htmlspecialchars(substr($last_msg['message'], 0, 40)) . (strlen($last_msg['message']) > 40 ? '...' : '');
                                    ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Chat Area -->
        <div class="chat-area">
            <?php if ($selected_user): ?>
                <div class="chat-header">
                    <div class="chat-user-info">
                        <div class="chat-user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="chat-user-name">
                            <h4><?= htmlspecialchars($selected_user['user_name']) ?></h4>
                            <p><?= htmlspecialchars($selected_user['user_email']) ?></p>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge--primary">
                            <?= count($selected_messages) ?> message(s)
                        </span>
                    </div>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <?php 
                    $last_date = '';
                    foreach ($selected_messages as $msg):
                        $msg_date = date('F d, Y', strtotime($msg['created_at']));
                        if ($msg_date != $last_date):
                            $last_date = $msg_date;
                    ?>
                        <div class="reply-divider"><?= $msg_date ?></div>
                    <?php endif; ?>
                        <div class="message-group">
                            <div class="message-bubble user">
                                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                <div class="message-time">
                                    <?= date('g:i A', strtotime($msg['created_at'])) ?>
                                    <?php if ($msg['status'] == 'replied'): ?>
                                        <span style="color: var(--primary); margin-left: 0.5rem;">
                                            <i class="fas fa-check-double"></i> Replied
                                        </span>
                                    <?php elseif ($msg['status'] == 'read'): ?>
                                        <span style="color: var(--gray-light); margin-left: 0.5rem;">
                                            <i class="fas fa-check"></i> Read
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php 
                        // Display admin replies for this message
                        foreach ($_SESSION['admin_replies'] as $reply):
                            if ($reply['message_id'] == $msg['id']):
                        ?>
                            <div class="message-group">
                                <div class="message-bubble admin">
                                    <p><?= nl2br(htmlspecialchars($reply['reply'])) ?></p>
                                    <div class="message-time">
                                        <i class="fas fa-reply"></i> Admin · <?= date('g:i A', strtotime($reply['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    <?php endforeach; ?>
                </div>
                
                <form method="POST" class="reply-form" id="replyForm">
                    <input type="hidden" name="action" value="send_reply">
                    <input type="hidden" name="message_id" value="<?= $selected_messages[0]['id'] ?? '' ?>">
                    <input type="hidden" name="user_id" value="<?= $selected_user['user_id'] ?>">
                    <input type="hidden" name="user_name" value="<?= htmlspecialchars($selected_user['user_name']) ?>">
                    <input type="hidden" name="user_email" value="<?= htmlspecialchars($selected_user['user_email']) ?>">
                    <textarea name="reply_text" id="replyText" placeholder="Type your reply to <?= htmlspecialchars($selected_user['user_name']) ?>..." rows="2"></textarea>
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </form>
            <?php else: ?>
                <div class="empty-chat">
                    <i class="fas fa-inbox"></i>
                    <h3>No conversation selected</h3>
                    <p>Select a customer from the left to view and reply to messages</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom of chat
const chatMessages = document.getElementById('chatMessages');
if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Filter conversations
function filterConversations() {
    const searchTerm = document.getElementById('searchConv').value.toLowerCase();
    const conversations = document.querySelectorAll('.conversation-item');
    
    conversations.forEach(conv => {
        const name = conv.getAttribute('data-name') || '';
        const email = conv.getAttribute('data-email') || '';
        
        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            conv.style.display = 'flex';
        } else {
            conv.style.display = 'none';
        }
    });
}

// Auto-resize textarea
const replyText = document.getElementById('replyText');
if (replyText) {
    replyText.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
}

// Confirm before sending
document.getElementById('replyForm')?.addEventListener('submit', function(e) {
    const replyText = document.getElementById('replyText');
    if (replyText && replyText.value.trim() === '') {
        e.preventDefault();
        alert('Please enter a reply message.');
    }
});

// Refresh page to show new messages (or use AJAX polling)
// For demo, simple refresh after reply
<?php if ($reply_success): ?>
setTimeout(function() {
    window.location.href = window.location.href;
}, 1500);
<?php endif; ?>
</script>

</body>
</html>
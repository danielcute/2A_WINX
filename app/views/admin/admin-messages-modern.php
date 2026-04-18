<?php
/**
 * ADMIN MESSAGING INTERFACE - Modern Minimalist
 * Location: app/views/admin/admin-messages-modern.php
 * Real database integration for user-admin messaging
 */

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: /SINTA/public/index.php?route=admin-auth');
    exit;
}

require_once ROOT_PATH . '/app/models/Message.php';
require_once ROOT_PATH . '/app/models/User.php';

$pageTitle = 'Messages';
$messageModel = new Message();
$userModel = new User();

// Get all conversations with unread counts
$allConversations = $messageModel->getAllConversations();

// Get selected conversation
$selectedUserId = (int)($_GET['user_id'] ?? 0);
if ($selectedUserId > 0) {
    $selectedUser = $userModel->findById($selectedUserId);
    if ($selectedUser) {
        $conversation = $messageModel->getConversation($selectedUserId);
        $messageModel->markUserMessagesAsRead($selectedUserId);
    } else {
        $selectedUser = null;
        $conversation = [];
    }
} else {
    $selectedUser = null;
    $conversation = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin</title>
    <link rel="stylesheet" href="/SINTA/public/assets/css/admin-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .messages-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 1.5rem;
            height: 600px;
        }

        .conversations-list {
            background: var(--bg-secondary);
            border-radius: 8px;
            overflow-y: auto;
            border: 1px solid var(--border);
        }

        .conversation-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: var(--transition);
            background: transparent;
        }

        .conversation-item:hover {
            background: var(--bg-hover);
        }

        .conversation-item.active {
            background: var(--primary);
            color: white;
            border-left: 4px solid var(--accent);
        }

        .conversation-item__name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .conversation-item__preview {
            font-size: 0.85rem;
            opacity: 0.8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-item__unread {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 0.5rem;
        }

        .chat-panel {
            display: flex;
            flex-direction: column;
            background: var(--bg-secondary);
            border-radius: 8px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .chat-header__title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .chat-header__status {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            display: flex;
            gap: 0.75rem;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.sent {
            justify-content: flex-end;
        }

        .message__avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            color: white;
            flex-shrink: 0;
        }

        .message.received .message__avatar {
            background: var(--secondary);
        }

        .message.sent .message__avatar {
            background: var(--primary);
        }

        .message__content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            max-width: 70%;
        }

        .message.sent .message__content {
            align-items: flex-end;
        }

        .message__text {
            background: var(--bg-hover);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            word-wrap: break-word;
        }

        .message.received .message__text {
            background: var(--border);
            border-radius: 8px 8px 8px 0;
        }

        .message.sent .message__text {
            background: var(--primary);
            color: white;
            border-radius: 8px 8px 0 8px;
        }

        .message__time {
            font-size: 0.75rem;
            opacity: 0.6;
        }

        .chat-input-area {
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            background: var(--bg-primary);
        }

        .chat-input-form {
            display: flex;
            gap: 0.75rem;
        }

        .chat-input-form textarea {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            resize: none;
            max-height: 100px;
        }

        .chat-input-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.1);
        }

        .chat-input-form button {
            padding: 0.75rem 1.5rem;
            align-self: flex-end;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        @media (max-width: 1024px) {
            .messages-container {
                grid-template-columns: 1fr;
                height: auto;
            }
            .conversations-list {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>
<body>

<?php include 'admin-nav-new.php'; ?>

<main>
    <div class="container" style="padding: 2rem 1.5rem;">
        <!-- PAGE HEADER -->
        <div class="section-header">
            <h1 class="section-title"><i class="fas fa-comments"></i> Messages</h1>
            <p class="section-subtitle">User conversations and support messages</p>
        </div>

        <!-- MAIN MESSAGING AREA -->
        <div class="messages-container">
            <!-- LEFT PANEL: Conversation List -->
            <div class="conversations-list">
                <?php if (empty($allConversations)): ?>
                    <div style="padding: 2rem 1rem; text-align: center; color: var(--text-secondary);">
                        <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                        <p>No messages yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($allConversations as $conv): ?>
                        <div class="conversation-item <?php echo $conv['user_id'] == $selectedUserId ? 'active' : ''; ?>"
                             onclick="selectConversation(<?php echo $conv['user_id']; ?>)">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="flex: 1;">
                                    <div class="conversation-item__name">
                                        <?php echo htmlspecialchars($conv['user_name']); ?>
                                    </div>
                                    <div class="conversation-item__preview">
                                        <?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)); ?>...
                                    </div>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="conversation-item__unread"><?php echo $conv['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- RIGHT PANEL: Chat View -->
            <div class="chat-panel">
                <?php if ($selectedUser): ?>
                    <!-- CHAT HEADER -->
                    <div class="chat-header">
                        <div class="chat-header__title">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($selectedUser['first_name'] . ' ' . $selectedUser['last_name']); ?>
                        </div>
                        <div class="chat-header__status">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($selectedUser['email']); ?> | 
                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($selectedUser['phone']); ?>
                        </div>
                    </div>

                    <!-- MESSAGES -->
                    <div class="chat-messages" id="chatMessages">
                        <?php if (empty($conversation)): ?>
                            <div class="empty-state">
                                <i class="fas fa-comments"></i>
                                <p>No messages in this conversation</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversation as $msg): ?>
                                <div class="message <?php echo $msg['is_admin_reply'] ? 'sent' : 'received'; ?>">
                                    <div class="message__avatar">
                                        <?php echo $msg['is_admin_reply'] ? 'A' : substr($selectedUser['first_name'], 0, 1); ?>
                                    </div>
                                    <div class="message__content">
                                        <div class="message__text">
                                            <?php echo htmlspecialchars($msg['content']); ?>
                                        </div>
                                        <div class="message__time">
                                            <?php echo date('M d, H:i', strtotime($msg['timestamp'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- MESSAGE INPUT -->
                    <div class="chat-input-area">
                        <form class="chat-input-form" onsubmit="sendReply(event, <?php echo $selectedUserId; ?>)">
                            <textarea name="reply" placeholder="Type your reply..." rows="3" required></textarea>
                            <button type="submit" class="btn btn--primary">
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- NO CONVERSATION SELECTED -->
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h2>Select a conversation</h2>
                        <p>Choose a user conversation from the left panel to start messaging</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
    function selectConversation(userId) {
        window.location.href = '/SINTA/public/index.php?route=admin-messages&user_id=' + userId;
    }

    function sendReply(e, userId) {
        e.preventDefault();
        
        const content = document.querySelector('textarea[name="reply"]').value.trim();
        
        if (!content) {
            alert('Please type a message');
            return;
        }

        fetch('/SINTA/public/api/messages/index.php?action=adminReply', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                user_id: userId,
                content: content
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.querySelector('textarea[name="reply"]').value = '';
                location.reload();
            } else {
                alert('Error sending message: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
        });
    }

    // Auto-scroll to latest message
    window.addEventListener('load', () => {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });

    // Auto-refresh for new user messages (poll every 5 seconds)
    setInterval(() => {
        const userId = new URLSearchParams(window.location.search).get('user_id');
        if (userId) {
            fetch('/SINTA/public/api/messages?action=check&user_id=' + userId)
                .then(r => r.json())
                .then(data => {
                    if (data.hasNewMessages) {
                        location.reload();
                    }
                });
        }
    }, 5000);
</script>

<?php include 'admin-footer.php'; ?>
</body>
</html>

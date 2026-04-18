<?php
/**
 * USER MESSAGES PAGE - Real-time User to Admin Messaging
 * Location: app/views/user/messages-modern.php
 * This creates a real-time messaging system between user and admin ONLY
 */

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

require_once ROOT_PATH . '/app/models/Message.php';

$pageTitle = 'Messages';
$messageModel = new Message();
$userId = $_SESSION['user_id'];

// Get user's conversation thread with admin
$conversation = $messageModel->getConversation($userId);

// Mark all messages as read
foreach ($conversation as $msg) {
    if ($msg['status'] === 'unread' && $msg['is_admin_reply'] === 0) {
        $messageModel->markAsRead($msg['message_id']);
    }
}

?>
<?php include 'header-modern.php'; ?>

<main style="max-height: calc(100vh - 200px); overflow-y: auto;">
    <div class="container" style="padding: 2rem 1.5rem;">
        <!-- PAGE HEADER -->
        <div class="section-header">
            <h1 class="section-title"><i class="fas fa-envelope"></i> Messages</h1>
            <p class="section-subtitle">Contact the admin team directly about your bookings or inquiries</p>
        </div>

        <!-- TWO COLUMN LAYOUT -->
        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem; height: calc(100vh - 350px);">
            
            <!-- SIDEBAR - CONVERSATION LIST -->
            <div style="background: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; overflow-y: auto; display: flex; flex-direction: column;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border); background: var(--bg-light);">
                    <h3 style="font-weight: 600;">Admin Support</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary);">Direct chat with our team</p>
                </div>

                <div style="flex: 1; overflow-y: auto; padding: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-light); border-radius: 8px; cursor: pointer;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                            A
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: var(--text-primary);">Admin Support</div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo !empty($conversation) ? 'Conversation active' : 'Start a new conversation'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAIN CHAT AREA -->
            <div style="background: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; display: flex; flex-direction: column;">
                
                <!-- CHAT HEADER -->
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); background: var(--bg-light);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                            A
                        </div>
                        <div>
                            <h3 style="font-weight: 600; margin: 0;">Admin Support Team</h3>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0;">We typically reply within 2 hours</p>
                        </div>
                    </div>
                </div>

                <!-- MESSAGES THREAD -->
                <div id="messageThread" style="flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                    <?php if (empty($conversation)): ?>
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-secondary); text-align: center;">
                            <div>
                                <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.5; display: block; margin-bottom: 1rem;"></i>
                                <p>No messages yet. Start the conversation below!</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversation as $msg): ?>
                            <div style="display: flex; gap: 0.75rem; align-items: flex-start; <?php echo $msg['is_admin_reply'] === 1 ? '' : 'justify-content: flex-end;'; ?>">
                                <?php if ($msg['is_admin_reply'] === 1): ?>
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; flex-shrink: 0;">
                                        A
                                    </div>
                                <?php endif; ?>
                                
                                <div style="<?php echo $msg['is_admin_reply'] === 1 ? 'background: var(--bg-light);' : 'background: var(--primary); color: white;'; ?> border: 1px solid var(--border); border-radius: 12px; padding: 1rem; max-width: 70%;">
                                    <div style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($msg['content']); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; opacity: 0.7;">
                                        <?php echo date('M d, g:i A', strtotime($msg['timestamp'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- MESSAGE INPUT -->
                <div style="padding: 1rem; border-top: 1px solid var(--border); background: var(--bg-light);">
                    <form id="messageForm" onsubmit="sendMessage(event)">
                        <div style="display: flex; gap: 1rem;">
                            <textarea id="messageInput" class="form-input" placeholder="Type your message..." 
                                    style="flex: 1; height: 50px; resize: none;" required></textarea>
                            <button type="submit" class="btn btn--primary" style="align-self: flex-end; height: 50px;">
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Auto-scroll to latest message
    const messageThread = document.getElementById('messageThread');
    if (messageThread) {
        messageThread.scrollTop = messageThread.scrollHeight;
    }

    // Send message function
    async function sendMessage(e) {
        e.preventDefault();
        const messageInput = document.getElementById('messageInput');
        const content = messageInput.value.trim();

        if (!content) return;

        // Disable button
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        try {
            const response = await fetch('/SINTA/public/api/messages/index.php?action=send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    content: content,
                    user_id: <?php echo $userId; ?>
                })
            });

            const data = await response.json();

            if (data.success) {
                messageInput.value = '';
                // Reload messages
                location.reload();
            } else {
                alert('Error sending message: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error sending message');
        } finally {
            submitBtn.disabled = false;
        }
    }

    // Poll for new messages every 3 seconds
    setInterval(function() {
        fetch('/SINTA/public/api/messages/check?user_id=<?php echo $userId; ?>')
            .then(r => r.json())
            .then(data => {
                if (data.hasNewMessages) {
                    // Auto-refresh to show new messages
                    location.reload();
                }
            });
    }, 3000);
</script>

<?php include 'footer.php'; ?>
</body>
</html>

<?php 
$page = 'messages';
if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}
$user_id = $_SESSION['user_id'];
require_once ROOT_PATH . '/app/controllers/MessagingController.php';
$msgCtrl = new MessagingController();

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message_text'] ?? '');
    $message_type = $_POST['message_type'] ?? 'inquiry';
    if ($subject && $message_text) {
        $result = $msgCtrl->sendMessage($user_id, $subject, $message_text, $message_type);
        if ($result['success']) {
            $_SESSION['msg_flash'] = 'Message sent successfully!';
        } else {
            $_SESSION['msg_error'] = 'Failed to send: ' . $result['error'];
        }
        header('Location: /SINTA/public/index.php?route=messages');
        exit;
    }
}

// Get all conversation threads for this user
$threads = $msgCtrl->getUserFullConversation($user_id);
$selected_id = isset($_GET['thread']) ? (int)$_GET['thread'] : ($threads[0]['message_id'] ?? 0);
$selected_thread = null;
if ($selected_id) {
    $selected_thread = $msgCtrl->getConversationByMessageId($selected_id, $user_id, false);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
  <style>
    .app-shell { padding-top: 76px; min-height: 100vh; background: var(--bg-primary); }
    .msg-container { display: flex; height: calc(100vh - 76px); max-width: 1400px; margin: 0 auto; }
    .msg-sidebar { width: 320px; border-right: 1px solid var(--border); background: var(--bg-card); display: flex; flex-direction: column; }
    .msg-sidebar__header { padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .msg-sidebar__header h3 { font-size: 1.2rem; }
    .msg-search { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); }
    .msg-search input { width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border); border-radius: 60px; background: var(--bg-alt); font-size: 0.85rem; outline: none; }
    .msg-threads { flex: 1; overflow-y: auto; }
    .msg-thread { display: flex; align-items: center; gap: 1rem; padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); cursor: pointer; transition: all 0.2s ease; }
    .msg-thread:hover { background: var(--bg-alt); }
    .msg-thread.active { background: var(--primary-pale); }
    .msg-thread__avatar { width: 48px; height: 48px; border-radius: 50%; background: var(--primary-pale); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 600; color: var(--primary); flex-shrink: 0; }
    .msg-thread__info { flex: 1; }
    .msg-thread__name { font-weight: 600; margin-bottom: 0.25rem; }
    .msg-thread__preview { font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .msg-thread__meta { text-align: right; }
    .msg-thread__time { font-size: 0.7rem; color: var(--text-muted); }
    .msg-thread__badge { background: var(--primary); color: white; font-size: 0.7rem; padding: 0.15rem 0.45rem; border-radius: 20px; margin-top: 0.25rem; display: inline-block; }
    .msg-chat { flex: 1; display: flex; flex-direction: column; background: var(--bg-secondary); }
    .msg-chat__header { padding: 1rem 1.5rem; background: var(--bg-card); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .msg-chat__contact { display: flex; align-items: center; gap: 1rem; }
    .msg-chat__name { font-weight: 600; font-size: 1rem; }
    .msg-chat__status { font-size: 0.75rem; color: var(--success); }
    .msg-chat__body { flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
    .msg-date { text-align: center; margin: 1rem 0; }
    .msg-date span { font-size: 0.7rem; color: var(--text-muted); background: var(--bg-alt); padding: 0.25rem 0.75rem; border-radius: 20px; }
    .msg-bubble { display: flex; max-width: 70%; }
    .msg-bubble.incoming { align-self: flex-start; }
    .msg-bubble.outgoing { align-self: flex-end; flex-direction: row-reverse; }
    .msg-bubble__content { padding: 0.75rem 1rem; border-radius: 20px; background: var(--bg-card); border: 1px solid var(--border); }
    .msg-bubble.outgoing .msg-bubble__content { background: var(--primary); border-color: var(--primary); color: white; }
    .msg-bubble__text { font-size: 0.85rem; line-height: 1.5; }
    .msg-bubble__time { font-size: 0.65rem; color: var(--text-muted); margin-top: 0.25rem; text-align: right; }
    .msg-bubble.outgoing .msg-bubble__time { color: rgba(255,255,255,0.7); }
    .msg-chat__footer { padding: 1rem 1.5rem; background: var(--bg-card); border-top: 1px solid var(--border); display: flex; gap: 1rem; align-items: center; }
    .msg-chat__footer input { flex: 1; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 60px; outline: none; font-size: 0.85rem; }
    .msg-chat__footer button { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); border: none; color: white; cursor: pointer; }
    @media (max-width: 768px) { .msg-sidebar { width: 80px; } .msg-thread__info, .msg-thread__meta { display: none; } .msg-sidebar__header h3 { display: none; } }
  </style>
</head>
<body>
<?php include __DIR__ . '/nav.php'; ?>
<div class="app-shell">
  <div class="msg-container">
    <aside class="msg-sidebar">
      <div class="msg-sidebar__header">
        <h3>Messages</h3>
        <button class="btn btn--primary btn--sm" id="newMsgBtn"><i class="fas fa-pen"></i> New</button>
      </div>
      <div class="msg-search">
        <input type="text" id="searchThreads" placeholder="Search conversations...">
      </div>
      <div class="msg-threads" id="threadList">
        <?php if (empty($threads)): ?>
          <div style="padding: 1rem; text-align:center; color: #999;">No conversations yet.<br>Click "New" to start.</div>
        <?php else: ?>
          <?php foreach ($threads as $thread): ?>
            <div class="msg-thread <?= ($selected_id == $thread['message_id']) ? 'active' : '' ?>" data-message-id="<?= $thread['message_id'] ?>" onclick="location.href='?route=messages&thread=<?= $thread['message_id'] ?>'">
              <div class="msg-thread__avatar">A</div>
              <div class="msg-thread__info">
                <div class="msg-thread__name">Admin Support</div>
                <div class="msg-thread__preview"><?= htmlspecialchars(substr($thread['subject'], 0, 40)) ?></div>
              </div>
              <div class="msg-thread__meta">
                <div class="msg-thread__time"><?= date('M d', strtotime($thread['created_at'])) ?></div>
                <?php if ($thread['status'] === 'unread'): ?>
                  <div class="msg-thread__badge">new</div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </aside>

    <main class="msg-chat">
      <?php if ($selected_thread): ?>
        <div class="msg-chat__header">
          <div class="msg-chat__contact">
            <div class="msg-thread__avatar">A</div>
            <div>
              <div class="msg-chat__name">Admin Support</div>
              <div class="msg-chat__status"><i class="fas fa-circle"></i> Online</div>
            </div>
          </div>
          <button class="btn btn--ghost btn--sm" id="eventInfoBtn"><i class="fas fa-info-circle"></i> Event Info</button>
        </div>
        <div class="msg-chat__body" id="chatBody">
          <div class="msg-date"><span><?= date('F j, Y', strtotime($selected_thread['created_at'])) ?></span></div>
          <div class="msg-bubble outgoing">
            <div class="msg-bubble__content">
              <div class="msg-bubble__text"><strong><?= htmlspecialchars($selected_thread['subject']) ?></strong><br><?= nl2br(htmlspecialchars($selected_thread['message_text'])) ?></div>
              <div class="msg-bubble__time"><?= date('g:i A', strtotime($selected_thread['created_at'])) ?></div>
            </div>
          </div>
          <?php foreach ($selected_thread['replies'] as $reply): ?>
            <?php $isAdmin = ($reply['role'] === 'admin'); ?>
            <div class="msg-bubble <?= $isAdmin ? 'incoming' : 'outgoing' ?>">
              <div class="msg-bubble__content">
                <div class="msg-bubble__text"><?= nl2br(htmlspecialchars($reply['reply_text'])) ?></div>
                <div class="msg-bubble__time"><?= date('g:i A', strtotime($reply['created_at'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="msg-chat__footer">
          <input type="text" id="replyInput" placeholder="Type a message... (admin will see as a new thread)">
          <button id="sendReplyBtn"><i class="fas fa-paper-plane"></i></button>
        </div>
      <?php else: ?>
        <div style="text-align:center; padding: 3rem; color: #999;">Select a conversation or start a new one.</div>
      <?php endif; ?>
    </main>
  </div>
</div>

<!-- New Message Modal -->
<div id="newMsgModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
  <div style="background:white; max-width:500px; width:90%; border-radius:12px; padding:1.5rem;">
    <h3>New Message to Admin</h3>
    <?php if (isset($_SESSION['msg_error'])): ?>
      <div style="color:red; margin-bottom:1rem;"><?= htmlspecialchars($_SESSION['msg_error']); unset($_SESSION['msg_error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['msg_flash'])): ?>
      <div style="color:green; margin-bottom:1rem;"><?= htmlspecialchars($_SESSION['msg_flash']); unset($_SESSION['msg_flash']); ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="action" value="send">
      <input type="text" name="subject" placeholder="Subject" required style="width:100%; margin-bottom:1rem; padding:0.5rem;">
      <textarea name="message_text" placeholder="Your message..." rows="5" required style="width:100%; margin-bottom:1rem;"></textarea>
      <select name="message_type" style="margin-bottom:1rem; width:100%; padding:0.5rem;">
        <option value="inquiry">Inquiry</option>
        <option value="support">Support</option>
        <option value="feedback">Feedback</option>
      </select>
      <div style="display:flex; justify-content:flex-end; gap:1rem;">
        <button type="button" id="closeModalBtn" class="btn btn--ghost">Cancel</button>
        <button type="submit" class="btn btn--primary">Send</button>
      </div>
    </form>
  </div>
</div>

<script>
const modal = document.getElementById('newMsgModal');
document.getElementById('newMsgBtn')?.addEventListener('click', () => modal.style.display = 'flex');
document.getElementById('closeModalBtn')?.addEventListener('click', () => modal.style.display = 'none');
window.onclick = function(e) { if (e.target === modal) modal.style.display = 'none'; }

// Sending a reply (creates a new message thread)
document.getElementById('sendReplyBtn')?.addEventListener('click', function() {
    let input = document.getElementById('replyInput');
    let text = input.value.trim();
    if (!text) return;
    let subject = "Re: <?= $selected_thread ? addslashes($selected_thread['subject']) : 'Conversation' ?>";
    let form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="send">
        <input type="hidden" name="subject" value="${subject.replace(/"/g, '&quot;')}">
        <input type="hidden" name="message_text" value="${text.replace(/"/g, '&quot;')}">
    `;
    document.body.appendChild(form);
    form.submit();
});

// Search filter
document.getElementById('searchThreads')?.addEventListener('keyup', function(e) {
    let term = e.target.value.toLowerCase();
    document.querySelectorAll('.msg-thread').forEach(thread => {
        let name = thread.querySelector('.msg-thread__name')?.innerText.toLowerCase() || '';
        let preview = thread.querySelector('.msg-thread__preview')?.innerText.toLowerCase() || '';
        if (name.includes(term) || preview.includes(term)) {
            thread.style.display = 'flex';
        } else {
            thread.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
<?php 
$page = 'messages';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?route=signin');
    exit;
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}
$user_id = $_SESSION['user_id'];
require_once ROOT_PATH . '/app/models/Plan.php';
require_once ROOT_PATH . '/app/controllers/MessagingController.php';

$planModel = new Plan();
$msgCtrl = new MessagingController();

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $plan_id = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
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
        header('Location: /index.php?route=messages&plan_id=' . $plan_id);
        exit;
    }
}

// Get all conversations for this user
$all_conversations = $msgCtrl->getUserFullConversation($user_id);

// Extract event names that have messages
$events_with_messages = [];
foreach ($all_conversations as $conv) {
    // Extract event name from subject (format: "Event Name - Inquiry")
    $subject = $conv['subject'];
    if (strpos($subject, ' - Inquiry') !== false) {
        $event_name = str_replace(' - Inquiry', '', $subject);
        if (!in_array($event_name, $events_with_messages)) {
            $events_with_messages[] = $event_name;
        }
    }
}

// Get user's booked events
$userPlans = $planModel->getUserPlans($user_id);

// Filter plans to show only those with messages
$plansWithMessages = [];
foreach ($userPlans as $plan) {
    if (in_array($plan['event_name'], $events_with_messages)) {
        $plansWithMessages[] = $plan;
    }
}

// Use filtered plans for display
$displayPlans = $plansWithMessages;

// Get selected plan (event)
$selected_plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : ($displayPlans[0]['plan_id'] ?? 0);
$selected_plan = null;
$selected_conversations = [];

if ($selected_plan_id) {
    $selected_plan = $planModel->findById($selected_plan_id);
    if ($selected_plan && $selected_plan['user_id'] == $user_id) {
        // Get conversations related to this event
        $event_name = $selected_plan['event_name'];
        $expected_subject = $event_name . ' - Inquiry';
        
        // Filter conversations related to this event - check exact subject match
        foreach ($all_conversations as $conv) {
            if (strcasecmp(trim($conv['subject']), trim($expected_subject)) === 0) {
                $selected_conversations[] = $conv;
            }
        }
    }
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
  <link rel="stylesheet" href="/assets/css/global.css">
  <style>
    .app-shell { padding-top: 76px; min-height: 100vh; background: var(--bg-primary); }
    .msg-container { display: flex; height: calc(100vh - 76px); max-width: 1400px; margin: 0 auto; }
    
    /* Sidebar with events */
    .msg-sidebar { width: 340px; border-right: 1px solid var(--border); background: white; display: flex; flex-direction: column; box-shadow: 1px 0 3px rgba(0,0,0,0.05); }
    .msg-sidebar__header { padding: 1.5rem; border-bottom: 1px solid var(--border); }
    .msg-sidebar__header h3 { font-size: 1.1rem; margin: 0 0 1rem 0; font-weight: 600; }
    .msg-search { padding: 0 1.5rem 1rem 1.5rem; }
    .msg-search input { width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border); border-radius: 20px; background: var(--bg-alt); font-size: 0.85rem; outline: none; transition: all 0.2s ease; }
    .msg-search input:focus { border-color: var(--primary); background: white; }
    .msg-events { flex: 1; overflow-y: auto; }
    .msg-event { padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: all 0.2s ease; }
    .msg-event:hover { background: var(--bg-alt); }
    .msg-event.active { background: var(--primary-pale); border-left: 3px solid var(--primary); padding-left: calc(1.5rem - 3px); }
    .msg-event__title { font-weight: 600; font-size: 0.95rem; margin-bottom: 0.3rem; color: #333; }
    .msg-event__date { font-size: 0.8rem; color: #999; }
    .msg-event__icon { width: 40px; height: 40px; background: linear-gradient(135deg, #f5d0a9 0%, #f9e4c8 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem; font-size: 1.2rem; flex-shrink: 0; }
    
    /* Chat area */
    .msg-chat { flex: 1; display: flex; flex-direction: column; background: white; }
    .msg-chat__header { padding: 1.5rem; background: white; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .msg-chat__contact { display: flex; flex-direction: column; }
    .msg-chat__name { font-weight: 600; font-size: 1.1rem; }
    .msg-chat__date { font-size: 0.8rem; color: #999; }
    .msg-chat__body { flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
    .msg-date { text-align: center; margin: 1rem 0; }
    .msg-date span { font-size: 0.75rem; color: #999; background: #f0f0f0; padding: 0.25rem 0.75rem; border-radius: 15px; display: inline-block; }
    
    .msg-bubble { display: flex; margin-bottom: 0.5rem; }
    .msg-bubble.incoming { justify-content: flex-start; }
    .msg-bubble.outgoing { justify-content: flex-end; }
    .msg-bubble__content { max-width: 60%; padding: 0.75rem 1rem; border-radius: 18px; word-wrap: break-word; }
    .msg-bubble.incoming .msg-bubble__content { background: #f0f0f0; color: #333; }
    .msg-bubble.outgoing .msg-bubble__content { background: var(--primary); color: white; }
    .msg-bubble__text { font-size: 0.9rem; line-height: 1.4; }
    .msg-bubble__time { font-size: 0.7rem; margin-top: 0.25rem; text-align: right; }
    .msg-bubble.incoming .msg-bubble__time { color: #999; }
    .msg-bubble.outgoing .msg-bubble__time { color: rgba(255,255,255,0.7); }
    
    .msg-chat__footer { padding: 1.5rem; background: white; border-top: 1px solid var(--border); display: flex; gap: 1rem; align-items: flex-end; }
    .msg-chat__input-wrapper { flex: 1; }
    .msg-chat__input-wrapper textarea { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 20px; outline: none; font-family: inherit; font-size: 0.9rem; resize: none; max-height: 120px; transition: all 0.2s ease; }
    .msg-chat__input-wrapper textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(166, 124, 82, 0.1); }
    .msg-chat__footer button { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); border: none; color: white; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .msg-chat__footer button:hover { transform: scale(1.05); }
    
    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #999; text-align: center; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; color: #ddd; }
    
    @media (max-width: 768px) { 
      .msg-container { flex-direction: column; height: auto; }
      .msg-sidebar { width: 100%; border-right: none; border-bottom: 1px solid var(--border); max-height: 200px; }
      .msg-chat { min-height: 60vh; }
      .msg-chat__header, .msg-chat__body, .msg-chat__footer { display: flex; }
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/nav.php'; ?>
<div class="app-shell">
  <div class="msg-container">
    <!-- Left Sidebar: Events List -->
    <aside class="msg-sidebar">
      <div class="msg-sidebar__header">
        <h3>Messages</h3>
        <button onclick="document.getElementById('newMsgModal').style.display='flex'" style="background:var(--primary);color:white;border:none;border-radius:8px;padding:0.4rem 0.8rem;font-size:0.8rem;cursor:pointer;margin-top:0.5rem;width:100%;"><i class="fas fa-plus"></i> New Message</button>
      </div>
      <div class="msg-search">
        <input type="text" id="searchEvents" placeholder="Search events...">
      </div>
      <div class="msg-events" id="eventList">
        <?php if (empty($displayPlans)): ?>
          <div style="padding: 2rem 1.5rem; text-align:center; color: #999; font-size:0.9rem;">
            <i class="fas fa-comments" style="display:block; font-size:1.5rem; margin-bottom:1rem; color:#ddd;"></i>
            No conversations yet
          </div>
        <?php else: ?>
          <?php foreach ($displayPlans as $plan): 
            $planDate = $plan['event_date'] ? date('M d, Y', strtotime($plan['event_date'])) : 'TBD';
            $isActive = ($selected_plan_id == $plan['plan_id']) ? 'active' : '';
          ?>
            <div class="msg-event <?= $isActive ?>" onclick="location.href='?route=messages&plan_id=<?= $plan['plan_id'] ?>'">
              <div class="msg-event__title"><?= htmlspecialchars($plan['event_name']) ?></div>
              <div class="msg-event__date"><?= htmlspecialchars($planDate) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </aside>

    <!-- Right Side: Chat Area -->
    <main class="msg-chat">
      <?php if ($selected_plan): ?>
        <!-- Chat Header -->
        <div class="msg-chat__header">
          <div class="msg-chat__contact">
            <div class="msg-chat__name"><?= htmlspecialchars($selected_plan['event_name']) ?></div>
            <div class="msg-chat__date">
              <?= date('F j, Y', strtotime($selected_plan['event_date'])) ?> at <?= htmlspecialchars($selected_plan['event_time'] ?: 'TBD') ?>
            </div>
          </div>
        </div>

        <!-- Chat Messages -->
        <div class="msg-chat__body" id="chatBody">
          <?php if (empty($selected_conversations)): ?>
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: #999; text-align: center;">
              <div>
                <i class="fas fa-comments" style="font-size: 2.5rem; color: #ddd; margin-bottom: 1rem; display: block;"></i>
                <p>No messages yet. Start a conversation!</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($selected_conversations as $conv): ?>
              <div class="msg-date"><span><?= date('F j, Y', strtotime($conv['created_at'])) ?></span></div>
              
              <!-- User's message -->
              <div class="msg-bubble outgoing">
                <div>
                  <div class="msg-bubble__content">
                    <div class="msg-bubble__text"><?= nl2br(htmlspecialchars($conv['message_text'])) ?></div>
                  </div>
                  <div class="msg-bubble__time"><?= date('g:i A', strtotime($conv['created_at'])) ?></div>
                </div>
              </div>
              
              <!-- Admin replies -->
              <?php if (!empty($conv['replies'])): ?>
                <?php foreach ($conv['replies'] as $reply): ?>
                  <div class="msg-bubble incoming">
                    <div>
                      <div class="msg-bubble__content">
                        <div class="msg-bubble__text"><?= nl2br(htmlspecialchars($reply['reply_text'])) ?></div>
                      </div>
                      <div class="msg-bubble__time"><?= date('g:i A', strtotime($reply['created_at'])) ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Chat Footer: Message Input -->
        <div class="msg-chat__footer">
          <form id="messageForm" method="POST" style="display: flex; gap: 1rem; width: 100%;">
            <input type="hidden" name="action" value="send">
            <input type="hidden" name="plan_id" value="<?= $selected_plan['plan_id'] ?>">
            <input type="hidden" name="subject" value="<?= htmlspecialchars($selected_plan['event_name'] . ' - Inquiry') ?>">
            <div class="msg-chat__input-wrapper">
              <textarea id="messageInput" name="message_text" placeholder="Message SINTA Event Team..." rows="1" required></textarea>
            </div>
            <button type="submit" title="Send"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
      <?php else: ?>
        <!-- No event selected -->
        <div class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>Select an event to start messaging</p>
        </div>
      <?php endif; ?>
    </main>
  </div>
</div>

<script>
  // Auto-resize textarea
  const textarea = document.getElementById('messageInput');
  if (textarea) {
    textarea.addEventListener('input', function() {
      this.style.height = 'auto';
      this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
  }

  // Search events
  document.getElementById('searchEvents')?.addEventListener('keyup', function(e) {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('.msg-event').forEach(event => {
      const title = event.querySelector('.msg-event__title')?.textContent.toLowerCase() || '';
      const date = event.querySelector('.msg-event__date')?.textContent.toLowerCase() || '';
      if (title.includes(term) || date.includes(term)) {
        event.style.display = 'block';
      } else {
        event.style.display = 'none';
      }
    });
  });

  // Auto-scroll to bottom
  const chatBody = document.getElementById('chatBody');
  if (chatBody) {
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  // Form validation
  document.getElementById('messageForm')?.addEventListener('submit', function(e) {
    const text = document.getElementById('messageInput').value.trim();
    if (!text) {
      e.preventDefault();
      alert('Please enter a message');
    }
  });
</script>

<!-- New Message Modal -->
<div id="newMsgModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:2000;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:20px;padding:2rem;max-width:480px;width:90%;box-shadow:0 20px 40px rgba(0,0,0,0.15);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
      <h3 style="margin:0;font-family:'Cormorant Garamond',serif;font-size:1.4rem;">New Message</h3>
      <button onclick="document.getElementById('newMsgModal').style.display='none'" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;">&times;</button>
    </div>
    <form method="POST" action="/index.php?route=messages">
      <input type="hidden" name="action" value="send">
      <input type="hidden" name="plan_id" value="0">
      <div style="margin-bottom:1rem;">
        <label style="display:block;font-size:0.75rem;font-weight:600;color:#6B6463;margin-bottom:0.4rem;text-transform:uppercase;">Subject</label>
        <input type="text" name="subject" placeholder="e.g., General Inquiry" required style="width:100%;padding:0.75rem 1rem;border:1.5px solid #E2D9C8;border-radius:10px;font-family:inherit;font-size:0.9rem;">
      </div>
      <div style="margin-bottom:1.25rem;">
        <label style="display:block;font-size:0.75rem;font-weight:600;color:#6B6463;margin-bottom:0.4rem;text-transform:uppercase;">Message</label>
        <textarea name="message_text" placeholder="Type your message here..." required rows="4" style="width:100%;padding:0.75rem 1rem;border:1.5px solid #E2D9C8;border-radius:10px;font-family:inherit;font-size:0.9rem;resize:vertical;"></textarea>
      </div>
      <div style="display:flex;gap:0.75rem;">
        <button type="submit" style="flex:1;padding:0.85rem;background:var(--primary);color:white;border:none;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.85rem;"><i class="fas fa-paper-plane"></i> Send</button>
        <button type="button" onclick="document.getElementById('newMsgModal').style.display='none'" style="flex:1;padding:0.85rem;background:transparent;color:#6B6463;border:1.5px solid #E2D9C8;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.85rem;">Cancel</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>

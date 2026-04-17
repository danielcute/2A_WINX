<?php $page = 'messages'; ?>
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
    .app-shell {
      padding-top: 76px;
      min-height: 100vh;
      background: var(--bg-primary);
    }
    
    .msg-container {
      display: flex;
      height: calc(100vh - 76px);
      max-width: 1400px;
      margin: 0 auto;
    }
    
    /* Sidebar */
    .msg-sidebar {
      width: 320px;
      border-right: 1px solid var(--border);
      background: var(--bg-card);
      display: flex;
      flex-direction: column;
    }
    
    .msg-sidebar__header {
      padding: 1.5rem;
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .msg-sidebar__header h3 {
      font-size: 1.2rem;
    }
    
    .msg-search {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid var(--border);
    }
    
    .msg-search input {
      width: 100%;
      padding: 0.6rem 1rem;
      border: 1px solid var(--border);
      border-radius: 60px;
      background: var(--bg-alt);
      font-size: 0.85rem;
      outline: none;
    }
    
    .msg-search input:focus {
      border-color: var(--primary);
    }
    
    .msg-threads {
      flex: 1;
      overflow-y: auto;
    }
    
    .msg-thread {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid var(--border);
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .msg-thread:hover {
      background: var(--bg-alt);
    }
    
    .msg-thread.active {
      background: var(--primary-pale);
    }
    
    .msg-thread__avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: var(--primary-pale);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--primary);
      flex-shrink: 0;
      overflow: hidden;
    }
    
    .msg-thread__avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .msg-thread__info {
      flex: 1;
    }
    
    .msg-thread__name {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    
    .msg-thread__preview {
      font-size: 0.8rem;
      color: var(--text-muted);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .msg-thread__meta {
      text-align: right;
    }
    
    .msg-thread__time {
      font-size: 0.7rem;
      color: var(--text-muted);
    }
    
    .msg-thread__badge {
      background: var(--primary);
      color: white;
      font-size: 0.7rem;
      padding: 0.15rem 0.45rem;
      border-radius: 20px;
      margin-top: 0.25rem;
      display: inline-block;
    }
    
    /* Chat Area */
    .msg-chat {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: var(--bg-secondary);
    }
    
    .msg-chat__header {
      padding: 1rem 1.5rem;
      background: var(--bg-card);
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .msg-chat__contact {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .msg-chat__name {
      font-weight: 600;
      font-size: 1rem;
    }
    
    .msg-chat__status {
      font-size: 0.75rem;
      color: var(--success);
    }
    
    .msg-chat__body {
      flex: 1;
      overflow-y: auto;
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    
    .msg-date {
      text-align: center;
      margin: 1rem 0;
    }
    
    .msg-date span {
      font-size: 0.7rem;
      color: var(--text-muted);
      background: var(--bg-alt);
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
    }
    
    .msg-bubble {
      display: flex;
      max-width: 70%;
    }
    
    .msg-bubble.incoming {
      align-self: flex-start;
    }
    
    .msg-bubble.outgoing {
      align-self: flex-end;
      flex-direction: row-reverse;
    }
    
    .msg-bubble__content {
      padding: 0.75rem 1rem;
      border-radius: 20px;
      background: var(--bg-card);
      border: 1px solid var(--border);
    }
    
    .msg-bubble.outgoing .msg-bubble__content {
      background: var(--primary);
      border-color: var(--primary);
      color: white;
    }
    
    .msg-bubble__text {
      font-size: 0.85rem;
      line-height: 1.5;
    }
    
    .msg-bubble__time {
      font-size: 0.65rem;
      color: var(--text-muted);
      margin-top: 0.25rem;
      text-align: right;
    }
    
    .msg-bubble.outgoing .msg-bubble__time {
      color: rgba(255,255,255,0.7);
    }
    
    .msg-chat__footer {
      padding: 1rem 1.5rem;
      background: var(--bg-card);
      border-top: 1px solid var(--border);
      display: flex;
      gap: 1rem;
      align-items: center;
    }
    
    .msg-chat__footer input {
      flex: 1;
      padding: 0.75rem 1rem;
      border: 1px solid var(--border);
      border-radius: 60px;
      outline: none;
      font-size: 0.85rem;
    }
    
    .msg-chat__footer input:focus {
      border-color: var(--primary);
    }
    
    .msg-chat__footer button {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary);
      border: none;
      color: white;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .msg-chat__footer button:hover {
      background: var(--primary-dark);
      transform: scale(1.05);
    }
    
    @media (max-width: 768px) {
      .msg-sidebar {
        width: 80px;
      }
      .msg-thread__info,
      .msg-thread__meta {
        display: none;
      }
      .msg-sidebar__header h3 {
        display: none;
      }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="app-shell">
  <div class="msg-container">
    
    <!-- Sidebar -->
    <aside class="msg-sidebar">
      <div class="msg-sidebar__header">
        <h3>Messages</h3>
        <button class="btn btn--primary btn--sm"><i class="fas fa-pen"></i> New</button>
      </div>
      <div class="msg-search">
        <input type="text" placeholder="Search conversations...">
      </div>
      <div class="msg-threads">
        <div class="msg-thread active" onclick="selectThread(this)">
          <div class="msg-thread__avatar">S</div>
          <div class="msg-thread__info">
            <div class="msg-thread__name">Sinta Support</div>
            <div class="msg-thread__preview">Hi Maria! How can we help you today?</div>
          </div>
          <div class="msg-thread__meta">
            <div class="msg-thread__time">2h ago</div>
            <div class="msg-thread__badge">2</div>
          </div>
        </div>
        <div class="msg-thread" onclick="selectThread(this)">
          <div class="msg-thread__avatar"><img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Ana"></div>
          <div class="msg-thread__info">
            <div class="msg-thread__name">Ana Garcia</div>
            <div class="msg-thread__preview">Your floor plan is ready for review!</div>
          </div>
          <div class="msg-thread__meta">
            <div class="msg-thread__time">Yesterday</div>
          </div>
        </div>
        <div class="msg-thread" onclick="selectThread(this)">
          <div class="msg-thread__avatar">B</div>
          <div class="msg-thread__info">
            <div class="msg-thread__name">Billing Team</div>
            <div class="msg-thread__preview">Payment confirmation for Aug 12 event</div>
          </div>
          <div class="msg-thread__meta">
            <div class="msg-thread__time">3d ago</div>
          </div>
        </div>
      </div>
    </aside>
    
    <!-- Chat Area -->
    <main class="msg-chat">
      <div class="msg-chat__header">
        <div class="msg-chat__contact">
          <div class="msg-thread__avatar">S</div>
          <div>
            <div class="msg-chat__name">Sinta Support</div>
            <div class="msg-chat__status"><i class="fas fa-circle"></i> Online</div>
          </div>
        </div>
        <button class="btn btn--ghost btn--sm"><i class="fas fa-info-circle"></i> Event Info</button>
      </div>
      
      <div class="msg-chat__body" id="chatBody">
        <div class="msg-date"><span>Today</span></div>
        
        <div class="msg-bubble incoming">
          <div class="msg-bubble__content">
            <div class="msg-bubble__text">Hi Maria! 👋 Welcome to Sinta. How can we help you plan your perfect event?</div>
            <div class="msg-bubble__time">10:02 AM</div>
          </div>
        </div>
        
        <div class="msg-bubble outgoing">
          <div class="msg-bubble__content">
            <div class="msg-bubble__text">Hi! I'd like to ask about the Santos Wedding booking. Is everything confirmed?</div>
            <div class="msg-bubble__time">10:15 AM</div>
          </div>
        </div>
        
        <div class="msg-bubble incoming">
          <div class="msg-bubble__content">
            <div class="msg-bubble__text">Yes! Your booking for <strong>Santos Wedding on August 12</strong> is fully confirmed. Your coordinator Ana Garcia will be in touch shortly to discuss the floor plan and program flow. 🎉</div>
            <div class="msg-bubble__time">10:17 AM</div>
          </div>
        </div>
        
        <div class="msg-bubble outgoing">
          <div class="msg-bubble__content">
            <div class="msg-bubble__text">Thank you so much! Can we also discuss the floral arrangement options?</div>
            <div class="msg-bubble__time">10:20 AM</div>
          </div>
        </div>
        
        <div class="msg-bubble incoming">
          <div class="msg-bubble__content">
            <div class="msg-bubble__text">Of course! We have a beautiful selection of floral designs. I'll send you our lookbook right away. 🌸</div>
            <div class="msg-bubble__time">10:22 AM</div>
          </div>
        </div>
      </div>
      
      <div class="msg-chat__footer">
        <button class="btn btn--ghost btn--sm"><i class="fas fa-paperclip"></i></button>
        <input type="text" id="msgInput" placeholder="Type a message..." onkeypress="if(event.key==='Enter') sendMessage()">
        <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
      </div>
    </main>
    
  </div>
</div>

<script>
function selectThread(element) {
  document.querySelectorAll('.msg-thread').forEach(t => t.classList.remove('active'));
  element.classList.add('active');
}

function sendMessage() {
  const input = document.getElementById('msgInput');
  const text = input.value.trim();
  if (!text) return;
  
  const body = document.getElementById('chatBody');
  const now = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
  
  const messageDiv = document.createElement('div');
  messageDiv.className = 'msg-bubble outgoing';
  messageDiv.innerHTML = `
    <div class="msg-bubble__content">
      <div class="msg-bubble__text">${text}</div>
      <div class="msg-bubble__time">${now}</div>
    </div>
  `;
  
  body.appendChild(messageDiv);
  input.value = '';
  body.scrollTop = body.scrollHeight;
}
</script>
</body>
</html>
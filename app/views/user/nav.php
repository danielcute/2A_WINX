<?php
// Navigation for user pages
?>
<nav class="app-nav" id="mainNav">
  <div class="app-nav__inner">
    <a href="index.php?route=homepage" class="app-nav__logo">
      <img src="/assets/img/logo.png" alt="Sinta Logo" class="nav__logo-img" onerror="this.src='https://placehold.co/32x32/8A7650/white?text=S'">
      <span class="nav__logo-text">Sinta</span>
    </a>

    <div class="app-nav__links">
      <a href="index.php?route=homepage" class="app-nav__link <?= (isset($page) && $page === 'homepage') ? 'active' : '' ?>">
        <i class="fas fa-home"></i><span>Home</span>
      </a>
      <a href="index.php?route=plans" class="app-nav__link <?= (isset($page) && $page === 'plans') ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i><span>Plans</span>
      </a>
      <a href="index.php?route=occasions" class="app-nav__link <?= (isset($page) && $page === 'occasions') ? 'active' : '' ?>">
        <i class="fas fa-gift"></i><span>Occasions</span>
      </a>
      <a href="index.php?route=messages" class="app-nav__link <?= (isset($page) && $page === 'messages') ? 'active' : '' ?>">
        <i class="fas fa-comment"></i><span>Messages</span>
      </a>
    </div>

    <div class="app-nav__search">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Search events, packages...">
    </div>

    <div class="app-nav__right">
      <div class="app-nav__notif-wrapper" style="position: relative;">
        <button class="app-nav__icon-btn" id="notifBtn">
          <i class="fas fa-bell"></i>
          <span class="app-nav__badge">3</span>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-dropdown__header">
            <span>Notifications</span>
            <a href="#">Mark all read</a>
          </div>
          <div class="notif-dropdown__container">
            <!-- Notifications will be loaded here by JavaScript -->
            <div class="notif-loading"><i class="fas fa-spinner fa-spin"></i></div>
          </div>
        </div>
      </div>

      <div class="app-nav__profile-wrapper" style="position: relative;">
        <div class="app-nav__profile" id="profileBtn">
          <div class="app-nav__avatar">
t             <?php if (!empty($_SESSION['user_avatar'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['user_avatar']); ?>" alt="Profile">
            <?php else: ?>
                <div class="app-nav__avatar-placeholder" style="width:100%; height:100%; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold;"><?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?></div>
            <?php endif; ?>
          </div>
          <span class="app-nav__profile-name"><?php echo htmlspecialchars(substr($_SESSION['user_name'] ?? 'User', 0, 1) . ' ' . $_SESSION['user_last_name'] ?? 'User'); ?></span>
          <i class="fas fa-chevron-down app-nav__chevron"></i>
        </div>
        <div class="profile-nav-dropdown" id="profileDropdown">
          <div class="profile-dropdown__header">
            <div class="profile-name"><?php echo htmlspecialchars(($_SESSION['user_name'] ?? 'User') . ' ' . ($_SESSION['user_last_name'] ?? '')); ?></div>
            <div class="profile-email-nav"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'email@example.com'); ?></div>
          </div>
          <a href="index.php?route=profile" class="profile-dropdown__item">
            <i class="fas fa-user"></i> Profile
          </a>
          <hr class="profile-dropdown__divider">
          <a href="#" class="profile-dropdown__item profile-dropdown__item--danger" onclick="openLogoutModal(event);">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    </div>

    <button class="app-nav__mobile-btn" id="mobileMenuBtn">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
  <div class="mobile-menu__inner">
    <div class="mobile-menu__logo">
      <img src="/assets/img/logo.png" alt="Sinta Logo" class="mobile-menu__logo-img">
      <span class="mobile-menu__logo-text">Sinta</span>
    </div>
    <div class="mobile-menu__user">
      <img src="<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? '/assets/img/default-avatar.jpg'); ?>" alt="<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>">
      <div>
        <div class="mobile-menu__name"><?php echo htmlspecialchars(($_SESSION['user_name'] ?? 'User') . ' ' . ($_SESSION['user_last_name'] ?? '')); ?></div>
        <div class="mobile-menu__email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'email@example.com'); ?></div>
      </div>
    </div>
    <div class="mobile-menu__links">
      <a href="index.php?route=homepage" class="mobile-menu__link">Home</a>
      <a href="index.php?route=plans" class="mobile-menu__link">Plans</a>
      <a href="index.php?route=occasions" class="mobile-menu__link">Occasions</a>
      <a href="index.php?route=messages" class="mobile-menu__link">Messages</a>
      <a href="index.php?route=profile" class="mobile-menu__link">Profile</a>
    </div>
    <div class="mobile-menu__footer">
      <a href="#" class="mobile-menu__logout" onclick="openLogoutModal(event);">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>
</div>

<style>
/* Your existing CSS styles - keep exactly as is */
.app-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  height: 76px;
  background: rgba(255, 255, 255, 0.96);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid #E2D9C8;
  transition: all 0.3s ease;
}

.app-nav.scrolled {
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
}

.app-nav__inner {
  max-width: 1400px;
  margin: 0 auto;
  height: 100%;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  gap: 2rem;
}

.app-nav__logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  flex-shrink: 0;
}

.nav__logo-img {
  width: 32px;
  height: 32px;
  object-fit: contain;
  border-radius: 8px;
}

.nav__logo-text {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 1.4rem;
  font-weight: 600;
  color: #2C2820;
}

.app-nav__links {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0 auto;
}

.app-nav__link {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 1.3rem;
  border-radius: 60px;
  font-family: 'Inter', sans-serif;
  font-size: 0.95rem;
  font-weight: 500;
  color: #6B6463;
  text-decoration: none;
  transition: all 0.2s ease;
}

.app-nav__link i {
  font-size: 1rem;
  width: 20px;
  text-align: center;
  color: #8A7650;
}

.app-nav__link:hover {
  color: #8A7650;
  background: rgba(138, 118, 80, 0.12);
}

.app-nav__link.active {
  color: #8A7650;
  background: rgba(138, 118, 80, 0.12);
  font-weight: 600;
}

.app-nav__search {
  flex: 1;
  max-width: 280px;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  background: #F5F0E8;
  border: 1px solid #E2D9C8;
  border-radius: 60px;
  padding: 0.5rem 1.1rem;
  transition: all 0.2s ease;
}

.app-nav__search:focus-within {
  background: white;
  border-color: #8A7650;
  box-shadow: 0 0 0 3px rgba(138, 118, 80, 0.12);
}

.app-nav__search i {
  color: #8A8475;
  font-size: 0.9rem;
}

.app-nav__search input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 0.9rem;
  width: 100%;
  font-family: 'Inter', sans-serif;
}

.app-nav__search input::placeholder {
  color: #A8A09B;
}

.app-nav__right {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  flex-shrink: 0;
}

.app-nav__icon-btn {
  position: relative;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: transparent;
  border: 1px solid #E2D9C8;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: #6B6463;
  transition: all 0.2s ease;
}

.app-nav__icon-btn:hover {
  background: rgba(138, 118, 80, 0.12);
  border-color: #8A7650;
  color: #8A7650;
}

.app-nav__badge {
  position: absolute;
  top: -2px;
  right: -2px;
  min-width: 18px;
  height: 18px;
  background: #8A7650;
  color: white;
  font-size: 0.65rem;
  font-weight: 600;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.app-nav__profile {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.35rem 0.8rem 0.35rem 0.4rem;
  border-radius: 60px;
  border: 1px solid #E2D9C8;
  cursor: pointer;
  transition: all 0.2s ease;
}

.app-nav__profile:hover {
  background: rgba(138, 118, 80, 0.12);
  border-color: #8A7650;
}

.app-nav__avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
}

.app-nav__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.app-nav__profile-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: #2C2820;
}

.app-nav__chevron {
  font-size: 0.7rem;
  color: #8A8475;
  transition: transform 0.2s ease;
}

.app-nav__profile:hover .app-nav__chevron {
  transform: rotate(180deg);
}

.profile-nav-dropdown,
.notif-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 280px;
  background: white;
  border: 1px solid #E2D9C8;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  opacity: 0;
  transform: translateY(-8px);
  pointer-events: none;
  transition: all 0.2s ease;
  z-index: 100;
}

.profile-nav-dropdown.active,
.notif-dropdown.active {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

.profile-dropdown__header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #E2D9C8;
}

.profile-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #2C2820;
}

.profile-email-nav {
  font-size: 0.75rem;
  color: #8A8475;
  margin-top: 0.15rem;
}

.profile-dropdown__item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.25rem;
  font-size: 0.85rem;
  color: #2C2820;
  text-decoration: none;
  transition: background 0.2s ease;
}

.profile-dropdown__item i {
  width: 18px;
  color: #8A8475;
}

.profile-dropdown__item:hover {
  background: #F5F0E8;
}

.profile-dropdown__item--danger {
  color: #c0392b;
}

.profile-dropdown__item--danger i {
  color: #c0392b;
}

.profile-dropdown__divider {
  margin: 0;
  border: none;
  border-top: 1px solid #E2D9C8;
}

.notif-dropdown__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.9rem 1.25rem;
  border-bottom: 1px solid #E2D9C8;
  font-size: 0.85rem;
  font-weight: 500;
  color: #2C2820;
}

.notif-dropdown__header a {
  font-size: 0.75rem;
  color: #8A7650;
  text-decoration: none;
}

.notif-dropdown__item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid #E2D9C8;
  cursor: pointer;
  transition: background 0.2s ease;
  position: relative;
}

.notif-dropdown__item:last-child {
  border-bottom: none;
}

.notif-dropdown__item:hover {
  background: #F5F0E8;
}

.notif-dropdown__item.notif-read {
  opacity: 0.7;
  background: #FCFAF7;
}

.notif-icon-wrapper {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(138, 118, 80, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.notif-icon-wrapper i {
  font-size: 0.9rem;
}

.notif-content {
  flex: 1;
  min-width: 0;
}

.notif-title {
  font-size: 0.82rem;
  color: #2C2820;
  font-weight: 500;
  margin: 0;
  word-break: break-word;
}

.notif-message {
  font-size: 0.75rem;
  color: #8A8475;
  margin-top: 0.25rem;
  word-break: break-word;
}

.notif-time {
  font-size: 0.7rem;
  color: #A8A09B;
  margin-top: 0.3rem;
}

.notif-actions {
  display: flex;
  gap: 0.4rem;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.notif-dropdown__item:hover .notif-actions {
  opacity: 1;
}

.notif-action-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: rgba(138, 118, 80, 0.1);
  border-radius: 6px;
  color: #8A7650;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  transition: all 0.2s ease;
  flex-shrink: 0;
  padding: 0;
}

.notif-action-btn:hover {
  background: rgba(138, 118, 80, 0.2);
  color: #6B5A3E;
}

.notif-action-btn.notif-delete-btn:hover {
  background: rgba(192, 57, 43, 0.2);
  color: #c0392b;
}

.notif-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.25rem;
  color: #8A8475;
}

.notif-loading i {
  font-size: 1.5rem;
}

.notif-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #8A7650;
  flex-shrink: 0;
  margin-top: 5px;
}

.notif-dot.read {
  background: #A8A09B;
}

.app-nav__mobile-btn {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  margin-left: auto;
}

.app-nav__mobile-btn span {
  width: 22px;
  height: 2px;
  background: #2C2820;
  border-radius: 2px;
  transition: all 0.25s ease;
}

.app-nav__mobile-btn.active span:nth-child(1) {
  transform: translateY(7px) rotate(45deg);
}

.app-nav__mobile-btn.active span:nth-child(2) {
  opacity: 0;
}

.app-nav__mobile-btn.active span:nth-child(3) {
  transform: translateY(-7px) rotate(-45deg);
}

.mobile-menu {
  position: fixed;
  top: 0;
  right: -100%;
  width: 85%;
  max-width: 320px;
  height: 100vh;
  background: white;
  z-index: 1001;
  transition: right 0.3s ease;
  overflow-y: auto;
  box-shadow: -4px 0 24px rgba(0, 0, 0, 0.1);
}

.mobile-menu.active {
  right: 0;
}

.mobile-menu__inner {
  padding: 1.5rem;
}

.mobile-menu__logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #E2D9C8;
}

.mobile-menu__logo-img {
  width: 28px;
  height: 28px;
  object-fit: contain;
  border-radius: 6px;
}

.mobile-menu__logo-text {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.4rem;
  font-weight: 600;
}

.mobile-menu__user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #E2D9C8;
}

.mobile-menu__user img {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.mobile-menu__name {
  font-weight: 600;
  font-size: 1rem;
  color: #2C2820;
}

.mobile-menu__email {
  font-size: 0.8rem;
  color: #8A8475;
}

.mobile-menu__links {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.mobile-menu__link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.8rem 1rem;
  border-radius: 12px;
  font-size: 0.95rem;
  color: #5C5648;
  text-decoration: none;
  transition: all 0.2s ease;
}

.mobile-menu__link i {
  width: 20px;
  text-align: center;
  color: #8A7650;
}

.mobile-menu__link:hover {
  background: rgba(138, 118, 80, 0.12);
  color: #8A7650;
}

.mobile-menu__link.active {
  background: rgba(138, 118, 80, 0.12);
  color: #8A7650;
  font-weight: 500;
}

.mobile-menu__footer {
  padding-top: 1rem;
  border-top: 1px solid #E2D9C8;
}

.mobile-menu__logout {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.8rem 1rem;
  border-radius: 12px;
  font-size: 0.95rem;
  color: #c0392b;
  text-decoration: none;
  transition: all 0.2s ease;
}

.mobile-menu__logout:hover {
  background: rgba(192, 57, 43, 0.1);
}

@media (max-width: 968px) {
  .app-nav__links,
  .app-nav__search {
    display: none;
  }
  .app-nav__mobile-btn {
    display: flex;
  }
  .app-nav__right {
    margin-left: auto;
  }
}

@media (max-width: 480px) {
  .app-nav__inner {
    padding: 0 1rem;
  }
  .app-nav__profile-name,
  .app-nav__chevron {
    display: none;
  }
}
</style>

<script>
// Notification Type Configuration
const notificationTypes = {
  'realtime': { icon: 'fa-clock', label: 'Real-time Update', color: '#8A7650' },
  'messages': { icon: 'fa-envelope', label: 'Message', color: '#3498db' },
  'ratings': { icon: 'fa-star', label: 'Rating', color: '#f39c12' },
  'replies': { icon: 'fa-reply', label: 'Reply', color: '#1abc9c' },
  'feedback': { icon: 'fa-comment-dots', label: 'Feedback', color: '#9b59b6' },
  'book_confirmation': { icon: 'fa-calendar-check', label: 'Book Confirmation', color: '#27ae60' },
  'new_updates': { icon: 'fa-bell', label: 'New Update', color: '#e74c3c' },
  'receipts': { icon: 'fa-receipt', label: 'Receipt', color: '#2c3e50' },
  'system_update': { icon: 'fa-cog', label: 'System Update', color: '#95a5a6' }
};

(function() {
  const nav = document.getElementById('mainNav');
  const mobileBtn = document.getElementById('mobileMenuBtn');
  const mobileMenu = document.getElementById('mobileMenu');
  const profileBtn = document.getElementById('profileBtn');
  const profileDropdown = document.getElementById('profileDropdown');
  const notifBtn = document.getElementById('notifBtn');
  const notifDropdown = document.getElementById('notifDropdown');
  const notifContainer = document.querySelector('.notif-dropdown__container');
  const notifBadge = document.querySelector('.app-nav__badge');
  let notificationRefreshInterval = null;

  // Function to get notification icon and color
  function getNotificationTypeInfo(type) {
    return notificationTypes[type] || notificationTypes['system_update'];
  }

  // Function to format time
  function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // seconds
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    
    return date.toLocaleDateString();
  }

  // Function to load notifications
  function loadNotifications() {
    if (!notifContainer) return;

    fetch('api-notification.php?action=get_unread&limit=10')
      .then(response => {
        if (!response.ok) throw new Error('Failed to fetch notifications');
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) throw new TypeError('Invalid notification response');
        return response.json();
      })
      .then(data => {
        if (data.success && data.notifications) {
          // Update badge
          if (notifBadge && data.unread_count > 0) {
            notifBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
            notifBadge.style.display = 'flex';
          } else if (notifBadge) {
            notifBadge.style.display = 'none';
          }

          // Render notifications
          renderNotifications(data.notifications);
        } else {
          notifContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #8A8475;"><p>No notifications yet</p></div>';
        }
      })
      .catch(error => {
        console.error('Error loading notifications:', error);
        notifContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #c0392b;"><p>Error loading notifications</p></div>';
      });
  }

  // Function to render notifications
  function renderNotifications(notifications) {
    if (!notifContainer) return;

    if (notifications.length === 0) {
      notifContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #8A8475;"><p>No notifications</p></div>';
      return;
    }

    let html = '';
    notifications.forEach(notif => {
      const typeInfo = getNotificationTypeInfo(notif.type);
      const isRead = notif.is_read == 1;
      const timeStr = formatTime(notif.created_at);

      html += `
        <div class="notif-dropdown__item ${isRead ? 'notif-read' : ''}" data-id="${notif.id}">
          <div class="notif-icon-wrapper">
            <i class="fas ${typeInfo.icon}" style="color: ${typeInfo.color};"></i>
          </div>
          <div class="notif-content">
            <div class="notif-title">${notif.title || typeInfo.label}</div>
            <div class="notif-message" style="font-size: 0.75rem; color: #8A8475; margin-top: 0.25rem;">${notif.message || ''}</div>
            <div class="notif-time">${timeStr}</div>
          </div>
          <div class="notif-actions">
            <button class="notif-action-btn" onclick="markNotificationAsRead(${notif.id})" title="Mark as read">
              <i class="fas fa-check"></i>
            </button>
            <button class="notif-action-btn notif-delete-btn" onclick="deleteNotification(${notif.id})" title="Delete">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </div>
      `;
    });

    notifContainer.innerHTML = html;
  }

  // Mark notification as read
  window.markNotificationAsRead = function(notificationId) {
    fetch('api-notification.php?action=mark_as_read', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadNotifications();
      }
    })
    .catch(error => console.error('Error marking notification as read:', error));
  };

  // Delete notification
  window.deleteNotification = function(notificationId) {
    fetch('api-notification.php?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadNotifications();
      }
    })
    .catch(error => console.error('Error deleting notification:', error));
  };

  // Mark all as read
  window.markAllNotificationsAsRead = function(e) {
    e.preventDefault();
    fetch('api-notification.php?action=mark_all_as_read', { method: 'POST' })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadNotifications();
        }
      })
      .catch(error => console.error('Error marking all as read:', error));
  };

  // Setup notification button click
  if (notifBtn) {
    notifBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      notifDropdown.classList.toggle('active');
      if (profileDropdown) profileDropdown.classList.remove('active');
      
      // Load notifications when dropdown opens
      if (notifDropdown.classList.contains('active')) {
        loadNotifications();
        
        // Auto-refresh every 10 seconds while dropdown is open for "real-time" feel
        if (!notificationRefreshInterval) {
          notificationRefreshInterval = setInterval(loadNotifications, 10000);
        }
      } else {
        // Clear interval when dropdown closes
        if (notificationRefreshInterval) {
          clearInterval(notificationRefreshInterval);
          notificationRefreshInterval = null;
        }
      }
    });
  }

  // Mark all read link
  const markAllReadLink = document.querySelector('.notif-dropdown__header a');
  if (markAllReadLink) {
    markAllReadLink.addEventListener('click', window.markAllNotificationsAsRead);
  }

  if (nav) {
    window.addEventListener('scroll', function() {
      nav.classList.toggle('scrolled', window.scrollY > 20);
    });
  }

  if (mobileBtn && mobileMenu) {
    mobileBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      mobileBtn.classList.toggle('active');
      mobileMenu.classList.toggle('active');
      document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
    });
  }

  document.querySelectorAll('.mobile-menu__link, .mobile-menu__logout').forEach(function(link) {
    link.addEventListener('click', function() {
      if (mobileMenu) mobileMenu.classList.remove('active');
      if (mobileBtn) mobileBtn.classList.remove('active');
      document.body.style.overflow = '';
    });
  });

  if (profileBtn && profileDropdown) {
    profileBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      profileDropdown.classList.toggle('active');
      if (notifDropdown) notifDropdown.classList.remove('active');
    });
  }

  if (notifBtn && notifDropdown) {
    notifBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      notifDropdown.classList.toggle('active');
      if (profileDropdown) profileDropdown.classList.remove('active');
    });
  }

  document.addEventListener('click', function(e) {
    if (profileBtn && profileBtn.contains(e.target)) return;
    if (profileDropdown && profileDropdown.contains(e.target)) return;
    if (notifBtn && notifBtn.contains(e.target)) return;
    if (notifDropdown && notifDropdown.contains(e.target)) return;
    
    if (profileDropdown) profileDropdown.classList.remove('active');
    if (notifDropdown) notifDropdown.classList.remove('active');
  });

  // Search functionality
  const searchInput = document.querySelector('.app-nav__search input');
  if (searchInput) {
    searchInput.addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase().trim();
      
      // Get all package cards from current page
      const packageCards = document.querySelectorAll('.pkg-card');
      let visibleCount = 0;
      
      packageCards.forEach(card => {
        // Get package name from h3
        const nameElement = card.querySelector('h3');
        const descElement = card.querySelector('.pkg-card__desc');
        
        const packageName = nameElement ? nameElement.textContent.toLowerCase() : '';
        const packageDesc = descElement ? descElement.textContent.toLowerCase() : '';
        
        // Check if search term matches package name or description
        const matches = searchTerm === '' || 
                       packageName.includes(searchTerm) || 
                       packageDesc.includes(searchTerm);
        
        // Show or hide the card
        if (matches) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });
      
      // Show "no results" message if needed
      const grid = document.querySelector('.pkg-grid');
      if (grid) {
        let noResultsMsg = grid.querySelector('.search-no-results');
        
        if (visibleCount === 0 && searchTerm !== '') {
          if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'search-no-results';
            noResultsMsg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 3rem; color: #8A8475;';
            noResultsMsg.innerHTML = `<i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                      <p style="margin: 0;">No packages found matching "<strong>${searchTerm}</strong>"</p>`;
            grid.appendChild(noResultsMsg);
          }
        } else if (noResultsMsg) {
          noResultsMsg.remove();
        }
      }
    });
  }
})();

// Logout Modal Functions
function openLogoutModal(event) {
  event.preventDefault();
  document.getElementById('logoutModal').style.display = 'flex';
}

function closeLogoutModal() {
  document.getElementById('logoutModal').style.display = 'none';
}

function confirmLogout() {
  window.location.href = '/index.php?route=logout';
}

// Close modal when clicking outside of it
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('logoutModal').addEventListener('click', function(event) {
    if (event.target === this) {
      closeLogoutModal();
    }
  });
});
</script>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal" style="display: none;">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Confirm Logout</h2>
      <button class="modal-close" onclick="closeLogoutModal()">×</button>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to logout?</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-cancel" onclick="closeLogoutModal()">Cancel</button>
      <button class="btn btn-confirm" onclick="confirmLogout()">Logout</button>
    </div>
  </div>
</div>

<style>
  /* Modal Styles */
  .modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }

  .modal-content {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    min-width: 350px;
    animation: slideIn 0.3s ease;
  }

  @keyframes slideIn {
    from {
      transform: translateY(-50px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  .modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #E2D9C8;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h2 {
    margin: 0;
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.8rem;
    color: #2C2820;
  }

  .modal-close {
    background: none;
    border: none;
    font-size: 2rem;
    color: #8B7355;
    cursor: pointer;
    padding: 0;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
  }

  .modal-close:hover {
    color: #2C2820;
  }

  .modal-body {
    padding: 1.5rem;
    color: #555;
    font-size: 1rem;
  }

  .modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #E2D9C8;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
  }

  .btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.95rem;
  }

  .btn-cancel {
    background: #E2D9C8;
    color: #2C2820;
  }

  .btn-cancel:hover {
    background: #D4CCC0;
  }

  .btn-confirm {
    background: #C62828;
    color: white;
  }

  .btn-confirm:hover {
    background: #A02020;
  }
</style>
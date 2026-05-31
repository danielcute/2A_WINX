﻿<?php
$unread = $_SESSION['admin_unread_count'] ?? 0;
?>
<style>
/* ----- ADMIN DESIGN SYSTEM v2 ----- */
:root {
  --gold: #8A7650;
  --gold-light: #B5A07A;
  --gold-dark: #6B5A3E;
  --charcoal: #2C2820;
  --charcoal-soft: #3B3630;
  --sand: #F5F0E8;
  --sand-light: #FCFAF7;
  --sand-dark: #E6DFD4;
  --white: #FFFFFF;
  --gray-light: #E2D9C8;
  --gray-mid: #A39B8B;
  --shadow-sm: 0 8px 20px rgba(0,0,0,0.03), 0 2px 6px rgba(0,0,0,0.05);
  --shadow-md: 0 12px 28px rgba(0,0,0,0.08);
  --shadow-lg: 0 20px 40px rgba(0,0,0,0.12);
  --radius-sm: 12px;
  --radius-md: 18px;
  --radius-lg: 24px;
  --transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
}

/* Base & Typography */
body {
  background: var(--sand);
  font-family: 'DM Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
  font-size: 15px;
  line-height: 1.5;
  color: var(--charcoal);
  -webkit-font-smoothing: antialiased;
}

.admin-wrapper {
  display: flex;
  min-height: 100vh;
  background: linear-gradient(145deg, var(--sand) 0%, #F2EDE6 100%);
}

/* --- Sidebar Premium --- */
.admin-sidebar {
  width: 280px;
  background: linear-gradient(180deg, var(--charcoal) 0%, #1E1B17 100%);
  color: rgba(255,255,255,0.85);
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  overflow-y: auto;
  overflow-x: hidden;
  transition: var(--transition);
  z-index: 100;
  border-right: 1px solid rgba(255,255,255,0.05);
  display: flex;
  flex-direction: column;
}

.admin-sidebar__header {
  padding: 2rem 1.5rem 1.5rem;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  text-align: center;
}

.admin-sidebar__logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.7rem;
  text-decoration: none;
}

.admin-sidebar__logo-img {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  object-fit: cover;
  box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.admin-sidebar__logo-text {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.9rem;
  font-weight: 600;
  letter-spacing: -0.02em;
  color: white;
}

.admin-sidebar__sub {
  font-size: 0.7rem;
  color: rgba(255,255,255,0.45);
  margin-top: 0.5rem;
  display: block;
}

.admin-sidebar__nav {
  padding: 1.5rem 0.75rem;
  flex: 1;
  overflow-y: auto;
}

.admin-sidebar__link {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.85rem 1.2rem;
  margin: 0.25rem 0;
  color: rgba(255,255,255,0.7);
  text-decoration: none;
  transition: var(--transition);
  border-radius: 14px;
  font-weight: 500;
  font-size: 0.9rem;
  position: relative;
}

.admin-sidebar__link i {
  width: 24px;
  font-size: 1.1rem;
  text-align: center;
}

.admin-sidebar__link:hover {
  background: rgba(138,118,80,0.2);
  color: white;
  transform: translateX(5px);
}

.admin-sidebar__link.active {
  background: rgba(138,118,80,0.25);
  color: white;
  font-weight: 600;
  border-left: 3px solid var(--gold);
}

.admin-sidebar__badge {
  position: absolute;
  right: 1.2rem;
  background: var(--gold);
  color: white;
  font-size: 0.7rem;
  padding: 0.2rem 0.6rem;
  border-radius: 30px;
  font-weight: 700;
}

.admin-sidebar__footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(255,255,255,0.08);
  margin-top: auto;
  flex-shrink: 0;
  pointer-events: auto;
  z-index: 1100;
  position: relative;
}

.admin-sidebar__logout {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  color: rgba(255,255,255,0.6);
  text-decoration: none;
  font-size: 0.85rem;
  transition: var(--transition);
  font-weight: 500;
}

.admin-sidebar__logout:hover {
  color: #ff8a7a;
  transform: translateX(3px);
}

/* --- Main area --- */
.admin-main {
  flex: 1;
  margin-left: 280px;
  padding: 1.8rem 2rem 2rem;
  min-height: 100vh;
  transition: var(--transition);
}

.admin-topbar {
  background: var(--white);
  border-radius: var(--radius-md);
  padding: 0.8rem 1.8rem;
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--gray-light);
  gap: 1rem;
}

.admin-topbar__user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  white-space: nowrap;
  flex-wrap: nowrap;
}

.admin-topbar__title h2 {
  font-size: 1.5rem;
  margin: 0;
  font-family: 'Cormorant Garamond', serif;
  font-weight: 600;
  color: var(--charcoal);
  letter-spacing: -0.01em;
}

.admin-topbar__title p {
  font-size: 0.8rem;
  color: var(--gray-mid);
  margin: 0.2rem 0 0;
}

.admin-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: rgba(138,118,80,0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gold);
  cursor: pointer;
  transition: var(--transition);
  border: 1px solid var(--gray-light);
}

.admin-avatar:hover {
  background: rgba(138,118,80,0.2);
  transform: scale(1.05);
  border-color: var(--gold);
}

/* Responsive - Tablet */
@media (max-width: 1024px) {
  .admin-sidebar {
    width: 240px;
  }
  .admin-main {
    margin-left: 240px;
    padding: 1.2rem;
  }
  .admin-topbar {
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
  }
}

/* Responsive - Mobile */
@media (max-width: 768px) {
  :root {
    font-size: 14px;
  }
  
  .admin-wrapper {
    flex-direction: column;
  }

  .admin-sidebar {
    transform: translateX(-100%);
    width: 100%;
    max-width: 280px;
    height: 100vh;
    position: fixed;
    z-index: 1000;
    top: 0;
    left: 0;
    box-shadow: var(--shadow-lg);
    border-right: none;
  }
  
  .admin-sidebar.open {
    transform: translateX(0);
  }
  
  .admin-sidebar::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    z-index: -1;
  }
  
  .admin-sidebar.open::before {
    opacity: 1;
    pointer-events: all;
  }

  .admin-main {
    margin-left: 0;
    padding: 0.8rem;
    min-height: calc(100vh - 60px);
  }
  
  .admin-topbar {
    padding: 0.8rem;
    margin-bottom: 1rem;
    border-radius: var(--radius-sm);
    flex-wrap: wrap;
    gap: 0.8rem;
  }
  
  .admin-sidebar__header {
    padding: 1rem;
  }
  
  .admin-sidebar__logo-img {
    width: 36px;
    height: 36px;
  }
  
  .admin-sidebar__logo-text {
    font-size: 1rem;
  }
  
  .admin-sidebar__nav {
    padding: 1rem 0.5rem;
  }
  
  .admin-sidebar__item {
    margin: 0.3rem 0;
    border-radius: var(--radius-sm);
  }
  
  .admin-sidebar__link {
    padding: 0.8rem 1rem;
    font-size: 0.9rem;
  }
  
  .admin-sidebar__link i {
    font-size: 1.1rem;
  }
  
  .admin-topbar__user {
    gap: 0.5rem;
  }
  
  .admin-topbar__user-name {
    display: none;
  }
  
  .fab-add {
    bottom: 1.5rem;
    right: 1.5rem;
    width: 50px;
    height: 50px;
    font-size: 1.3rem;
  }
}

/* Responsive - Small Mobile */
@media (max-width: 480px) {
  .admin-sidebar {
    max-width: 100%;
  }
  
  .admin-main {
    padding: 0.6rem;
  }
  
  .admin-topbar {
    padding: 0.6rem;
    gap: 0.5rem;
  }
  
  .admin-sidebar__header {
    padding: 0.8rem;
  }
  
  .admin-sidebar__logo-text {
    font-size: 0.9rem;
  }
  
  .admin-sidebar__logout {
    font-size: 0.75rem;
    padding: 1rem;
  }
  
  .fab-add {
    bottom: 1rem;
    right: 1rem;
  }
  
  .admin-icon-btn {
    width: 36px;
    height: 36px;
  }
}

/* Floating Action Button (used on many pages) */
.fab-add {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 56px;
  height: 56px;
  border-radius: 28px;
  background: linear-gradient(135deg, var(--gold), var(--gold-dark));
  color: white;
  border: none;
  box-shadow: 0 6px 14px rgba(0,0,0,0.2);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  transition: all 0.2s;
  z-index: 100;
}
.fab-add:hover {
  transform: scale(1.08);
  background: linear-gradient(135deg, var(--gold-light), var(--gold));
}

/* Admin Notification Styles */
.admin-icon-btn {
  position: relative;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: rgba(138,118,80,0.1);
  border: 1px solid var(--gray-light);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: var(--gold);
  transition: all 0.2s ease;
  padding: 0;
}

.admin-icon-btn:hover {
  background: rgba(138,118,80,0.2);
  border-color: var(--gold);
}

.admin-notif-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 20px;
  height: 20px;
  background: var(--gold);
  color: white;
  font-size: 0.65rem;
  font-weight: 700;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
}

.admin-notif-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 320px;
  background: white;
  border: 1px solid var(--gray-light);
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0,0,0,0.15);
  overflow: hidden;
  opacity: 0;
  transform: translateY(-8px);
  pointer-events: none;
  transition: all 0.2s ease;
  z-index: 1000;
}

.admin-notif-dropdown.active {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

.admin-notif-dropdown__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--gray-light);
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--charcoal);
}

.admin-notif-dropdown__header a {
  font-size: 0.75rem;
  color: var(--gold);
  text-decoration: none;
  cursor: pointer;
}

.admin-notif-dropdown__header a:hover {
  text-decoration: underline;
}

.admin-notif-dropdown__container {
  max-height: 400px;
  overflow-y: auto;
}

.admin-notif-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid var(--gray-light);
  cursor: pointer;
  transition: background 0.2s ease;
  position: relative;
}

.admin-notif-item:last-child {
  border-bottom: none;
}

.admin-notif-item:hover {
  background: rgba(138,118,80,0.05);
}

.admin-notif-item.admin-notif-read {
  opacity: 0.7;
  background: rgba(245,240,232,0.5);
}

.admin-notif-icon-wrapper {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(138,118,80,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.admin-notif-icon-wrapper i {
  font-size: 0.9rem;
}

.admin-notif-content {
  flex: 1;
  min-width: 0;
}

.admin-notif-title {
  font-size: 0.82rem;
  color: var(--charcoal);
  font-weight: 500;
  margin: 0;
  word-break: break-word;
}

.admin-notif-message {
  font-size: 0.75rem;
  color: var(--gray-mid);
  margin-top: 0.25rem;
  word-break: break-word;
}

.admin-notif-time {
  font-size: 0.7rem;
  color: var(--gray-mid);
  margin-top: 0.3rem;
}

.admin-notif-actions {
  display: flex;
  gap: 0.4rem;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.admin-notif-item:hover .admin-notif-actions {
  opacity: 1;
}

.admin-notif-action-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: rgba(138,118,80,0.1);
  border-radius: 6px;
  color: var(--gold);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  transition: all 0.2s ease;
  flex-shrink: 0;
  padding: 0;
}

.admin-notif-action-btn:hover {
  background: rgba(138,118,80,0.2);
}

.admin-notif-action-btn.admin-notif-delete-btn:hover {
  background: rgba(192,57,43,0.2);
  color: #c0392b;
}

.admin-notif-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.25rem;
  color: var(--gray-mid);
}

.admin-notif-loading i {
  font-size: 1.5rem;
}
</style>
<div class="admin-wrapper">
<button class="admin-mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
<aside class="admin-sidebar" id="adminSidebar">
<div class="admin-sidebar__header"><a href="<?php echo BASE_URL; ?>/index.php?route=admin-dashboard" class="admin-sidebar__logo"><img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Sinta" class="admin-sidebar__logo-img"><span class="admin-sidebar__logo-text">Sinta</span></a><span class="admin-sidebar__sub">Admin Panel</span></div>
<nav class="admin-sidebar__nav">
<a href="index.php?route=admin-dashboard" class="admin-sidebar__link<?php echo isset($page) && $page === 'admin-dashboard' ? ' active' : ''; ?>"><i class="fas fa-home"></i><span>Dashboard</span></a>
<a href="index.php?route=admin-manage-packages" class="admin-sidebar__link<?php echo isset($page) && $page === 'admin-packages' ? ' active' : ''; ?>"><i class="fas fa-box"></i><span>Packages</span></a>
<a href="index.php?route=admin-occasions" class="admin-sidebar__link<?php echo isset($page) && $page === 'admin-occasions' ? ' active' : ''; ?>"><i class="fas fa-calendar-day"></i><span>Occasions</span></a>
<a href="index.php?route=admin-bookings" class="admin-sidebar__link<?php echo isset($page) && $page === 'admin-bookings' ? ' active' : ''; ?>"><i class="fas fa-calendar-check"></i><span>Bookings</span></a>
<a href="index.php?route=admin-feedback" class="admin-sidebar__link<?php echo isset($page) && $page === 'admin-feedback' ? ' active' : ''; ?>"><i class="fas fa-comment-dots"></i><span>Feedback</span></a>
<a href="index.php?route=admin-customize" class="admin-sidebar__link<?php echo isset($page) && $page === 'admin-customize' ? ' active' : ''; ?>"><i class="fas fa-palette"></i><span>Customization</span></a>
<a href="index.php?route=admin-wardrobe" class="admin-sidebar__link<?php echo isset($page) && (strpos($page, 'admin-wardrobe') !== false) ? ' active' : ''; ?>"><i class="fas fa-tshirt"></i><span>Wardrobe</span></a>
<a href="index.php?route=admin-messages" class="admin-sidebar__link<?php echo isset($page) && $page === 'admin-messages' ? ' active' : ''; ?>"><i class="fas fa-envelope"></i><span>Messages</span><?php if($unread>0): ?><span class="admin-sidebar__badge"><?= $unread ?></span><?php endif; ?></a>
</nav>
<div class="admin-sidebar__footer"><a href="#" class="admin-sidebar__logout" onclick="openLogoutModal(event);"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
</aside>
<main class="admin-main">
<div class="admin-topbar">
<div class="admin-topbar__title"><h2 id="pageTitle"><?php echo $page_title ?? 'Dashboard'; ?></h2><p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p></div>
<div class="admin-topbar__user">
  <div class="admin-notif-wrapper" style="position: relative;">
    <button class="admin-icon-btn" id="adminNotifBtn" title="Notifications">
      <i class="fas fa-bell"></i>
      <span class="admin-notif-badge" id="adminNotifBadge">0</span>
    </button>
    <div class="admin-notif-dropdown" id="adminNotifDropdown">
      <div class="admin-notif-dropdown__header">
        <span>Notifications</span>
        <a href="#" onclick="markAllAdminNotificationsAsRead(event)">Mark all read</a>
      </div>
      <div class="admin-notif-dropdown__container" id="adminNotifContainer">
        <div class="admin-notif-loading"><i class="fas fa-spinner fa-spin"></i></div>
      </div>
    </div>
  </div>
  <span style="font-size: 0.9rem;">Administrator</span>
  <div class="admin-avatar" onclick="window.location.href='<?php echo BASE_URL; ?>/index.php?route=admin-profile'"><i class="fas fa-user-cog"></i></div>
</div>
</div>
<div class="admin-content">

<script>
// Base URL for API calls
const baseUrl = '<?php echo BASE_URL; ?>';

// Admin Notification Type Configuration
const adminNotificationTypes = {
  'realtime': { icon: 'fa-clock', label: 'Real-time Update', color: '#8A7650' },
  'messages': { icon: 'fa-envelope', label: 'Message', color: '#3498db' },
  'ratings': { icon: 'fa-star', label: 'Rating', color: '#f39c12' },
  'replies': { icon: 'fa-reply', label: 'Reply', color: '#1abc9c' },
  'feedback': { icon: 'fa-comment-dots', label: 'Feedback', color: '#9b59b6' },
  'book_confirmation': { icon: 'fa-calendar-check', label: 'Book Confirmation', color: '#27ae60' },
  'new_updates': { icon: 'fa-bell', label: 'New Update', color: '#e74c3c' },
  'receipts': { icon: 'fa-receipt', label: 'Receipt', color: '#2c3e50' },
  'user_activity': { icon: 'fa-user', label: 'User Activity', color: '#16a085' },
  'system_update': { icon: 'fa-cog', label: 'System Update', color: '#95a5a6' }
};

(function() {
  const adminNotifBtn = document.getElementById('adminNotifBtn');
  const adminNotifDropdown = document.getElementById('adminNotifDropdown');
  const adminNotifContainer = document.getElementById('adminNotifContainer');
  const adminNotifBadge = document.getElementById('adminNotifBadge');
  let adminNotificationRefreshInterval = null;

  // Function to get notification icon and color
  function getAdminNotificationTypeInfo(type) {
    return adminNotificationTypes[type] || adminNotificationTypes['system_update'];
  }

  // Function to format time
  function formatAdminTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    
    return date.toLocaleDateString();
  }

  // Function to load admin notifications
  function loadAdminNotifications() {
    if (!adminNotifContainer) return;

    fetch(baseUrl + '/index.php?route=admin-notifications&action=get_unread&limit=15', {
      credentials: 'same-origin'
    })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.notifications) {
          // Update badge
          if (adminNotifBadge) {
            if (data.unread_count > 0) {
              adminNotifBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
              adminNotifBadge.style.display = 'flex';
            } else {
              adminNotifBadge.style.display = 'none';
            }
          }

          // Render notifications
          renderAdminNotifications(data.notifications);
        } else {
          adminNotifContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #A39B8B;"><p>No notifications</p></div>';
        }
      })
      .catch(error => {
        console.error('Error loading admin notifications:', error);
        adminNotifContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #c0392b;"><p>Error loading notifications</p></div>';
      });
  }

  // Function to render admin notifications
  function renderAdminNotifications(notifications) {
    if (!adminNotifContainer) return;

    if (notifications.length === 0) {
      adminNotifContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #A39B8B;"><p>No notifications</p></div>';
      return;
    }

    let html = '';
    notifications.forEach(notif => {
      const typeInfo = getAdminNotificationTypeInfo(notif.type);
      const isRead = notif.is_read == 1;
      const timeStr = formatAdminTime(notif.created_at);

      html += `
        <div class="admin-notif-item ${isRead ? 'admin-notif-read' : ''}" data-id="${notif.id}">
          <div class="admin-notif-icon-wrapper">
            <i class="fas ${typeInfo.icon}" style="color: ${typeInfo.color};"></i>
          </div>
          <div class="admin-notif-content">
            <div class="admin-notif-title">${notif.title || typeInfo.label}</div>
            <div class="admin-notif-message" style="font-size: 0.75rem; color: #A39B8B; margin-top: 0.25rem;">${notif.message || ''}</div>
            <div class="admin-notif-time">${timeStr}</div>
          </div>
          <div class="admin-notif-actions">
            <button class="admin-notif-action-btn" onclick="markAdminNotificationAsRead(${notif.id})" title="Mark as read">
              <i class="fas fa-check"></i>
            </button>
            <button class="admin-notif-action-btn admin-notif-delete-btn" onclick="deleteAdminNotification(${notif.id})" title="Delete">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </div>
      `;
    });

    adminNotifContainer.innerHTML = html;
  }

  // Mark admin notification as read
  window.markAdminNotificationAsRead = function(notificationId) {
    fetch(baseUrl + '/index.php?route=admin-notifications&action=mark_as_read', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      credentials: 'same-origin',
      body: 'notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadAdminNotifications();
      }
    })
    .catch(error => console.error('Error marking admin notification as read:', error));
  };

  // Delete admin notification
  window.deleteAdminNotification = function(notificationId) {
    fetch(baseUrl + '/index.php?route=admin-notifications&action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      credentials: 'same-origin',
      body: 'notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadAdminNotifications();
      }
    })
    .catch(error => console.error('Error deleting admin notification:', error));
  };

  // Mark all as read
  window.markAllAdminNotificationsAsRead = function(e) {
    e.preventDefault();
    fetch(baseUrl + '/index.php?route=admin-notifications&action=mark_all_as_read', { 
      method: 'POST',
      credentials: 'same-origin'
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadAdminNotifications();
        }
      })
      .catch(error => console.error('Error marking all admin notifications as read:', error));
  };

  // Setup admin notification button click
  if (adminNotifBtn && adminNotifDropdown) {
    adminNotifBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      adminNotifDropdown.classList.toggle('active');
      
      // Load notifications when dropdown opens
      if (adminNotifDropdown.classList.contains('active')) {
        loadAdminNotifications();
        
        // Auto-refresh every 30 seconds while dropdown is open
        if (!adminNotificationRefreshInterval) {
          adminNotificationRefreshInterval = setInterval(loadAdminNotifications, 30000);
        }
      } else {
        // Clear interval when dropdown closes
        if (adminNotificationRefreshInterval) {
          clearInterval(adminNotificationRefreshInterval);
          adminNotificationRefreshInterval = null;
        }
      }
    });
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (adminNotifBtn && adminNotifBtn.contains(e.target)) return;
    if (adminNotifDropdown && adminNotifDropdown.contains(e.target)) return;
    
    if (adminNotifDropdown) adminNotifDropdown.classList.remove('active');
  });

  // Load notifications on page load
  loadAdminNotifications();
})();

// Mobile sidebar toggle
const mobileToggle = document.getElementById('mobileToggle');
const adminSidebar = document.getElementById('adminSidebar');

if (mobileToggle && adminSidebar) {
  mobileToggle.addEventListener('click', function() {
    adminSidebar.classList.toggle('open');
  });
}
</script>
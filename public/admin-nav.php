<?php
// partials/admin-nav.php - Admin Sidebar Navigation (UPDATED with Messages)
// Include this file in all admin pages
?>
<style>
/* Admin Sidebar Styles */
.admin-wrapper {
  display: flex;
  min-height: 100vh;
  background: var(--bg-secondary);
}

.admin-sidebar {
  width: 280px;
  background: var(--dark);
  color: white;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  overflow-y: auto;
  transition: all 0.3s ease;
  z-index: 100;
}

.admin-sidebar__header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  text-align: center;
}

.admin-sidebar__logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  text-decoration: none;
}

.admin-sidebar__logo-img {
  width: 40px;
  height: 40px;
  border-radius: 10px;
}

.admin-sidebar__logo-text {
  font-family: var(--serif);
  font-size: 1.5rem;
  font-weight: 600;
  color: white;
}

.admin-sidebar__sub {
  font-size: 0.7rem;
  color: rgba(255,255,255,0.5);
  margin-top: 0.5rem;
  display: block;
}

.admin-sidebar__nav {
  padding: 1.5rem 0;
}

.admin-sidebar__link {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.85rem 1.5rem;
  color: rgba(255,255,255,0.7);
  text-decoration: none;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
  position: relative;
}

.admin-sidebar__link i {
  width: 22px;
  font-size: 1rem;
}

.admin-sidebar__link span {
  font-size: 0.85rem;
  font-weight: 500;
}

.admin-sidebar__link:hover {
  background: rgba(255,255,255,0.05);
  color: white;
  border-left-color: var(--primary);
}

.admin-sidebar__link.active {
  background: rgba(255,255,255,0.08);
  color: white;
  border-left-color: var(--primary);
}

/* Message badge for unread count */
.admin-sidebar__badge {
  position: absolute;
  right: 1.5rem;
  background: var(--primary);
  color: white;
  font-size: 0.7rem;
  padding: 0.15rem 0.5rem;
  border-radius: 20px;
  font-weight: 600;
}

.admin-sidebar__footer {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 1.5rem;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.admin-sidebar__logout {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: rgba(255,255,255,0.6);
  text-decoration: none;
  font-size: 0.85rem;
  transition: all 0.2s ease;
}

.admin-sidebar__logout:hover {
  color: #ff6b6b;
}

/* Main Content Area */
.admin-main {
  flex: 1;
  margin-left: 280px;
  padding: 2rem;
  min-height: 100vh;
}

.admin-topbar {
  background: white;
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1rem 1.5rem;
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.admin-topbar__title h2 {
  font-size: 1.2rem;
  margin: 0;
}

.admin-topbar__title p {
  font-size: 0.75rem;
  color: var(--gray);
  margin: 0;
}

.admin-topbar__user {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.admin-topbar__user span {
  font-size: 0.85rem;
  font-weight: 500;
}

.admin-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary-pale);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
}

/* Mobile Menu Button */
.admin-mobile-toggle {
  display: none;
  position: fixed;
  top: 1rem;
  left: 1rem;
  z-index: 200;
  background: var(--primary);
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  cursor: pointer;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  .admin-sidebar.open {
    transform: translateX(0);
  }
  .admin-main {
    margin-left: 0;
  }
  .admin-mobile-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}
</style>

<?php
// Get unread message count for badge
$unread_count = 0;
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // In production, query database for unread messages
    // For now using session for demo
    if (!isset($_SESSION['admin_unread_count'])) {
        $_SESSION['admin_unread_count'] = 0;
    }
    $unread_count = $_SESSION['admin_unread_count'];
}
?>

<div class="admin-wrapper">
  <!-- Mobile Toggle Button -->
  <button class="admin-mobile-toggle" id="mobileToggle">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar__header">
      <a href="admin-dashboard.php" class="admin-sidebar__logo">
        <img src="assets/img/logo.png" alt="Sinta" class="admin-sidebar__logo-img">
        <span class="admin-sidebar__logo-text">Sinta</span>
      </a>
      <span class="admin-sidebar__sub">Admin Panel</span>
    </div>
    
    <nav class="admin-sidebar__nav">
      <a href="admin-dashboard.php" class="admin-sidebar__link <?= basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
      </a>
      <a href="admin-packages.php" class="admin-sidebar__link <?= basename($_SERVER['PHP_SELF']) == 'admin-packages.php' ? 'active' : '' ?>">
        <i class="fas fa-box"></i>
        <span>Package Management</span>
      </a>
      <a href="admin-bookings.php" class="admin-sidebar__link <?= basename($_SERVER['PHP_SELF']) == 'admin-bookings.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i>
        <span>Booking Management</span>
      </a>
      <a href="admin-testimonials.php" class="admin-sidebar__link <?= basename($_SERVER['PHP_SELF']) == 'admin-testimonials.php' ? 'active' : '' ?>">
        <i class="fas fa-star"></i>
        <span>Testimonial Management</span>
      </a>
      <!-- NEW: Messages Link -->
      <a href="admin-messages.php" class="admin-sidebar__link <?= basename($_SERVER['PHP_SELF']) == 'admin-messages.php' ? 'active' : '' ?>">
        <i class="fas fa-envelope"></i>
        <span>Messages</span>
        <?php if ($unread_count > 0): ?>
          <span class="admin-sidebar__badge"><?= $unread_count ?></span>
        <?php endif; ?>
      </a>
    </nav>
    
    <div class="admin-sidebar__footer">
     <a href="admin-logout.php" class="admin-sidebar__logout">
    <i class="fas fa-sign-out-alt"></i>
    <span>Logout</span>
</a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div class="admin-topbar__title">
        <h2><?= $page_title ?? 'Dashboard' ?></h2>
        <p>Welcome back, Admin</p>
      </div>
      <div class="admin-topbar__user">
        <span>Administrator</span>
        <div class="admin-avatar">
          <i class="fas fa-user-cog"></i>
        </div>
      </div>
    </div>
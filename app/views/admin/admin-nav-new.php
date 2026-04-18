<?php
// admin-nav.php - Modern Minimalist Admin Navigation
$unread_count = $_SESSION['admin_unread_count'] ?? 0;
?>
<style>
/* ================================
   MODERN MINIMALIST ADMIN DESIGN
   ================================ */

:root {
    --primary-color: #4F46E5;      /* Indigo */
    --secondary-color: #10B981;    /* Emerald */
    --danger-color: #EF4444;       /* Red */
    --warning-color: #F59E0B;      /* Amber */
    --bg-primary: #FFFFFF;         /* Pure white */
    --bg-secondary: #F9FAFB;       /* Light gray */
    --bg-tertiary: #F3F4F6;        /* Medium gray */
    --text-primary: #111827;       /* Dark gray */
    --text-secondary: #6B7280;     /* Medium gray */
    --border-color: #E5E7EB;       /* Light border */
    --sidebar-bg: #FFFFFF;         /* White sidebar */
    --sidebar-accent: #F0F9FF;     /* Light blue */
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.admin-wrapper {
    display: flex;
    min-height: 100vh;
    background: var(--bg-secondary);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', sans-serif;
}

/* ================================
   SIDEBAR NAVIGATION
   ================================ */

.admin-sidebar {
    width: 260px;
    background: var(--sidebar-bg);
    color: var(--text-primary);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    overflow-y: auto;
    border-right: 1px solid var(--border-color);
    z-index: 100;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-sidebar::-webkit-scrollbar {
    width: 6px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--text-secondary);
}

.admin-sidebar__header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-primary);
}

.admin-sidebar__logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--text-primary);
    transition: opacity 0.2s ease;
}

.admin-sidebar__logo:hover {
    opacity: 0.8;
}

.admin-sidebar__logo-img {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}

.admin-sidebar__logo-text {
    font-size: 1.3rem;
    font-weight: 600;
    letter-spacing: -0.5px;
    color: var(--text-primary);
}

.admin-sidebar__sub {
    font-size: 0.7rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    opacity: 0.7;
}

.admin-sidebar__nav {
    padding: 1.5rem 0.75rem;
}

.admin-sidebar__section {
    margin-bottom: 1.5rem;
}

.admin-sidebar__section-title {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    color: var(--text-secondary);
    text-transform: uppercase;
    padding: 0.75rem 0.75rem;
    opacity: 0.6;
}

.admin-sidebar__link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    margin: 0.25rem 0;
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    font-size: 0.95rem;
    font-weight: 500;
}

.admin-sidebar__link i {
    width: 20px;
    text-align: center;
    font-size: 1.1rem;
    opacity: 0.7;
}

.admin-sidebar__link:hover {
    background: var(--sidebar-accent);
    color: var(--primary-color);
}

.admin-sidebar__link:hover i {
    opacity: 1;
}

.admin-sidebar__link.active {
    background: var(--sidebar-accent);
    color: var(--primary-color);
    font-weight: 600;
}

.admin-sidebar__link.active i {
    opacity: 1;
}

.admin-sidebar__badge {
    margin-left: auto;
    background: var(--danger-color);
    color: white;
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    min-width: 20px;
    text-align: center;
    font-weight: 600;
}

.admin-sidebar__logout {
    position: absolute;
    bottom: 1.5rem;
    left: 0;
    right: 0;
    padding: 0 0.75rem;
}

.admin-sidebar__logout .admin-sidebar__link {
    color: var(--text-secondary);
    margin: 0;
}

.admin-sidebar__logout .admin-sidebar__link:hover {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger-color);
}

/* ================================
   MAIN CONTENT AREA
   ================================ */

.admin-main {
    flex: 1;
    margin-left: 260px;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    background: var(--bg-primary);
    border-bottom: 1px solid var(--border-color);
    gap: 1.5rem;
}

.admin-header__title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    letter-spacing: -0.5px;
}

.admin-header__subtitle {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.admin-header__user {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-left: 1.5rem;
    border-left: 1px solid var(--border-color);
}

.admin-header__user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.admin-header__user-info {
    display: flex;
    flex-direction: column;
}

.admin-header__user-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-primary);
}

.admin-header__user-role {
    font-size: 0.75rem;
    color: var(--text-secondary);
    opacity: 0.8;
}

.admin-content {
    padding: 2rem;
    max-width: 1600px;
}

/* ================================
   RESPONSIVE DESIGN
   ================================ */

@media (max-width: 768px) {
    .admin-sidebar {
        width: 240px;
    }
    
    .admin-main {
        margin-left: 240px;
    }
    
    .admin-header {
        padding: 1rem 1.5rem;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-content {
        padding: 1.5rem;
    }
}

@media (max-width: 640px) {
    .admin-sidebar {
        transform: translateX(-100%);
        width: 240px;
    }
    
    .admin-main {
        margin-left: 0;
    }
    
    .admin-header {
        padding: 1rem;
    }
    
    .admin-content {
        padding: 1rem;
    }
}
</style>

<!-- SIDEBAR NAVIGATION -->
<aside class="admin-sidebar">
    <!-- Logo Section -->
    <div class="admin-sidebar__header">
        <a href="/SINTA/public/index.php?route=admin-dashboard" class="admin-sidebar__logo">
            <div class="admin-sidebar__logo-img">S</div>
            <div>
                <div class="admin-sidebar__logo-text">Sinta</div>
                <span class="admin-sidebar__sub">Admin</span>
            </div>
        </a>
    </div>

    <!-- Navigation Links -->
    <nav class="admin-sidebar__nav">
        <!-- Main Section -->
        <div class="admin-sidebar__section">
            <div class="admin-sidebar__section-title">Main</div>
            <a href="/SINTA/public/index.php?route=admin-dashboard" 
               class="admin-sidebar__link <?php echo ($_GET['route'] ?? '') === 'admin-dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Management Section -->
        <div class="admin-sidebar__section">
            <div class="admin-sidebar__section-title">Management</div>
            <a href="/SINTA/public/index.php?route=admin-packages" 
               class="admin-sidebar__link <?php echo ($_GET['route'] ?? '') === 'admin-packages' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Packages</span>
            </a>
            <a href="/SINTA/public/index.php?route=admin-bookings" 
               class="admin-sidebar__link <?php echo ($_GET['route'] ?? '') === 'admin-bookings' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
            </a>
            <a href="/SINTA/public/index.php?route=admin-customizations" 
               class="admin-sidebar__link <?php echo ($_GET['route'] ?? '') === 'admin-customizations' ? 'active' : ''; ?>">
                <i class="fas fa-sparkles"></i>
                <span>Customizations</span>
            </a>
        </div>

        <!-- Communication Section -->
        <div class="admin-sidebar__section">
            <div class="admin-sidebar__section-title">Communication</div>
            <a href="/SINTA/public/index.php?route=admin-testimonials" 
               class="admin-sidebar__link <?php echo ($_GET['route'] ?? '') === 'admin-testimonials' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i>
                <span>Testimonials</span>
            </a>
            <a href="/SINTA/public/index.php?route=admin-messages" 
               class="admin-sidebar__link <?php echo ($_GET['route'] ?? '') === 'admin-messages' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
                <?php if ($unread_count > 0): ?>
                    <span class="admin-sidebar__badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </nav>

    <!-- Logout Section -->
    <div class="admin-sidebar__logout">
        <a href="/SINTA/public/index.php?route=admin-logout" class="admin-sidebar__link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<!-- MAIN CONTENT WRAPPER -->
<div class="admin-main">
    <!-- Header Section -->
    <header class="admin-header">
        <div>
            <h1 class="admin-header__title" id="page-title">Dashboard</h1>
            <p class="admin-header__subtitle">Welcome back, Admin</p>
        </div>
        <div class="admin-header__user">
            <div class="admin-header__user-avatar">A</div>
            <div class="admin-header__user-info">
                <div class="admin-header__user-name">Administrator</div>
                <div class="admin-header__user-role">Admin Panel</div>
            </div>
        </div>
    </header>

    <!-- Content Area -->
    <main class="admin-content" id="admin-content">
        <!-- Content will be injected here -->
    </main>
</div>

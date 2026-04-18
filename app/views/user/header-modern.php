<?php
/**
 * USER HEADER & NAVIGATION - Modern Minimalist
 * Location: app/views/user/header-modern.php
 */

// Get unread messages count
require_once ROOT_PATH . '/app/models/Message.php';
$messageModel = new Message();
$unreadCount = 0;

if (isset($_SESSION['user_id'])) {
    $userMessages = $messageModel->getUserMessages($_SESSION['user_id']);
    $unreadCount = count(array_filter($userMessages, fn($m) => $m['status'] === 'unread' && $m['is_admin_reply'] === 1));
}

$currentRoute = $_GET['route'] ?? 'homepage';
$isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'];
$userName = $_SESSION['user_name'] ?? 'User';
$userInitial = strtoupper(substr($userName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Sinta' : 'Sinta - Event Planning'; ?></title>
    <link rel="stylesheet" href="/SINTA/public/assets/css/user-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-header__actions .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
        }
        
        .user-header__icon-link {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            color: var(--text-secondary);
            transition: var(--transition);
            cursor: pointer;
            background: var(--bg-light);
        }
        
        .user-header__icon-link:hover {
            background: var(--bg-lighter);
            color: var(--primary);
        }
    </style>
</head>
<body>

<!-- HEADER & NAVIGATION -->
<header class="user-header">
    <div class="user-header__container">
        <!-- Logo -->
        <a href="/SINTA/public/index.php?route=homepage" class="user-header__logo">
            <div class="user-header__logo-icon">
                <i class="fas fa-gift"></i>
            </div>
            <span>Sinta</span>
        </a>

        <!-- Navigation Links -->
        <nav class="user-header__nav">
            <a href="/SINTA/public/index.php?route=homepage" 
               class="user-header__link <?php echo $currentRoute === 'homepage' ? 'active' : ''; ?>">
                Home
            </a>
            <a href="/SINTA/public/index.php?route=packages" 
               class="user-header__link <?php echo $currentRoute === 'packages' ? 'active' : ''; ?>">
                Packages
            </a>
            <a href="/SINTA/public/index.php?route=occasions" 
               class="user-header__link <?php echo $currentRoute === 'occasions' ? 'active' : ''; ?>">
                Events
            </a>
            <a href="/SINTA/public/index.php?route=about" 
               class="user-header__link <?php echo $currentRoute === 'about' ? 'active' : ''; ?>">
                About
            </a>
        </nav>

        <!-- Action Buttons -->
        <div class="user-header__actions">
            <?php if ($isLoggedIn): ?>
                <!-- Messages Icon -->
                <a href="/SINTA/public/index.php?route=messages" class="user-header__icon-link" title="Messages">
                    <i class="fas fa-envelope"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notification-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>

                <!-- Profile Menu -->
                <div class="user-header__user" onclick="toggleProfileMenu()">
                    <div class="user-header__avatar"><?php echo $userInitial; ?></div>
                    <span><?php echo htmlspecialchars($userName); ?></span>
                    <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                </div>

                <!-- Profile Dropdown -->
                <div id="profileMenu" style="display: none; position: absolute; top: 60px; right: 20px; background: white; border: 1px solid var(--border); border-radius: 8px; box-shadow: var(--shadow-lg); z-index: 200; min-width: 200px; overflow: hidden;">
                    <a href="/SINTA/public/index.php?route=profile" style="display: block; padding: 1rem; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border); transition: var(--transition);" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a href="/SINTA/public/index.php?route=bookings" style="display: block; padding: 1rem; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border); transition: var(--transition);" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-calendar"></i> My Bookings
                    </a>
                    <a href="/SINTA/public/index.php?route=logout" style="display: block; padding: 1rem; color: var(--danger); text-decoration: none; transition: var(--transition);" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            <?php else: ?>
                <a href="/SINTA/public/index.php?route=signin" class="btn btn--secondary btn--sm">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </a>
                <a href="/SINTA/public/index.php?route=signup" class="btn btn--primary btn--sm">
                    <i class="fas fa-user-plus"></i> Sign Up
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    function toggleProfileMenu() {
        const menu = document.getElementById('profileMenu');
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('profileMenu');
        const userButton = document.querySelector('.user-header__user');
        if (menu && !menu.contains(event.target) && !userButton.contains(event.target)) {
            menu.style.display = 'none';
        }
    });
</script>

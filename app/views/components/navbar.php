<?php
// Navbar Component
// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? null;
?>
<nav class="nav">
    <div class="nav__inner">
        <!-- Logo -->
        <a href="<?php echo BASE_URL; ?>/index.php?route=landing" class="nav__logo">
            <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="SINTA Logo" class="nav__logo-img">
            <span class="nav__logo-text">SINTA</span>
        </a>

        <!-- Navigation Links -->
        <div class="nav__links">
            <a href="<?php echo BASE_URL; ?>/index.php?route=landing#features" class="nav-link">Features</a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=landing#pricing" class="nav-link">Pricing</a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=landing#about" class="nav-link">About</a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=landing#contact" class="nav-link">Contact</a>
        </div>

        <!-- Actions (Login/Signup or Dashboard) -->
        <div class="nav__actions">
            <?php if ($is_logged_in): ?>
                <!-- Logged in user options -->
                <?php if ($user_role === 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin-dashboard" class="btn btn--ghost">Admin Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=homepage" class="btn btn--ghost">Dashboard</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn--outline">Logout</a>
            <?php else: ?>
                <!-- Not logged in - show login/signup buttons -->
                <a href="<?php echo BASE_URL; ?>/index.php?route=signin" class="btn btn--outline">Login</a>
                <a href="<?php echo BASE_URL; ?>/index.php?route=signup" class="btn btn--primary">Sign Up</a>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="nav__toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<script>
// Add scrolled class to navbar when scrolling
document.addEventListener('DOMContentLoaded', function() {
    const nav = document.querySelector('.nav');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 10) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });

    // Mobile menu toggle
    const toggle = document.querySelector('.nav__toggle');
    if (toggle) {
        toggle.addEventListener('click', function() {
            const links = document.querySelector('.nav__links');
            links.classList.toggle('active');
        });
    }

    // Close menu when clicking on a link
    document.querySelectorAll('.nav__links a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelector('.nav__links').classList.remove('active');
        });
    });

    // Set active link based on current route
    const currentRoute = new URLSearchParams(window.location.search).get('route') || 'landing';
    document.querySelectorAll('.nav__links a').forEach(link => {
        if (link.getAttribute('href').includes(currentRoute)) {
            link.classList.add('active');
        }
    });
});
</script>

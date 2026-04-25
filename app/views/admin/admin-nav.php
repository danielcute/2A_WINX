<?php
$unread = $_SESSION['admin_unread_count'] ?? 0;
?>
<style>
@keyframes iconRotate {from {transform:rotate(0deg);opacity:0.8} to {transform:rotate(360deg);opacity:1}}
@keyframes titleFade {from {opacity:0;transform:translateX(-10px)} to {opacity:1;transform:translateX(0)}}
@keyframes floatBounce {0%, 100% {transform:translateY(0)} 50% {transform:translateY(-8px)}}
.admin-wrapper {display:flex;min-height:100vh;background:linear-gradient(135deg,#f8f6f2 0%,#f0ebe2 100%)}
.admin-sidebar {width:280px;background:linear-gradient(180deg,#2C2820 0%,#1a1815 100%);color:white;position:fixed;left:0;top:0;bottom:0;overflow-y:auto;transition:all 0.3s ease;z-index:100;box-shadow:2px 0 15px rgba(0,0,0,0.15)}
.admin-sidebar__header {padding:1.5rem;border-bottom:1px solid rgba(255,255,255,0.1);text-align:center}
.admin-sidebar__logo {display:flex;align-items:center;justify-content:center;gap:0.75rem;text-decoration:none}
.admin-sidebar__logo-img {width:40px;height:40px;border-radius:10px;object-fit:cover}
.admin-sidebar__logo-text {font-family:"Cormorant Garamond",serif;font-size:1.5rem;font-weight:600;color:white}
.admin-sidebar__sub {font-size:0.7rem;color:rgba(255,255,255,0.5);margin-top:0.5rem;display:block}
.admin-sidebar__nav {padding:1.5rem 0}
.admin-sidebar__link {display:flex;align-items:center;gap:1rem;padding:0.85rem 1.5rem;color:rgba(255,255,255,0.7);text-decoration:none;transition:all 0.3s;border-left:3px solid transparent;position:relative;margin:0 0.75rem 0.5rem 0;border-radius:0 12px 12px 0}
.admin-sidebar__link i {width:22px;font-size:1rem;transition:all 0.3s}
.admin-sidebar__link span {font-size:0.85rem;font-weight:500}
.admin-sidebar__link:hover {background:rgba(138,118,80,0.2);color:white;border-left-color:#8A7650;padding-left:1.8rem;transform:translateX(4px)}
.admin-sidebar__link.active {background:rgba(138,118,80,0.3);color:#8A7650;border-left-color:#8A7650;font-weight:600}
.admin-sidebar__badge {position:absolute;right:1.5rem;background:#8A7650;color:white;font-size:0.7rem;padding:0.15rem 0.5rem;border-radius:20px;font-weight:600}
.admin-sidebar__footer {position:absolute;bottom:0;left:0;right:0;padding:1.5rem;border-top:1px solid rgba(255,255,255,0.1)}
.admin-sidebar__logout {display:flex;align-items:center;gap:0.75rem;color:rgba(255,255,255,0.6);text-decoration:none;font-size:0.85rem;transition:all 0.2s}
.admin-sidebar__logout:hover {color:#ff6b6b}
.admin-main {flex:1;margin-left:280px;padding:2.5rem 3rem;min-height:100vh;background:linear-gradient(135deg,#f8f6f2 0%,#f0ebe2 100%)}
.admin-topbar {background:white;border:1px solid #E2D9C8;border-radius:20px;padding:1rem 1.5rem;margin-bottom:2rem;display:flex;justify-content:space-between;align-items:center}
.admin-topbar__title h2 {font-size:1.2rem;margin:0;color:#2C2820;animation:titleFade 0.6s ease}
.admin-topbar__title p {font-size:0.75rem;color:#6B6463;margin:0.25rem 0 0 0}
.admin-topbar__user {display:flex;align-items:center;gap:1rem}
.admin-topbar__user span {font-size:0.85rem;font-weight:500}
.admin-avatar {width:40px;height:40px;border-radius:50%;background:rgba(138,118,80,0.12);display:flex;align-items:center;justify-content:center;color:#8A7650;cursor:pointer;transition:all 0.3s ease;position:relative}
.admin-avatar i {animation:iconRotate 4s linear infinite;transition:all 0.3s ease}
.admin-avatar:hover {background:rgba(138,118,80,0.25);transform:scale(1.1)}
.admin-avatar:hover i {animation:iconRotate 2s linear infinite}
.admin-mobile-toggle {display:none;position:fixed;top:1rem;left:1rem;z-index:200;background:#8A7650;color:white;border:none;width:40px;height:40px;border-radius:10px;cursor:pointer;animation:floatBounce 3s ease-in-out infinite;box-shadow:0 4px 12px rgba(138,118,80,0.3);transition:all 0.3s ease}
.admin-mobile-toggle:hover {background:#6b5a40;box-shadow:0 6px 16px rgba(138,118,80,0.4);animation:none;transform:translateY(-3px)}
@media (max-width:768px) {.admin-sidebar {transform:translateX(-100%);transition:transform 0.3s}.admin-sidebar.open {transform:translateX(0)}.admin-main {margin-left:0;padding:1.5rem}.admin-mobile-toggle {display:flex;align-items:center;justify-content:center}}
</style>
<div class="admin-wrapper"><button class="admin-mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
<aside class="admin-sidebar" id="adminSidebar">
<div class="admin-sidebar__header"><a href="/SINTA/public/index.php?route=admin-dashboard" class="admin-sidebar__logo"><img src="/SINTA/public/assets/img/logo.png" alt="Sinta" class="admin-sidebar__logo-img"><span class="admin-sidebar__logo-text">Sinta</span></a><span class="admin-sidebar__sub">Admin Panel</span></div>
<nav class="admin-sidebar__nav">
<a href="/SINTA/public/index.php?route=admin-dashboard" class="admin-sidebar__link" data-title="Dashboard"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
<a href="/SINTA/public/index.php?route=admin-packages" class="admin-sidebar__link" data-title="Package Management"><i class="fas fa-box"></i><span>Package Management</span></a>
<a href="/SINTA/public/index.php?route=admin-occasions" class="admin-sidebar__link" data-title="Occasions Management"><i class="fas fa-calendar-alt"></i><span>Occasions</span></a>
<a href="/SINTA/public/index.php?route=admin-bookings" class="admin-sidebar__link" data-title="Booking Management"><i class="fas fa-calendar-check"></i><span>Booking Management</span></a>
<a href="/SINTA/public/index.php?route=admin-feedback" class="admin-sidebar__link" data-title="Feedback Management"><i class="fas fa-comments"></i><span>Feedback Management</span></a>
<a href="/SINTA/public/index.php?route=admin-customize" class="admin-sidebar__link" data-title="Customization Management"><i class="fas fa-palette"></i><span>Customizations</span></a>
<a href="/SINTA/public/index.php?route=admin-messages" class="admin-sidebar__link" data-title="Message Management"><i class="fas fa-envelope"></i><span>Messages</span><?php if($unread>0): ?><span class="admin-sidebar__badge"><?= $unread ?></span><?php endif; ?></a>
</nav>
<div class="admin-sidebar__footer"><a href="#" class="admin-sidebar__logout" onclick="openLogoutModal(event);"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
</aside>
<main class="admin-main"><div class="admin-topbar"><div class="admin-topbar__title"><h2><?= $page_title ?? "Dashboard" ?></h2><p>Welcome back, Admin</p></div><div class="admin-topbar__user"><span>Administrator</span><div class="admin-avatar"><i class="fas fa-user-cog"></i></div></div></div>
<script>
// Set active sidebar link based on current route
document.addEventListener('DOMContentLoaded', function() {
  const currentUrl = window.location.href;
  const sidebarLinks = document.querySelectorAll('.admin-sidebar__link');
  const pageTitle = document.querySelector('.admin-topbar__title h2');
  
  sidebarLinks.forEach(link => {
    link.classList.remove('active');
    if (link.href === currentUrl) {
      link.classList.add('active');
      if (pageTitle && link.dataset.title) {
        // Animate title change
        pageTitle.style.animation = 'none';
        setTimeout(() => {
          pageTitle.textContent = link.dataset.title;
          pageTitle.style.animation = 'titleFade 0.6s ease';
        }, 10);
      }
    }
  });
  
  // Mobile toggle functionality
  const mobileToggle = document.getElementById('mobileToggle');
  const adminSidebar = document.getElementById('adminSidebar');
  if (mobileToggle) {
    mobileToggle.addEventListener('click', function() {
      adminSidebar.classList.toggle('open');
    });
    
    // Close sidebar on link click
    sidebarLinks.forEach(link => {
      link.addEventListener('click', function() {
        adminSidebar.classList.remove('open');
      });
    });
  }
});
</script>

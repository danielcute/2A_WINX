<?php
// Navigation for user pages
?>
<nav class="app-nav" id="mainNav">
  <div class="app-nav__inner">
    <a href="/SINTA/public/index.php?route=homepage" class="app-nav__logo">
      <img src="/SINTA/public/assets/img/logo.png" alt="Sinta Logo" class="nav__logo-img" onerror="this.src='https://placehold.co/32x32/8A7650/white?text=S'">
      <span class="nav__logo-text">Sinta</span>
    </a>

    <div class="app-nav__links">
      <a href="/SINTA/public/index.php?route=homepage" class="app-nav__link <?= (isset($page) && $page === 'homepage') ? 'active' : '' ?>">
        <i class="fas fa-home"></i><span>Home</span>
      </a>
      <a href="/SINTA/public/index.php?route=plans" class="app-nav__link <?= (isset($page) && $page === 'plans') ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i><span>Plans</span>
      </a>
      <a href="/SINTA/public/index.php?route=occasions" class="app-nav__link <?= (isset($page) && $page === 'occasions') ? 'active' : '' ?>">
        <i class="fas fa-gift"></i><span>Occasions</span>
      </a>
      <a href="/SINTA/public/index.php?route=messages" class="app-nav__link <?= (isset($page) && $page === 'messages') ? 'active' : '' ?>">
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
          <div class="notif-dropdown__item unread">
            <div class="notif-dot"></div>
            <div>
              <div class="notif-title">New Promo — 15% off</div>
              <div class="notif-time">2 hours ago</div>
            </div>
          </div>
          <div class="notif-dropdown__item unread">
            <div class="notif-dot"></div>
            <div>
              <div class="notif-title">Your booking is confirmed!</div>
              <div class="notif-time">Yesterday</div>
            </div>
          </div>
          <div class="notif-dropdown__item">
            <div class="notif-dot read"></div>
            <div>
              <div class="notif-title">Payment received</div>
              <div class="notif-time">3 days ago</div>
            </div>
          </div>
        </div>
      </div>

      <div class="app-nav__profile-wrapper" style="position: relative;">
        <div class="app-nav__profile" id="profileBtn">
          <div class="app-nav__avatar">
            <img src="<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? '/SINTA/public/assets/img/default-avatar.jpg'); ?>" alt="<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>">
          </div>
          <span class="app-nav__profile-name"><?php echo htmlspecialchars(substr($_SESSION['user_name'] ?? 'User', 0, 1) . ' ' . $_SESSION['user_last_name'] ?? 'User'); ?></span>
          <i class="fas fa-chevron-down app-nav__chevron"></i>
        </div>
        <div class="profile-nav-dropdown" id="profileDropdown">
          <div class="profile-dropdown__header">
            <div class="profile-name"><?php echo htmlspecialchars(($_SESSION['user_name'] ?? 'User') . ' ' . ($_SESSION['user_last_name'] ?? '')); ?></div>
            <div class="profile-email-nav"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'email@example.com'); ?></div>
          </div>
          <a href="/SINTA/public/index.php?route=profile" class="profile-dropdown__item">
            <i class="fas fa-user"></i> Profile
          </a>
          <hr class="profile-dropdown__divider">
          <a href="/SINTA/public/index.php?route=logout" class="profile-dropdown__item profile-dropdown__item--danger">
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
      <img src="/SINTA/public/assets/img/logo.png" alt="Sinta Logo" class="mobile-menu__logo-img">
      <span class="mobile-menu__logo-text">Sinta</span>
    </div>
    <div class="mobile-menu__user">
      <img src="<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? '/SINTA/public/assets/img/default-avatar.jpg'); ?>" alt="<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>">
      <div>
        <div class="mobile-menu__name"><?php echo htmlspecialchars(($_SESSION['user_name'] ?? 'User') . ' ' . ($_SESSION['user_last_name'] ?? '')); ?></div>
        <div class="mobile-menu__email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'email@example.com'); ?></div>
      </div>
    </div>
    <div class="mobile-menu__links">
      <a href="/SINTA/public/index.php?route=homepage" class="mobile-menu__link">Home</a>
      <a href="/SINTA/public/index.php?route=plans" class="mobile-menu__link">Plans</a>
      <a href="/SINTA/public/index.php?route=occasions" class="mobile-menu__link">Occasions</a>
      <a href="/SINTA/public/index.php?route=messages" class="mobile-menu__link">Messages</a>
      <a href="/SINTA/public/index.php?route=profile" class="mobile-menu__link">Profile</a>
    </div>
    <div class="mobile-menu__footer">
      <a href="/SINTA/public/index.php?route=logout" class="mobile-menu__logout">
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
}

.notif-dropdown__item:last-child {
  border-bottom: none;
}

.notif-dropdown__item:hover {
  background: #F5F0E8;
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

.notif-title {
  font-size: 0.82rem;
  color: #2C2820;
  font-weight: 500;
}

.notif-time {
  font-size: 0.7rem;
  color: #8A8475;
  margin-top: 0.2rem;
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
(function() {
  const nav = document.getElementById('mainNav');
  const mobileBtn = document.getElementById('mobileMenuBtn');
  const mobileMenu = document.getElementById('mobileMenu');
  const profileBtn = document.getElementById('profileBtn');
  const profileDropdown = document.getElementById('profileDropdown');
  const notifBtn = document.getElementById('notifBtn');
  const notifDropdown = document.getElementById('notifDropdown');

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
})();
</script>
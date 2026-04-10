<?php 
session_start(); 
$page = 'packages';
$occasion = $_GET['occasion'] ?? 'wedding';

// Define all occasions including 'other'
$labels = [
  'wedding' => 'Wedding',
  'birthday' => 'Birthday', 
  'big-events' => 'Big Events & Gala',
  'other' => 'Other Events'
];

// Set label with fallback
$label = $labels[$occasion] ?? 'Custom Event';

// Icon mapping for each occasion
$occasionIcons = [
  'wedding' => 'fas fa-ring',
  'birthday' => 'fas fa-birthday-cake',
  'big-events' => 'fas fa-star',
  'other' => 'fas fa-plus-circle'
];

// Set current icon with fallback
$currentIcon = $occasionIcons[$occasion] ?? 'fas fa-calendar-alt';

// Determine which package section to show
$showWedding = ($occasion == 'wedding');
$showBirthday = ($occasion == 'birthday');
$showBigEvents = ($occasion == 'big-events');
$showOther = ($occasion == 'other');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($label) ?> Packages — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/global.css">
  <link rel="stylesheet" href="assets/css/nav.css">
  <style>
    .pkg-main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem 2rem 5rem;
      padding-top: calc(76px + 2rem);
    }
    
    .pkg-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .pkg-header h1 {
      margin: 0.5rem 0 0.25rem;
    }
    
    .pkg-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }
    
    .pkg-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      overflow: hidden;
      transition: all var(--t-base);
      position: relative;
    }
    
    .pkg-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary);
    }
    
    .pkg-card.featured {
      border: 1px solid var(--border);
    }
    
    .pkg-card__badge {
      position: absolute;
      top: 1rem;
      left: 1rem;
      background: var(--primary);
      color: white;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      font-size: 0.7rem;
      font-weight: 600;
      z-index: 1;
    }
    
    .pkg-card__img {
      height: 200px;
      background-size: cover;
      background-position: center;
      transition: transform 0.5s ease;
    }
    
    .pkg-card:hover .pkg-card__img {
      transform: scale(1.05);
    }
    
    .pkg-card__body {
      padding: 1.5rem;
    }
    
    .pkg-card h3 {
      font-size: 1.3rem;
      margin-bottom: 0.5rem;
    }
    
    .pkg-card__desc {
      color: var(--text-muted);
      font-size: 0.85rem;
      margin-bottom: 1rem;
      line-height: 1.5;
    }
    
    .pkg-features {
      list-style: none;
      margin: 1rem 0;
    }
    
    .pkg-features li {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 0;
      font-size: 0.8rem;
    }
    
    .pkg-features li i {
      color: var(--primary);
      font-size: 0.7rem;
    }
    
    .pkg-card__footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 1rem;
      border-top: 1px solid var(--border);
      margin-top: 1rem;
    }
    
    .pkg-price {
      font-family: var(--serif);
      font-size: 1.4rem;
      font-weight: 600;
      color: var(--primary);
    }
    
    .pkg-price small {
      font-size: 0.7rem;
      font-weight: normal;
    }
    
    .customize-cta {
      text-align: center;
      margin-top: 3rem;
      padding: 2rem;
      background: var(--bg-warm);
      border-radius: var(--radius-xl);
    }
    
    .customize-cta p {
      margin-bottom: 1rem;
      color: var(--text-muted);
    }
    
    /* Custom Dropdown - SOLID BACKGROUND */
    .custom-dropdown {
      position: relative;
      display: inline-block;
      min-width: 220px;
    }
    
    .dropdown-selected {
      padding: 0.6rem 1.2rem;
      border: 1px solid var(--border);
      border-radius: 40px;
      background: var(--white);
      font-family: var(--sans);
      font-size: 0.9rem;
      color: var(--dark);
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 10px;
      justify-content: space-between;
    }
    
    .dropdown-selected:hover {
      border-color: var(--primary);
    }
    
    .dropdown-selected .selected-content {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .dropdown-selected .selected-content i {
      width: 20px;
      color: var(--primary);
      font-size: 1rem;
    }
    
    .dropdown-arrow {
      transition: transform 0.2s ease;
      color: var(--text-muted);
    }
    
    .custom-dropdown.open .dropdown-arrow {
      transform: rotate(180deg);
    }
    
    .dropdown-options {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 20px;
      margin-top: 8px;
      box-shadow: var(--shadow-lg);
      z-index: 1000;
      display: none;
      overflow: hidden;
      background-color: #ffffff;
      backdrop-filter: none;
      opacity: 1;
    }
    
    .custom-dropdown.open .dropdown-options {
      display: block;
      animation: dropdownFadeIn 0.2s ease;
    }
    
    @keyframes dropdownFadeIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .dropdown-option {
      padding: 0.6rem 1.2rem;
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      transition: background 0.2s ease;
      font-size: 0.9rem;
      background-color: #ffffff;
      color: var(--dark);
      backdrop-filter: none;
    }
    
    .dropdown-option:hover {
      background-color: rgba(212, 175, 55, 0.1);
    }
    
    .dropdown-option i {
      width: 20px;
      color: var(--primary);
      font-size: 1rem;
    }
    
    .dropdown-option.selected {
      background-color: rgba(212, 175, 55, 0.15);
      font-weight: 500;
    }
    
    .occasion-selector {
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }
    
    .occasion-selector label {
      font-weight: 500;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    /* Other Events specific styles */
    .other-events-content {
      text-align: center;
      padding: 3rem;
      background: var(--bg-card);
      border-radius: var(--radius-xl);
      border: 1px solid var(--border);
    }
    
    .other-events-icon {
      font-size: 4rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }
    
    .other-events-content h2 {
      margin-bottom: 1rem;
    }
    
    .other-events-content p {
      color: var(--text-muted);
      margin-bottom: 2rem;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
    }
    
    @media (max-width: 900px) {
      .pkg-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    @media (max-width: 600px) {
      .pkg-grid {
        grid-template-columns: 1fr;
      }
      .pkg-main {
        padding: 1.5rem 1rem 4rem;
        padding-top: calc(76px + 1rem);
      }
      .custom-dropdown {
        min-width: 180px;
      }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../partials/nav.php'; ?>

<main class="pkg-main">
  <div class="breadcrumb">
    <a href="occasions.php">Occasions</a> <span>/</span>
    <span><?= htmlspecialchars($label) ?></span>
  </div>
  
  <div class="occasion-selector">
    <label><i class="fas fa-calendar-alt fa-fw"></i> Select Occasion:</label>
    
    <!-- Custom Dropdown with Animated Icons -->
    <div class="custom-dropdown" id="occasionDropdown">
      <div class="dropdown-selected" onclick="toggleDropdown()">
        <div class="selected-content">
          <i class="<?= $currentIcon ?> fa-fw"></i>
          <span><?= htmlspecialchars($label) ?></span>
        </div>
        <i class="fas fa-chevron-down dropdown-arrow"></i>
      </div>
      <div class="dropdown-options">
        <div class="dropdown-option <?= $occasion == 'wedding' ? 'selected' : '' ?>" data-value="wedding" onclick="selectOccasion('wedding')">
          <i class="fas fa-ring fa-fw"></i>
          <span>Wedding</span>
        </div>
        <div class="dropdown-option <?= $occasion == 'birthday' ? 'selected' : '' ?>" data-value="birthday" onclick="selectOccasion('birthday')">
          <i class="fas fa-birthday-cake fa-fw"></i>
          <span>Birthday</span>
        </div>
        <div class="dropdown-option <?= $occasion == 'big-events' ? 'selected' : '' ?>" data-value="big-events" onclick="selectOccasion('big-events')">
          <i class="fas fa-star fa-fw"></i>
          <span>Big Events / Gala</span>
        </div>
        <div class="dropdown-option <?= $occasion == 'other' ? 'selected' : '' ?>" data-value="other" onclick="selectOccasion('other')">
          <i class="fas fa-plus-circle fa-fw"></i>
          <span>Other Events</span>
        </div>
      </div>
    </div>
  </div>
  
  <div class="pkg-header">
    <div>
      <div class="eyebrow"><span class="rule"></span> Step 2 of 3</div>
      <h1><?= htmlspecialchars($label) ?> <em>Packages</em></h1>
      <p>Choose a package or customize your own event</p>
    </div>
  </div>
  
  <!-- WEDDING PACKAGES -->
  <div id="wedding-packages" class="occasion-packages" style="display: <?= $showWedding ? 'grid' : 'none' ?>; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
    <div class="pkg-card featured">
      <div class="pkg-card__badge">Most Popular</div>
      <div class="pkg-card__img" style="background-image: url('assets/img/classic.jpg')"></div>
      <div class="pkg-card__body">
        <h3>Classic Wedding</h3>
        <p class="pkg-card__desc">Perfect for those who want a beautifully organized event without complexity.</p>
        <ul class="pkg-features">
          <li><i class="fas fa-check-circle"></i> Full Event Coordination</li>
          <li><i class="fas fa-check-circle"></i> Venue Setup & Styling</li>
          <li><i class="fas fa-check-circle"></i> Catering (100 pax)</li>
          <li><i class="fas fa-check-circle"></i> Photography (6 hours)</li>
          <li><i class="fas fa-check-circle"></i> Professional Host/Emcee</li>
        </ul>
        <div class="pkg-card__footer">
          <div class="pkg-price">₱150,000 <small>+ VAT</small></div>
          <button class="btn btn--primary" onclick="addToCart('Classic Wedding', 150000)">Select Package</button>
        </div>
      </div>
    </div>
    
    <div class="pkg-card">
      <div class="pkg-card__img" style="background-image: url('assets/img/elegant.jpg')"></div>
      <div class="pkg-card__body">
        <h3>Elegant Wedding</h3>
        <p class="pkg-card__desc">Elevated experience with premium vendors and extended services.</p>
        <ul class="pkg-features">
          <li><i class="fas fa-check-circle"></i> Full Event Coordination</li>
          <li><i class="fas fa-check-circle"></i> Premium Venue Setup</li>
          <li><i class="fas fa-check-circle"></i> Catering (150 pax)</li>
          <li><i class="fas fa-check-circle"></i> Photography + Video (8 hours)</li>
          <li><i class="fas fa-check-circle"></i> Professional Host + Assistant</li>
          <li><i class="fas fa-check-circle"></i> Premium Floral Design</li>
        </ul>
        <div class="pkg-card__footer">
          <div class="pkg-price">₱250,000 <small>+ VAT</small></div>
          <button class="btn btn--primary" onclick="addToCart('Elegant Wedding', 250000)">Select Package</button>
        </div>
      </div>
    </div>
    
    <div class="pkg-card">
      <div class="pkg-card__img" style="background-image: url('assets/img/premiumevent.jpg')"></div>
      <div class="pkg-card__body">
        <h3>Premium Wedding</h3>
        <p class="pkg-card__desc">The ultimate luxury experience with bespoke services.</p>
        <ul class="pkg-features">
          <li><i class="fas fa-check-circle"></i> Dedicated Event Director</li>
          <li><i class="fas fa-check-circle"></i> Luxury Venue Setup</li>
          <li><i class="fas fa-check-circle"></i> Fine Dining (200 pax)</li>
          <li><i class="fas fa-check-circle"></i> Cinematic Video + SDE</li>
          <li><i class="fas fa-check-circle"></i> Celebrity Host/Emcee</li>
          <li><i class="fas fa-check-circle"></i> Luxury Floral Arch</li>
        </ul>
        <div class="pkg-card__footer">
          <div class="pkg-price">₱450,000 <small>+ VAT</small></div>
          <button class="btn btn--primary" onclick="addToCart('Premium Wedding', 450000)">Select Package</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- BIRTHDAY PACKAGES -->
  <div id="birthday-packages" class="occasion-packages" style="display: <?= $showBirthday ? 'grid' : 'none' ?>; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
    <div class="pkg-card featured">
      <div class="pkg-card__badge">Most Popular</div>
      <div class="pkg-card__img" style="background-image: url('assets/img/classicbday.jpg')"></div>
      <div class="pkg-card__body">
        <h3>Classic Birthday</h3>
        <p class="pkg-card__desc">Fun and festive celebration with all the essentials.</p>
        <ul class="pkg-features">
          <li><i class="fas fa-check-circle"></i> Party Coordination</li>
          <li><i class="fas fa-check-circle"></i> Themed Decor & Balloons</li>
          <li><i class="fas fa-check-circle"></i> Catering (50 pax)</li>
          <li><i class="fas fa-check-circle"></i> Photography (4 hours)</li>
          <li><i class="fas fa-check-circle"></i> Birthday Cake (2-tier)</li>
          <li><i class="fas fa-check-circle"></i> Party Host/Game Master</li>
        </ul>
        <div class="pkg-card__footer">
          <div class="pkg-price">₱45,000 <small>+ VAT</small></div>
          <button class="btn btn--primary" onclick="addToCart('Classic Birthday', 45000)">Select Package</button>
        </div>
      </div>
    </div>
    
    <div class="pkg-card">
      <div class="pkg-card__img" style="background-image: url('assets/img/deluxbday.jpg')"></div>
      <div class="pkg-card__body">
        <h3>Deluxe Birthday</h3>
        <p class="pkg-card__desc">Extra special celebration with premium entertainment.</p>
        <ul class="pkg-features">
          <li><i class="fas fa-check-circle"></i> Full Party Coordination</li>
          <li><i class="fas fa-check-circle"></i> Premium Themed Decor</li>
          <li><i class="fas fa-check-circle"></i> Catering (100 pax)</li>
          <li><i class="fas fa-check-circle"></i> Photography + Video (6 hours)</li>
          <li><i class="fas fa-check-circle"></i> Custom 3-Tier Cake</li>
          <li><i class="fas fa-check-circle"></i> Live Band (3 pieces)</li>
          <li><i class="fas fa-check-circle"></i> Photo Booth + Giveaways</li>
        </ul>
        <div class="pkg-card__footer">
          <div class="pkg-price">₱85,000 <small>+ VAT</small></div>
          <button class="btn btn--primary" onclick="addToCart('Deluxe Birthday', 85000)">Select Package</button>
        </div>
      </div>
    </div>
    
    <div class="pkg-card">
      <div class="pkg-card__img" style="background-image: url('assets/img/ultimatebday.jpg')"></div>
      <div class="pkg-card__body">
        <h3>Ultimate Birthday Bash</h3>
        <p class="pkg-card__desc">Over-the-top celebration with everything you can imagine.</p>
        <ul class="pkg-features">
          <li><i class="fas fa-check-circle"></i> Dedicated Event Manager</li>
          <li><i class="fas fa-check-circle"></i> Luxury Themed Transformation</li>
          <li><i class="fas fa-check-circle"></i> Catering (150 pax)</li>
          <li><i class="fas fa-check-circle"></i> Cinematic Coverage + SDE</li>
          <li><i class="fas fa-check-circle"></i> Designer Cake + Dessert Buffet</li>
          <li><i class="fas fa-check-circle"></i> Celebrity Guest / Performer</li>
          <li><i class="fas fa-check-circle"></i> Fireworks Display</li>
        </ul>
        <div class="pkg-card__footer">
          <div class="pkg-price">₱180,000 <small>+ VAT</small></div>
          <button class="btn btn--primary" onclick="addToCart('Ultimate Birthday Bash', 180000)">Select Package</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- BIG EVENTS / GALA PACKAGES -->
  <div id="bigevents-packages" class="occasion-packages" style="display: <?= $showBigEvents ? 'grid' : 'none' ?>; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
    <div class="pkg-card featured">
      <div class="pkg-card__badge">Best Value</div>
      <div class="pkg-card__img" style="background-image: url('assets/img/corpgala.jpg')"></div>
      <div class="pkg-card__body">
        <h3>Corporate Gala</h3>
        <p class="pkg-card__desc">Professional and elegant event for company celebrations.</p>
        <ul class="pkg-features">
          <li><i class="fas fa-check-circle"></i> Event Planning & Logistics</li>
          <li><i class="fas fa-check-circle"></i> Venue Selection & Setup</li>
          <li><i class="fas fa-check-circle"></i> Catering (200 pax)</li>
          <li><i class="fas fa-check-circle"></i> Professional Photography</li>
          <li><i class="fas fa-check-circle"></i> AV Equipment & Lighting</li>
          <li><i class="fas fa-check-circle"></i> Professional Emcee</li>
        </ul>
        <div class="pkg-card__footer">
          <div class="pkg-price">₱250,000 <small>+ VAT</small></div>
          <button class="btn btn--primary" onclick="addToCart('Corporate Gala', 250000)">Select Package</button>
        </div>
      </div>
    </div>
    
    <div class="pkg-card">
      <div class="pkg-card__img" style="background-image: url('assets/img/premgala.jpg')"></div>
      <div class="pkg-card__body">
        <h3>Premium Gala Experience</h3>
        <p class="pkg-card__desc">High-end corporate celebration with VIP treatment.</p>
        <ul class="pkg-features">
          <li><i class="fas fa-check-circle"></i> Full Production Management</li>
          <li><i class="fas fa-check-circle"></i> Luxury Venue & Stage Design</li>
          <li><i class="fas fa-check-circle"></i> Fine Dining (300 pax)</li>
          <li><i class="fas fa-check-circle"></i> Photo + Video Team</li>
          <li><i class="fas fa-check-circle"></i> LED Wall & Sound System</li>
          <li><i class="fas fa-check-circle"></i> Celebrity Host / Speaker</li>
          <li><i class="fas fa-check-circle"></i> Red Carpet Arrival</li>
        </ul>
        <div class="pkg-card__footer">
          <div class="pkg-price">₱500,000 <small>+ VAT</small></div>
          <button class="btn btn--primary" onclick="addToCart('Premium Gala Experience', 500000)">Select Package</button>
        </div>
      </div>
    </div>
    
    <div class="pkg-card">
      <div class="pkg-card__img" style="background-image: url('assets/img/luxgala.jpg')"></div>
      <div class="pkg-card__body">
        <h3>Luxury Gala & Awards</h3>
        <p class="pkg-card__desc">The pinnacle of corporate events with international standards.</p>
        <ul class="pkg-features">
          <li><i class="fas fa-check-circle"></i> Global Standard Production</li>
          <li><i class="fas fa-check-circle"></i> Bespoke Venue Transformation</li>
          <li><i class="fas fa-check-circle"></i> 5-Course Plated Dinner (500 pax)</li>
          <li><i class="fas fa-check-circle"></i> Multi-Cam Live Production</li>
          <li><i class="fas fa-check-circle"></i> 360° Photo + Video Booth</li>
          <li><i class="fas fa-check-circle"></i> International Artist / Performer</li>
          <li><i class="fas fa-check-circle"></i> Drone Light Show / Fireworks</li>
          <li><i class="fas fa-check-circle"></i> Full Security & VIP Coordination</li>
        </ul>
        <div class="pkg-card__footer">
          <div class="pkg-price">₱1,200,000 <small>+ VAT</small></div>
          <button class="btn btn--primary" onclick="addToCart('Luxury Gala & Awards', 1200000)">Select Package</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- OTHER EVENTS SECTION -->
  <div id="other-packages" class="occasion-packages" style="display: <?= $showOther ? 'block' : 'none' ?>;">
    <div class="other-events-content">
      <div class="other-events-icon">
        <i class="fas fa-plus-circle"></i>
      </div>
      <h2>Custom Event Planning</h2>
      <p>For christenings, reunions, graduations, or any special celebration you have in mind, we'll create a package tailored just for you.</p>
      <a href="customize.php?occasion=other" class="btn btn--primary btn--lg">
        <i class="fas fa-magic"></i> Create Custom Package
      </a>
    </div>
  </div>
  
  <!-- Customize CTA Button (shown for non-other occasions) -->
  <?php if (!$showOther): ?>
  <div class="customize-cta animate-fade-up delay-2">
    <div class="eyebrow"><span class="rule"></span> Want Something Unique?</div>
    <h3 style="margin: 0.5rem 0;">Build Your <em>Own Package</em></h3>
    <p>Can't find what you're looking for? Create a completely customized event tailored to your vision.</p>
    <a href="customize.php?occasion=<?= urlencode($occasion) ?>" class="btn btn--primary btn--lg">
      <i class="fas fa-magic"></i> Start Customizing
    </a>
  </div>
  <?php endif; ?>
</main>

<script>
let cart = [];

function addToCart(packageName, price) {
  cart.push({ name: packageName, price: price, type: 'package' });
  alert('✓ ' + packageName + ' added to cart!\nTotal: ₱' + price.toLocaleString());
  
  sessionStorage.setItem('checkoutCart', JSON.stringify(cart));
  if (confirm('Proceed to checkout?')) {
    window.location.href = 'checkout.php';
  }
}

function toggleDropdown() {
  const dropdown = document.getElementById('occasionDropdown');
  dropdown.classList.toggle('open');
}

function selectOccasion(occasion) {
  window.location.href = 'packages.php?occasion=' + occasion;
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
  const dropdown = document.getElementById('occasionDropdown');
  if (dropdown && !dropdown.contains(event.target)) {
    dropdown.classList.remove('open');
  }
});

// Navbar scroll effect
window.addEventListener('scroll', function() {
  const nav = document.querySelector('.app-nav');
  if (nav) {
    nav.classList.toggle('scrolled', window.scrollY > 20);
  }
});

// Mobile menu toggle
const mobileBtn = document.getElementById('mobileMenuBtn');
const mobileMenu = document.getElementById('mobileMenu');
if (mobileBtn && mobileMenu) {
  mobileBtn.addEventListener('click', function() {
    mobileBtn.classList.toggle('active');
    mobileMenu.classList.toggle('active');
    document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
  });
}

// Close mobile menu on link click
document.querySelectorAll('.mobile-menu__link').forEach(function(link) {
  link.addEventListener('click', function() {
    if (mobileMenu) mobileMenu.classList.remove('active');
    if (mobileBtn) mobileBtn.classList.remove('active');
    document.body.style.overflow = '';
  });
});

// Profile dropdown
const profileBtn = document.getElementById('profileBtn');
const profileDropdown = document.getElementById('profileDropdown');
if (profileBtn && profileDropdown) {
  profileBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    profileDropdown.classList.toggle('active');
    const notifDropdown = document.getElementById('notifDropdown');
    if (notifDropdown) notifDropdown.classList.remove('active');
  });
}

// Notifications dropdown
const notifBtn = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');
if (notifBtn && notifDropdown) {
  notifBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    notifDropdown.classList.toggle('active');
    if (profileDropdown) profileDropdown.classList.remove('active');
  });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function() {
  if (profileDropdown) profileDropdown.classList.remove('active');
  if (notifDropdown) notifDropdown.classList.remove('active');
});
</script>
</body>
</html>
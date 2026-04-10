<?php 
session_start(); 
$page = 'customize';
$occasion = $_GET['occasion'] ?? 'wedding';
$occasionLabel = ucfirst($occasion);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customize Your Event — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* ========================================
       SINTA - COMPLETE CUSTOMIZE STYLES
       ======================================== */
    :root {
      --primary: #8A7650;
      --primary-dark: #6B5A3E;
      --primary-light: #A6956F;
      --primary-pale: rgba(138, 118, 80, 0.12);
      --secondary: #8E977D;
      --white: #FFFFFF;
      --cream: #F5F0E8;
      --dark: #2C2820;
      --charcoal: #3D3835;
      --gray: #6B6463;
      --gray-light: #A8A09B;
      --border: #E2D9C8;
      --border-2: #D4CAB5;
      --serif: 'Cormorant Garamond', Georgia, serif;
      --sans: 'Inter', -apple-system, sans-serif;
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 20px;
      --radius-xl: 28px;
      --radius-2xl: 36px;
      --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
      --shadow-md: 0 8px 24px rgba(0,0,0,0.08);
      --shadow-lg: 0 20px 40px rgba(0,0,0,0.12);
      --t-fast: 0.2s ease;
      --t-base: 0.3s ease;
      --nav-height: 76px;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: var(--sans);
      background: var(--cream);
      color: var(--dark);
      line-height: 1.5;
      font-size: 1rem;
    }

    h1, h2, h3, h4 {
      font-family: var(--serif);
      font-weight: 500;
      letter-spacing: -0.02em;
    }
    h1 { font-size: 2.8rem; }
    h2 { font-size: 2.2rem; }
    h3 { font-size: 1.5rem; }
    h4 { font-size: 1.2rem; }
    p { font-size: 1rem; line-height: 1.6; }

    /* Breadcrumb & Eyebrow */
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.85rem;
      color: var(--gray);
      margin-bottom: 1.5rem;
    }
    .breadcrumb a { color: var(--primary); text-decoration: none; }
    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--primary);
    }
    .rule { width: 2rem; height: 1px; background: var(--primary); display: inline-block; }

    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
      padding: 0.8rem 1.6rem;
      font-family: var(--sans);
      font-size: 0.85rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      text-decoration: none;
      border-radius: 999px;
      transition: all var(--t-base);
      cursor: pointer;
      border: none;
    }
    .btn--sm { padding: 0.55rem 1.2rem; font-size: 0.75rem; }
    .btn--lg { padding: 1rem 2.2rem; font-size: 0.9rem; }
    .btn--primary { background: var(--primary); color: white; }
    .btn--primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .btn--ghost { background: transparent; color: var(--gray); border: 1.5px solid var(--border); }
    .btn--ghost:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-2px); }

    /* Customize Main */
    .customize-main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem 2rem 5rem;
      padding-top: calc(var(--nav-height) + 2rem);
    }
    .customize-header { margin-bottom: 2.5rem; animation: fadeUp 0.5s ease both; }
    .customize-header h1 { margin: 0.5rem 0 0.75rem; }
    .customize-header h1 em { color: var(--primary); font-style: italic; }
    .customize-header__sub { color: var(--gray); font-size: 1rem; max-width: 600px; }

    /* Section Headers */
    .section-header {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1.25rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid var(--border);
    }
    .section-header i { color: var(--primary); font-size: 1.3rem; }
    .section-header h3 { font-size: 1.3rem; margin: 0; }

    /* Photo Card Grid */
    .photo-card-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.25rem;
      margin-bottom: 2rem;
    }
    .photo-card {
      background: var(--white);
      border: 2px solid var(--border);
      border-radius: var(--radius-xl);
      overflow: hidden;
      cursor: pointer;
      transition: all var(--t-base);
    }
    .photo-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); }
    .photo-card.selected { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-pale); }
    .photo-card__image {
      width: 100%;
      height: 160px;
      background-size: cover;
      background-position: center;
      transition: transform 0.5s ease;
    }
    .photo-card:hover .photo-card__image { transform: scale(1.05); }
    .photo-card__content { padding: 1rem; text-align: center; }
    .photo-card__icon {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: var(--primary-pale);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      margin: -1.5rem auto 0.5rem auto;
      position: relative;
      z-index: 1;
      border: 3px solid var(--white);
    }
    .photo-card__title { font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem; color: var(--dark); }
    .photo-card__desc { font-size: 0.75rem; color: var(--gray); margin-bottom: 0.5rem; }
    .photo-card__price { font-weight: 700; color: var(--primary); font-size: 0.85rem; }

    /* Addons Grid */
    .addons-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-bottom: 2rem;
    }
    .addon-card {
      background: var(--white);
      border: 2px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 1rem;
      cursor: pointer;
      transition: all var(--t-fast);
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .addon-card:hover { border-color: var(--primary); background: var(--primary-pale); transform: translateY(-3px); }
    .addon-card.selected { border-color: var(--primary); background: var(--primary-pale); }
    .addon-card input { width: 20px; height: 20px; accent-color: var(--primary); cursor: pointer; }
    .addon-card__icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--cream);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      color: var(--primary);
    }
    .addon-card__info { flex: 1; }
    .addon-card__name { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.2rem; }
    .addon-card__price { font-size: 0.75rem; color: var(--primary); font-weight: 600; }

    /* Summary Footer */
    .customize-footer {
      position: sticky;
      bottom: 0;
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 1.25rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 2rem;
      box-shadow: var(--shadow-lg);
      z-index: 10;
    }
    .summary-details { display: flex; gap: 2rem; flex-wrap: wrap; }
    .summary-item { display: flex; flex-direction: column; }
    .summary-item__label { font-size: 0.7rem; text-transform: uppercase; color: var(--gray); letter-spacing: 0.1em; }
    .summary-item__value { font-family: var(--serif); font-size: 1.4rem; font-weight: 600; color: var(--primary); }
    .summary-actions { display: flex; gap: 1rem; }

    /* Toast */
    .toast {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: var(--dark);
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 999px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      z-index: 2000;
      animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(100px); }
      to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 968px) {
      .photo-card-grid { grid-template-columns: repeat(2, 1fr); }
      .addons-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .customize-main { padding: 1.5rem 1rem 4rem; padding-top: calc(var(--nav-height) + 1rem); }
      .photo-card-grid { grid-template-columns: 1fr; }
      .addons-grid { grid-template-columns: 1fr; }
      .customize-footer { flex-direction: column; text-align: center; }
      .summary-details { justify-content: center; }
    }
  </style>
</head>
<body>

<!-- Include external navigation -->
<?php include __DIR__ . '/../partials/nav.php'; ?>

<main class="customize-main">
  <div class="breadcrumb">
    <a href="occasions.php">Occasions</a> <span>/</span>
    <a href="packages.php?occasion=<?= urlencode($occasion) ?>">Packages</a> <span>/</span>
    <span>Customize</span>
  </div>
  
  <div class="customize-header">
    <div class="eyebrow"><span class="rule"></span> Build Your Dream Event</div>
    <h1>Customize your <em><?= htmlspecialchars($occasionLabel) ?></em></h1>
    <p class="customize-header__sub">Select from our curated options below to create your perfect celebration</p>
  </div>

  <!-- ===== THEME SELECTION ===== -->
  <div class="section-header">
    <i class="fas fa-palette"></i>
    <h3>1. Choose a Theme</h3>
  </div>
  <div class="photo-card-grid" id="themeGrid">
    <div class="photo-card" data-theme="garden" data-price="25000" data-name="Garden Romance" onclick="selectTheme(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/garden.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-leaf"></i></div>
        <div class="photo-card__title">Garden Romance</div>
        <div class="photo-card__desc">Whimsical floral arrangements, fairy lights, natural elegance</div>
        <div class="photo-card__price">+₱25,000</div>
      </div>
    </div>
    <div class="photo-card" data-theme="rustic" data-price="20000" data-name="Rustic Charm" onclick="selectTheme(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/rustic.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-tree"></i></div>
        <div class="photo-card__title">Rustic Charm</div>
        <div class="photo-card__desc">Woodland vibes, mason jars, burlap accents, warm tones</div>
        <div class="photo-card__price">+₱20,000</div>
      </div>
    </div>
    <div class="photo-card" data-theme="modern" data-price="35000" data-name="Modern Elegance" onclick="selectTheme(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/modern.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-gem"></i></div>
        <div class="photo-card__title">Modern Elegance</div>
        <div class="photo-card__desc">Sleek lines, minimalist decor, contemporary chic</div>
        <div class="photo-card__price">+₱35,000</div>
      </div>
    </div>
    <div class="photo-card" data-theme="tropical" data-price="30000" data-name="Tropical Paradise" onclick="selectTheme(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/tropical.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-umbrella-beach"></i></div>
        <div class="photo-card__title">Tropical Paradise</div>
        <div class="photo-card__desc">Vibrant colors, exotic flowers, tiki torches, island vibes</div>
        <div class="photo-card__price">+₱30,000</div>
      </div>
    </div>
  </div>

  <!-- ===== VENUE SELECTION ===== -->
  <div class="section-header">
    <i class="fas fa-location-dot"></i>
    <h3>2. Select Your Venue</h3>
  </div>
  <div class="photo-card-grid" id="venueGrid">
    <div class="photo-card" data-venue="garden" data-price="50000" data-name="Garden Venue" onclick="selectVenue(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/gardenvenue.png')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-tree"></i></div>
        <div class="photo-card__title">Garden Venue</div>
        <div class="photo-card__desc">Beautiful outdoor setting, up to 150 guests</div>
        <div class="photo-card__price">+₱50,000</div>
      </div>
    </div>
    <div class="photo-card" data-venue="ballroom" data-price="80000" data-name="Hotel Ballroom" onclick="selectVenue(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/hotel.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-building"></i></div>
        <div class="photo-card__title">Hotel Ballroom</div>
        <div class="photo-card__desc">Elegant indoor venue, up to 300 guests</div>
        <div class="photo-card__price">+₱80,000</div>
      </div>
    </div>
    <div class="photo-card" data-venue="beach" data-price="120000" data-name="Beach Resort" onclick="selectVenue(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/beach.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-umbrella-beach"></i></div>
        <div class="photo-card__title">Beach Resort</div>
        <div class="photo-card__desc">Oceanfront paradise, up to 200 guests</div>
        <div class="photo-card__price">+₱120,000</div>
      </div>
    </div>
    <div class="photo-card" data-venue="estate" data-price="150000" data-name="Private Estate" onclick="selectVenue(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/private.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-home"></i></div>
        <div class="photo-card__title">Private Estate</div>
        <div class="photo-card__desc">Luxury mansion setting, up to 400 guests</div>
        <div class="photo-card__price">+₱150,000</div>
      </div>
    </div>
  </div>

  <!-- ===== CATERING SELECTION ===== -->
  <div class="section-header">
    <i class="fas fa-utensils"></i>
    <h3>3. Choose Your Catering</h3>
  </div>
  <div class="photo-card-grid" id="cateringGrid">
    <div class="photo-card" data-catering="buffet" data-price="45000" data-name="Premium Buffet" onclick="selectCatering(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/premium buffet.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-utensil-spoon"></i></div>
        <div class="photo-card__title">Premium Buffet</div>
        <div class="photo-card__desc">Wide selection of dishes, live stations, 100 pax</div>
        <div class="photo-card__price">+₱45,000</div>
      </div>
    </div>
    <div class="photo-card" data-catering="plated" data-price="75000" data-name="Plated Dinner" onclick="selectCatering(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/plated.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-plate-wheat"></i></div>
        <div class="photo-card__title">Plated Dinner</div>
        <div class="photo-card__desc">Elegant 5-course meal, 120 pax</div>
        <div class="photo-card__price">+₱75,000</div>
      </div>
    </div>
    <div class="photo-card" data-catering="foodtruck" data-price="55000" data-name="Food Truck Fiesta" onclick="selectCatering(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/foodtruck.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-truck"></i></div>
        <div class="photo-card__title">Food Truck Fiesta</div>
        <div class="photo-card__desc">Casual dining with gourmet food trucks, 100 pax</div>
        <div class="photo-card__price">+₱55,000</div>
      </div>
    </div>
    <div class="photo-card" data-catering="seafood" data-price="95000" data-name="Seafood Extravaganza" onclick="selectCatering(this)">
      <div class="photo-card__image" style="background-image: url('assets/img/seafood.jpg')"></div>
      <div class="photo-card__content">
        <div class="photo-card__icon"><i class="fas fa-fish"></i></div>
        <div class="photo-card__title">Seafood Extravaganza</div>
        <div class="photo-card__desc">Fresh seafood buffet, oyster bar, 100 pax</div>
        <div class="photo-card__price">+₱95,000</div>
      </div>
    </div>
  </div>

  <!-- ===== ADD-ONS ===== -->
  <div class="section-header">
    <i class="fas fa-plus-circle"></i>
    <h3>4. Add Extras (Optional)</h3>
  </div>
  <div class="addons-grid" id="addonsGrid">
    <div class="addon-card" data-addon="photography" data-price="30000" data-name="Premium Photography" onclick="toggleAddon(this)">
      <input type="checkbox" onclick="event.stopPropagation()">
      <div class="addon-card__icon"><i class="fas fa-camera"></i></div>
      <div class="addon-card__info">
        <div class="addon-card__name">Premium Photography</div>
        <div class="addon-card__price">+₱30,000</div>
      </div>
    </div>
    <div class="addon-card" data-addon="videography" data-price="40000" data-name="Cinematic Videography" onclick="toggleAddon(this)">
      <input type="checkbox" onclick="event.stopPropagation()">
      <div class="addon-card__icon"><i class="fas fa-video"></i></div>
      <div class="addon-card__info">
        <div class="addon-card__name">Cinematic Videography</div>
        <div class="addon-card__price">+₱40,000</div>
      </div>
    </div>
    <div class="addon-card" data-addon="flowers" data-price="18000" data-name="Floral Arrangements" onclick="toggleAddon(this)">
      <input type="checkbox" onclick="event.stopPropagation()">
      <div class="addon-card__icon"><i class="fas fa-feather-alt"></i></div>
      <div class="addon-card__info">
        <div class="addon-card__name">Floral Arrangements</div>
        <div class="addon-card__price">+₱18,000</div>
      </div>
    </div>
    <div class="addon-card" data-addon="entertainment" data-price="50000" data-name="Live Band / DJ" onclick="toggleAddon(this)">
      <input type="checkbox" onclick="event.stopPropagation()">
      <div class="addon-card__icon"><i class="fas fa-music"></i></div>
      <div class="addon-card__info">
        <div class="addon-card__name">Live Band / DJ</div>
        <div class="addon-card__price">+₱50,000</div>
      </div>
    </div>
    <div class="addon-card" data-addon="cake" data-price="8000" data-name="Designer Cake" onclick="toggleAddon(this)">
      <input type="checkbox" onclick="event.stopPropagation()">
      <div class="addon-card__icon"><i class="fas fa-cake-candles"></i></div>
      <div class="addon-card__info">
        <div class="addon-card__name">Designer Cake</div>
        <div class="addon-card__price">+₱8,000</div>
      </div>
    </div>
    <div class="addon-card" data-addon="lights" data-price="25000" data-name="Lighting & Sound" onclick="toggleAddon(this)">
      <input type="checkbox" onclick="event.stopPropagation()">
      <div class="addon-card__icon"><i class="fas fa-lightbulb"></i></div>
      <div class="addon-card__info">
        <div class="addon-card__name">Lighting & Sound</div>
        <div class="addon-card__price">+₱25,000</div>
      </div>
    </div>
    <div class="addon-card" data-addon="photoBooth" data-price="15000" data-name="Photo Booth" onclick="toggleAddon(this)">
      <input type="checkbox" onclick="event.stopPropagation()">
      <div class="addon-card__icon"><i class="fas fa-camera-retro"></i></div>
      <div class="addon-card__info">
        <div class="addon-card__name">Photo Booth</div>
        <div class="addon-card__price">+₱15,000</div>
      </div>
    </div>
    <div class="addon-card" data-addon="coordinator" data-price="20000" data-name="Day-of Coordinator" onclick="toggleAddon(this)">
      <input type="checkbox" onclick="event.stopPropagation()">
      <div class="addon-card__icon"><i class="fas fa-clipboard-list"></i></div>
      <div class="addon-card__info">
        <div class="addon-card__name">Day-of Coordinator</div>
        <div class="addon-card__price">+₱20,000</div>
      </div>
    </div>
  </div>

  <!-- Summary Footer -->
  <div class="customize-footer">
    <div class="summary-details">
      <div class="summary-item">
        <span class="summary-item__label">Total Package</span>
        <span class="summary-item__value" id="totalPrice">₱0</span>
      </div>
      <div class="summary-item">
        <span class="summary-item__label">Selected Items</span>
        <span class="summary-item__value" id="selectedCount">0</span>
      </div>
    </div>
    <div class="summary-actions">
      <button class="btn btn--ghost" onclick="resetSelection()">Reset All</button>
      <button class="btn btn--primary" onclick="proceedToCheckout()">Proceed to Checkout <i class="fas fa-arrow-right"></i></button>
    </div>
  </div>
</main>

<script>
  // Selection state
  let selectedTheme = null;
  let selectedVenue = null;
  let selectedCatering = null;
  let selectedAddons = new Set();

  // Helper function to show toast message
  function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }

  // Generic selection handler
  function handleSelection(type, element, dataAttr) {
    const cards = document.querySelectorAll(`[data-${dataAttr}]`);
    cards.forEach(card => card.classList.remove('selected'));
    element.classList.add('selected');
    return element.getAttribute(`data-${dataAttr}`);
  }

  // Theme selection
  function selectTheme(element) {
    selectedTheme = handleSelection('theme', element, 'theme');
    showToast(`Theme selected: ${element.querySelector('.photo-card__title').innerText}`);
    updateSummary();
  }

  // Venue selection
  function selectVenue(element) {
    selectedVenue = handleSelection('venue', element, 'venue');
    showToast(`Venue selected: ${element.querySelector('.photo-card__title').innerText}`);
    updateSummary();
  }

  // Catering selection
  function selectCatering(element) {
    selectedCatering = handleSelection('catering', element, 'catering');
    showToast(`Catering selected: ${element.querySelector('.photo-card__title').innerText}`);
    updateSummary();
  }

  // Toggle add-on
  function toggleAddon(element) {
    const checkbox = element.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
    const addonId = element.getAttribute('data-addon');
    const addonName = element.querySelector('.addon-card__name').innerText;
    
    if (checkbox.checked) {
      selectedAddons.add(addonId);
      element.classList.add('selected');
      showToast(`Added: ${addonName}`);
    } else {
      selectedAddons.delete(addonId);
      element.classList.remove('selected');
      showToast(`Removed: ${addonName}`);
    }
    updateSummary();
  }

  // Calculate total price
  function calculateTotal() {
    let total = 0;
    
    if (selectedTheme) {
      const themeCard = document.querySelector(`[data-theme="${selectedTheme}"]`);
      if (themeCard) total += parseInt(themeCard.getAttribute('data-price')) || 0;
    }
    
    if (selectedVenue) {
      const venueCard = document.querySelector(`[data-venue="${selectedVenue}"]`);
      if (venueCard) total += parseInt(venueCard.getAttribute('data-price')) || 0;
    }
    
    if (selectedCatering) {
      const cateringCard = document.querySelector(`[data-catering="${selectedCatering}"]`);
      if (cateringCard) total += parseInt(cateringCard.getAttribute('data-price')) || 0;
    }
    
    selectedAddons.forEach(addonId => {
      const addonCard = document.querySelector(`[data-addon="${addonId}"]`);
      if (addonCard) total += parseInt(addonCard.getAttribute('data-price')) || 0;
    });
    
    return total;
  }

  // Count selected items
  function countSelectedItems() {
    let count = 0;
    if (selectedTheme) count++;
    if (selectedVenue) count++;
    if (selectedCatering) count++;
    count += selectedAddons.size;
    return count;
  }

  // Update summary display
  function updateSummary() {
    const total = calculateTotal();
    const count = countSelectedItems();
    document.getElementById('totalPrice').innerHTML = `₱${total.toLocaleString()}`;
    document.getElementById('selectedCount').innerText = count;
  }

  // Reset all selections
  function resetSelection() {
    selectedTheme = null;
    selectedVenue = null;
    selectedCatering = null;
    selectedAddons.clear();
    
    document.querySelectorAll('.photo-card').forEach(card => card.classList.remove('selected'));
    document.querySelectorAll('.addon-card').forEach(card => {
      card.classList.remove('selected');
      const checkbox = card.querySelector('input[type="checkbox"]');
      if (checkbox) checkbox.checked = false;
    });
    
    updateSummary();
    showToast('All selections have been reset');
  }

  // Proceed to checkout
  function proceedToCheckout() {
    if (!selectedTheme || !selectedVenue || !selectedCatering) {
      showToast('Please select a theme, venue, and catering package before proceeding');
      return;
    }
    
    const selections = {
      theme: selectedTheme,
      venue: selectedVenue,
      catering: selectedCatering,
      addons: Array.from(selectedAddons),
      total: calculateTotal(),
      occasion: '<?= htmlspecialchars($occasion) ?>'
    };
    
    // Store selections in sessionStorage for checkout page
    sessionStorage.setItem('customizationData', JSON.stringify(selections));
    
    // Redirect to checkout page
    window.location.href = `checkout.php?occasion=<?= urlencode($occasion) ?>`;
  }

  // Initial update
  updateSummary();
</script>
</body>
</html>
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

// Load packages from database
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/app/controllers/AdminPackageController.php';
require_once ROOT_PATH . '/config/database.php';

$packageController = new AdminPackageController();
$allPackages = $packageController->getAll();

// Group packages by occasion
$packagesByOccasion = [];
foreach ($allPackages as $pkg) {
    $occasionName = strtolower($pkg['occasion_name']);
    if (!isset($packagesByOccasion[$occasionName])) {
        $packagesByOccasion[$occasionName] = [];
    }
    $packagesByOccasion[$occasionName][] = $pkg;
}

// Sort packages within each occasion
foreach ($packagesByOccasion as $occasion => &$packages) {
    usort($packages, function($a, $b) {
        // Custom sort order based on package name
        $order = [
            'Classic Wedding' => 1,
            'Elegant Wedding' => 2,
            'Premium Wedding' => 3,
            'Classic Birthday' => 1,
            'Birthday Bundle A' => 2,
            'Birthday Bundle B' => 3,
            'Birthday Bundle C' => 4,
            'Corporate Gala' => 1,
            'Premium Gala Experience' => 2,
            'Luxury Gala & Awards' => 3
        ];
        
        $aOrder = $order[$a['package_name']] ?? 999;
        $bOrder = $order[$b['package_name']] ?? 999;
        
        if ($aOrder === $bOrder) {
            return $a['price'] <=> $b['price'];
        }
        
        return $aOrder <=> $bOrder;
    });
}

// Map URL occasions to database occasion names
$occasionMapping = [
    'wedding' => 'wedding',
    'birthday' => 'birthday',
    'big-events' => 'corporate', // Map big-events to corporate
    'other' => ['debut', 'anniversary'] // Multiple occasions for other
];

$currentOccasionPackages = [];
if ($occasion === 'other') {
    foreach ($occasionMapping[$occasion] as $occ) {
        if (isset($packagesByOccasion[$occ])) {
            $currentOccasionPackages = array_merge($currentOccasionPackages, $packagesByOccasion[$occ]);
        }
    }
} else {
    $currentOccasionPackages = $packagesByOccasion[$occasionMapping[$occasion] ?? 'wedding'] ?? [];
}
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
  <link rel="stylesheet" href="/assets/css/global.css">
  <link rel="stylesheet" href="/assets/css/nav.css">
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

    /* Toast Notifications */
    .toast {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      color: white;
      font-weight: 600;
      z-index: 3000;
      animation: slideInToast 0.3s ease;
      max-width: 300px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    .toast.success {
      background: #2e7d32;
    }
    .toast.error {
      background: #c62828;
    }
    .toast.info {
      background: var(--primary);
    }
    @keyframes slideInToast {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    @media (max-width: 640px) {
      .toast {
        bottom: 1rem;
        right: 1rem;
        left: 1rem;
        max-width: none;
      }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

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
    <?php 
    $weddingPackages = $packagesByOccasion['wedding'] ?? [];
    $featuredIndex = 0; // Make first package featured
    foreach ($weddingPackages as $index => $pkg): 
    ?>
      <div class="pkg-card <?= $index === $featuredIndex ? 'featured' : '' ?>">
        <?php if ($index === $featuredIndex): ?>
          <div class="pkg-card__badge">Most Popular</div>
        <?php endif; ?>
        <div class="pkg-card__img" style="background-image: url('<?= htmlspecialchars($pkg['image'] ?? '/assets/img/placeholder.jpg') ?>')"></div>
        <div class="pkg-card__body">
          <h3><?= htmlspecialchars($pkg['package_name'] ?? $pkg['name'] ?? 'Unnamed Package') ?></h3>
          <p class="pkg-card__desc"><?= htmlspecialchars(substr($pkg['description'], 0, 80)) ?>...</p>
          <ul class="pkg-features">
            <?php if (!empty($pkg['features'])): ?>
              <?php $features = explode("\n", $pkg['features']); ?>
              <?php foreach ($features as $feature): ?>
                <?php if (trim($feature)): ?>
                  <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars(trim($feature)) ?></li>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <li><i class="fas fa-check-circle"></i> Full Event Coordination</li>
              <li><i class="fas fa-check-circle"></i> Venue Setup & Styling</li>
              <li><i class="fas fa-check-circle"></i> Catering</li>
              <li><i class="fas fa-check-circle"></i> Photography</li>
            <?php endif; ?>
          </ul>
          <div class="pkg-card__footer">
            <div class="pkg-price">₱<?= number_format($pkg['price'], 2) ?> <small>+ VAT</small></div>
            <button class="btn btn--primary" onclick="addToCart({name: '<?= htmlspecialchars($pkg['package_name'] ?? $pkg['name']) ?>', price: <?= $pkg['price'] ?>, description: '<?= htmlspecialchars(strtr(str_replace(["\r\n", "\n", "\r"], ["", "", ""], $pkg['description'] ?? ''), ["'" => "&#39;"])) ?>', features: '<?= htmlspecialchars(strtr(str_replace(["\r\n", "\n", "\r"], [" | ", " | ", " | "], $pkg['features'] ?? ''), ["'" => "&#39;"])) ?>', image: '<?= htmlspecialchars($pkg['image'] ?? '') ?>', occasion: 'wedding'})">Select Package</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    
    <?php if (empty($weddingPackages)): // Fallback if no packages in DB ?>
      <div class="pkg-card featured">
        <div class="pkg-card__badge">Most Popular</div>
        <div class="pkg-card__img" style="background-image: url('/assets/img/classic.jpg')"></div>
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
            <button class="btn btn--primary" onclick="addToCart({name: 'Classic Wedding', price: 150000, description: 'Perfect for those who want a beautifully organized event without complexity.', features: 'Full Event Coordination\nVenue Setup & Styling\nCatering (100 pax)\nPhotography (6 hours)\nProfessional Host/Emcee', image: '/assets/img/classic.jpg', occasion: 'wedding'})">Select Package</button>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- BIRTHDAY PACKAGES -->
  <div id="birthday-packages" class="occasion-packages" style="display: <?= $showBirthday ? 'grid' : 'none' ?>; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
    <?php 
    $birthdayPackages = $packagesByOccasion['birthday'] ?? [];
    $featuredIndex = 0;
    foreach ($birthdayPackages as $index => $pkg): 
    ?>
      <div class="pkg-card <?= $index === $featuredIndex ? 'featured' : '' ?>">
        <?php if ($index === $featuredIndex): ?>
          <div class="pkg-card__badge">Most Popular</div>
        <?php elseif (stripos($pkg['package_name'] ?? $pkg['name'], 'Bundle') !== false): ?>
          <div class="pkg-card__badge">Bundle <?= substr($pkg['package_name'] ?? $pkg['name'], -1) ?></div>
        <?php endif; ?>
        <div class="pkg-card__img" style="background-image: url('<?= htmlspecialchars($pkg['image'] ?? '/assets/img/placeholder.jpg') ?>')"></div>
        <div class="pkg-card__body">
          <h3><?= htmlspecialchars($pkg['package_name'] ?? $pkg['name'] ?? 'Unnamed Package') ?></h3>
          <p class="pkg-card__desc"><?= htmlspecialchars(substr($pkg['description'], 0, 80)) ?>...</p>
          <ul class="pkg-features">
            <?php if (!empty($pkg['features'])): ?>
              <?php $features = explode("\n", $pkg['features']); ?>
              <?php foreach ($features as $feature): ?>
                <?php if (trim($feature)): ?>
                  <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars(trim($feature)) ?></li>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <li><i class="fas fa-check-circle"></i> Party Coordination</li>
              <li><i class="fas fa-check-circle"></i> Themed Decor</li>
              <li><i class="fas fa-check-circle"></i> Catering</li>
              <li><i class="fas fa-check-circle"></i> Photography</li>
            <?php endif; ?>
          </ul>
          <div class="pkg-card__footer">
            <div class="pkg-price">₱<?= number_format($pkg['price'], 2) ?> <small>+ VAT</small></div>
            <button class="btn btn--primary" onclick="addToCart({name: '<?= htmlspecialchars($pkg['package_name'] ?? $pkg['name']) ?>', price: <?= $pkg['price'] ?>, description: '<?= htmlspecialchars(strtr(str_replace(["\r\n", "\n", "\r"], ["", "", ""], $pkg['description'] ?? ''), ["'" => "&#39;"])) ?>', features: '<?= htmlspecialchars(strtr(str_replace(["\r\n", "\n", "\r"], [" | ", " | ", " | "], $pkg['features'] ?? ''), ["'" => "&#39;"])) ?>', image: '<?= htmlspecialchars($pkg['image'] ?? '') ?>', occasion: 'birthday'})">Select Package</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    
    <?php if (empty($birthdayPackages)): // Fallback hardcoded packages ?>
      <div class="pkg-card featured">
        <div class="pkg-card__badge">Most Popular</div>
        <div class="pkg-card__img" style="background-image: url('/assets/img/classicbday.jpg')"></div>
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
            <button class="btn btn--primary" onclick="addToCart({name: 'Classic Birthday', price: 45000, description: 'Fun and festive celebration with all the essentials.', features: 'Party Coordination\nThemed Decor & Balloons\nCatering (50 pax)\nPhotography (4 hours)\nBirthday Cake (2-tier)\nParty Host/Game Master', image: '/assets/img/classicbday.jpg', occasion: 'birthday'})">Select Package</button>
          </div>
        </div>
      </div>
      
      <div class="pkg-card">
        <div class="pkg-card__img" style="background-image: url('/assets/img/deluxbday.jpg')"></div>
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
            <button class="btn btn--primary" onclick="addToCart({name: 'Deluxe Birthday', price: 85000, description: 'Extra special celebration with premium entertainment.', features: 'Full Party Coordination\nPremium Themed Decor\nCatering (100 pax)\nPhotography + Video (6 hours)\nCustom 3-Tier Cake\nLive Band (3 pieces)\nPhoto Booth + Giveaways', image: '/assets/img/deluxbday.jpg', occasion: 'birthday'})">Select Package</button>
          </div>
        </div>
      </div>
      
      <div class="pkg-card">
        <div class="pkg-card__img" style="background-image: url('/assets/img/ultimatebday.jpg')"></div>
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
            <button class="btn btn--primary" onclick="addToCart({name: 'Ultimate Birthday Bash', price: 180000, description: 'Over-the-top celebration with everything you can imagine.', features: 'Dedicated Event Manager\nLuxury Themed Transformation\nCatering (150 pax)\nCinematic Coverage + SDE\nDesigner Cake + Dessert Buffet\nCelebrity Guest / Performer\nFireworks Display', image: '/assets/img/ultimatebday.jpg', occasion: 'birthday'})">Select Package</button>
          </div>
        </div>
      </div>

      <!-- Additional Birthday Bundles -->
      <div class="pkg-card">
        <div class="pkg-card__badge">Bundle A</div>
        <div class="pkg-card__img" style="background-image: url('/assets/img/bundlea.jpg')"></div>
        <div class="pkg-card__body">
          <h3>Birthday Bundle A</h3>
          <p class="pkg-card__desc">Intimate celebration perfect for close family and friends.</p>
          <ul class="pkg-features">
            <li><i class="fas fa-check-circle"></i> Event Coordination & Planning</li>
            <li><i class="fas fa-check-circle"></i> Venue Setup (2 hours)</li>
            <li><i class="fas fa-check-circle"></i> Basic Themed Decorations</li>
            <li><i class="fas fa-check-circle"></i> Catering for 30 guests</li>
            <li><i class="fas fa-check-circle"></i> Birthday Cake (1-tier)</li>
            <li><i class="fas fa-check-circle"></i> Photography (2 hours)</li>
            <li><i class="fas fa-check-circle"></i> Sound System Setup</li>
            <li><i class="fas fa-check-circle"></i> Party Favors for guests</li>
          </ul>
          <div class="pkg-card__footer">
            <div class="pkg-price">₱25,000 <small>+ VAT</small></div>
            <button class="btn btn--primary" onclick="addToCart({name: 'Birthday Bundle A', price: 25000, description: 'Intimate celebration perfect for close family and friends.', features: 'Event Coordination & Planning\nVenue Setup (2 hours)\nBasic Themed Decorations\nCatering for 30 guests\nBirthday Cake (1-tier)\nPhotography (2 hours)\nSound System Setup\nParty Favors for guests', image: '/assets/img/bundlea.jpg', occasion: 'birthday'})">Select Package</button>
          </div>
        </div>
      </div>

      <div class="pkg-card">
        <div class="pkg-card__badge">Bundle B</div>
        <div class="pkg-card__img" style="background-image: url('/assets/img/bundleb.jpg')"></div>
        <div class="pkg-card__body">
          <h3>Birthday Bundle B</h3>
          <p class="pkg-card__desc">Fun-filled celebration with entertainment and activities.</p>
          <ul class="pkg-features">
            <li><i class="fas fa-check-circle"></i> Full Event Management</li>
            <li><i class="fas fa-check-circle"></i> Creative Venue Styling</li>
            <li><i class="fas fa-check-circle"></i> Catering for 75 guests</li>
            <li><i class="fas fa-check-circle"></i> Custom Birthday Cake (2-tier)</li>
            <li><i class="fas fa-check-circle"></i> Photo & Video Coverage (4 hours)</li>
            <li><i class="fas fa-check-circle"></i> Live DJ/Music Entertainment</li>
            <li><i class="fas fa-check-circle"></i> Games & Activities Host</li>
            <li><i class="fas fa-check-circle"></i> Photo Booth with Props</li>
            <li><i class="fas fa-check-circle"></i> Custom Invitations</li>
          </ul>
          <div class="pkg-card__footer">
            <div class="pkg-price">₱65,000 <small>+ VAT</small></div>
            <button class="btn btn--primary" onclick="addToCart({name: 'Birthday Bundle B', price: 65000, description: 'Fun-filled celebration with entertainment and activities.', features: 'Full Event Management\nCreative Venue Styling\nCatering for 75 guests\nCustom Birthday Cake (2-tier)\nPhoto & Video Coverage (4 hours)\nLive DJ/Music Entertainment\nGames & Activities Host\nPhoto Booth with Props\nCustom Invitations', image: '/assets/img/bundleb.jpg', occasion: 'birthday'})">Select Package</button>
          </div>
        </div>
      </div>

      <div class="pkg-card">
        <div class="pkg-card__badge">Bundle C</div>
        <div class="pkg-card__img" style="background-image: url('/assets/img/bundlec.jpg')"></div>
        <div class="pkg-card__body">
          <h3>Birthday Bundle C</h3>
          <p class="pkg-card__desc">Luxurious celebration with premium services and VIP treatment.</p>
          <ul class="pkg-features">
            <li><i class="fas fa-check-circle"></i> Dedicated Event Coordinator</li>
            <li><i class="fas fa-check-circle"></i> Premium Venue Transformation</li>
            <li><i class="fas fa-check-circle"></i> Gourmet Catering for 120 guests</li>
            <li><i class="fas fa-check-circle"></i> Designer Cake (3-tier)</li>
            <li><i class="fas fa-check-circle"></i> Professional Photo/Video Team (6 hours)</li>
            <li><i class="fas fa-check-circle"></i> Live Band Performance</li>
            <li><i class="fas fa-check-circle"></i> Celebrity Guest Appearance</li>
            <li><i class="fas fa-check-circle"></i> Luxury Photo Booth</li>
            <li><i class="fas fa-check-circle"></i> Red Carpet Entrance</li>
            <li><i class="fas fa-check-circle"></i> VIP Guest Coordination</li>
          </ul>
          <div class="pkg-card__footer">
            <div class="pkg-price">₱150,000 <small>+ VAT</small></div>
            <button class="btn btn--primary" onclick="addToCart({name: 'Birthday Bundle C', price: 150000, description: 'Luxurious celebration with premium services and VIP treatment.', features: 'Dedicated Event Coordinator\nPremium Venue Transformation\nGourmet Catering for 120 guests\nDesigner Cake (3-tier)\nProfessional Photo/Video Team (6 hours)\nLive Band Performance\nCelebrity Guest Appearance\nLuxury Photo Booth\nRed Carpet Entrance\nVIP Guest Coordination', image: '/assets/img/bundlec.jpg', occasion: 'birthday'})">Select Package</button>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- BIG EVENTS / GALA PACKAGES -->
  <div id="bigevents-packages" class="occasion-packages" style="display: <?= $showBigEvents ? 'grid' : 'none' ?>; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
    <?php 
    $bigEventsPackages = array_merge(
        $packagesByOccasion['corporate'] ?? []
    );
    $featuredIndex = 0;
    foreach ($bigEventsPackages as $index => $pkg): 
    ?>
      <div class="pkg-card <?= $index === $featuredIndex ? 'featured' : '' ?>">
        <?php if ($index === $featuredIndex): ?>
          <div class="pkg-card__badge">Best Value</div>
        <?php elseif (stripos($pkg['package_name'] ?? $pkg['name'], 'Bundle') !== false): ?>
          <div class="pkg-card__badge">Bundle <?= substr($pkg['package_name'] ?? $pkg['name'], -1) ?></div>
        <?php endif; ?>
        <div class="pkg-card__img" style="background-image: url('<?= htmlspecialchars($pkg['image'] ?? '/assets/img/placeholder.jpg') ?>')"></div>
        <div class="pkg-card__body">
          <h3><?= htmlspecialchars($pkg['package_name'] ?? $pkg['name'] ?? 'Unnamed Package') ?></h3>
          <p class="pkg-card__desc"><?= htmlspecialchars(substr($pkg['description'], 0, 80)) ?>...</p>
          <ul class="pkg-features">
            <?php if (!empty($pkg['features'])): ?>
              <?php $features = explode("\n", $pkg['features']); ?>
              <?php foreach ($features as $feature): ?>
                <?php if (trim($feature)): ?>
                  <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars(trim($feature)) ?></li>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <li><i class="fas fa-check-circle"></i> Event Planning & Logistics</li>
              <li><i class="fas fa-check-circle"></i> Venue Selection & Setup</li>
              <li><i class="fas fa-check-circle"></i> Catering</li>
              <li><i class="fas fa-check-circle"></i> Professional Photography</li>
            <?php endif; ?>
          </ul>
          <div class="pkg-card__footer">
            <div class="pkg-price">₱<?= number_format($pkg['price'], 2) ?> <small>+ VAT</small></div>
            <button class="btn btn--primary" onclick="addToCart({name: '<?= htmlspecialchars($pkg['package_name'] ?? $pkg['name']) ?>', price: <?= $pkg['price'] ?>, description: '<?= htmlspecialchars(strtr(str_replace(["
", "
", "
"], ["", "", ""], $pkg['description'] ?? ''), ["'" => "&#39;"])) ?>', features: '<?= htmlspecialchars(strtr(str_replace(["
", "
", "
"], [" | ", " | ", " | "], $pkg['features'] ?? ''), ["'" => "&#39;"])) ?>', image: '<?= htmlspecialchars($pkg['image'] ?? '') ?>', occasion: 'big-events'})">Select Package</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    
    <?php if (empty($bigEventsPackages)): // Fallback hardcoded packages ?>
      <div class="pkg-card featured">
        <div class="pkg-card__badge">Best Value</div>
        <div class="pkg-card__img" style="background-image: url('/assets/img/corpgala.jpg')"></div>
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
            <button class="btn btn--primary" onclick="addToCart({name: 'Corporate Gala', price: 250000, description: 'Professional and elegant event for company celebrations.', features: 'Event Planning & Logistics\nVenue Selection & Setup\nCatering (200 pax)\nProfessional Photography\nAV Equipment & Lighting\nProfessional Emcee', image: '/assets/img/corpgala.jpg', occasion: 'big-events'})">Select Package</button>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- OTHER EVENTS SECTION -->
  <div id="other-packages" class="occasion-packages" style="display: <?= $showOther ? 'block' : 'none' ?>;">
    <div class="other-events-content">
      <div class="other-events-icon">
        <i class="fas fa-plus-circle"></i>
      </div>
      <h2>Custom Event Planning</h2>
      <p>For christenings, reunions, graduations, or any special celebration you have in mind, we'll create a package tailored just for you.</p>
      <a href="/index.php?route=customize&occasion=other" class="btn btn--primary btn--lg">
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
    <a href="/index.php?route=customize&occasion=<?= urlencode($occasion) ?>" class="btn btn--primary btn--lg">
      <i class="fas fa-magic"></i> Start Customizing
    </a>
  </div>
  <?php endif; ?>
</main>

<script>
// Toast notification function
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

let cart = [];

function addToCart(packageData) {
  // Handle both legacy format (name, price) and new format (object)
  let packageItem;
  if (typeof packageData === 'string') {
    // Legacy: addToCart(name, price)
    packageItem = { 
      name: packageData, 
      price: arguments[1], 
      type: 'package',
      details: ''
    };
  } else {
    // New format: addToCart({...})
    packageItem = {
      name: packageData.name,
      price: packageData.price,
      type: 'package',
      package_id: packageData.package_id || null,
      description: packageData.description || '',
      features: packageData.features || '',
      image: packageData.image || '',
      occasion: packageData.occasion || '',
      details: packageData.features ? packageData.features.split('\n').slice(0, 3).join(', ') : ''
    };
  }
  
  cart = [packageItem]; // Replace cart with selected package only
  showToast('✓ ' + packageItem.name + ' added to cart! Total: ₱' + packageItem.price.toLocaleString(), 'success');
  
  // Store in sessionStorage for client-side access
  sessionStorage.setItem('checkoutCart', JSON.stringify(cart));
  // Store in session via POST for server-side access
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/index.php?route=checkout';
  const cartInput = document.createElement('input');
  cartInput.type = 'hidden';
  cartInput.name = 'cart_data';
  cartInput.value = JSON.stringify(cart);
  form.appendChild(cartInput);
  document.body.appendChild(form);
  form.submit();
}

function toggleDropdown() {
  const dropdown = document.getElementById('occasionDropdown');
  dropdown.classList.toggle('open');
}

function selectOccasion(occasion) {
 window.location.href = '/index.php?route=packages&occasion=' + occasion;
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
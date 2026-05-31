<?php 
session_start(); 
$page = 'customize';
$occasion = $_GET['occasion'] ?? 'wedding';
$occasionLabel = ucfirst($occasion);

// Load customization options from database
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/app/models/Customization.php';

$customization = new Customization();
$allOptions = $customization->getAllOptions();

// Group options by category
$optionsByCategory = [
    'Theme' => [],
    'Color Combinations' => [],
    'Venue' => [],
    'Food' => [],
    'Sweets' => [],
    'Catering' => [],
    'Pastries' => [],
    'Beverages' => [],
    'Add-ons' => [],
    'Extras' => []  // Keep for backward compatibility
];

foreach ($allOptions as $opt) {
    if (isset($optionsByCategory[$opt['category']])) {
        $optionsByCategory[$opt['category']][] = $opt;
    }
}

$extraIconMap = [
    'Premium Photography' => 'fa-camera',
    'Cinematic Videography' => 'fa-video',
    'Floral Arrangements' => 'fa-seedling',
    'Live Band / DJ' => 'fa-music',
    'Designer Cake' => 'fa-cake-candles',
    'Lighting & Sound' => 'fa-lightbulb',
    'Photo Booth' => 'fa-camera-retro',
    'Day-of Coordinator' => 'fa-clipboard-list',
    'Gold deco' => 'fa-star',
    'Default' => 'fa-plus'
];
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

    .summary-panel {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    .order-summary,
    .program-flow-section {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 1.5rem;
    }
    .summary-list {
      min-height: 180px;
      display: grid;
      gap: 0.75rem;
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      font-size: 0.95rem;
      color: var(--dark);
    }
    .summary-row span:last-child { font-weight: 600; color: var(--primary); }
    .summary-placeholder {
      color: var(--gray);
      font-size: 0.95rem;
      line-height: 1.6;
      padding: 1rem 0;
    }
    .program-flow-section textarea {
      width: 100%;
      min-height: 140px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      padding: 1rem;
      font-family: var(--sans);
      font-size: 0.95rem;
      resize: vertical;
      transition: border-color var(--t-fast);
    }
    .program-flow-section textarea:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(138,118,80,0.12);
    }
    .program-flow-note {
      margin-top: 0.75rem;
      color: var(--gray);
      font-size: 0.85rem;
    }

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
      .summary-panel { grid-template-columns: 1fr; }
      .customize-footer { flex-direction: column; text-align: center; }
      .summary-details { justify-content: center; }
    }
  </style>
</head>
<body>

<!-- Include external navigation -->
<?php include __DIR__ . '/nav.php'; ?>

<main class="customize-main">
  <div class="breadcrumb">
   <a href="/index.php?route=occasions">Occasions</a> <span>/</span>
   <a href="/index.php?route=packages&occasion=<?= urlencode($occasion) ?>">Packages</a> <span>/</span>
    <span>Customize</span>
  </div>
  
  <div class="customize-header">
    <div class="eyebrow"><span class="rule"></span> Build Your Dream Event</div>
    <h1>Customize your <em><?= htmlspecialchars($occasionLabel) ?></em></h1>
    <p class="customize-header__sub">Select from our curated options below to create your perfect celebration</p>
  </div>

  <!-- ===== VENUE DECORATION SECTION ===== -->
  <div style="margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 2px solid #E2D9C8;">
    <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #8A7650; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
      <i class="fas fa-home"></i> Venue Decoration
    </h2>
    
    <!-- Theme -->
    <div class="section-header">
      <i class="fas fa-palette"></i>
      <h3>1. Choose a Theme</h3>
    </div>
    <div class="photo-card-grid" id="themeGrid">
      <?php if (!empty($optionsByCategory['Theme'])): ?>
        <?php foreach ($optionsByCategory['Theme'] as $opt): ?>
          <?php
            $imageStyle = '';
            if (!empty($opt['image']) && !empty($opt['image_type'])) {
                $imageStyle = 'background-image: url(\'data:' . htmlspecialchars($opt['image_type']) . ';base64,' . base64_encode($opt['image']) . '\');';
            }
            $iconClass = 'fa-palette';
          ?>
          <div class="photo-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Theme" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="selectOption(this)">
            <div class="photo-card__image" style="<?= $imageStyle ?: 'background: linear-gradient(135deg, rgba(138,118,80,0.12) 0%, rgba(138,118,80,0.04) 100%);' ?>"></div>
            <div class="photo-card__content">
              <div class="photo-card__icon"><i class="fas <?= $iconClass ?>"></i></div>
              <div class="photo-card__title"><?= htmlspecialchars($opt['name']) ?></div>
              <div class="photo-card__desc"><?= htmlspecialchars($opt['description'] ?: 'Choose this theme option') ?></div>
              <div class="photo-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No theme options available at the moment.</p>
      <?php endif; ?>
    </div>

    <!-- Color Combinations -->
    <div class="section-header" style="margin-top: 2rem;">
      <i class="fas fa-droplet"></i>
      <h3>2. Choose Your Color Palette</h3>
    </div>
    <div class="photo-card-grid" id="colorGrid">
      <?php if (!empty($optionsByCategory['Color Combinations'])): ?>
        <?php 
        // Separate options and organize them: preset combos first, then existing, then organizer choice, then other
        $presetCombos = [];
        $existingOptions = [];
        $organizerChoice = null;
        $otherOption = null;
        
        foreach ($optionsByCategory['Color Combinations'] as $opt) {
          if ($opt['name'] === 'Other') {
            $otherOption = $opt;
          } elseif ($opt['name'] === 'Organizer Choice') {
            $organizerChoice = $opt;
          } elseif (strpos($opt['name'], 'Color:') !== false) {
            $presetCombos[] = $opt;
          } else {
            $existingOptions[] = $opt;
          }
        }
        
        // Display in order: preset combos, existing, organizer choice, other
        $optionsToDisplay = array_merge(
          $presetCombos,
          $existingOptions,
          ($organizerChoice ? [$organizerChoice] : []),
          ($otherOption ? [$otherOption] : [])
        );
        
        foreach ($optionsToDisplay as $opt): ?>
          <?php
            $imageStyle = '';
            $colorSwatches = '';
            $colorImagesDisplay = '';
            
            // Check if this option has color palette images
            $colorImages = $customization->getColorPaletteImages($opt['option_id']);
            
            if (!empty($colorImages)) {
                // Display color images in a grid
                $colorImagesDisplay = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(40px, 1fr)); gap: 4px; width: 100%; height: 100%; padding: 4px;">';
                foreach ($colorImages as $colorImg) {
                    if (!empty($colorImg['image'])) {
                        $colorImagesDisplay .= '<div style="
                            width: 100%;
                            height: 100%;
                            min-height: 40px;
                            background-image: url(\'data:' . htmlspecialchars($colorImg['image_type']) . ';base64,' . base64_encode($colorImg['image']) . '\');
                            background-size: cover;
                            background-position: center;
                            border-radius: 4px;
                            border: 1px solid rgba(0,0,0,0.1);
                        "></div>';
                    }
                }
                $colorImagesDisplay .= '</div>';
            }
            
            // Fallback to color hex codes if no images
            if (empty($colorImagesDisplay) && !empty($opt['colors_json'])) {
                $colors = json_decode($opt['colors_json'], true);
                if (is_array($colors)) {
                    $colorSwatches = '<div style="display: flex; width: 100%; height: 100%; overflow: hidden;">';
                    $colorWidth = 100 / count($colors);
                    foreach ($colors as $color) {
                        $colorSwatches .= '<div style="flex: 1; background-color: ' . htmlspecialchars($color) . '; height: 100%;"></div>';
                    }
                    $colorSwatches .= '</div>';
                    $imageStyle = '';
                }
            } elseif (empty($colorImagesDisplay) && !empty($opt['image']) && !empty($opt['image_type'])) {
                $imageStyle = 'background-image: url(\'data:' . htmlspecialchars($opt['image_type']) . ';base64,' . base64_encode($opt['image']) . '\');';
            }
          ?>
          <div class="photo-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Color Combinations" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="selectOption(this)">
            <div class="photo-card__image" style="<?= !empty($colorImagesDisplay) ? 'padding: 0; overflow: hidden; display: flex; align-items: center; justify-content: center;' : ($imageStyle ?: ($colorSwatches ? 'padding: 0; overflow: hidden; display: block;' : 'background: linear-gradient(135deg, rgba(138,118,80,0.12) 0%, rgba(138,118,80,0.04) 100%);')) ?>">
              <?php if (!empty($colorImagesDisplay)): ?>
                <?= $colorImagesDisplay ?>
              <?php elseif (!empty($colorSwatches)): ?>
                <?= $colorSwatches ?>
              <?php endif; ?>
            </div>
            <div class="photo-card__content">
              <div class="photo-card__icon"><i class="fas fa-palette"></i></div>
              <div class="photo-card__title"><?= htmlspecialchars($opt['name']) ?></div>
              <div class="photo-card__desc"><?= htmlspecialchars($opt['description'] ?: 'Choose this color palette') ?></div>
              <div class="photo-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No color combination options available at the moment.</p>
      <?php endif; ?>
    </div>
    
    <!-- Custom Color Modal -->
    <div id="customColorModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
      <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h3 style="margin: 0; font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: #2C2820;">
            <i class="fas fa-palette"></i> Create Your Custom Color Combination
          </h3>
          <button onclick="closeColorModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999;">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <p style="color: #6B6463; margin-bottom: 1rem;">Select 3 to 5 colors from the palette below to create your perfect combination:</p>
        
        <div style="margin-bottom: 1.5rem;">
          <label style="display: block; font-weight: 600; margin-bottom: 0.75rem; color: #2C2820;">Selected Colors: <span id="selectedColorCount">0</span>/5</label>
          <div id="customColorPalette" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.75rem; margin-bottom: 1rem;">
            <!-- Colors will be generated by JavaScript -->
          </div>
          <div id="selectedColorsDisplay" style="display: flex; flex-wrap: wrap; gap: 0.75rem; padding: 1rem; background: #f5f0e8; border-radius: 8px; min-height: 50px; align-items: center;">
            <!-- Selected colors will appear here -->
          </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
          <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #2C2820;">Optional: Add Description</label>
          <textarea id="customColorDescription" placeholder="e.g., Rose gold accents with ivory..." style="width: 100%; padding: 0.75rem; border: 1px solid #E2D9C8; border-radius: 8px; font-family: inherit; font-size: 0.95rem; resize: vertical; min-height: 60px;"></textarea>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
          <button onclick="closeColorModal()" style="padding: 0.75rem 1.5rem; background: #f5f0e8; color: #2C2820; border: 1px solid #E2D9C8; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;">
            Cancel
          </button>
          <button onclick="confirmCustomColors()" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #8A7650 0%, #6B5A40 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;">
            <i class="fas fa-check"></i> Confirm Selection
          </button>
        </div>
      </div>
    </div>

    <!-- Venue -->
    <div class="section-header" style="margin-top: 2rem;">
      <i class="fas fa-map-marker-alt"></i>
      <h3>3. Select Your Venue</h3>
    </div>
    <div class="photo-card-grid" id="venueGrid">
      <?php if (!empty($optionsByCategory['Venue'])): ?>
        <?php foreach ($optionsByCategory['Venue'] as $opt): ?>
          <?php
            $imageStyle = '';
            if (!empty($opt['image']) && !empty($opt['image_type'])) {
                $imageStyle = 'background-image: url(\'data:' . htmlspecialchars($opt['image_type']) . ';base64,' . base64_encode($opt['image']) . '\');';
            }
          ?>
          <div class="photo-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Venue" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="selectOption(this)">
            <div class="photo-card__image" style="<?= $imageStyle ?: 'background: linear-gradient(135deg, rgba(138,118,80,0.12) 0%, rgba(138,118,80,0.04) 100%);' ?>"></div>
            <div class="photo-card__content">
              <div class="photo-card__icon"><i class="fas fa-building"></i></div>
              <div class="photo-card__title"><?= htmlspecialchars($opt['name']) ?></div>
              <div class="photo-card__desc"><?= htmlspecialchars($opt['description'] ?: 'Choose this venue option') ?></div>
              <div class="photo-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No venue options available at the moment.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ===== FOOD SECTION ===== -->
  <div style="margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 2px solid #E2D9C8;">
    <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #8A7650; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
      <i class="fas fa-utensils"></i> Food
    </h2>
    
    <!-- Food Stations -->
    <div class="section-header">
      <i class="fas fa-plate-wheat"></i>
      <h3>4. Choose Food Stations</h3>
    </div>
    <div class="photo-card-grid" id="foodGrid">
      <?php if (!empty($optionsByCategory['Food'])): ?>
        <?php foreach ($optionsByCategory['Food'] as $opt): ?>
          <?php
            $imageStyle = '';
            if (!empty($opt['image']) && !empty($opt['image_type'])) {
                $imageStyle = 'background-image: url(\'data:' . htmlspecialchars($opt['image_type']) . ';base64,' . base64_encode($opt['image']) . '\');';
            }
          ?>
          <div class="photo-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Food" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="selectOption(this)">
            <div class="photo-card__image" style="<?= $imageStyle ?: 'background: linear-gradient(135deg, rgba(138,118,80,0.12) 0%, rgba(138,118,80,0.04) 100%);' ?>"></div>
            <div class="photo-card__content">
              <div class="photo-card__icon"><i class="fas fa-plate-wheat"></i></div>
              <div class="photo-card__title"><?= htmlspecialchars($opt['name']) ?></div>
              <div class="photo-card__desc"><?= htmlspecialchars($opt['description'] ?: 'Choose this food station') ?></div>
              <div class="photo-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No food station options available at the moment.</p>
      <?php endif; ?>
    </div>

    <!-- Sweets (Optional) -->
    <div class="section-header" style="margin-top: 2rem;">
      <i class="fas fa-candy-cane"></i>
      <h3>5. Add Sweets Station <span style="font-size: 0.8rem; color: #8A7650; font-weight: 400;">(Optional)</span></h3>
    </div>
    <div class="photo-card-grid" id="sweetsGrid">
      <?php if (!empty($optionsByCategory['Sweets'])): ?>
        <?php foreach ($optionsByCategory['Sweets'] as $opt): ?>
          <?php
            $imageStyle = '';
            if (!empty($opt['image']) && !empty($opt['image_type'])) {
                $imageStyle = 'background-image: url(\'data:' . htmlspecialchars($opt['image_type']) . ';base64,' . base64_encode($opt['image']) . '\');';
            }
          ?>
          <div class="photo-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Sweets" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="selectOption(this)">
            <div class="photo-card__image" style="<?= $imageStyle ?: 'background: linear-gradient(135deg, rgba(138,118,80,0.12) 0%, rgba(138,118,80,0.04) 100%);' ?>"></div>
            <div class="photo-card__content">
              <div class="photo-card__icon"><i class="fas fa-candy-cane"></i></div>
              <div class="photo-card__title"><?= htmlspecialchars($opt['name']) ?></div>
              <div class="photo-card__desc"><?= htmlspecialchars($opt['description'] ?: 'Choose this sweets option') ?></div>
              <div class="photo-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No sweets station options available at the moment.</p>
      <?php endif; ?>
    </div>

    <!-- Catering -->
    <div class="section-header" style="margin-top: 2rem;">
      <i class="fas fa-utensils"></i>
      <h3>6. Choose Your Catering</h3>
    </div>
    <div class="photo-card-grid" id="cateringGrid">
      <?php if (!empty($optionsByCategory['Catering'])): ?>
        <?php foreach ($optionsByCategory['Catering'] as $opt): ?>
          <?php
            $imageStyle = '';
            if (!empty($opt['image']) && !empty($opt['image_type'])) {
                $imageStyle = 'background-image: url(\'data:' . htmlspecialchars($opt['image_type']) . ';base64,' . base64_encode($opt['image']) . '\');';
            }
          ?>
          <div class="photo-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Catering" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="selectOption(this)">
            <div class="photo-card__image" style="<?= $imageStyle ?: 'background: linear-gradient(135deg, rgba(138,118,80,0.12) 0%, rgba(138,118,80,0.04) 100%);' ?>"></div>
            <div class="photo-card__content">
              <div class="photo-card__icon"><i class="fas fa-utensils"></i></div>
              <div class="photo-card__title"><?= htmlspecialchars($opt['name']) ?></div>
              <div class="photo-card__desc"><?= htmlspecialchars($opt['description'] ?: 'Choose this catering option') ?></div>
              <div class="photo-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No catering options available at the moment.</p>
      <?php endif; ?>
    </div>

    <!-- Pastries -->
    <div class="section-header" style="margin-top: 2rem;">
      <i class="fas fa-birthday-cake"></i>
      <h3>7. Choose Your Pastries</h3>
    </div>
    <div class="photo-card-grid" id="pastriesGrid">
      <?php if (!empty($optionsByCategory['Pastries'])): ?>
        <?php foreach ($optionsByCategory['Pastries'] as $opt): ?>
          <?php
            $imageStyle = '';
            if (!empty($opt['image']) && !empty($opt['image_type'])) {
                $imageStyle = 'background-image: url(\'data:' . htmlspecialchars($opt['image_type']) . ';base64,' . base64_encode($opt['image']) . '\');';
            }
          ?>
          <div class="photo-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Pastries" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="selectOption(this)">
            <div class="photo-card__image" style="<?= $imageStyle ?: 'background: linear-gradient(135deg, rgba(138,118,80,0.12) 0%, rgba(138,118,80,0.04) 100%);' ?>"></div>
            <div class="photo-card__content">
              <div class="photo-card__icon"><i class="fas fa-birthday-cake"></i></div>
              <div class="photo-card__title"><?= htmlspecialchars($opt['name']) ?></div>
              <div class="photo-card__desc"><?= htmlspecialchars($opt['description'] ?: 'Choose this pastry option') ?></div>
              <div class="photo-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No pastry options available at the moment.</p>
      <?php endif; ?>
    </div>

    <!-- Beverages -->
    <div class="section-header" style="margin-top: 2rem;">
      <i class="fas fa-wine-glass"></i>
      <h3>8. Choose Your Beverages</h3>
    </div>
    <div class="photo-card-grid" id="beveragesGrid">
      <?php if (!empty($optionsByCategory['Beverages'])): ?>
        <?php foreach ($optionsByCategory['Beverages'] as $opt): ?>
          <?php
            $imageStyle = '';
            if (!empty($opt['image']) && !empty($opt['image_type'])) {
                $imageStyle = 'background-image: url(\'data:' . htmlspecialchars($opt['image_type']) . ';base64,' . base64_encode($opt['image']) . '\');';
            }
          ?>
          <div class="photo-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Beverages" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="selectOption(this)">
            <div class="photo-card__image" style="<?= $imageStyle ?: 'background: linear-gradient(135deg, rgba(138,118,80,0.12) 0%, rgba(138,118,80,0.04) 100%);' ?>"></div>
            <div class="photo-card__content">
              <div class="photo-card__icon"><i class="fas fa-wine-glass"></i></div>
              <div class="photo-card__title"><?= htmlspecialchars($opt['name']) ?></div>
              <div class="photo-card__desc"><?= htmlspecialchars($opt['description'] ?: 'Choose this beverage option') ?></div>
              <div class="photo-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No beverage options available at the moment.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ===== ADD-ONS ===== -->
  <div>
    <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #8A7650; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
      <i class="fas fa-sparkles"></i> Add-Ons
    </h2>
    
    <div class="section-header">
      <i class="fas fa-plus-circle"></i>
      <h3>Optional Extras</h3>
    </div>
  </div>

  <div class="addons-grid" id="addonsGrid">
    <?php if (!empty($optionsByCategory['Add-ons'])): ?>
      <?php foreach ($optionsByCategory['Add-ons'] as $opt): ?>
        <?php $iconClass = $extraIconMap[$opt['name']] ?? $extraIconMap['Default']; ?>
        <div class="addon-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Add-ons" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="toggleAddon(this)">
          <input type="checkbox" onclick="event.stopPropagation()">
          <div class="addon-card__icon"><i class="fas <?= htmlspecialchars($iconClass) ?>"></i></div>
          <div class="addon-card__info">
            <div class="addon-card__name"><?= htmlspecialchars($opt['name']) ?></div>
            <div class="addon-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php elseif (!empty($optionsByCategory['Extras'])): ?>
      <?php foreach ($optionsByCategory['Extras'] as $opt): ?>
        <?php $iconClass = $extraIconMap[$opt['name']] ?? $extraIconMap['Default']; ?>
        <div class="addon-card" data-option-id="<?= $opt['option_id'] ?>" data-category="Extras" data-name="<?= htmlspecialchars($opt['name']) ?>" data-price="<?= (int)$opt['price'] ?>" onclick="toggleAddon(this)">
          <input type="checkbox" onclick="event.stopPropagation()">
          <div class="addon-card__icon"><i class="fas <?= htmlspecialchars($iconClass) ?>"></i></div>
          <div class="addon-card__info">
            <div class="addon-card__name"><?= htmlspecialchars($opt['name']) ?></div>
            <div class="addon-card__price">+₱<?= number_format((int)$opt['price'], 0, '.', ',') ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No extra options available at the moment.</p>
    <?php endif; ?>
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
  let selectedVenueDeco = null;
  let selectedColorCombination = null;
  let selectedVenue = null;
  let selectedFood = null;
  let selectedSweets = null; // Optional sweets selection
  let selectedCatering = null;
  let selectedPastries = null;
  let selectedBeverages = null;
  let selectedAddons = new Set();

  // Helper function to show toast message
  function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }

  function selectOption(element) {
    const category = element.getAttribute('data-category');
    const optionId = element.getAttribute('data-option-id');
    if (!category || !optionId) return;

    const cards = document.querySelectorAll(`.photo-card[data-category="${category}"]`);
    cards.forEach(card => card.classList.remove('selected'));
    element.classList.add('selected');

    if (category === 'Theme') selectedTheme = optionId;
    if (category === 'Venue Deco') selectedVenueDeco = optionId;
    if (category === 'Color Combinations') selectedColorCombination = optionId;
    if (category === 'Venue') selectedVenue = optionId;
    if (category === 'Food') selectedFood = optionId;
    if (category === 'Sweets') selectedSweets = optionId;
    if (category === 'Catering') selectedCatering = optionId;
    if (category === 'Pastries') selectedPastries = optionId;
    if (category === 'Beverages') selectedBeverages = optionId;

    showToast(`${category} selected: ${element.querySelector('.photo-card__title').innerText}`);
    updateSummary();
  }

  function toggleAddon(element) {
    const checkbox = element.querySelector('input[type="checkbox"]');
    const optionId = element.getAttribute('data-option-id');
    if (!optionId) return;

    checkbox.checked = !checkbox.checked;
    const addonName = element.querySelector('.addon-card__name').innerText;

    if (checkbox.checked) {
      selectedAddons.add(optionId);
      element.classList.add('selected');
      showToast(`Added: ${addonName}`);
    } else {
      selectedAddons.delete(optionId);
      element.classList.remove('selected');
      showToast(`Removed: ${addonName}`);
    }
    updateSummary();
  }

  function getSelectedItems() {
    const items = [];
    const selectedIds = [selectedTheme, selectedVenueDeco, selectedColorCombination, selectedVenue, selectedFood, selectedSweets, selectedCatering, selectedPastries, selectedBeverages].filter(Boolean);

    selectedIds.forEach(id => {
      const card = document.querySelector(`[data-option-id="${id}"]`);
      if (!card) return;
      items.push({
        category: card.dataset.category,
        name: card.dataset.name,
        price: parseInt(card.dataset.price) || 0,
      });
    });

    selectedAddons.forEach(addonId => {
      const addonCard = document.querySelector(`[data-option-id="${addonId}"]`);
      if (!addonCard) return;
      items.push({
        category: addonCard.dataset.category,
        name: addonCard.dataset.name,
        price: parseInt(addonCard.dataset.price) || 0,
      });
    });

    return items;
  }

  function renderOrderSummary() {
    const summaryList = document.getElementById('orderSummaryList');
    if (!summaryList) return; // Element doesn't exist, skip rendering
    
    const items = getSelectedItems();
    summaryList.innerHTML = '';

    if (items.length === 0) {
      summaryList.innerHTML = '<div class="summary-placeholder">Select a theme, venue, catering, and extras to preview your order here.</div>';
      return;
    }

    items.forEach(item => {
      const row = document.createElement('div');
      row.className = 'summary-row';
      row.innerHTML = `<span>${item.category}: ${item.name}</span><span>₱${item.price.toLocaleString()}</span>`;
      summaryList.appendChild(row);
    });

    const totalRow = document.createElement('div');
    totalRow.className = 'summary-row';
    totalRow.style.borderTop = '1px solid var(--border)';
    totalRow.style.paddingTop = '0.85rem';
    totalRow.style.marginTop = '0.85rem';
    totalRow.innerHTML = `<span><strong>Total</strong></span><span><strong>₱${calculateTotal().toLocaleString()}</strong></span>`;
    summaryList.appendChild(totalRow);
  }

  function calculateTotal() {
    let total = 0;

    const selectedIds = [selectedTheme, selectedVenueDeco, selectedColorCombination, selectedVenue, selectedFood, selectedSweets, selectedCatering].filter(Boolean);
    selectedIds.forEach(id => {
      const card = document.querySelector(`[data-option-id="${id}"]`);
      if (card) total += parseInt(card.getAttribute('data-price')) || 0;
    });

    selectedAddons.forEach(addonId => {
      const addonCard = document.querySelector(`[data-option-id="${addonId}"]`);
      if (addonCard) total += parseInt(addonCard.getAttribute('data-price')) || 0;
    });

    return total;
  }

  function countSelectedItems() {
    let count = 0;
    if (selectedTheme) count++;
    if (selectedVenue) count++;
    if (selectedCatering) count++;
    count += selectedAddons.size;
    return count;
  }

  function updateSummary() {
    const total = calculateTotal();
    const count = countSelectedItems();
    document.getElementById('totalPrice').innerHTML = `₱${total.toLocaleString()}`;
    document.getElementById('selectedCount').innerText = count;
    renderOrderSummary();
  }

  // Color Palette - Define available colors
  const colorPalette = [
    { name: 'Rose Gold', hex: '#B76E79' },
    { name: 'Blush Pink', hex: '#FFC0CB' },
    { name: 'Gold', hex: '#FFD700' },
    { name: 'Champagne', hex: '#F7E7CE' },
    { name: 'Burgundy', hex: '#800020' },
    { name: 'Ivory', hex: '#FFFFF0' },
    { name: 'Ocean Blue', hex: '#006994' },
    { name: 'Silver', hex: '#C0C0C0' },
    { name: 'Emerald', hex: '#50C878' },
    { name: 'Sage Green', hex: '#9DC183' },
    { name: 'Taupe', hex: '#B38B6D' },
    { name: 'Coral', hex: '#FF7F50' },
    { name: 'White', hex: '#FFFFFF' },
    { name: 'Cream', hex: '#FFFDD0' },
    { name: 'Lavender', hex: '#E6E6FA' },
    { name: 'Peach', hex: '#FFDAB9' },
    { name: 'Mint Green', hex: '#98FF98' },
    { name: 'Navy Blue', hex: '#000080' },
    { name: 'Rose Pink', hex: '#FF007F' },
    { name: 'Gold Accent', hex: '#DAA520' }
  ];

  let customColors = [];

  function initColorPicker() {
    const palette = document.getElementById('customColorPalette');
    palette.innerHTML = '';
    colorPalette.forEach(color => {
      const colorBtn = document.createElement('button');
      colorBtn.type = 'button';
      colorBtn.style.cssText = `
        width: 100%;
        padding: 1rem;
        background: ${color.hex};
        border: 3px solid transparent;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        font-size: 0.75rem;
        color: ${['Rose Gold', 'Blush Pink', 'Champagne', 'Ivory', 'White', 'Cream', 'Lavender', 'Peach', 'Mint Green'].includes(color.name) ? '#333' : '#fff'};
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      `;
      colorBtn.textContent = color.name;
      colorBtn.title = color.hex;
      colorBtn.onclick = (e) => {
        e.preventDefault();
        toggleColorSelection(colorBtn, color);
      };
      palette.appendChild(colorBtn);
    });
  }

  function toggleColorSelection(btn, color) {
    const isSelected = customColors.some(c => c.hex === color.hex);
    
    if (isSelected) {
      customColors = customColors.filter(c => c.hex !== color.hex);
      btn.style.border = '3px solid transparent';
    } else {
      if (customColors.length >= 5) {
        showToast('Maximum 5 colors allowed');
        return;
      }
      customColors.push(color);
      btn.style.border = '3px solid #8A7650';
    }

    updateColorDisplay();
  }

  function updateColorDisplay() {
    const display = document.getElementById('selectedColorsDisplay');
    const count = document.getElementById('selectedColorCount');
    
    count.textContent = customColors.length;
    display.innerHTML = '';

    if (customColors.length === 0) {
      display.innerHTML = '<span style="color: #999; font-style: italic;">No colors selected yet</span>';
      return;
    }

    customColors.forEach(color => {
      const colorBox = document.createElement('div');
      colorBox.style.cssText = `
        width: 60px;
        height: 60px;
        background: ${color.hex};
        border: 2px solid #8A7650;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.75rem;
        font-weight: 600;
        color: ${['Rose Gold', 'Blush Pink', 'Champagne', 'Ivory', 'White', 'Cream', 'Lavender', 'Peach', 'Mint Green'].includes(color.name) ? '#333' : '#fff'};
        position: relative;
      `;
      colorBox.title = color.name;
      colorBox.onclick = () => {
        const btn = Array.from(document.getElementById('customColorPalette').children).find(b => b.title === color.hex);
        toggleColorSelection(btn, color);
      };
      const label = document.createElement('small');
      label.textContent = color.name;
      colorBox.appendChild(label);
      display.appendChild(colorBox);
    });
  }

  function openColorModal() {
    const modal = document.getElementById('customColorModal');
    modal.style.display = 'flex';
    customColors = [];
    document.getElementById('customColorDescription').value = '';
    document.getElementById('selectedColorCount').textContent = '0';
    initColorPicker();
    updateColorDisplay();
  }

  function closeColorModal() {
    const modal = document.getElementById('customColorModal');
    modal.style.display = 'none';
    customColors = [];
  }

  function confirmCustomColors() {
    if (customColors.length < 3) {
      showToast('Please select at least 3 colors');
      return;
    }

    const description = document.getElementById('customColorDescription').value || 'Custom color combination';
    const colorNames = customColors.map(c => c.name).join(' + ');
    
    // Find the "Other" option
    const otherOption = document.querySelector('[data-name="Other"][data-category="Color Combinations"]');
    if (otherOption) {
      // Store custom colors in data attributes
      otherOption.dataset.customColors = JSON.stringify(customColors);
      otherOption.dataset.customDescription = colorNames + ' - ' + description;
      
      // Create visual representation of selected colors on the card
      const imageDiv = otherOption.querySelector('.photo-card__image');
      if (imageDiv) {
        let colorBars = '<div style="display: flex; width: 100%; height: 100%; gap: 0;">';
        customColors.forEach(color => {
          colorBars += `<div style="flex: 1; background-color: ${color.hex}; border-right: 1px solid white;"></div>`;
        });
        colorBars += '</div>';
        imageDiv.innerHTML = colorBars;
        imageDiv.style.overflow = 'hidden';
        imageDiv.style.display = 'flex';
      }
      
      // Update the description to show selected colors
      const descDiv = otherOption.querySelector('.photo-card__desc');
      if (descDiv) {
        descDiv.textContent = `Selected: ${colorNames}`;
      }
      
      // Directly select it without calling the wrapped selectOption function
      // (which would reopen the color modal)
      const category = otherOption.getAttribute('data-category');
      const cards = document.querySelectorAll(`.photo-card[data-category="${category}"]`);
      cards.forEach(card => card.classList.remove('selected'));
      otherOption.classList.add('selected');
      
      // Update the selection state
      selectedColorCombination = otherOption.getAttribute('data-option-id');
      updateSummary();
      
      showToast(`✓ Custom colors confirmed: ${colorNames}`);
    }

    closeColorModal();
  }

  // Modify selectOption to detect "Other" color option and "Organizer Choice"
  const originalSelectOption = selectOption;
  selectOption = function(element) {
    const name = element.getAttribute('data-name');
    const category = element.getAttribute('data-category');
    
    if (name === 'Other' && category === 'Color Combinations') {
      openColorModal();
      return;
    }
    
    if (name === 'Organizer Choice' && category === 'Color Combinations') {
      showOrganizerChoiceNotice();
      originalSelectOption.call(this, element);
      return;
    }
    
    originalSelectOption.call(this, element);
  };
  
  function showOrganizerChoiceNotice() {
    showToast('✓ Organizer will choose the colors for your event', 'info');
  }

  function resetSelection() {
    selectedTheme = null;
    selectedVenue = null;
    selectedCatering = null;
    selectedSweets = null;
    selectedAddons.clear();
    customColors = [];

    document.querySelectorAll('.photo-card').forEach(card => card.classList.remove('selected'));
    document.querySelectorAll('.addon-card').forEach(card => {
      card.classList.remove('selected');
      const checkbox = card.querySelector('input[type="checkbox"]');
      if (checkbox) checkbox.checked = false;
    });

    updateSummary();
    showToast('All selections have been reset');
  }

  function getDataAttr(element, dataAttr) {
    return element.getAttribute(`data-${dataAttr}`) || '';
  }

  function proceedToCheckout() {
    if (!selectedTheme || !selectedVenue || !selectedCatering) {
      showToast('Please select a theme, venue, and catering package before proceeding');
      return;
    }

    const cartItems = [];
    const selectedCards = [selectedTheme, selectedVenue, selectedCatering];

    selectedCards.forEach(id => {
      const card = document.querySelector(`[data-option-id="${id}"]`);
      if (!card) return;
      
      let cartItem = {
        category: card.getAttribute('data-category'),
        option_id: id,
        name: getDataAttr(card, 'name'),
        price: parseInt(getDataAttr(card, 'price')) || 0,
        type: 'custom',
        details: `${card.getAttribute('data-category')}: ${getDataAttr(card, 'name')}`
      };

      // Add custom colors if "Other" was selected
      if (card.getAttribute('data-name') === 'Other' && card.getAttribute('data-category') === 'Color Combinations') {
        cartItem.customColors = card.dataset.customColors;
        cartItem.customDescription = card.dataset.customDescription;
      }

      cartItems.push(cartItem);
    });

    // Add optional selections if they exist
    if (selectedVenueDeco) {
      const card = document.querySelector(`[data-option-id="${selectedVenueDeco}"]`);
      if (card) {
        cartItems.push({
          category: card.getAttribute('data-category'),
          option_id: selectedVenueDeco,
          name: getDataAttr(card, 'name'),
          price: parseInt(getDataAttr(card, 'price')) || 0,
          type: 'custom',
          details: `${card.getAttribute('data-category')}: ${getDataAttr(card, 'name')}`
        });
      }
    }

    if (selectedColorCombination) {
      const card = document.querySelector(`[data-option-id="${selectedColorCombination}"]`);
      if (card) {
        let item = {
          category: card.getAttribute('data-category'),
          option_id: selectedColorCombination,
          name: getDataAttr(card, 'name'),
          price: parseInt(getDataAttr(card, 'price')) || 0,
          type: 'custom',
          details: `${card.getAttribute('data-category')}: ${getDataAttr(card, 'name')}`
        };
        
        if (card.getAttribute('data-name') === 'Other') {
          item.customColors = card.dataset.customColors;
          item.customDescription = card.dataset.customDescription;
        }
        
        cartItems.push(item);
      }
    }

    if (selectedFood) {
      const card = document.querySelector(`[data-option-id="${selectedFood}"]`);
      if (card) {
        cartItems.push({
          category: card.getAttribute('data-category'),
          option_id: selectedFood,
          name: getDataAttr(card, 'name'),
          price: parseInt(getDataAttr(card, 'price')) || 0,
          type: 'custom',
          details: `${card.getAttribute('data-category')}: ${getDataAttr(card, 'name')}`
        });
      }
    }

    if (selectedSweets) {
      const card = document.querySelector(`[data-option-id="${selectedSweets}"]`);
      if (card) {
        cartItems.push({
          category: card.getAttribute('data-category'),
          option_id: selectedSweets,
          name: getDataAttr(card, 'name'),
          price: parseInt(getDataAttr(card, 'price')) || 0,
          type: 'custom',
          details: `${card.getAttribute('data-category')}: ${getDataAttr(card, 'name')}`
        });
      }
    }

    if (selectedPastries) {
      const card = document.querySelector(`[data-option-id="${selectedPastries}"]`);
      if (card) {
        cartItems.push({
          category: card.getAttribute('data-category'),
          option_id: selectedPastries,
          name: getDataAttr(card, 'name'),
          price: parseInt(getDataAttr(card, 'price')) || 0,
          type: 'custom',
          details: `${card.getAttribute('data-category')}: ${getDataAttr(card, 'name')}`
        });
      }
    }

    if (selectedBeverages) {
      const card = document.querySelector(`[data-option-id="${selectedBeverages}"]`);
      if (card) {
        cartItems.push({
          category: card.getAttribute('data-category'),
          option_id: selectedBeverages,
          name: getDataAttr(card, 'name'),
          price: parseInt(getDataAttr(card, 'price')) || 0,
          type: 'custom',
          details: `${card.getAttribute('data-category')}: ${getDataAttr(card, 'name')}`
        });
      }
    }

    selectedAddons.forEach(addonId => {
      const addonCard = document.querySelector(`[data-option-id="${addonId}"]`);
      if (!addonCard) return;
      cartItems.push({
        category: addonCard.getAttribute('data-category'),
        option_id: addonId,
        name: getDataAttr(addonCard, 'name'),
        price: parseInt(getDataAttr(addonCard, 'price')) || 0,
        type: 'custom',
        details: `Extras: ${getDataAttr(addonCard, 'name')}`
      });
    });

    // Store cart items in sessionStorage for wardrobe selection page
    sessionStorage.setItem('customizationItems', JSON.stringify({
      items: cartItems,
      programFlow: document.getElementById('programFlow')?.value || '',
      occasion: '<?= urlencode($occasion) ?>'
    }));

    // Redirect to wardrobe selection page
    window.location.href = '/index.php?route=wardrobe&occasion=<?= urlencode($occasion) ?>';
  }

  updateSummary();
</script>
</body>
</html>
<?php 
session_start(); 
$page = 'wardrobe';

// Load wardrobe options from database
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/app/models/Wardrobe.php';

$wardrobeModel = new Wardrobe();
$categories = $wardrobeModel->getCategories();
$wardrobesByCategory = $wardrobeModel->getAllByCategory();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Choose Your Wardrobe — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* ========================================
       SINTA - WARDROBE SELECTION STYLES
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

    /* Header */
    .wardrobe-header {
      background: var(--white);
      border-bottom: 1px solid var(--border);
      padding: 2rem 0;
      margin-bottom: 2rem;
    }

    .wardrobe-header__inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1.5rem;
    }

    .wardrobe-title {
      font-family: var(--serif);
      font-size: 2.5rem;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 0.5rem;
    }

    .wardrobe-subtitle {
      font-size: 1rem;
      color: var(--gray);
    }

    /* Main Content */
    .wardrobe-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1.5rem 2rem;
    }

    /* Search Bar */
    .wardrobe-search-bar {
      margin-bottom: 2rem;
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .wardrobe-search-input {
      flex: 1;
      min-width: 250px;
      padding: 0.75rem 1rem;
      border: 2px solid var(--border);
      border-radius: var(--radius-lg);
      font-size: 1rem;
      font-family: var(--sans);
      transition: all var(--t-fast);
    }

    .wardrobe-search-input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-pale);
    }

    /* Category Tabs */
    .wardrobe-category-tabs {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      border-bottom: 2px solid var(--border);
      overflow-x: auto;
      padding-bottom: 0.5rem;
    }

    .category-tab {
      padding: 0.75rem 1.25rem;
      background: transparent;
      border: none;
      border-bottom: 3px solid transparent;
      color: var(--gray);
      cursor: pointer;
      font-size: 0.95rem;
      font-weight: 500;
      transition: all var(--t-fast);
      white-space: nowrap;
    }

    .category-tab:hover {
      color: var(--primary);
    }

    .category-tab.active {
      border-bottom-color: var(--primary);
      color: var(--primary);
    }

    /* Wardrobes Grid */
    .wardrobes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .wardrobe-card {
      background: var(--white);
      border: 2px solid var(--border);
      border-radius: var(--radius-lg);
      overflow: hidden;
      cursor: pointer;
      transition: all var(--t-base);
      position: relative;
      display: flex;
      flex-direction: column;
      height: auto;
    }

    .wardrobe-card:hover {
      border-color: var(--primary);
      box-shadow: var(--shadow-md);
      transform: translateY(-5px);
    }

    .wardrobe-card.selected {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-pale);
    }

    .wardrobe-card__image {
      width: 100%;
      height: 320px;
      background: linear-gradient(135deg, var(--primary-pale), var(--cream));
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      color: var(--primary);
      flex-shrink: 0;
    }

    .wardrobe-card__content {
      padding: 0.8rem;
      display: flex;
      flex-direction: column;
      flex: 1;
      justify-content: space-between;
    }

    .wardrobe-card__name {
      font-weight: 600;
      font-size: 0.85rem;
      margin-bottom: 0.3rem;
      color: var(--dark);
      line-height: 1.3;
    }

    .wardrobe-card__desc {
      font-size: 0.7rem;
      color: var(--gray);
      margin-bottom: 0.4rem;
      min-height: auto;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      flex: 1;
    }

    .wardrobe-card__price {
      font-weight: 700;
      color: var(--primary);
      font-size: 0.85rem;
      margin-top: auto;
    }

    .wardrobe-card__height {
      font-size: 0.7rem;
      color: var(--gray-light);
      margin-top: 0.5rem;
      padding-top: 0.5rem;
      border-top: 1px solid var(--border);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-weight: 500;
    }

    .wardrobe-card__button {
      width: 100%;
      padding: 0.6rem 0.8rem;
      margin-top: 0.8rem;
      background: var(--primary);
      color: var(--white);
      border: none;
      border-radius: var(--radius-md);
      font-size: 0.75rem;
      font-weight: 600;
      cursor: pointer;
      transition: all var(--t-fast);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .wardrobe-card__button:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(138, 118, 80, 0.3);
    }

    .wardrobe-card__button:active {
      transform: translateY(0);
      box-shadow: 0 2px 6px rgba(138, 118, 80, 0.2);
    }
    .wardrobe-empty {
      text-align: center;
      padding: 3rem 1.5rem;
      background: var(--white);
      border: 2px dashed var(--border);
      border-radius: var(--radius-xl);
    }

    .wardrobe-empty__icon {
      font-size: 3rem;
      color: var(--gray-light);
      margin-bottom: 1rem;
    }

    .wardrobe-empty__text {
      color: var(--gray);
      font-size: 1rem;
    }

    /* Footer */
    .wardrobe-footer {
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
      box-shadow: var(--shadow-lg);
      z-index: 10;
      margin-top: 2rem;
    }

    .wardrobe-footer__summary {
      display: flex;
      gap: 2rem;
      flex-wrap: wrap;
    }

    .wardrobe-footer__item {
      display: flex;
      flex-direction: column;
    }

    .wardrobe-footer__label {
      font-size: 0.7rem;
      text-transform: uppercase;
      color: var(--gray);
      letter-spacing: 0.1em;
    }

    .wardrobe-footer__value {
      font-family: var(--serif);
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--primary);
    }

    .wardrobe-footer__actions {
      display: flex;
      gap: 1rem;
    }

    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: var(--radius-md);
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: all var(--t-fast);
      font-family: var(--sans);
    }

    .btn-primary {
      background: var(--primary);
      color: var(--white);
    }

    .btn-primary:hover {
      background: var(--primary-dark);
    }

    .btn-secondary {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--border);
    }

    .btn-secondary:hover {
      border-color: var(--primary);
      background: var(--primary-pale);
    }

    .btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    /* Wardrobe Detail Modal */
    .wardrobe-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.3s ease;
    }

    .wardrobe-modal.active {
      display: flex;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .wardrobe-modal__content {
      background: var(--white);
      border-radius: var(--radius-xl);
      max-width: 1000px;
      width: 90%;
      max-height: 85vh;
      overflow: hidden;
      animation: slideUp 0.3s ease;
      box-shadow: var(--shadow-lg);
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-template-rows: auto 1fr;
    }

    @keyframes slideUp {
      from {
        transform: translateY(20px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .wardrobe-modal__header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem;
      border-bottom: 1px solid var(--border);
      background: linear-gradient(135deg, var(--primary-pale), var(--cream));
      grid-column: 1 / -1;
    }

    .wardrobe-modal__title {
      font-family: var(--serif);
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--dark);
    }

    .wardrobe-modal__close {
      background: transparent;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--gray);
      padding: 0;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: var(--radius-md);
      transition: all var(--t-fast);
    }

    .wardrobe-modal__close:hover {
      background: var(--white);
      color: var(--primary);
    }

    .wardrobe-modal__body {
      padding: 1.5rem;
      grid-column: 2;
      grid-row: 2;
      overflow-y: auto;
      max-height: 100%;
    }

    .wardrobe-modal__image {
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, var(--primary-pale), var(--cream));
      border-radius: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 4rem;
      color: var(--primary);
      margin-bottom: 0;
      overflow: hidden;
      grid-column: 1;
      grid-row: 2;
      min-height: 500px;
    }

    .wardrobe-modal__image img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      object-position: center;
    }

    .wardrobe-modal__section {
      margin-bottom: 1.5rem;
    }

    .wardrobe-modal__section-title {
      font-size: 0.8rem;
      text-transform: uppercase;
      color: var(--gray);
      letter-spacing: 0.1em;
      margin-bottom: 0.5rem;
      font-weight: 700;
    }

    .wardrobe-modal__section-title-with-line {
      display: flex;
      align-items: center;
      gap: 0.8rem;
      font-size: 0.75rem;
      text-transform: uppercase;
      color: var(--primary);
      letter-spacing: 0.1em;
      margin-bottom: 1rem;
      font-weight: 700;
      padding-left: 0;
    }

    .wardrobe-modal__section-title-with-line::before {
      content: '';
      width: 3px;
      height: 18px;
      background: var(--primary);
      border-radius: 2px;
      display: inline-block;
    }

    .wardrobe-modal__section-title-with-line i {
      display: none;
    }

    .wardrobe-modal__section-content {
      font-size: 1rem;
      color: var(--dark);
      line-height: 1.6;
    }

    .wardrobe-modal__specs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      padding: 1.5rem;
      background: var(--cream);
      border-radius: var(--radius-md);
      margin-bottom: 1.5rem;
    }

    .wardrobe-modal__spec {
      display: flex;
      align-items: flex-start;
      gap: 0.8rem;
    }

    .wardrobe-modal__spec-icon {
      font-size: 1.5rem;
      color: var(--primary);
      margin-top: 0.2rem;
      flex-shrink: 0;
    }

    .wardrobe-modal__spec-info {
      display: flex;
      flex-direction: column;
    }

    .wardrobe-modal__spec-label {
      font-size: 0.7rem;
      text-transform: uppercase;
      color: var(--gray);
      letter-spacing: 0.05em;
      margin-bottom: 0.3rem;
      font-weight: 600;
    }

    .wardrobe-modal__spec-value {
      font-weight: 700;
      color: var(--primary);
      font-size: 0.95rem;
    }

    .wardrobe-modal__price-section {
      margin-bottom: 1.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid var(--border);
    }

    .wardrobe-modal__price-label-with-icon {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      font-size: 0.7rem;
      text-transform: uppercase;
      color: var(--gray);
      letter-spacing: 0.05em;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .wardrobe-modal__price-label-with-icon i {
      font-size: 1rem;
      color: var(--primary);
    }

    .wardrobe-modal__price {
      font-family: var(--serif);
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--primary);
    }

    .wardrobe-modal__actions {
      display: flex;
      gap: 0.8rem;
      margin-top: 0;
      padding-top: 0;
      border-top: none;
      flex-direction: row;
    }

    .wardrobe-modal__actions .btn {
      flex: 1;
      width: 100%;
      padding: 0.9rem 1.2rem;
      font-size: 0.95rem;
      font-weight: 600;
      border-radius: var(--radius-md);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all var(--t-fast);
    }

    .btn-outline {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
    }

    .btn-outline:hover {
      background: var(--primary-pale);
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Hover Preview Tooltip */
    .wardrobe-hover-preview {
      position: fixed;
      display: none;
      background: var(--white);
      border-radius: var(--radius-lg);
      box-shadow: 0 20px 80px rgba(0, 0, 0, 0.3);
      width: 90%;
      max-width: 500px;
      height: 90vh;
      max-height: 800px;
      z-index: 9999;
      overflow-y: auto;
      top: 50% !important;
      left: 50% !important;
      transform: translate(-50%, -50%);
    }

    .wardrobe-hover-preview.active {
      display: flex;
      flex-direction: column;
      pointer-events: auto;
      animation: fadeInScale 0.3s ease;
    }

    @keyframes slideUpTooltip {
      from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.9);
      }
      to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
      }
    }

    @keyframes fadeInScale {
      from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.9);
      }
      to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
      }
    }

    .wardrobe-hover-preview__image {
      width: 100%;
      height: 400px;
      background: linear-gradient(135deg, var(--primary-pale), var(--cream));
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 5rem;
      color: var(--primary);
      position: relative;
      overflow: hidden;
      flex-shrink: 0;
    }

    .wardrobe-hover-preview__image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .wardrobe-hover-preview__content {
      padding: 2rem;
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
      flex: 1;
      overflow-y: auto;
    }

    .wardrobe-hover-preview__name {
      font-weight: 700;
      font-size: 1.8rem;
      color: var(--dark);
      font-family: var(--serif);
    }

    .wardrobe-hover-preview__description {
      font-size: 0.95rem;
      color: var(--gray);
      line-height: 1.6;
      max-height: 120px;
      overflow-y: auto;
    }

    .wardrobe-hover-preview__specs-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.8rem;
    }

    .wardrobe-hover-preview__spec {
      font-size: 0.9rem;
    }

    .wardrobe-hover-preview__spec-label {
      text-transform: uppercase;
      color: var(--gray);
      letter-spacing: 0.05em;
      font-weight: 600;
      margin-bottom: 0.3rem;
      font-size: 0.8rem;
    }

    .wardrobe-hover-preview__spec-value {
      font-weight: 700;
      color: var(--primary);
      font-size: 1.1rem;
    }

    .wardrobe-hover-preview__price {
      font-family: var(--serif);
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--primary);
      border-top: 2px solid var(--border);
      padding-top: 1rem;
      margin-top: 1rem;
    }

    .wardrobe-hover-preview__cta {
      font-size: 0.95rem;
      color: white;
      text-align: center;
      padding: 1rem 1.5rem;
      background: var(--primary);
      border: none;
      border-radius: var(--radius-md);
      margin-top: 1.5rem;
      cursor: pointer;
      font-weight: 600;
      transition: all var(--t-fast);
      border-top: none;
    }

    .wardrobe-hover-preview__cta:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .wardrobe-hover-preview__cta:active {
      transform: translateY(0);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .wardrobe-modal__content {
        grid-template-columns: 1fr;
        max-width: 90%;
      }

      .wardrobe-modal__image {
        grid-column: 1;
        grid-row: 2;
        height: 400px;
      }

      .wardrobe-modal__body {
        grid-column: 1;
        grid-row: 3;
      }

      .wardrobe-title { font-size: 1.8rem; }
      .wardrobes-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
      }
      .wardrobe-footer {
        flex-direction: column;
        align-items: flex-start;
      }
      .wardrobe-footer__actions {
        width: 100%;
        flex-direction: column;
      }
      .wardrobe-footer__actions .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <?php include VIEW_PATH . '/user/nav.php'; ?>

  <div class="wardrobe-header">
    <div class="wardrobe-header__inner">
      <h1 class="wardrobe-title">Choose Your Wardrobe</h1>
      <p class="wardrobe-subtitle">Select the perfect attire for your event</p>
    </div>
  </div>

  <div class="wardrobe-container">
    <!-- Search Bar -->
    <div class="wardrobe-search-bar">
      <input 
        type="text" 
        class="wardrobe-search-input" 
        id="wardrobeSearch" 
        placeholder="Search wardrobes (e.g., 'dress', 'suit', 'gown')..."
      >
    </div>

    <!-- Category Tabs -->
    <div class="wardrobe-category-tabs">
      <button class="category-tab active" data-category="all">All Categories</button>
      <?php foreach ($categories as $category): ?>
        <button class="category-tab" data-category="<?php echo htmlspecialchars($category); ?>">
          <?php echo htmlspecialchars($category); ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Wardrobes Grid -->
    <div id="wardrobesContainer" class="wardrobes-grid">
      <!-- Wardrobes will be loaded here by JavaScript -->
    </div>

    <!-- Empty State -->
    <div id="wardrobeEmpty" class="wardrobe-empty" style="display: none;">
      <div class="wardrobe-empty__icon">
        <i class="fas fa-search"></i>
      </div>
      <p class="wardrobe-empty__text">No wardrobes found. Try adjusting your search.</p>
    </div>
  </div>

  <!-- Footer with Summary -->
  <div class="wardrobe-footer">
    <div class="wardrobe-footer__summary">
      <div class="wardrobe-footer__item">
        <span class="wardrobe-footer__label">Selected Wardrobe</span>
        <span class="wardrobe-footer__value" id="selectedWardrobeName">None</span>
      </div>
      <div class="wardrobe-footer__item">
        <span class="wardrobe-footer__label">Price</span>
        <span class="wardrobe-footer__value" id="selectedWardrobePrice">₱0</span>
      </div>
    </div>
    <div class="wardrobe-footer__actions">
      <button class="btn btn-secondary" id="backBtn">← Back to Customization</button>
      <button class="btn btn-primary" id="proceedBtn" disabled>Proceed to Checkout →</button>
    </div>
  </div>

  <!-- Wardrobe Hover Preview Tooltip -->
  <div id="hoverPreview" class="wardrobe-hover-preview">
    <div class="wardrobe-hover-preview__image" id="hoverImageContainer">
      <i class="fas fa-tuxedo"></i>
    </div>
    <div class="wardrobe-hover-preview__content">
      <div class="wardrobe-hover-preview__name" id="hoverName">Wardrobe Name</div>
      <div class="wardrobe-hover-preview__description" id="hoverDesc">Description goes here</div>
      
      <div class="wardrobe-hover-preview__specs-row">
        <div class="wardrobe-hover-preview__spec">
          <div class="wardrobe-hover-preview__spec-label">Category</div>
          <div class="wardrobe-hover-preview__spec-value" id="hoverCategory">-</div>
        </div>
        <div class="wardrobe-hover-preview__spec">
          <div class="wardrobe-hover-preview__spec-label">Stock</div>
          <div class="wardrobe-hover-preview__spec-value" id="hoverStock">-</div>
        </div>
      </div>

      <div class="wardrobe-hover-preview__specs-row">
        <div class="wardrobe-hover-preview__spec">
          <div class="wardrobe-hover-preview__spec-label">Duration</div>
          <div class="wardrobe-hover-preview__spec-value" id="hoverDuration">-</div>
        </div>
        <div class="wardrobe-hover-preview__spec">
          <div class="wardrobe-hover-preview__spec-label">Sizes</div>
          <div class="wardrobe-hover-preview__spec-value" id="hoverSizes">-</div>
        </div>
      </div>

      <div class="wardrobe-hover-preview__price" id="hoverPrice">₱0</div>
      <button class="wardrobe-hover-preview__cta" id="hoverSelectBtn" onclick="selectWardrobeFromHover()">Select This Item</button>
    </div>
  </div>

  <!-- Wardrobe Detail Modal -->
  <div id="wardrobeModal" class="wardrobe-modal">
    <div class="wardrobe-modal__content">
      <div class="wardrobe-modal__header">
        <h2 class="wardrobe-modal__title" id="modalTitle">Wardrobe Details</h2>
        <button class="wardrobe-modal__close" id="modalClose">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="wardrobe-modal__image" id="modalImage">
        <i class="fas fa-tuxedo"></i>
      </div>
      <div class="wardrobe-modal__body">
        <div class="wardrobe-modal__section">
          <div class="wardrobe-modal__section-title-with-line">
            <i class="fas fa-align-left"></i>
            <span>Description</span>
          </div>
          <div class="wardrobe-modal__section-content" id="modalDescription">
            No description available
          </div>
        </div>

        <div class="wardrobe-modal__specs">
          <div class="wardrobe-modal__spec">
            <div class="wardrobe-modal__spec-icon">
              <i class="fas fa-calendar"></i>
            </div>
            <div class="wardrobe-modal__spec-info">
              <span class="wardrobe-modal__spec-label">Category</span>
              <span class="wardrobe-modal__spec-value" id="modalCategory">-</span>
            </div>
          </div>
          <div class="wardrobe-modal__spec">
            <div class="wardrobe-modal__spec-icon">
              <i class="fas fa-box"></i>
            </div>
            <div class="wardrobe-modal__spec-info">
              <span class="wardrobe-modal__spec-label">Stock Available</span>
              <span class="wardrobe-modal__spec-value" id="modalStock">-</span>
            </div>
          </div>
          <div class="wardrobe-modal__spec">
            <div class="wardrobe-modal__spec-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="wardrobe-modal__spec-info">
              <span class="wardrobe-modal__spec-label">Rental Duration</span>
              <span class="wardrobe-modal__spec-value" id="modalDuration">-</span>
            </div>
          </div>
          <div class="wardrobe-modal__spec">
            <div class="wardrobe-modal__spec-icon">
              <i class="fas fa-ruler"></i>
            </div>
            <div class="wardrobe-modal__spec-info">
              <span class="wardrobe-modal__spec-label">Available Sizes</span>
              <span class="wardrobe-modal__spec-value" id="modalSizes">-</span>
            </div>
          </div>
        </div>

        <div class="wardrobe-modal__price-section">
          <div class="wardrobe-modal__price-label-with-icon">
            <i class="fas fa-tag"></i>
            <span>Rental Price</span>
          </div>
          <div class="wardrobe-modal__price" id="modalPrice">₱0</div>
        </div>

        <div class="wardrobe-modal__actions">
          <button class="btn btn-outline" id="modalSelectBtn"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
          <button class="btn btn-primary" id="modalCloseBtn"><i class="fas fa-times"></i> Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let selectedWardrobes = []; // Array of selected wardrobes for multiple selections
    let allWardrobes = [];
    let currentFilter = 'all';
    let modalWardrobe = null;
    let hoverPreviewTimeout = null;

    // Load wardrobes on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadWardrobes();
      setupEventListeners();
      setupModalListeners();
      
      // Check for wardrobe in session/cart
      const urlParams = new URLSearchParams(window.location.search);
      const wardrobeId = urlParams.get('wardrobe_id');
      if (wardrobeId) {
        selectWardrobeById(parseInt(wardrobeId));
      }
    });

    // Setup hover preview listener
    function setupHoverPreviewListener() {
      const preview = document.getElementById('hoverPreview');
      
      preview.addEventListener('mouseenter', function() {
        if (hoverPreviewTimeout) {
          clearTimeout(hoverPreviewTimeout);
        }
      });

      preview.addEventListener('mouseleave', function() {
        hideHoverPreview();
      });

      preview.addEventListener('click', function(e) {
        if (modalWardrobe) {
          openModal(modalWardrobe);
        }
      });
    }

    // Show hover preview on card hover
    function showHoverPreview(wardrobe, event) {
      console.log('✓ showHoverPreview called for:', wardrobe.name);
      const preview = document.getElementById('hoverPreview');

      if (!preview) {
        console.error('  ERROR: hoverPreview element not found!');
        return;
      }

      // Update preview content
      document.getElementById('hoverName').textContent = escapeHtml(wardrobe.name);
      document.getElementById('hoverDesc').textContent = escapeHtml(wardrobe.description || 'No description available');
      document.getElementById('hoverCategory').textContent = wardrobe.category || '-';
      document.getElementById('hoverStock').textContent = (wardrobe.availability_count || '-') + ' pcs';
      document.getElementById('hoverDuration').textContent = (wardrobe.rental_duration_days || '-') + 'd';
      
      // Format sizes as badges for hover preview
      const sizesText = wardrobe.sizes_available || '-';
      const hoverSizesElement = document.getElementById('hoverSizes');
      if (sizesText !== '-') {
        const sizes = sizesText.split(',').map(s => s.trim()).filter(s => s);
        hoverSizesElement.innerHTML = sizes.map(size => `<span style="display: inline-block; background: #f0f0f0; padding: 3px 8px; border-radius: 12px; font-size: 0.85rem; margin-right: 4px; margin-bottom: 4px; font-weight: 500;">${escapeHtml(size)}</span>`).join('');
      } else {
        hoverSizesElement.textContent = '-';
      }
      
      document.getElementById('hoverPrice').textContent = '₱' + parseFloat(wardrobe.rental_price || wardrobe.price || 0).toFixed(2);

      const imageContainer = document.getElementById('hoverImageContainer');
      if (wardrobe.image && wardrobe.image_type) {
        imageContainer.innerHTML = `<img src="data:${wardrobe.image_type};base64,${wardrobe.image}" alt="${escapeHtml(wardrobe.name)}">`;
      } else {
        imageContainer.innerHTML = '<i class="fas fa-tuxedo"></i>';
      }

      // Position centered on screen
      preview.style.top = '50%';
      preview.style.left = '50%';
      
      // Force reflow before adding active class
      void preview.offsetHeight;
      
      preview.classList.add('active');

      console.log('  Preview centered on screen and active');

      // Store wardrobe for click handler
      modalWardrobe = wardrobe;

      // Clear existing timeout
      if (hoverPreviewTimeout) {
        clearTimeout(hoverPreviewTimeout);
      }
    }

    // Hide hover preview
    function hideHoverPreview() {
      const preview = document.getElementById('hoverPreview');
      console.log('hideHoverPreview called, will remove active class after 200ms');
      hoverPreviewTimeout = setTimeout(function() {
        preview.classList.remove('active');
        modalWardrobe = null;
        console.log('Active class removed from preview');
      }, 200);
    }

    function setupModalListeners() {
      const modal = document.getElementById('wardrobeModal');
      const closeBtn = document.getElementById('modalClose');
      const closeBtnBottom = document.getElementById('modalCloseBtn');
      const selectBtn = document.getElementById('modalSelectBtn');

      closeBtn.addEventListener('click', closeModal);
      closeBtnBottom.addEventListener('click', closeModal);
      selectBtn.addEventListener('click', function() {
        if (modalWardrobe) {
          selectWardrobe(modalWardrobe.wardrobe_id, allWardrobes);
          closeModal();
        }
      });

      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModal();
        }
      });
    }

    function openModal(wardrobe) {
      modalWardrobe = wardrobe;
      
      document.getElementById('modalTitle').textContent = wardrobe.name;
      document.getElementById('modalDescription').textContent = wardrobe.description || 'No description available';
      document.getElementById('modalCategory').textContent = wardrobe.category || '-';
      document.getElementById('modalStock').textContent = (wardrobe.availability_count || '-') + ' pcs';
      document.getElementById('modalDuration').textContent = (wardrobe.rental_duration_days || '-') + ' day(s)';
      
      // Format sizes as badges
      const sizesText = wardrobe.sizes_available || '-';
      const sizesElement = document.getElementById('modalSizes');
      if (sizesText !== '-') {
        const sizes = sizesText.split(',').map(s => s.trim()).filter(s => s);
        sizesElement.innerHTML = sizes.map(size => `<span style="display: inline-block; background: #f0f0f0; padding: 4px 10px; border-radius: 12px; font-size: 0.9rem; margin-right: 4px; margin-bottom: 4px; font-weight: 500;">${escapeHtml(size)}</span>`).join('');
      } else {
        sizesElement.textContent = '-';
      }
      
      document.getElementById('modalPrice').textContent = '₱' + parseFloat(wardrobe.rental_price || wardrobe.price || 0).toFixed(2);
      
      const imageContainer = document.getElementById('modalImage');
      if (wardrobe.image && wardrobe.image_type) {
        imageContainer.innerHTML = `<img src="data:${wardrobe.image_type};base64,${wardrobe.image}" alt="${escapeHtml(wardrobe.name)}">`;
      } else {
        imageContainer.innerHTML = '<i class="fas fa-tuxedo"></i>';
      }

      document.getElementById('wardrobeModal').classList.add('active');
    }

    function closeModal() {
      document.getElementById('wardrobeModal').classList.remove('active');
      modalWardrobe = null;
    }

    function openModalForCard(wardrobeId) {
      const wardrobe = allWardrobes.find(w => String(w.wardrobe_id) === String(wardrobeId));
      if (wardrobe) {
        openModal(wardrobe);
      }
    }

    function setupEventListeners() {
      // Search input
      document.getElementById('wardrobeSearch').addEventListener('input', function(e) {
        const query = e.target.value.trim();
        if (query.length >= 2) {
          searchWardrobes(query);
        } else {
          filterByCategory(currentFilter);
        }
      });

      // Category tabs
      document.querySelectorAll('.category-tab').forEach(tab => {
        tab.addEventListener('click', function() {
          document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
          this.classList.add('active');
          currentFilter = this.dataset.category;
          filterByCategory(currentFilter);
          document.getElementById('wardrobeSearch').value = '';
        });
      });

      // Buttons
      document.getElementById('backBtn').addEventListener('click', function() {
        // Go back to customize page
        const urlParams = new URLSearchParams(window.location.search);
        const occasion = urlParams.get('occasion') || 'wedding';
        window.location.href = '/index.php?route=customize&occasion=' + occasion;
      });

      document.getElementById('proceedBtn').addEventListener('click', function() {
        if (selectedWardrobes.length > 0) {
          // Get customization items from sessionStorage
          const customizationData = sessionStorage.getItem('customizationItems');
          const customizationItems = customizationData ? JSON.parse(customizationData) : {};

          // Combine wardrobes with customization items
          const allItems = customizationItems.items || [];
          
          // Add all selected wardrobes to cart
          selectedWardrobes.forEach(wardrobe => {
            allItems.push({
              category: 'Wardrobe',
              name: wardrobe.name,
              wardrobe_id: wardrobe.wardrobe_id,
              price: parseFloat(wardrobe.rental_price || wardrobe.price || 0),
              type: 'wardrobe',
              details: 'Wardrobe: ' + wardrobe.name
            });
          });

          // Create form to submit to checkout
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '/index.php?route=checkout&occasion=' + (customizationItems.occasion || 'wedding');

          const cartDataInput = document.createElement('input');
          cartDataInput.type = 'hidden';
          cartDataInput.name = 'cart_data';
          cartDataInput.value = JSON.stringify({
            items: allItems,
            programFlow: customizationItems.programFlow || ''
          });

          form.appendChild(cartDataInput);
          document.body.appendChild(form);
          form.submit();

          // Clean up session storage
          sessionStorage.removeItem('customizationItems');
        }
      });
    }

    function loadWardrobes() {
      fetch('/api-wardrobe.php?action=getAll')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            allWardrobes = data.data;
            displayWardrobes(allWardrobes);
          }
        })
        .catch(error => console.error('Error loading wardrobes:', error));
    }

    function filterByCategory(category) {
      if (category === 'all') {
        displayWardrobes(allWardrobes);
      } else {
        const filtered = allWardrobes.filter(w => w.category === category);
        displayWardrobes(filtered);
      }
    }

    function searchWardrobes(query) {
      fetch('/api-wardrobe.php?action=search&q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displayWardrobes(data.data);
          }
        })
        .catch(error => console.error('Error searching wardrobes:', error));
    }

    function displayWardrobes(wardrobes) {
      const container = document.getElementById('wardrobesContainer');
      const emptyState = document.getElementById('wardrobeEmpty');
      
      console.log('displayWardrobes called with', wardrobes.length, 'wardrobes');
      
      if (wardrobes.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
      }
      
      container.style.display = 'grid';
      emptyState.style.display = 'none';
      
      container.innerHTML = wardrobes.map(wardrobe => `
        <div class="wardrobe-card ${selectedWardrobes.find(w => w.wardrobe_id == wardrobe.wardrobe_id) ? 'selected' : ''}" 
             data-wardrobe-id="${wardrobe.wardrobe_id}">
          <div class="wardrobe-card__image">
            ${wardrobe.image && wardrobe.image_type ? 
              `<img src="data:${wardrobe.image_type};base64,${wardrobe.image}" alt="${escapeHtml(wardrobe.name)}" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;">` :
              `<i class="fas fa-tuxedo" style="cursor: pointer;"></i>`
            }
          </div>
          <div class="wardrobe-card__content">
            <div class="wardrobe-card__name">${escapeHtml(wardrobe.name)}</div>
            <div class="wardrobe-card__desc">${escapeHtml(wardrobe.description || '')}</div>
            <div class="wardrobe-card__price">₱${parseFloat(wardrobe.rental_price || wardrobe.price || 0).toFixed(2)}</div>
            <button class="wardrobe-card__button" onclick="event.stopPropagation(); openModalForCard(${wardrobe.wardrobe_id})">View Details</button>
          </div>
        </div>
      `).join('');
      
      console.log('Cards rendered. Attaching hover listeners...');
      
      // Add click and hover handlers
      container.querySelectorAll('.wardrobe-card').forEach((card, index) => {
        const wardrobeId = card.dataset.wardrobeId; // Keep as string for comparison
        const wardrobe = wardrobes.find(w => String(w.wardrobe_id) === String(wardrobeId));

        console.log('Attaching listeners to card', index, 'wardrobeId:', wardrobeId, 'found:', !!wardrobe);

        if (wardrobe) {
          // Store wardrobe data on the element
          card.dataset.wardrobeData = JSON.stringify(wardrobe);

          // Click listener - select wardrobe when clicking anywhere on the card (except button)
          card.addEventListener('click', function(e) {
            if (!e.target.closest('.wardrobe-card__button')) {
              selectWardrobe(wardrobeId, wardrobes);
            }
          });
        }
      });
      
      console.log('Listeners attached successfully');
    }

    function selectWardrobe(wardrobeId, wardrobes) {
      const wardrobe = wardrobes.find(w => String(w.wardrobe_id) === String(wardrobeId));
      if (!wardrobe) return;

      const selectedCard = document.querySelector(`[data-wardrobe-id="${wardrobeId}"]`);
      if (!selectedCard) return;

      // Toggle selection
      const isSelected = selectedCard.classList.toggle('selected');
      
      // Add or remove from selectedWardrobes array
      if (isSelected) {
        if (!selectedWardrobes.find(w => w.wardrobe_id == wardrobeId)) {
          selectedWardrobes.push(wardrobe);
        }
      } else {
        selectedWardrobes = selectedWardrobes.filter(w => w.wardrobe_id != wardrobeId);
      }
      
      console.log('Selected wardrobes:', selectedWardrobes.length, selectedWardrobes.map(w => w.name));
      updateFooter();
    }

    function updateFooter() {
      const selectedCount = selectedWardrobes.length;
      const selectedWardrobeDiv = document.getElementById('selectedWardrobeName');
      const selectedPriceDiv = document.getElementById('selectedWardrobePrice');
      const checkoutBtn = document.getElementById('proceedBtn');
      
      if (selectedWardrobeDiv && selectedPriceDiv && checkoutBtn) {
        if (selectedCount === 0) {
          selectedWardrobeDiv.textContent = 'None';
          selectedPriceDiv.textContent = '₱0';
          checkoutBtn.disabled = true;
        } else if (selectedCount === 1) {
          selectedWardrobeDiv.textContent = selectedWardrobes[0].name;
          selectedPriceDiv.textContent = '₱' + parseFloat(selectedWardrobes[0].rental_price || selectedWardrobes[0].price || 0).toFixed(2);
          checkoutBtn.disabled = false;
        } else {
          selectedWardrobeDiv.textContent = selectedCount + ' items selected';
          const totalPrice = selectedWardrobes.reduce((sum, w) => sum + parseFloat(w.rental_price || w.price || 0), 0);
          selectedPriceDiv.textContent = '₱' + totalPrice.toFixed(2);
          checkoutBtn.disabled = false;
        }
      }
    }

    function selectWardrobeFromHover() {
      if (modalWardrobe) {
        selectWardrobe(modalWardrobe.wardrobe_id, allWardrobes);
      }
    }

    function selectWardrobeById(wardrobeId) {
      const wardrobe = allWardrobes.find(w => String(w.wardrobe_id) === String(wardrobeId));
      if (wardrobe) {
        selectWardrobe(wardrobeId, allWardrobes);
      }
    }

    function calculateTotal() {
      return selectedWardrobe ? parseFloat(selectedWardrobe.price) : 0;
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
</body>
</html>

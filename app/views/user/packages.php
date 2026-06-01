<?php 
session_start(); 
$page = 'packages';

// Get occasion from URL parameter
$occasion = $_GET['occasion'] ?? 'wedding';
$occasionLabel = ucfirst($occasion);

// Load models
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Package.php';
require_once ROOT_PATH . '/app/models/Occasion.php';

$db = new Database();
$packageModel = new Package();
$occasionModel = new Occasion();

// Get occasion ID from occasion name
$occasionData = $db->query("SELECT occasion_id FROM occasions_tbl WHERE events = '" . $db->real_escape_string($occasion) . "' LIMIT 1");
$occasionId = null;
if ($occasionData && $occasionData->num_rows > 0) {
    $row = $occasionData->fetch_assoc();
    $occasionId = $row['occasion_id'];
}

// Get all packages for this occasion
$packages = $packageModel->getAll($occasionId);
if (!$packages) {
    $packages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Packages for <?= htmlspecialchars($occasionLabel) ?> — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
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
      line-height: 1.6;
      font-size: 1rem;
    }

    .packages-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem 1.5rem;
    }

    .packages-header {
      text-align: center;
      margin-bottom: 3rem;
    }

    .packages-header h1 {
      font-family: var(--serif);
      font-size: 2.5rem;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 0.5rem;
    }

    .packages-header p {
      color: var(--gray);
      font-size: 1.1rem;
    }

    .breadcrumb {
      margin-bottom: 2rem;
      font-size: 0.95rem;
      color: var(--gray);
    }

    .breadcrumb a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .breadcrumb a:hover {
      text-decoration: underline;
    }

    .packages-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
    }

    .package-card {
      background: white;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: all var(--t-base);
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .package-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-4px);
    }

    .package-image {
      width: 100%;
      height: 200px;
      background: linear-gradient(135deg, var(--primary-pale), rgba(138, 118, 80, 0.05));
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      font-size: 3rem;
      overflow: hidden;
    }

    .package-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .package-content {
      padding: 1.5rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .package-name {
      font-family: var(--serif);
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 0.5rem;
    }

    .package-description {
      color: var(--gray);
      font-size: 0.95rem;
      margin-bottom: 1rem;
      flex: 1;
    }

    .package-features {
      list-style: none;
      margin: 1rem 0;
      padding: 0;
    }

    .package-features li {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
      color: var(--gray);
    }

    .package-features i {
      color: var(--primary);
      width: 18px;
    }

    .package-price {
      font-family: var(--serif);
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--primary);
      margin: 1rem 0;
    }

    .package-price small {
      font-size: 0.7em;
      color: var(--gray);
      font-weight: 400;
    }

    .customize-btn {
      background: var(--primary);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: var(--radius-md);
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: background var(--t-base);
      text-decoration: none;
      display: inline-block;
      text-align: center;
      margin-top: auto;
    }

    .customize-btn:hover {
      background: var(--primary-dark);
    }

    .no-packages {
      text-align: center;
      padding: 3rem 2rem;
      color: var(--gray);
    }

    .no-packages i {
      font-size: 3rem;
      color: var(--primary-pale);
      margin-bottom: 1rem;
    }

    .back-link {
      display: inline-block;
      margin-bottom: 1.5rem;
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      transition: color var(--t-fast);
    }

    .back-link:hover {
      color: var(--primary-dark);
    }

    @media (max-width: 768px) {
      .packages-container {
        padding: 1.5rem 1rem;
      }

      .packages-header h1 {
        font-size: 2rem;
      }

      .packages-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <?php require ROOT_PATH . '/app/views/user/nav.php'; ?>

  <main class="packages-container">
    <div class="breadcrumb">
      <a href="index.php?route=occasions"><i class="fas fa-arrow-left"></i> Back to Occasions</a>
      <span> / </span>
      <strong><?= htmlspecialchars($occasionLabel) ?> Packages</strong>
    </div>

    <div class="packages-header">
      <h1>Choose Your <?= htmlspecialchars($occasionLabel) ?> Package</h1>
      <p>Select a package below and customize it to match your vision</p>
    </div>

    <?php if (!empty($packages)): ?>
      <div class="packages-grid">
        <?php foreach ($packages as $package): ?>
          <div class="package-card">
            <div class="package-image">
              <?php if (!empty($package['image'])): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($package['image']) ?>" alt="<?= htmlspecialchars($package['name']) ?>" />
              <?php else: ?>
                <i class="fas fa-gift"></i>
              <?php endif; ?>
            </div>

            <div class="package-content">
              <h3 class="package-name"><?= htmlspecialchars($package['name']) ?></h3>
              <p class="package-description"><?= htmlspecialchars($package['description']) ?></p>

              <?php if (!empty($package['features'])): ?>
                <ul class="package-features">
                  <?php 
                    $features = explode(',', $package['features']);
                    foreach (array_slice($features, 0, 3) as $feature): 
                  ?>
                    <li>
                      <i class="fas fa-check-circle"></i>
                      <?= htmlspecialchars(trim($feature)) ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>

              <div class="package-price">
                ₱<?= number_format($package['price'], 2) ?>
              </div>

              <a href="index.php?route=customize&occasion=<?= urlencode($occasion) ?>&package=<?= (int)$package['package_id'] ?>" class="customize-btn">
                <i class="fas fa-magic"></i> Customize This Package
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="no-packages">
        <i class="fas fa-inbox"></i>
        <h3>No Packages Available</h3>
        <p>Packages for <?= htmlspecialchars($occasionLabel) ?> are coming soon! Please check back later.</p>
        <a href="index.php?route=occasions" class="back-link">Browse Other Occasions</a>
      </div>
    <?php endif; ?>
  </main>

  <?php require ROOT_PATH . '/app/views/user/footer.php'; ?>
</body>
</html>

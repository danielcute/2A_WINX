<?php 
require_once dirname(__DIR__, 2) . '/models/Package.php';
require_once dirname(__DIR__, 2) . '/models/Occasion.php';

$page = 'packages';
$occasion = $_GET['occasion'] ?? 'wedding';

$packageModel = new Package();
$occasionModel = new Occasion();

// Get occasion data
$occasionData = $occasionModel->findByName($occasion);
$occasionId = $occasionData ? $occasionData['occasion_id'] : null;

// Get packages for this occasion
$packages = $packageModel->getOccasionPackages($occasionId);

$occasionLabel = $occasionData ? $occasionData['events'] : ucfirst($occasion);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($occasionLabel) ?> Packages — Sinta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
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
        .btn--primary { background: #8A7650; color: white; }
        .btn--primary:hover { background: #6B5A3E; transform: translateY(-2px); }
        .btn--ghost { background: transparent; border: 1.5px solid var(--border); }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: #2e7d32; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; z-index: 3000; animation: slideIn 0.3s ease; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @media (max-width: 900px) {
            .pkg-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .pkg-grid { grid-template-columns: 1fr; }
            .pkg-main { padding: 1.5rem 1rem 4rem; padding-top: calc(76px + 1rem); }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<main class="pkg-main">
    <div class="breadcrumb">
        <a href="/SINTA/public/index.php?route=occasions">Occasions</a> <span>/</span>
        <span><?= htmlspecialchars($occasionLabel) ?></span>
    </div>
    
    <div class="pkg-header">
        <div>
            <div class="eyebrow"><span class="rule"></span> Step 2 of 3</div>
            <h1><?= htmlspecialchars($occasionLabel) ?> <em>Packages</em></h1>
            <p>Choose a package or customize your own event</p>
        </div>
    </div>
    
    <div class="pkg-grid">
        <?php if (empty($packages)): ?>
            <div style="text-align: center; padding: 3rem; grid-column: span 3;">
                <p>No packages available for this occasion yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <?php foreach ($packages as $package): ?>
                <div class="pkg-card">
                    <?php if (!empty($package['images'])): ?>
                        <div class="pkg-card__img" style="background-image: url('<?= htmlspecialchars($package['images'][0]) ?>')"></div>
                    <?php else: ?>
                        <div class="pkg-card__img" style="background-image: url('/SINTA/public/assets/img/package-placeholder.jpg')"></div>
                    <?php endif; ?>
                    <div class="pkg-card__body">
                        <h3><?= htmlspecialchars($package['name']) ?></h3>
                        <p class="pkg-card__desc"><?= htmlspecialchars(substr($package['description'] ?? '', 0, 120)) ?></p>
                        <?php if (!empty($package['inclusions'])): ?>
                            <ul class="pkg-features">
                                <?php foreach (array_slice($package['inclusions'], 0, 4) as $inclusion): ?>
                                    <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($inclusion) ?></li>
                                <?php endforeach; ?>
                                <?php if (count($package['inclusions']) > 4): ?>
                                    <li><i class="fas fa-plus-circle"></i> And more...</li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="pkg-card__footer">
                            <div class="pkg-price">₱<?= number_format($package['price'], 0) ?></div>
                            <button class="btn btn--primary" onclick="addToCart(<?= htmlspecialchars(json_encode($package)) ?>)">Select Package</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Customize CTA -->
    <div class="customize-cta" style="text-align: center; margin-top: 3rem; padding: 2rem; background: var(--bg-warm); border-radius: var(--radius-xl);">
        <div class="eyebrow"><span class="rule"></span> Want Something Unique?</div>
        <h3 style="margin: 0.5rem 0;">Build Your <em>Own Package</em></h3>
        <p style="margin-bottom: 1rem;">Can't find what you're looking for? Create a completely customized event tailored to your vision.</p>
        <a href="/SINTA/public/index.php?route=customize&occasion=<?= urlencode($occasion) ?>" class="btn btn--primary btn--lg">
            <i class="fas fa-magic"></i> Start Customizing
        </a>
    </div>
</main>

<script>
let cart = JSON.parse(sessionStorage.getItem('checkoutCart') || '[]');

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function addToCart(packageData) {
    cart.push({ 
        id: packageData.package_id,
        name: packageData.name, 
        price: parseFloat(packageData.price), 
        type: 'package',
        inclusions: packageData.inclusions || []
    });
    
    sessionStorage.setItem('checkoutCart', JSON.stringify(cart));
    showToast('✓ ' + packageData.name + ' added to cart!');
    
    if (confirm('Proceed to checkout?')) {
        window.location.href = '/SINTA/public/index.php?route=checkout';
    }
}
</script>
</body>
</html>
<?php
/**
 * USER PACKAGES PAGE - Modern Minimalist
 * Location: app/views/user/packages-modern.php
 */

require_once ROOT_PATH . '/app/models/Package.php';
require_once ROOT_PATH . '/app/models/Occasion.php';

$pageTitle = 'Packages';
$packageModel = new Package();
$occasionModel = new Occasion();

// Get filters
$occasionId = $_GET['occasion'] ?? null;
$searchTerm = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'price_asc';

// Fetch packages
$packages = $packageModel->getAll($occasionId);

// Apply search filter
if ($searchTerm) {
    $searchTerm = strtolower($searchTerm);
    $packages = array_filter($packages, fn($p) => 
        strpos(strtolower($p['name']), $searchTerm) !== false ||
        strpos(strtolower($p['description']), $searchTerm) !== false
    );
}

// Apply sorting
if ($sortBy === 'price_asc') {
    usort($packages, fn($a, $b) => $a['price'] <=> $b['price']);
} elseif ($sortBy === 'price_desc') {
    usort($packages, fn($a, $b) => $b['price'] <=> $a['price']);
}

// Get all occasions for filter
$occasions = $occasionModel->getAll();

?>
<?php include 'header-modern.php'; ?>

<main>
    <div class="container" style="padding: 3rem 1.5rem;">
        <!-- PAGE HEADER -->
        <div class="section-header" style="margin-bottom: 2rem;">
            <h1 class="section-title">Our Packages</h1>
            <p class="section-subtitle">Browse our collection of professionally curated event packages</p>
        </div>

        <!-- FILTERS & SEARCH -->
        <div style="background: var(--bg-white); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            
            <!-- Search -->
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="margin-bottom: 0.5rem;">Search</label>
                <input type="text" class="form-input" id="searchInput" placeholder="Search packages..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>

            <!-- Occasion Filter -->
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="margin-bottom: 0.5rem;">Event Type</label>
                <select class="form-select" id="occasionFilter">
                    <option value="">All Events</option>
                    <?php foreach ($occasions as $occasion): ?>
                        <option value="<?php echo $occasion['occasion_id']; ?>" 
                            <?php echo ($occasionId == $occasion['occasion_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($occasion['events']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sort -->
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="margin-bottom: 0.5rem;">Sort By</label>
                <select class="form-select" id="sortFilter">
                    <option value="price_asc" <?php echo $sortBy === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sortBy === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                </select>
            </div>

            <!-- Filter Button -->
            <div class="form-group" style="margin: 0; display: flex; align-items: flex-end;">
                <button class="btn btn--primary" style="width: 100%;" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>

        <!-- RESULTS COUNT -->
        <div style="margin-bottom: 1.5rem; color: var(--text-secondary);">
            <strong><?php echo count($packages); ?></strong> package<?php echo count($packages) !== 1 ? 's' : ''; ?> found
        </div>

        <!-- PACKAGES GRID -->
        <?php if (!empty($packages)): ?>
            <div class="grid grid--3">
                <?php foreach ($packages as $package): ?>
                    <div class="package-card">
                        <!-- Images Carousel -->
                        <?php if (!empty($package['images'])): ?>
                            <div style="height: 200px; background: var(--bg-light); border-radius: 8px 8px 0 0; overflow: hidden; position: relative;">
                                <img src="<?php echo htmlspecialchars($package['images'][0]); ?>" 
                                     alt="<?php echo htmlspecialchars($package['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php endif; ?>

                        <div class="package-card__header">
                            <div class="package-card__name"><?php echo htmlspecialchars($package['name']); ?></div>
                            <div class="package-card__price">
                                <span style="font-size: 1rem;">$</span><?php echo number_format($package['price']); ?>
                            </div>
                            <div class="package-card__price-label"><?php echo htmlspecialchars($package['occasion_name']); ?></div>
                        </div>

                        <div class="package-card__body">
                            <p class="package-card__description">
                                <?php echo htmlspecialchars(substr($package['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="package-card__features">
                                <?php foreach (array_slice($package['inclusions'], 0, 4) as $inclusion): ?>
                                    <div class="package-card__feature">
                                        <i class="fas fa-check"></i>
                                        <span><?php echo htmlspecialchars($inclusion); ?></span>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($package['inclusions']) > 4): ?>
                                    <div class="package-card__feature" style="color: var(--primary); font-weight: 600;">
                                        <i class="fas fa-plus"></i>
                                        <span><?php echo count($package['inclusions']) - 4; ?> more items</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="package-card__footer">
                            <a href="/SINTA/public/index.php?route=package-detail&id=<?php echo $package['package_id']; ?>" class="btn btn--secondary">
                                <i class="fas fa-eye"></i> Details
                            </a>
                            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                                <a href="/SINTA/public/index.php?route=checkout&package_id=<?php echo $package['package_id']; ?>" class="btn btn--primary">
                                    <i class="fas fa-shopping-cart"></i> Book
                                </a>
                            <?php else: ?>
                                <a href="/SINTA/public/index.php?route=signin" class="btn btn--primary">
                                    <i class="fas fa-sign-in-alt"></i> Sign In
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem;">
                <i class="fas fa-search" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                <h3 style="color: var(--text-secondary); margin-bottom: 1rem;">No packages found</h3>
                <p style="color: var(--text-light); margin-bottom: 2rem;">Try adjusting your search or filter criteria</p>
                <a href="/SINTA/public/index.php?route=packages" class="btn btn--primary">
                    <i class="fas fa-redo"></i> Reset Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const occasion = document.getElementById('occasionFilter').value;
        const sort = document.getElementById('sortFilter').value;

        let url = '/SINTA/public/index.php?route=packages';
        if (search) url += '&search=' + encodeURIComponent(search);
        if (occasion) url += '&occasion=' + encodeURIComponent(occasion);
        if (sort) url += '&sort=' + encodeURIComponent(sort);

        window.location.href = url;
    }

    // Allow pressing Enter in search
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });
</script>

<?php include 'footer.php'; ?>
</main>
</body>
</html>

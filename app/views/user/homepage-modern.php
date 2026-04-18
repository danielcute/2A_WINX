<?php
/**
 * USER HOMEPAGE - Modern Minimalist
 * Location: app/views/user/homepage-modern.php
 */

require_once ROOT_PATH . '/app/models/Package.php';
require_once ROOT_PATH . '/app/models/Occasion.php';
require_once ROOT_PATH . '/app/models/Testimonial.php';

$pageTitle = 'Home';
$packageModel = new Package();
$occasionModel = new Occasion();

// Fetch featured packages
$featuredPackages = array_slice($packageModel->getAll(), 0, 6);

// Fetch occasions
$occasions = $occasionModel->getAll();

// Sample stats
$stats = [
    ['icon' => 'fas fa-users', 'label' => 'Happy Couples', 'value' => '500+', 'color' => 'primary'],
    ['icon' => 'fas fa-calendar-check', 'label' => 'Events Organized', 'value' => '1,200+', 'color' => 'success'],
    ['icon' => 'fas fa-star', 'label' => 'Client Rating', 'value' => '4.9/5', 'color' => 'warning'],
    ['icon' => 'fas fa-award', 'label' => 'Years Experience', 'value' => '15+', 'color' => 'info'],
];

?>
<?php include 'header-modern.php'; ?>

<main style="padding-bottom: 3rem;">
    <!-- HERO SECTION -->
    <div class="hero" style="max-width: 1400px; margin: 2rem auto;">
        <h1 class="hero__title">Make Your Event Unforgettable</h1>
        <p class="hero__subtitle">Professional event planning services for all occasions. Let us bring your dream event to life.</p>
        <div class="hero__actions">
            <a href="/SINTA/public/index.php?route=packages" class="btn btn--primary btn--lg">
                <i class="fas fa-arrow-right"></i> Explore Packages
            </a>
            <a href="/SINTA/public/index.php?route=contact" class="btn btn--outline btn--lg" style="color: white; border-color: white;">
                <i class="fas fa-envelope"></i> Contact Us
            </a>
        </div>
    </div>

    <!-- CONTAINER -->
    <div class="container">
        <!-- FEATURED PACKAGES -->
        <section class="section">
            <h2 class="section-title">Featured Packages</h2>
            <p class="section-subtitle">Choose from our curated collection of event packages</p>
            
            <div class="grid grid--3">
                <?php foreach ($featuredPackages as $package): ?>
                    <div class="package-card">
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
                                <?php foreach (array_slice($package['inclusions'], 0, 3) as $inclusion): ?>
                                    <div class="package-card__feature">
                                        <i class="fas fa-check"></i>
                                        <span><?php echo htmlspecialchars($inclusion); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="package-card__footer">
                            <a href="/SINTA/public/index.php?route=package-detail&id=<?php echo $package['package_id']; ?>" class="btn btn--secondary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                                <a href="/SINTA/public/index.php?route=checkout&package_id=<?php echo $package['package_id']; ?>" class="btn btn--primary">
                                    <i class="fas fa-shopping-cart"></i> Book Now
                                </a>
                            <?php else: ?>
                                <a href="/SINTA/public/index.php?route=signin" class="btn btn--primary">
                                    <i class="fas fa-sign-in-alt"></i> Sign In to Book
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="/SINTA/public/index.php?route=packages" class="btn btn--outline">
                    View All Packages <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </section>

        <!-- STATISTICS -->
        <section class="section" style="margin: 4rem 0;">
            <div class="grid grid--4">
                <?php foreach ($stats as $stat): ?>
                    <div class="card text-center" style="padding: 2rem 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--<?php echo $stat['color']; ?>); margin-bottom: 1rem;">
                            <i class="<?php echo $stat['icon']; ?>"></i>
                        </div>
                        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">
                            <?php echo $stat['value']; ?>
                        </div>
                        <div style="color: var(--text-secondary); font-size: 0.95rem;">
                            <?php echo $stat['label']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- EVENTS/OCCASIONS -->
        <section class="section" style="margin: 4rem 0;">
            <h2 class="section-title">Events We Specialize In</h2>
            <p class="section-subtitle">From weddings to corporate events, we handle it all</p>
            
            <div class="grid grid--3">
                <?php if (!empty($occasions)): ?>
                    <?php foreach (array_slice($occasions, 0, 6) as $occasion): ?>
                        <a href="/SINTA/public/index.php?route=occasions&type=<?php echo urlencode($occasion['events']); ?>" 
                           class="card" style="text-decoration: none; text-align: center; transition: var(--transition); cursor: pointer;">
                            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($occasion['events']); ?>
                            </h3>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Explore our <?php echo htmlspecialchars($occasion['events']); ?> packages
                            </p>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- CTA SECTION -->
        <section class="section" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); 
                                        color: white; padding: 3rem; border-radius: 16px; margin: 4rem 0; text-align: center;">
            <h2 class="section-title" style="color: white; margin-bottom: 1rem;">Ready to Plan Your Event?</h2>
            <p class="section-subtitle" style="color: rgba(255,255,255,0.9); margin-bottom: 2rem;">
                Get started today and let our experienced team handle all the details
            </p>
            <a href="/SINTA/public/index.php?route=<?php echo isset($_SESSION['user_logged_in']) ? 'packages' : 'signin'; ?>" 
               class="btn btn--primary btn--lg" style="background: white; color: var(--primary);">
                Get Started <i class="fas fa-arrow-right"></i>
            </a>
        </section>
    </div>
</main>

<!-- FOOTER -->
<footer style="background: var(--bg-darker, #1F2937); color: white; padding: 3rem 2rem; text-align: center; margin-top: 4rem;">
    <div class="container">
        <p style="margin-bottom: 1rem;">&copy; 2024 Sinta Event Planning. All rights reserved.</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="#" style="color: rgba(255,255,255,0.7); text-decoration: none;">Privacy Policy</a>
            <a href="#" style="color: rgba(255,255,255,0.7); text-decoration: none;">Terms of Service</a>
            <a href="#" style="color: rgba(255,255,255,0.7); text-decoration: none;">Contact</a>
        </div>
    </div>
</footer>

</body>
</html>

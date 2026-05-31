<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinta — Plan Your Perfect Event | Premium Event Organizer</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/landing.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
</head>
<body>
    <?php require ROOT_PATH . '/app/views/components/navbar.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <p class="subtitle">Premium Event Organizer · Philippines</p>
                    <h1>Crafting <span class="highlight">moments worth remembering</span></h1>
                    <p class="description">From intimate weddings to grand galas — we design extraordinary celebrations with meticulous care and flawless execution.</p>
                    
                    <div class="hero-buttons">
                        <a href="<?php echo BASE_URL; ?>/index.php?route=signup" class="btn btn-primary">Start Planning</a>
                        <a href="#bundles" class="btn btn-secondary">View Packages</a>
                    </div>

                    <div class="hero-stats">
                        <div class="stat">
                            <div class="stat-number">1,200+</div>
                            <div class="stat-label">Events Planned</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">98%</div>
                            <div class="stat-label">Satisfaction Rate</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">8 Yrs</div>
                            <div class="stat-label">Of Excellence</div>
                        </div>
                    </div>
                </div>

                <div class="hero-image">
                    <img src="<?php echo BASE_URL; ?>/assets/img/wedding-hero.jpg" alt="Elegant wedding ceremony">
                    <div class="testimonial-card">
                        <p>"The most magical day of our lives."</p>
                        <p class="author">— Maria & James · Wedding 2024</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Event Types Section -->
        <section class="event-types">
            <div class="event-grid">
                <div class="event-type">Weddings</div>
                <div class="event-type">Corporate Events</div>
                <div class="event-type">Birthday Celebrations</div>
                <div class="event-type">Anniversaries</div>
                <div class="event-type">Debut Parties</div>
                <div class="event-type">Gala Dinners</div>
                <div class="event-type">Product Launches</div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works" id="how-it-works">
            <div class="section-header">
                <p class="section-label">Simple Process</p>
                <h2>How it <span class="highlight">works</span></h2>
                <p class="section-description">Four steps to your perfect event, from vision to reality.</p>
            </div>

            <div class="process-steps">
                <div class="step">
                    <div class="step-number">01</div>
                    <div class="step-icon">💬</div>
                    <h3>Consultation</h3>
                    <p>Share your vision, budget, and preferences in a free discovery call.</p>
                </div>
                <div class="step">
                    <div class="step-number">02</div>
                    <div class="step-icon">✏️</div>
                    <h3>Design & Plan</h3>
                    <p>We create mood boards, source premium vendors, and build your timeline.</p>
                </div>
                <div class="step">
                    <div class="step-number">03</div>
                    <div class="step-icon">✓</div>
                    <h3>Execution</h3>
                    <p>Our team manages every detail — you relax and soak in every moment.</p>
                </div>
                <div class="step">
                    <div class="step-number">04</div>
                    <div class="step-icon">📷</div>
                    <h3>Celebrate</h3>
                    <p>Experience your flawless event and receive memories that last a lifetime.</p>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="about" id="about">
            <div class="about-content">
                <div class="about-image">
                    <img src="<?php echo BASE_URL; ?>/assets/img/about-sinta.jpg" alt="About Sinta Events">
                    <div class="about-badge">8 Years of Excellence</div>
                </div>

                <div class="about-text">
                    <p class="section-label">Our Story</p>
                    <h2>Crafting <span class="highlight">unforgettable experiences</span> since 2016</h2>
                    <p>Sinta began with a simple belief: extraordinary events don't just happen — they are carefully curated with passion, precision, and heart. Today, we're the Philippines' most trusted event partner, turning visions into lasting memories.</p>

                    <div class="features-grid">
                        <div class="feature">
                            <div class="feature-icon">🎨</div>
                            <h4>Personalized Approach</h4>
                            <p>Every event tailored to your unique vision and style.</p>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">⭐</div>
                            <h4>Premium Vendors</h4>
                            <p>Curated network of the industry's finest partners.</p>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">😌</div>
                            <h4>Stress-Free Planning</h4>
                            <p>We handle every detail so you can enjoy your day.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Packages Section -->
        <section class="packages" id="bundles">
            <div class="section-header">
                <p class="section-label">Investment Tiers</p>
                <h2>Choose Your <span class="highlight">Perfect Package</span></h2>
                <p class="section-description">Flexible options designed to fit your event and budget.</p>
            </div>

            <div class="packages-grid">
                <div class="package-card">
                    <h3>Essence</h3>
                    <div class="price">₱15,000 - ₱30,000</div>
                    <p class="description">Perfect for intimate gatherings</p>
                    <ul class="features">
                        <li>✓ Up to 50 guests</li>
                        <li>✓ 4-hour duration</li>
                        <li>✓ Venue coordination</li>
                        <li>✓ Basic catering</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=signup" class="btn btn-primary">Get Started</a>
                </div>

                <div class="package-card featured">
                    <div class="badge">Most Popular</div>
                    <h3>Elegance</h3>
                    <div class="price">₱30,000 - ₱75,000</div>
                    <p class="description">Our most versatile offering</p>
                    <ul class="features">
                        <li>✓ Up to 150 guests</li>
                        <li>✓ Full-day coverage</li>
                        <li>✓ Decor & styling</li>
                        <li>✓ Premium catering</li>
                        <li>✓ Photography</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=signup" class="btn btn-primary">Choose Plan</a>
                </div>

                <div class="package-card">
                    <h3>Grandeur</h3>
                    <div class="price">₱75,000+</div>
                    <p class="description">For extraordinary celebrations</p>
                    <ul class="features">
                        <li>✓ Unlimited guests</li>
                        <li>✓ Multi-day events</li>
                        <li>✓ Full production crew</li>
                        <li>✓ Premium everything</li>
                        <li>✓ Video coverage</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=signup" class="btn btn-primary">Book Consultation</a>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <div class="section-header">
                <p class="section-label">Client Feedback</p>
                <h2>What our <span class="highlight">clients</span> say</h2>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <p class="quote">"Sinta transformed our vision into reality. Every detail was perfect!"</p>
                    <p class="author">— Sarah & Michael · Wedding</p>
                </div>
                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <p class="quote">"Professional, creative, and incredibly attentive. Highly recommended!"</p>
                    <p class="author">— Juan · Corporate Event</p>
                </div>
                <div class="testimonial">
                    <div class="stars">★★★★★</div>
                    <p class="quote">"They made our daughter's debut truly special. Thank you, Sinta!"</p>
                    <p class="author">— Mrs. Cruz · Debut Party</p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta">
            <h2>Ready for your perfect event?</h2>
            <p>Let's create something extraordinary together</p>
            <a href="<?php echo BASE_URL; ?>/index.php?route=signup" class="btn btn-primary btn-large">Start Your Journey</a>
        </section>
    </main>

    <?php require ROOT_PATH . '/app/views/components/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>/assets/js/landing.js"></script>
</body>
</html>

<?php $page = 'landing'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Sinta — Plan Your Perfect Event | Premium Event Organizer</title>
  <meta name="description" content="Sinta curates extraordinary events — from intimate weddings to grand celebrations — with meticulous care and seamless execution.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/landing.css">
</head>
<body>

<!-- Navigation -->
<nav class="nav" id="navbar">
  <div class="nav__inner">
    <a href="<?php echo BASE_URL; ?>/index.php?route=landing" class="nav__logo">
      <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Sinta Logo" class="nav__logo-img" onerror="this.src='https://placehold.co/38x38/8A7650/white?text=S'">
      <span class="nav__logo-text">Sinta</span>
    </a>
    <button class="nav__toggle" id="navToggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
    <div class="nav__links" id="navLinks">
      <a href="#home">Home</a>
      <a href="#about">About</a>
      <a href="#bundles">Services</a>
      <a href="#how-it-works">How It Works</a>
      <a href="#contact">Contact</a>
    </div>
    <div class="nav__actions">
      <a href="<?php echo BASE_URL; ?>/index.php?route=signin" class="btn btn--ghost btn--sm">Sign In</a>
      <a href="<?php echo BASE_URL; ?>/index.php?route=signup" class="btn btn--primary btn--sm">Get Started</a>
    </div>
  </div>
</nav>

<main>
  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="hero__content">
      <div class="hero__eyebrow">
        <span class="rule"></span>
        <span>Premium Event Organizer · Philippines</span>
      </div>
      <h1 class="hero__title">Crafting moments<br>worth <em>remembering</em></h1>
      <p class="hero__sub">From intimate weddings to grand galas — we design extraordinary celebrations with meticulous care and flawless execution.</p>
      <div class="hero__actions">
        <a href="<?php echo BASE_URL; ?>/index.php?route=signup" class="btn btn--primary btn--lg">Start Planning</a>
        <a href="#bundles" class="btn btn--outline btn--lg">View Packages</a>
      </div>
      <div class="hero__stats">
        <div class="hero__stat">
          <div class="hero__stat-number">1,200+</div>
          <div class="hero__stat-label">Events Planned</div>
        </div>
        <div class="hero__stat-divider"></div>
        <div class="hero__stat">
          <div class="hero__stat-number">98%</div>
          <div class="hero__stat-label">Satisfaction Rate</div>
        </div>
        <div class="hero__stat-divider"></div>
        <div class="hero__stat">
          <div class="hero__stat-number">8 Yrs</div>
          <div class="hero__stat-label">Of Excellence</div>
        </div>
      </div>
    </div>
    <div class="hero__visual">
      <div class="hero__visual-inner">
        <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=900&h=1100&fit=crop" alt="Elegant wedding ceremony" class="hero__visual-img">
      </div>
      <div class="hero__visual-card animate-float">
        <div class="hero__visual-quote">"The most magical day of our lives."</div>
        <div class="hero__visual-author">— Maria & James · Wedding 2024</div>
      </div>
    </div>
  </section>

  <!-- Marquee -->
  <div class="marquee">
    <div class="marquee__track">
      <span class="marquee__item">Weddings <span class="marquee__dot"></span></span>
      <span class="marquee__item">Corporate Events <span class="marquee__dot"></span></span>
      <span class="marquee__item">Birthday Celebrations <span class="marquee__dot"></span></span>
      <span class="marquee__item">Anniversaries <span class="marquee__dot"></span></span>
      <span class="marquee__item">Debut Parties <span class="marquee__dot"></span></span>
      <span class="marquee__item">Gala Dinners <span class="marquee__dot"></span></span>
      <span class="marquee__item">Product Launches <span class="marquee__dot"></span></span>
      <span class="marquee__item">Weddings <span class="marquee__dot"></span></span>
      <span class="marquee__item">Corporate Events <span class="marquee__dot"></span></span>
      <span class="marquee__item">Birthday Celebrations <span class="marquee__dot"></span></span>
      <span class="marquee__item">Anniversaries <span class="marquee__dot"></span></span>
      <span class="marquee__item">Debut Parties <span class="marquee__dot"></span></span>
      <span class="marquee__item">Gala Dinners <span class="marquee__dot"></span></span>
      <span class="marquee__item">Product Launches <span class="marquee__dot"></span></span>
    </div>
  </div>

  <!-- About Section -->
  <section class="section" id="about">
    <div class="container">
      <div class="about__grid">
        <div class="about__visual reveal">
          <img src="/assets/img/sinta eventsjpg.jpg" alt="About Sinta Events">
          <div class="about__visual-badge animate-float-delayed">
            <span class="about__visual-badge-num">8</span>
            <span class="about__visual-badge-label">Years of Excellence</span>
          </div>
        </div>
        <div class="about__content reveal">
          <div class="eyebrow"><span class="rule"></span> Our Story</div>
          <h2>Crafting <em>unforgettable</em><br>experiences since 2016</h2>
          <p class="about__desc">Sinta began with a simple belief: extraordinary events don't just happen — they are carefully curated with passion, precision, and heart. Today, we're the Philippines' most trusted event partner, turning visions into lasting memories.</p>
          <div class="about__features">
            <div class="about__feature">
              <div class="about__feature-icon"><i class="fas fa-heart"></i></div>
              <div><h4>Personalized Approach</h4><p>Every event tailored to your unique vision and style.</p></div>
            </div>
            <div class="about__feature">
              <div class="about__feature-icon"><i class="fas fa-star"></i></div>
              <div><h4>Premium Vendors</h4><p>Curated network of the industry's finest partners.</p></div>
            </div>
            <div class="about__feature">
              <div class="about__feature-icon"><i class="fas fa-clock"></i></div>
              <div><h4>Stress-Free Planning</h4><p>We handle every detail so you can enjoy your day.</p></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Bundles/Services -->
  <section class="section section--alt" id="bundles">
    <div class="container">
      <div class="section__header text-center">
        <div class="eyebrow"><span class="rule"></span> Signature Collections</div>
        <h2>Curated event <em>packages</em><br>for every occasion</h2>
        <p class="section__subtitle">All-inclusive bundles or fully customized — your celebration, your way.</p>
      </div>
      <div class="bundles__grid stagger">
        <div class="bundle__card">
          <div class="bundle__image">
            <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=600&h=400&fit=crop" alt="Wedding event">
            <div class="bundle__tag">Most Popular</div>
          </div>
          <div class="bundle__content">
            <div class="bundle__icon-wrap"><i class="fas fa-ring"></i></div>
            <h3 class="bundle__title">Timeless Wedding</h3>
            <p class="bundle__desc">Complete wedding planning including venue styling, catering, photography, and full coordination.</p>
            <ul class="bundle__features">
              <li><i class="fas fa-check"></i> Full Coordination</li>
              <li><i class="fas fa-check"></i> Premium Styling</li>
              <li><i class="fas fa-check"></i> Photography & Video</li>
              <li><i class="fas fa-check"></i> Catering & Cake</li>
            </ul>
            <div class="bundle__footer">
              <div class="bundle__price">From ₱150K</div>
              <a href="/index.php?route=signup" class="btn btn--primary btn--sm">Inquire</a>
            </div>
          </div>
        </div>
        <div class="bundle__card">
          <div class="bundle__image">
            <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600&h=400&fit=crop" alt="Corporate event">
            <div class="bundle__tag">Best Value</div>
          </div>
          <div class="bundle__content">
            <div class="bundle__icon-wrap"><i class="fas fa-building"></i></div>
            <h3 class="bundle__title">Corporate Elegance</h3>
            <p class="bundle__desc">Professional conferences, product launches, and gala dinners with AV support and branding.</p>
            <ul class="bundle__features">
              <li><i class="fas fa-check"></i> AV & Lighting</li>
              <li><i class="fas fa-check"></i> Venue Sourcing</li>
              <li><i class="fas fa-check"></i> Catering Services</li>
              <li><i class="fas fa-check"></i> On-site Management</li>
            </ul>
            <div class="bundle__footer">
              <div class="bundle__price">From ₱200K</div>
              <a href="/index.php?route=signup" class="btn btn--primary btn--sm">Inquire</a>
            </div>
          </div>
        </div>
        <div class="bundle__card">
          <div class="bundle__image">
            <img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=600&h=400&fit=crop" alt="Birthday party">
          </div>
          <div class="bundle__content">
            <div class="bundle__icon-wrap"><i class="fas fa-birthday-cake"></i></div>
            <h3 class="bundle__title">Birthday & Socials</h3>
            <p class="bundle__desc">Intimate birthday parties, anniversaries, and milestone celebrations made truly memorable.</p>
            <ul class="bundle__features">
              <li><i class="fas fa-check"></i> Party Styling</li>
              <li><i class="fas fa-check"></i> Entertainment</li>
              <li><i class="fas fa-check"></i> Photography</li>
              <li><i class="fas fa-check"></i> Invitations</li>
            </ul>
            <div class="bundle__footer">
              <div class="bundle__price">From ₱50K</div>
              <a href="/index.php?route=signup" class="btn btn--primary btn--sm">Inquire</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Events Gallery -->
  <section class="section">
    <div class="container">
      <div class="section__header text-center">
        <div class="eyebrow"><span class="rule"></span> Real Moments</div>
        <h2>Events we've<br>brought to <em>life</em></h2>
        <p class="section__subtitle">A glimpse into the unforgettable celebrations we've crafted for our beloved clients.</p>
      </div>
      <div class="featured__grid stagger">
        <div class="featured__card">
          <div class="featured__image-wrap">
            <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=700&h=520&fit=crop" alt="Ethereal Garden Wedding">
            <div class="featured__overlay">
              <div class="featured__category">Wedding</div>
              <h3 class="featured__title">Ethereal Garden Wedding</h3>
              <a href="#" class="featured__link">View Story <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <div class="featured__card">
          <div class="featured__image-wrap">
            <img src="https://images.unsplash.com/photo-1470058869958-2a77ade41c02?w=700&h=520&fit=crop" alt="Annual Awards Night">
            <div class="featured__overlay">
              <div class="featured__category">Corporate Gala</div>
              <h3 class="featured__title">Annual Awards Night</h3>
              <a href="#" class="featured__link">View Story <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <div class="featured__card">
          <div class="featured__image-wrap">
            <img src="/assets/img/30th.png" alt="Magical 30th Birthday">
            <div class="featured__overlay">
              <div class="featured__category">Birthday</div>
              <h3 class="featured__title">Magical 30th Birthday</h3>
              <a href="#" class="featured__link">View Story <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <div class="featured__card">
          <div class="featured__image-wrap">
            <img src="/assets/img/golden.jpg" alt="Golden Anniversary Gala">
            <div class="featured__overlay">
              <div class="featured__category">Anniversary</div>
              <h3 class="featured__title">Golden Anniversary Gala</h3>
              <a href="#" class="featured__link">View Story <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
      <div class="text-center" style="margin-top: 2.5rem;">
        <a href="#" class="btn btn--outline">View Full Gallery <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section class="section section--alt" id="how-it-works">
    <div class="container">
      <div class="section__header text-center">
        <div class="eyebrow"><span class="rule"></span> Simple Process</div>
        <h2>How it <em>works</em></h2>
        <p class="section__subtitle">Four steps to your perfect event, from vision to reality.</p>
      </div>
      <div class="howit__grid">
        <div class="howit__step reveal">
          <div class="howit__number">01</div>
          <div class="howit__icon"><i class="fas fa-comment-dots"></i></div>
          <h3>Consultation</h3>
          <p>Share your vision, budget, and preferences in a free discovery call.</p>
        </div>
        <div class="howit__step reveal">
          <div class="howit__number">02</div>
          <div class="howit__icon"><i class="fas fa-magic"></i></div>
          <h3>Design & Plan</h3>
          <p>We create mood boards, source premium vendors, and build your timeline.</p>
        </div>
        <div class="howit__step reveal">
          <div class="howit__number">03</div>
          <div class="howit__icon"><i class="fas fa-check-double"></i></div>
          <h3>Execution</h3>
          <p>Our team manages every detail — you relax and soak in every moment.</p>
        </div>
        <div class="howit__step reveal">
          <div class="howit__number">04</div>
          <div class="howit__icon"><i class="fas fa-camera-retro"></i></div>
          <h3>Celebrate</h3>
          <p>Experience your flawless event and receive memories that last a lifetime.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Client Feedback Section -->
  <section class="section">
    <div class="container">
      <div class="section__header text-center">
        <div class="eyebrow"><span class="rule"></span> Client Feedback</div>
        <h2>What our <em>clients</em> say</h2>
        <p class="section__subtitle">Hear from the people who've experienced Sinta's service firsthand.</p>
      </div>
      <div class="feedback__grid stagger" id="feedbackContainer">
        <div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: var(--gray);">
          <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
          <p>Loading client feedback...</p>
        </div>
      </div>
    </div>
  </section>
  
  <style>
    .feedback__grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }
    
    .feedback__card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 1.5rem;
      transition: all 0.3s ease;
    }
    
    .feedback__card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .feedback__meta {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }
    
    .feedback__user {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex: 1;
    }
    
    .feedback__avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary-light);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      font-weight: 600;
      font-size: 0.8rem;
    }
    
    .feedback__user-info h4 {
      margin: 0;
      font-size: 0.9rem;
      color: var(--text-primary);
    }
    
    .feedback__user-date {
      margin: 0.2rem 0 0;
      font-size: 0.75rem;
      color: var(--gray);
    }
    
    .feedback__subject {
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 0.75rem;
      font-size: 1rem;
    }
    
    .feedback__message {
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 1rem;
      font-size: 0.95rem;
    }
    
    .feedback__footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 1rem;
      border-top: 1px solid var(--border);
      font-size: 0.8rem;
    }
    
    .feedback__status {
      display: inline-block;
      padding: 0.25rem 0.6rem;
      border-radius: 20px;
      background: var(--primary-pale);
      color: var(--primary);
      font-weight: 600;
    }
    
    .feedback__empty {
      grid-column: 1/-1;
      text-align: center;
      padding: 3rem 1rem;
      color: var(--gray);
    }
  </style>

  <!-- CTA Section -->
  <section class="section--sm">
    <div class="container">
      <div class="cta__band reveal">
        <h2>Ready to create your<br><em>perfect event?</em></h2>
        <p>Let's build something unforgettable together. Book a free consultation today.</p>
        <div class="cta__actions">
          <a href="/index.php?route=signup" class="btn btn--primary btn--lg">Get Started <i class="fas fa-arrow-right"></i></a>
          <a href="#contact" class="btn btn--outline-light btn--lg">Contact Us</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="section" id="contact">
    <div class="container">
      <div class="contact__grid">
        <div class="reveal">
          <div class="eyebrow"><span class="rule"></span> Get in Touch</div>
          <h2>Let's bring your<br><em>vision</em> to life</h2>
          <p style="color: var(--gray); margin: 1rem 0 2rem;">Have questions or ready to start planning? Reach out — we'd love to hear from you.</p>
          <div class="contact__details">
            <div class="contact__detail"><i class="fas fa-map-marker-alt"></i> Unit 8, The Sapphire Tower, BGC, Manila</div>
            <div class="contact__detail"><i class="fas fa-phone"></i> +63 (2) 8123 4567</div>
            <div class="contact__detail"><i class="fas fa-envelope"></i> hello@sintaevents.com</div>
          </div>
          <div class="contact__social">
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Pinterest"><i class="fab fa-pinterest"></i></a>
          </div>
        </div>
        <div class="reveal">
          <form class="contact__form">
            <div class="form__row">
              <input type="text" placeholder="Your Name">
              <input type="email" placeholder="Email Address">
            </div>
            <div class="form__row">
              <input type="tel" placeholder="Phone Number">
              <select>
                <option value="">Event Type</option>
                <option>Wedding</option>
                <option>Corporate Event</option>
                <option>Birthday Party</option>
                <option>Anniversary</option>
                <option>Other</option>
              </select>
            </div>
            <textarea rows="4" placeholder="Tell us about your event vision..."></textarea>
            <button type="button" class="btn btn--primary">Send Message <i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer__top">
      <div>
        <a href="/index.php?route=landing" class="footer__logo">
          <img src="/assets/img/logo.png" alt="Sinta" class="footer__logo-img" onerror="this.src='https://placehold.co/32x32/8A7650/white?text=S'">
          <span>Sinta</span>
        </a>
        <p class="footer__desc">Crafting extraordinary event experiences across the Philippines. Every occasion deserves to be unforgettable.</p>
      </div>
      <div class="footer__col">
        <div class="footer__col-title">Explore</div>
        <ul>
          <li><a href="#home">Home</a></li>
          <li><a href="#about">About Us</a></li>
          <li><a href="#bundles">Services</a></li>
          <li><a href="#how-it-works">How It Works</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <div class="footer__col-title">Account</div>
        <ul>
          <li><a href="/index.php?route=signin">Sign In</a></li>
          <li><a href="/index.php?route=signup">Get Started</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <div class="footer__col-title">Stay Inspired</div>
        <p style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.75rem;">Get event ideas and exclusive deals in your inbox.</p>
        <div class="footer__input-wrap">
          <input type="email" placeholder="your@email.com">
          <button><i class="fas fa-arrow-right"></i></button>
        </div>
      </div>
    </div>
    <div class="footer__bottom">
      <span>© <?= date('Y') ?> Sinta Events. All rights reserved.</span>
      <div class="footer__bottom-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>

<script>
  // Mobile nav toggle
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');
  
  if (navToggle) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('active');
      navToggle.classList.toggle('active');
    });
  }
  
  document.querySelectorAll('.nav__links a').forEach(link => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('active');
      navToggle.classList.remove('active');
    });
  });

  // Navbar scroll effect
  window.addEventListener('scroll', () => {
    const navbar = document.getElementById('navbar');
    if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 50);
  });

  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // Scroll reveal
  const reveals = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('active');
      }
    });
  }, { threshold: 0.1 });
  
  reveals.forEach(el => observer.observe(el));

  // Load client feedback
  async function loadClientFeedback() {
    try {
      const response = await fetch('/api-feedback.php');
      const data = await response.json();
      
      const container = document.getElementById('feedbackContainer');
      
      if (!data.success || !data.data || data.data.length === 0) {
        container.innerHTML = `
          <div class="feedback__empty">
            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
            <p>Client feedback will appear here soon...</p>
          </div>
        `;
        return;
      }
      
      container.innerHTML = data.data.map((feedback, index) => {
        const date = new Date(feedback.created_at).toLocaleDateString('en-US', { 
          year: 'numeric', 
          month: 'short', 
          day: 'numeric' 
        });
        const initials = (feedback.user_name || 'U').split(' ').map(n => n[0]).join('').toUpperCase();
        
        // Generate star rating
        let stars = '';
        for (let i = 0; i < 5; i++) {
          stars += (i < feedback.rating) ? '★' : '☆';
        }
        
        // Format admin reply if exists
        let adminReplyHtml = '';
        if (feedback.admin_reply) {
          const replyDate = new Date(feedback.admin_reply_date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
          });
          adminReplyHtml = `
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #E2D9C8; background: #f9f9f9; padding: 1rem; border-radius: 8px;">
              <div style="font-weight: 600; color: #8A7650; margin-bottom: 0.5rem;">
                <i class="fas fa-reply"></i> Admin Response
              </div>
              <div style="color: #555; font-size: 0.95rem; line-height: 1.5; margin-bottom: 0.5rem;">${feedback.admin_reply}</div>
              <div style="font-size: 0.8rem; color: #8B7355;">${replyDate}</div>
            </div>
          `;
        }
        
        return `
          <div class="feedback__card" style="animation: fadeInUp 0.5s ease forwards; animation-delay: ${index * 0.1}s; opacity: 0;">
            <div class="feedback__meta">
              <div class="feedback__user">
                <div class="feedback__avatar">${initials}</div>
                <div class="feedback__user-info">
                  <h4>${feedback.user_name || 'Anonymous'}</h4>
                  <div class="feedback__user-date">${date}</div>
                </div>
              </div>
            </div>
            <div style="display: inline-block; padding: 0.25rem 0.6rem; border-radius: 20px; background: #FFF3E0; color: #E65100; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.75rem;">${stars}</div>
            <div class="feedback__subject">${feedback.subject}</div>
            <div class="feedback__message">${feedback.message}</div>
            ${adminReplyHtml}
            <div class="feedback__footer">
              <span class="feedback__status">${feedback.status === 'closed' ? 'Completed' : 'Resolved'}</span>
            </div>
          </div>
        `;
      }).join('');
      
      // Add animation styles if not already present
      if (!document.querySelector('style[data-feedback-animation]')) {
        const style = document.createElement('style');
        style.setAttribute('data-feedback-animation', '');
        style.textContent = `
          @keyframes fadeInUp {
            from {
              opacity: 0;
              transform: translateY(20px);
            }
            to {
              opacity: 1;
              transform: translateY(0);
            }
          }
        `;
        document.head.appendChild(style);
      }
    } catch (error) {
      console.error('Error loading feedback:', error);
      document.getElementById('feedbackContainer').innerHTML = `
        <div class="feedback__empty" style="grid-column: 1/-1;">
          <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
          <p>Unable to load client feedback at this time.</p>
        </div>
      `;
    }
  }

  // Load feedback when page is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadClientFeedback);
  } else {
    loadClientFeedback();
  }
</script>
</body>
</html>
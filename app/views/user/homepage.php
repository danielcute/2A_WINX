<?php $page = 'homepage'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
  <style>
    /* Styles remain the same as before */
    .app-shell {
      padding-top: 76px;
      min-height: 100vh;
      background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
    }
    
    .home-main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem 2rem 5rem;
    }
    
    /* Hero Section */
    .home-hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 3rem;
      background: var(--bg-card);
      border-radius: var(--radius-2xl);
      padding: 3rem 4rem;
      margin-bottom: 3rem;
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border);
      position: relative;
      overflow: hidden;
    }
    
    .home-hero::before {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, var(--primary-pale) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    
    .home-hero__content {
      flex: 1;
    }
    
    .home-hero__title {
      font-size: clamp(2rem, 4vw, 3rem);
      margin: 0.5rem 0 1rem;
    }
    
    .home-hero__title em {
      color: var(--primary);
    }
    
    .home-hero__sub {
      color: var(--text-muted);
      font-size: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .home-hero__actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }
    
    .home-hero__visual {
      flex-shrink: 0;
    }
    
    .home-hero__visual-card {
      background: var(--bg-secondary);
      border-radius: var(--radius-xl);
      padding: 2rem;
      width: 260px;
      text-align: center;
      border: 1px solid var(--border);
    }
    
    .hero-quote {
      font-family: var(--serif);
      font-size: 1rem;
      font-style: italic;
      color: var(--text-primary);
      margin-bottom: 0.5rem;
    }
    
    .hero-quote-author {
      font-size: 0.7rem;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }
    
    /* Stats Section */
    .home-stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-bottom: 3rem;
    }
    
    .stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 1.5rem;
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
      border-color: var(--primary);
    }
    
    .stat-card__icon {
      width: 48px;
      height: 48px;
      background: var(--primary-pale);
      color: var(--primary);
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      margin: 0 auto 1rem;
    }
    
    .stat-card__number {
      font-family: var(--serif);
      font-size: 2rem;
      font-weight: 500;
      color: var(--text-primary);
    }
    
    .stat-card__label {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--text-muted);
      margin-top: 0.25rem;
    }
    
    /* Section Header */
    .home-section {
      margin-bottom: 3rem;
    }
    
    .home-section__head {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .home-section__head h2 {
      font-size: 1.8rem;
      margin-top: 0.25rem;
    }
    
    /* Occasions Grid */
    .occasions-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 1rem;
    }
    
    .occasion-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      overflow: hidden;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .occasion-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
      border-color: var(--primary);
    }
    
    .occasion-card__image {
      height: 120px;
      background-size: cover;
      background-position: center;
    }
    
    .occasion-card__content {
      padding: 1rem;
      text-align: center;
    }
    
    .occasion-card__icon {
      width: 40px;
      height: 40px;
      background: var(--primary-pale);
      color: var(--primary);
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      margin: 0 auto 0.5rem;
    }
    
    .occasion-card h4 {
      font-size: 0.9rem;
      margin-bottom: 0.25rem;
      color: var(--text-primary);
    }
    
    .occasion-card__price {
      font-size: 0.7rem;
      color: var(--primary);
      font-weight: 600;
    }
    
    .occasion-card--more {
      background: var(--bg-secondary);
    }
    
    .occasion-card--more .occasion-card__content {
      padding: 1.5rem;
    }
    
    /* Events List */
    .events-list {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }
    
    .event-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 1rem;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .event-card:hover {
      transform: translateX(5px);
      box-shadow: var(--shadow-sm);
      border-color: var(--primary);
    }
    
    .event-card__image {
      width: 60px;
      height: 60px;
      border-radius: var(--radius-md);
      background-size: cover;
      background-position: center;
      flex-shrink: 0;
    }
    
    .event-card__info {
      flex: 1;
    }
    
    .event-card__info h4 {
      font-size: 1rem;
      margin-bottom: 0.25rem;
      color: var(--text-primary);
    }
    
    .event-card__meta {
      display: flex;
      gap: 1rem;
      font-size: 0.7rem;
      color: var(--text-muted);
    }
    
    .event-card__meta i {
      color: var(--primary);
      margin-right: 0.25rem;
    }
    
    .event-card__arrow {
      color: var(--text-light);
    }
    
    /* Testimonials Grid */
    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
    }
    
    .testimonial-card {
      position: relative;
      border-radius: var(--radius-xl);
      overflow: hidden;
      aspect-ratio: 3/4;
    }
    
    .testimonial-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
    
    .testimonial-card:hover img {
      transform: scale(1.05);
    }
    
    .testimonial-card__overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(44, 40, 32, 0.9) 0%, rgba(44, 40, 32, 0.2) 60%, transparent 100%);
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 1.5rem;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .testimonial-card:hover .testimonial-card__overlay {
      opacity: 1;
    }
    
    .testimonial-card__stars {
      color: var(--primary);
      font-size: 0.8rem;
      margin-bottom: 0.5rem;
    }
    
    .testimonial-card__quote {
      font-family: var(--serif);
      font-size: 0.85rem;
      color: white;
      font-style: italic;
      margin-bottom: 1rem;
      line-height: 1.5;
    }
    
    .testimonial-card__author {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .testimonial-card__author img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
    }
    
    .testimonial-card__author strong {
      display: block;
      font-size: 0.8rem;
      color: white;
    }
    
    .testimonial-card__author span {
      font-size: 0.65rem;
      color: var(--primary-light);
    }
    
    @media (max-width: 1024px) {
      .occasions-grid {
        grid-template-columns: repeat(3, 1fr);
      }
      .testimonials-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      .home-stats {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    @media (max-width: 768px) {
      .home-main {
        padding: 1.5rem 1rem 4rem;
      }
      .home-hero {
        flex-direction: column;
        padding: 2rem;
        text-align: center;
      }
      .home-hero__actions {
        justify-content: center;
      }
      .occasions-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      .testimonials-grid {
        grid-template-columns: 1fr;
      }
      .home-stats {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="app-shell">
  <main class="home-main">
    
    <!-- Hero Section -->
    <section class="home-hero animate-fade-up">
      <div class="home-hero__content">
        <div class="eyebrow"><span class="rule"></span> Welcome back</div>
        <h1 class="home-hero__title">Good Day, <em><?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest') ?>!</em></h1>
        <p class="home-hero__sub">What kind of celebration are we planning today?</p>
        <div class="home-hero__actions">
        <a href="/SINTA/public/index.php?route=occasions" class="btn btn--primary btn--lg"><i class="fas fa-sparkles"></i> Start Planning</a>
          <a href="/SINTA/public/index.php?route=packages" class="btn btn--outline btn--lg"><i class="fas fa-box"></i> Browse Packages</a>
        </div>
      </div>
      <div class="home-hero__visual animate-float">
        <div class="home-hero__visual-card">
          <div class="hero-quote">"Your next unforgettable celebration starts here"</div>
          <div class="hero-quote-author">— Trusted by 1,200+ clients</div>
        </div>
      </div>
    </section>
    
    <!-- Stats Section -->
    <section class="home-stats stagger">
      <div class="stat-card">
        <div class="stat-card__icon"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-card__number">2</div>
        <div class="stat-card__label">Upcoming Events</div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon"><i class="fas fa-clock-rotate-left"></i></div>
        <div class="stat-card__number">5</div>
        <div class="stat-card__label">Past Events</div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon"><i class="fas fa-envelope-open-text"></i></div>
        <div class="stat-card__number">1</div>
        <div class="stat-card__label">Active Inquiry</div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon"><i class="fas fa-star"></i></div>
        <div class="stat-card__number">4.9</div>
        <div class="stat-card__label">Your Avg Rating</div>
      </div>
    </section>
    
    <!-- Occasions Section -->
    <section class="home-section">
      <div class="home-section__head">
        <div>
          <div class="eyebrow"><span class="rule"></span> Step 1</div>
          <h2>Choose an <em>Occasion</em></h2>
        </div>
       <a href="/SINTA/public/index.php?route=occasions" class="btn btn--ghost btn--sm">View All <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="occasions-grid stagger">
       <a href="/SINTA/public/index.php?route=packages&occasion=wedding" class="occasion-card">
          <div class="occasion-card__image" style="background-image: url('/SINTA/public/assets/img/wedding.jpg')"></div>
          <div class="occasion-card__content">
            <div class="occasion-card__icon"><i class="fas fa-ring"></i></div>
            <h4>Wedding</h4>
            <span class="occasion-card__price">From ₱150K</span>
          </div>
        </a>
        <a href="/SINTA/public/index.php?route=packages&occasion=birthday" class="occasion-card">
          <div class="occasion-card__image" style="background-image: url('/SINTA/public/assets/img/birthday.jpg')"></div>
          <div class="occasion-card__content">
            <div class="occasion-card__icon"><i class="fas fa-cake-candles"></i></div>
            <h4>Birthday</h4>
            <span class="occasion-card__price">From ₱50K</span>
          </div>
        </a>
        <a href="/SINTA/public/index.php?route=packages&occasion=debut" class="occasion-card">
          <div class="occasion-card__image" style="background-image: url('/SINTA/public/assets/img/debut.jpg')"></div>
          <div class="occasion-card__content">
            <div class="occasion-card__icon"><i class="fas fa-crown"></i></div>
            <h4>Debut</h4>
            <span class="occasion-card__price">From ₱80K</span>
          </div>
        </a>
        <a href="/SINTA/public/index.php?route=packages&occasion=corporate" class="occasion-card">
          <div class="occasion-card__image" style="background-image: url('/SINTA/public/assets/img/corporate.jpg')"></div>
          <div class="occasion-card__content">
            <div class="occasion-card__icon"><i class="fas fa-briefcase"></i></div>
            <h4>Corporate</h4>
            <span class="occasion-card__price">From ₱200K</span>
          </div>
        </a>
        <a href="/SINTA/public/index.php?route=packages&occasion=anniversary" class="occasion-card">
          <div class="occasion-card__image" style="background-image: url('/SINTA/public/assets/img/anniversary.jpg')"></div>
          <div class="occasion-card__content">
            <div class="occasion-card__icon"><i class="fas fa-heart"></i></div>
            <h4>Anniversary</h4>
            <span class="occasion-card__price">From ₱60K</span>
          </div>
        </a>
        <a href="/SINTA/public/index.php?route=packages&occasion=other" class="occasion-card occasion-card--more">
          <div class="occasion-card__content">
            <div class="occasion-card__icon"><i class="fas fa-plus"></i></div>
            <h4>More Events</h4>
            <span class="occasion-card__price">Custom Quote</span>
          </div>
        </a>
      </div>
    </section>
    
    <!-- Upcoming Events Section -->
    <section class="home-section">
      <div class="home-section__head">
        <div>
          <div class="eyebrow"><span class="rule"></span> Your Plans</div>
          <h2>Upcoming <em>Events</em></h2>
        </div>
        <a href="/SINTA/public/index.php?route=plans" class="btn btn--ghost btn--sm">View All <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="events-list stagger">
        <a href="event-detail.php?id=1" class="event-card">
          <div class="event-card__image" style="background-image: url('/SINTA/public/assets/img/wedding.jpg')"></div>
          <div class="event-card__info">
            <h4>Santos Wedding</h4>
            <div class="event-card__meta">
              <span><i class="fas fa-location-dot"></i> Bacolod City</span>
              <span><i class="fas fa-calendar"></i> Aug 12, 2025</span>
            </div>
          </div>
          <span class="badge badge--success">Confirmed</span>
          <i class="fas fa-chevron-right event-card__arrow"></i>
        </a>
        <a href="event-detail.php?id=2" class="event-card">
          <div class="event-card__image" style="background-image: url('/SINTA/public/assets/img/70.jpg')"></div>
          <div class="event-card__info">
            <h4>Mom's 70th Birthday</h4>
            <div class="event-card__meta">
              <span><i class="fas fa-location-dot"></i> Iloilo City</span>
              <span><i class="fas fa-calendar"></i> Oct 3, 2025</span>
            </div>
          </div>
          <span class="badge badge--primary">Planning</span>
          <i class="fas fa-chevron-right event-card__arrow"></i>
        </a>
        <a href="event-detail.php?id=3" class="event-card">
          <div class="event-card__image" style="background-image: url('/SINTA/public/assets/img/ayala.jpg')"></div>
          <div class="event-card__info">
            <h4>Ayala Gala Night</h4>
            <div class="event-card__meta">
              <span><i class="fas fa-location-dot"></i> Bacolod City</span>
              <span><i class="fas fa-calendar"></i> Nov 28, 2025</span>
            </div>
          </div>
          <span class="badge badge--warning">Pending</span>
          <i class="fas fa-chevron-right event-card__arrow"></i>
        </a>
      </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="home-section">
      <div class="home-section__head">
        <div>
          <div class="eyebrow"><span class="rule"></span> Love Notes</div>
          <h2>What our <em>clients</em> say</h2>
        </div>
      </div>
      <div class="testimonials-grid stagger">
        <div class="testimonial-card">
          <img src="/SINTA/public/assets/img/wedding2.jpg" alt="Wedding">
          <div class="testimonial-card__overlay">
            <div class="testimonial-card__stars">★★★★★</div>
            <p class="testimonial-card__quote">"The most magical day of our lives. Sinta made every detail absolutely perfect."</p>
            <div class="testimonial-card__author">
              <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Isabella">
              <div>
                <strong>Isabella R.</strong>
                <span>Wedding · 2024</span>
              </div>
            </div>
          </div>
        </div>
        <div class="testimonial-card">
          <img src="/SINTA/public/assets/img/corporate2.jpg" alt="Corporate">
          <div class="testimonial-card__overlay">
            <div class="testimonial-card__stars">★★★★★</div>
            <p class="testimonial-card__quote">"Professional, creative, and handled every detail without a hitch."</p>
            <div class="testimonial-card__author">
              <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Marcus">
              <div>
                <strong>Marcus T.</strong>
                <span>Corporate · 2024</span>
              </div>
            </div>
          </div>
        </div>
        <div class="testimonial-card">
          <img src="/SINTA/public/assets/img/birthday2.jpg" alt="Birthday">
          <div class="testimonial-card__overlay">
            <div class="testimonial-card__stars">★★★★★</div>
            <p class="testimonial-card__quote">"Planning from abroad was effortless with Sinta. The surprise was perfection!"</p>
            <div class="testimonial-card__author">
              <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Chloe">
              <div>
                <strong>Chloe S.</strong>
                <span>Birthday · 2024</span>
              </div>
            </div>
          </div>
        </div>
        <div class="testimonial-card">
          <img src="/SINTA/public/assets/img/anniversary2.jpg" alt="Anniversary">
          <div class="testimonial-card__overlay">
            <div class="testimonial-card__stars">★★★★★</div>
            <p class="testimonial-card__quote">"50 years celebrated in the most elegant way possible. Thank you, Sinta!"</p>
            <div class="testimonial-card__author">
              <img src="https://randomuser.me/api/portraits/men/55.jpg" alt="Ramon">
              <div>
                <strong>Ramon & Luz V.</strong>
                <span>Anniversary · 2024</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
  </main>
</div>

<script>
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  document.querySelectorAll('.stagger').forEach(el => observer.observe(el));
</script>
</body>
</html>
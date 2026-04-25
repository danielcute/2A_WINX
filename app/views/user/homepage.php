<?php
$page = 'homepage';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$homePlans = [];
if (!empty($_SESSION['user_id'])) {
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(dirname(__DIR__)));
    }
    require_once ROOT_PATH . '/app/models/Plan.php';
    require_once ROOT_PATH . '/app/models/PlanAutoConfirmation.php';
    $planModel = new Plan();
    $autoConfirm = new PlanAutoConfirmation();
    $allPlans = $planModel->getUserPlans($_SESSION['user_id']);
    
    // Update plan statuses with auto-confirmation and cancellation info
    foreach ($allPlans as &$plan) {
        $planStatusInfo = $autoConfirm->getPlanStatusInfo($plan['plan_id']);
        if ($planStatusInfo) {
            $plan['status'] = $planStatusInfo['status'];
            $plan['can_cancel'] = $planStatusInfo['can_cancel'] ?? false;
            $plan['minutes_remaining'] = $planStatusInfo['minutes_remaining'] ?? 0;
        } else {
            $plan['can_cancel'] = false;
            $plan['minutes_remaining'] = 0;
        }
    }
    
    $homePlans = array_slice($allPlans, 0, 3);
}
?>
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
    
    @media (max-width: 1024px) {
      .occasions-grid {
        grid-template-columns: repeat(3, 1fr);
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
        <?php if (!empty($homePlans)): ?>
          <?php
            $homeBadgeMap = [
              'confirmed' => 'badge--success',
              'approved' => 'badge--success',
              'planning' => 'badge--primary',
              'pending' => 'badge--warning',
              'completed' => 'badge--info',
              'rejected' => 'badge--danger'
            ];
            $homeLabelMap = [
              'confirmed' => 'Confirmed',
              'approved' => 'Confirmed',
              'planning' => 'Planning',
              'pending' => 'Pending',
              'completed' => 'Completed',
              'rejected' => 'Rejected'
            ];
          ?>
          <?php foreach ($homePlans as $plan): ?>
            <?php
              $planStatus = $plan['status'] ?? 'pending';
              $planStatus = isset($homeLabelMap[$planStatus]) ? $planStatus : 'pending';
              $label = $homeLabelMap[$planStatus] ?? 'Pending';
              $badgeClass = $homeBadgeMap[$planStatus] ?? 'badge--warning';
              $eventTitle = htmlspecialchars($plan['event_name'] ?: ($plan['occasion_name'] ?: 'Your Event'));
              $eventDate = $plan['event_date'] ? date('M j, Y', strtotime($plan['event_date'])) : 'TBD';
              $eventLocation = htmlspecialchars($plan['venue'] ?: 'TBD');
              $eventLink = '/SINTA/public/index.php?route=event-detail&id=' . urlencode($plan['plan_id']);
              $eventText = strtolower(trim(($plan['occasion_name'] ?? '') . ' ' . ($plan['package_name'] ?? '') . ' ' . ($plan['event_name'] ?? '')));
              $eventImageMap = [
                  'wedding' => '/SINTA/public/assets/img/wedding3.jpg',
                  'debut' => '/SINTA/public/assets/img/debut.jpg',
                  'birthday' => '/SINTA/public/assets/img/birthday2.jpg',
                  'corporate' => '/SINTA/public/assets/img/corporate2.jpg',
                  'anniversary' => '/SINTA/public/assets/img/anniversary.jpg',
                  'beach' => '/SINTA/public/assets/img/beach.jpg',
                  'garden' => '/SINTA/public/assets/img/garden.jpg',
              ];
              $imageUrl = '/SINTA/public/assets/img/event-placeholder.jpg';
              foreach ($eventImageMap as $keyword => $url) {
                  if ($keyword && strpos($eventText, $keyword) !== false) {
                      $imageUrl = $url;
                      break;
                  }
              }
            ?>
            <a href="<?= $eventLink ?>" class="event-card">
              <div class="event-card__image" style="background-image: url('<?= $imageUrl ?>')"></div>
              <div class="event-card__info">
                <h4><?= $eventTitle ?></h4>
                <div class="event-card__meta">
                  <span><i class="fas fa-location-dot"></i> <?= $eventLocation ?></span>
                  <span><i class="fas fa-calendar"></i> <?= $eventDate ?></span>
                </div>
              </div>
              <span class="badge <?= $badgeClass ?>"><?= $label ?></span>
              <i class="fas fa-chevron-right event-card__arrow"></i>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
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
        <?php endif; ?>
      </div>
    </section>
    
    <!-- Feedback Section -->
    <section class="home-section">
      <div class="home-section__head">
        <div>
          <div class="eyebrow"><span class="rule"></span> Your Feedback</div>
          <h2>Share your <em>thoughts</em> with us</h2>
        </div>
      </div>
      <div style="background: var(--bg-card); border-radius: var(--radius-2xl); padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border);">
        <form id="homepageFeedbackForm" style="display: flex; flex-direction: column; gap: 1rem;">
          <div>
            <label for="home-feedback-subject" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;"><strong>Subject</strong></label>
            <input type="text" id="home-feedback-subject" name="subject" placeholder="Brief summary of your feedback" required style="width: 100%; padding: 0.75rem 1rem; border: 2px solid var(--border); border-radius: var(--radius-lg); font-family: inherit; font-size: 1rem; background: var(--bg-secondary);">
          </div>
          
          <div>
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;"><strong>Rating</strong></label>
            <div style="display: flex; gap: 0.5rem;" id="home-feedback-rating">
              <button type="button" class="star-btn" data-rating="1" style="background: none; border: none; font-size: 1.8rem; cursor: pointer; color: #ccc; transition: all 0.2s;">★</button>
              <button type="button" class="star-btn" data-rating="2" style="background: none; border: none; font-size: 1.8rem; cursor: pointer; color: #ccc; transition: all 0.2s;">★</button>
              <button type="button" class="star-btn" data-rating="3" style="background: none; border: none; font-size: 1.8rem; cursor: pointer; color: #ccc; transition: all 0.2s;">★</button>
              <button type="button" class="star-btn" data-rating="4" style="background: none; border: none; font-size: 1.8rem; cursor: pointer; color: #ccc; transition: all 0.2s;">★</button>
              <button type="button" class="star-btn" data-rating="5" style="background: none; border: none; font-size: 1.8rem; cursor: pointer; color: #ccc; transition: all 0.2s;">★</button>
            </div>
            <input type="hidden" id="home-feedback-rating-value" name="rating" value="0">
          </div>
          
          <div>
            <label for="home-feedback-message" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary); font-size: 0.95rem;"><strong>Message</strong></label>
            <textarea id="home-feedback-message" name="message" placeholder="Describe your feedback in detail..." required style="width: 100%; padding: 0.75rem 1rem; border: 2px solid var(--border); border-radius: var(--radius-lg); font-family: inherit; font-size: 1rem; background: var(--bg-secondary); min-height: 120px; resize: vertical;"></textarea>
          </div>
          
          <button type="submit" style="background: var(--primary); color: white; border: none; padding: 0.875rem 2rem; border-radius: var(--radius-lg); font-weight: 600; cursor: pointer; transition: all 0.3s; align-self: flex-start;"><i class="fas fa-paper-plane"></i> Submit Feedback</button>
        </form>
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

  // Star rating system
  const starButtons = document.querySelectorAll('#home-feedback-rating .star-btn');
  starButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const rating = this.dataset.rating;
      document.getElementById('home-feedback-rating-value').value = rating;
      starButtons.forEach(b => b.style.color = '#ccc');
      for (let i = 0; i < rating; i++) {
        starButtons[i].style.color = '#8A7650';
      }
    });
  });

  // Feedback form submission
  document.getElementById('homepageFeedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'submit_feedback');
    formData.append('subject', document.getElementById('home-feedback-subject').value);
    formData.append('message', document.getElementById('home-feedback-message').value);
    formData.append('rating', document.getElementById('home-feedback-rating-value').value);
    
    fetch('/SINTA/public/index.php?route=feedback', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showHomeFeedbackToast('Thank you! Your feedback has been submitted.', 'success');
        document.getElementById('homepageFeedbackForm').reset();
        document.querySelectorAll('#home-feedback-rating .star-btn').forEach(b => b.style.color = '#ccc');
        document.getElementById('home-feedback-rating-value').value = '0';
      } else {
        showHomeFeedbackToast(data.message || 'Error submitting feedback', 'error');
      }
    })
    .catch(err => {
      showHomeFeedbackToast('Error: ' + err.message, 'error');
    });
  });

  function showHomeFeedbackToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    toast.style.cssText = `
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      color: white;
      font-weight: 600;
      z-index: 9999;
      animation: slideIn 0.3s ease;
      background: ${type === 'success' ? '#2e7d32' : '#c62828'};
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }
</script>
</body>
</html>
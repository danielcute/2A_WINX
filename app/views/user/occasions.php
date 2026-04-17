<?php $page = 'occasions'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Occasions — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
  <style>
    /* Styles remain the same */
    .app-shell {
      padding-top: 76px;
      min-height: 100vh;
      background: var(--bg-primary);
    }
    
    .occ-main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 3rem 2rem 5rem;
    }
    
    .occ-header {
      text-align: center;
      margin-bottom: 3rem;
    }
    
    .occ-header h1 {
      margin: 0.5rem 0 0.75rem;
    }
    
    .occ-header__sub {
      color: var(--text-muted);
      max-width: 500px;
      margin: 0 auto;
    }
    
    .occ-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }
    
    .occ-card {
      background: var(--bg-card);
      border-radius: var(--radius-xl);
      overflow: hidden;
      border: 1px solid var(--border);
      text-decoration: none;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
    }
    
    .occ-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary);
    }
    
    .occ-card__img {
      height: 180px;
      background-size: cover;
      background-position: center;
      transition: transform 0.5s ease;
    }
    
    .occ-card:hover .occ-card__img {
      transform: scale(1.05);
    }
    
    .occ-card__body {
      padding: 1.5rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .occ-card__body--center {
      flex-direction: column;
      text-align: center;
      justify-content: center;
      padding: 2rem;
    }
    
    .occ-card__icon {
      width: 48px;
      height: 48px;
      border-radius: var(--radius-md);
      background: var(--primary-pale);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
      transition: all 0.3s ease;
    }
    
    .occ-card:hover .occ-card__icon {
      background: var(--primary);
      color: white;
    }
    
    .occ-card__icon--lg {
      width: 60px;
      height: 60px;
      font-size: 1.5rem;
    }
    
    .occ-card__text {
      flex: 1;
    }
    
    .occ-card h3 {
      font-size: 1.2rem;
      margin-bottom: 0.25rem;
      color: var(--text-primary);
    }
    
    .occ-card p {
      font-size: 0.8rem;
      color: var(--text-muted);
      line-height: 1.5;
    }
    
    .occ-card__price {
      display: inline-block;
      font-size: 0.7rem;
      font-weight: 600;
      color: var(--primary);
      background: var(--primary-pale);
      padding: 0.2rem 0.7rem;
      border-radius: var(--radius-full);
      margin-top: 0.5rem;
    }
    
    .occ-card__arrow {
      color: var(--text-light);
      transition: all 0.3s ease;
    }
    
    .occ-card:hover .occ-card__arrow {
      color: var(--primary);
      transform: translateX(4px);
    }
    
    @media (max-width: 900px) {
      .occ-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    @media (max-width: 560px) {
      .occ-grid {
        grid-template-columns: 1fr;
      }
      .occ-main {
        padding: 2rem 1rem 4rem;
      }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="app-shell">
  <main class="occ-main">
    
    <div class="occ-header animate-fade-up">
      <div class="eyebrow"><span class="rule"></span> Step 1 of 3</div>
      <h1>What are we <em>celebrating?</em></h1>
      <p class="occ-header__sub">Choose an occasion and we'll guide you to the perfect packages and vendors.</p>
    </div>
    
    <div class="occ-grid stagger">
      <a href="/SINTA/public/index.php?route=packages&occasion=wedding" class="occ-card">
        <div class="occ-card__img" style="background-image:url('/SINTA/public/assets/img/wedding.jpg')"></div>
        <div class="occ-card__body">
          <div class="occ-card__icon"><i class="fas fa-ring"></i></div>
          <div class="occ-card__text">
            <h3>Wedding</h3>
            <p>Full-service planning from intimate to grand</p>
            <span class="occ-card__price">From ₱150K</span>
          </div>
          <i class="fas fa-arrow-right occ-card__arrow"></i>
        </div>
      </a>
      
      <a href="/SINTA/public/index.php?route=packages&occasion=debut" class="occ-card">
        <div class="occ-card__img" style="background-image:url('/SINTA/public/assets/img/debut.jpg')"></div>
        <div class="occ-card__body">
          <div class="occ-card__icon"><i class="fas fa-crown"></i></div>
          <div class="occ-card__text">
            <h3>Debut</h3>
            <p>Celebrate 18th birthday in elegant style</p>
            <span class="occ-card__price">From ₱80K</span>
          </div>
          <i class="fas fa-arrow-right occ-card__arrow"></i>
        </div>
      </a>
      
      <a href="/SINTA/public/index.php?route=packages&occasion=birthday" class="occ-card">
        <div class="occ-card__img" style="background-image:url('/SINTA/public/assets/img/birthday.jpg')"></div>
        <div class="occ-card__body">
          <div class="occ-card__icon"><i class="fas fa-cake-candles"></i></div>
          <div class="occ-card__text">
            <h3>Birthday</h3>
            <p>Memorable celebrations for all ages</p>
            <span class="occ-card__price">From ₱50K</span>
          </div>
          <i class="fas fa-arrow-right occ-card__arrow"></i>
        </div>
      </a>
      
      <a href="/SINTA/public/index.php?route=packages&occasion=corporate" class="occ-card">
        <div class="occ-card__img" style="background-image:url('/SINTA/public/assets/img/corporate.jpg')"></div>
        <div class="occ-card__body">
          <div class="occ-card__icon"><i class="fas fa-briefcase"></i></div>
          <div class="occ-card__text">
            <h3>Corporate</h3>
            <p>Professional galas, conferences, launches</p>
            <span class="occ-card__price">From ₱200K</span>
          </div>
          <i class="fas fa-arrow-right occ-card__arrow"></i>
        </div>
      </a>
      
      <a href="/SINTA/public/index.php?route=packages&occasion=anniversary" class="occ-card">
        <div class="occ-card__img" style="background-image:url('/SINTA/public/assets/img/anniversary.jpg')"></div>
        <div class="occ-card__body">
          <div class="occ-card__icon"><i class="fas fa-heart"></i></div>
          <div class="occ-card__text">
            <h3>Anniversary</h3>
            <p>Celebrate years of love and commitment</p>
            <span class="occ-card__price">From ₱60K</span>
          </div>
          <i class="fas fa-arrow-right occ-card__arrow"></i>
        </div>
      </a>
      
      <a href="/SINTA/public/index.php?route=packages&occasion=other" class="occ-card">
        <div class="occ-card__body occ-card__body--center">
          <div class="occ-card__icon occ-card__icon--lg"><i class="fas fa-plus"></i></div>
          <div class="occ-card__text">
            <h3>Other Events</h3>
            <p>Christening, reunion, graduation, or custom celebration</p>
            <span class="occ-card__price">Custom Quote</span>
          </div>
          <i class="fas fa-arrow-right occ-card__arrow"></i>
        </div>
      </a>
    </div>
    
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
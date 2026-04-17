<?php $page = 'plans'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Plans — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
  <style>
    .app-shell {
      padding-top: 76px;
      min-height: 100vh;
      background: var(--bg-primary);
    }
    
    .plans-main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2.5rem 2rem 5rem;
    }
    
    .plans-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 1.5rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
    }
    
    .plans-header h1 {
      margin: 0.3rem 0 0.4rem;
    }
    
    .plans-header__sub {
      color: var(--text-muted);
      font-size: 0.9rem;
    }
    
    .plans-tabs {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
    }
    
    .plans-tab {
      padding: 0.6rem 1.2rem;
      border-radius: 60px;
      border: 1.5px solid var(--border);
      background: var(--bg-primary);
      font-size: 0.8rem;
      font-weight: 500;
      color: var(--text-muted);
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .plans-tab:hover {
      border-color: var(--primary);
      color: var(--primary);
    }
    
    .plans-tab.active {
      background: var(--primary);
      border-color: var(--primary);
      color: white;
    }
    
    .plans-tab__count {
      background: rgba(255,255,255,0.2);
      padding: 0.1rem 0.4rem;
      border-radius: 20px;
      margin-left: 0.4rem;
    }
    
    .plans-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }
    
    .plan-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      overflow: hidden;
      text-decoration: none;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
    }
    
    .plan-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary);
    }
    
    .plan-card__img {
      height: 180px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    
    .plan-card__img .badge {
      position: absolute;
      top: 1rem;
      left: 1rem;
    }
    
    .plan-card__body {
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .plan-card__type {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--primary);
      font-weight: 600;
    }
    
    .plan-card h3 {
      font-size: 1.2rem;
      margin: 0;
    }
    
    .plan-card__meta {
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
      margin: 0.5rem 0;
    }
    
    .plan-card__meta span {
      font-size: 0.75rem;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }
    
    .plan-card__meta i {
      color: var(--primary);
      font-size: 0.7rem;
    }
    
    .plan-card__footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 1rem;
      border-top: 1px solid var(--border);
      margin-top: auto;
    }
    
    .plan-card__price {
      font-family: var(--serif);
      font-size: 1.2rem;
      font-weight: 500;
      color: var(--text-primary);
    }
    
    .plan-card__link {
      font-size: 0.75rem;
      color: var(--primary);
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.3rem;
    }
    
    @media (max-width: 900px) {
      .plans-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    @media (max-width: 560px) {
      .plans-grid {
        grid-template-columns: 1fr;
      }
      .plans-main {
        padding: 2rem 1rem 4rem;
      }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="app-shell">
  <main class="plans-main">
    
    <div class="plans-header animate-fade-up">
      <div>
        <div class="eyebrow"><span class="rule"></span> Your Events</div>
        <h1>My <em>Plans</em></h1>
        <p class="plans-header__sub">Track all your upcoming and past celebrations in one place.</p>
      </div>
      <a href="/SINTA/public/index.php?route=occasions" class="btn btn--primary"><i class="fas fa-plus"></i> New Event</a>
    </div>
    
    <div class="plans-tabs animate-fade-up delay-1">
      <button class="plans-tab active" onclick="filterPlans('all',this)">All <span class="plans-tab__count">7</span></button>
      <button class="plans-tab" onclick="filterPlans('confirmed',this)">Confirmed <span class="plans-tab__count">2</span></button>
      <button class="plans-tab" onclick="filterPlans('planning',this)">Planning <span class="plans-tab__count">1</span></button>
      <button class="plans-tab" onclick="filterPlans('pending',this)">Pending <span class="plans-tab__count">1</span></button>
      <button class="plans-tab" onclick="filterPlans('completed',this)">Completed <span class="plans-tab__count">3</span></button>
    </div>
    
    <div class="plans-grid stagger" id="plansGrid">
      <?php
      $events = [
        ['id'=>1,'name'=>'Santos Wedding', 'type'=>'Wedding', 'date'=>'Aug 12, 2025','location'=>'Bacolod City','status'=>'confirmed','price'=>'₱220,000','img'=>'/SINTA/public/assets/img/wedding2.jpg'],
        ['id'=>2,'name'=>"Mom's 70th Birthday",'type'=>'Birthday', 'date'=>'Oct 3, 2025', 'location'=>'Iloilo City', 'status'=>'planning', 'price'=>'₱85,000', 'img'=>'/SINTA/public/assets/img/birthday2.jpg'],
        ['id'=>3,'name'=>'Ayala Gala Night', 'type'=>'Corporate','date'=>'Nov 28, 2025','location'=>'Bacolod City','status'=>'pending',  'price'=>'₱200,000','img'=>'/SINTA/public/assets/img/ayala.jpg'],
        ['id'=>4,'name'=>'Reyes Wedding', 'type'=>'Wedding', 'date'=>'Mar 15, 2025','location'=>'Manila', 'status'=>'completed','price'=>'₱180,000','img'=>'/SINTA/public/assets/img/wedding3.jpg'],
        ['id'=>5,'name'=>"Lani's Debut", 'type'=>'Debut', 'date'=>'Jan 20, 2025','location'=>'Bacolod City','status'=>'completed','price'=>'₱95,000', 'img'=>'/SINTA/public/assets/img/debut.jpg'],
        ['id'=>6,'name'=>'TechCorp Summit', 'type'=>'Corporate','date'=>'Feb 8, 2025', 'location'=>'Cebu City', 'status'=>'completed','price'=>'₱250,000','img'=>'/SINTA/public/assets/img/corporate2.jpg'],
      ];
      $badgeMap = ['confirmed'=>'badge--success','planning'=>'badge--primary','pending'=>'badge--warning','completed'=>'badge--info'];
      $labelMap = ['confirmed'=>'Confirmed','planning'=>'Planning','pending'=>'Pending','completed'=>'Completed'];
      foreach($events as $e): ?>
      <a href="/SINTA/public/index.php?route=event-detail&id=<?= $e['id'] ?>" class="plan-card" data-status="<?= $e['status'] ?>">
        <div class="plan-card__img" style="background-image:url('<?= $e['img'] ?>')">
          <span class="badge <?= $badgeMap[$e['status']] ?>"><?= $labelMap[$e['status']] ?></span>
        </div>
        <div class="plan-card__body">
          <span class="plan-card__type"><?= $e['type'] ?></span>
          <h3><?= $e['name'] ?></h3>
          <div class="plan-card__meta">
            <span><i class="fas fa-calendar"></i> <?= $e['date'] ?></span>
            <span><i class="fas fa-location-dot"></i> <?= $e['location'] ?></span>
          </div>
          <div class="plan-card__footer">
            <span class="plan-card__price"><?= $e['price'] ?></span>
            <span class="plan-card__link">View Details <i class="fas fa-arrow-right"></i></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    
  </main>
</div>

<script>
function filterPlans(status, btn) {
  document.querySelectorAll('.plans-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.plan-card').forEach(card => {
    card.style.display = (status === 'all' || card.dataset.status === status) ? '' : 'none';
  });
}

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
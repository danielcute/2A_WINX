<?php $page = 'about'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us — Sinta</title>
 <link rel="stylesheet" href="/SINTA/public/assets/css/global.css" />
  <style>
    .about-intro {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 5rem;
      align-items: center;
    }
    .about-visual {
      border-radius: var(--radius-xl);
      background: var(--cream);
      min-height: 520px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
      background-image: url('https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&h=600&fit=crop');
      background-size: cover;
      background-position: center;
    }
    .about-visual::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(196,149,106,0.2) 0%, rgba(0,0,0,0.1) 100%);
    }
    .about-visual::after {
      content: '"sinta"';
      position: absolute;
      bottom: 1.5rem;
      right: 2rem;
      font-family: var(--serif);
      font-style: italic;
      font-size: 1.2rem;
      color: var(--white);
      opacity: 0.8;
      z-index: 1;
    }
    .values-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
      margin-top: 3rem;
    }
    .value-card {
      padding: 2rem;
      background: var(--white);
      border-radius: var(--radius-lg);
      border: 1px solid var(--border);
      transition: all var(--t-base);
    }
    .value-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-md);
      border-color: transparent;
    }
    .value-card__icon { font-size: 2rem; margin-bottom: 1rem; }
    .value-card__title { font-family: var(--serif); font-size: 1.2rem; margin-bottom: 0.5rem; }
    
    .team-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.5rem;
      margin-top: 3rem;
    }
    .team-card { text-align: center; }
    .team-card__avatar {
      width: 100%;
      aspect-ratio: 1;
      border-radius: var(--radius-lg);
      background: var(--cream);
      border: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3.5rem;
      margin-bottom: 1.2rem;
      transition: all var(--t-base);
      background-size: cover;
      background-position: center;
    }
    .team-card:hover .team-card__avatar { box-shadow: var(--shadow-md); transform: translateY(-4px); }
    .team-card__name { font-family: var(--serif); font-size: 1.1rem; }
    .team-card__role { font-size: var(--fz-xs); color: var(--gold); font-weight: 500; letter-spacing: 0.12em; text-transform: uppercase; margin-top: 0.2rem; }
    
    .partner-row {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      margin-top: 2rem;
    }
    .partner-chip {
      padding: 0.6rem 1.25rem;
      border-radius: var(--radius-full);
      border: 1.5px solid var(--border);
      background: var(--white);
      font-size: var(--fz-sm);
      color: var(--mid);
      transition: all var(--t-fast);
    }
    .partner-chip:hover { border-color: var(--gold); color: var(--dark); transform: translateY(-2px); }
    
    @media (max-width: 1024px) { .team-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) {
      .about-intro { grid-template-columns: 1fr; }
      .about-visual { display: none; }
      .values-grid { grid-template-columns: 1fr; }
      .team-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>
<nav class="nav">
  <div class="nav__inner">
    <a href="/SINTA/public/index.php?route=landing" class="nav__logo">Sint<em>a</em></a>
    <div class="nav__links">
      <a href="/SINTA/public/index.php?route=landing">Home</a>
      <a href="/SINTA/public/index.php?route=occasions">Occasions</a>
      <a href="/SINTA/public/index.php?route=bundles">Bundles</a>
      <a href="/SINTA/public/index.php?route=gallery">Gallery</a>
      <a href="/SINTA/public/index.php?route=about" class="active">About</a>
      <a href="/SINTA/public/index.php?route=contact">Contact</a>
    </div>
    <div class="nav__actions">
      <a href="/SINTA/public/index.php?route=signin" class="btn btn--ghost btn--sm">Sign In</a>
      <a href="/SINTA/public/index.php?route=signup" class="btn btn--dark btn--sm">Get Started</a>
    </div>
  </div>
</nav>

<div class="page-hero">
  <div class="container">
    <div class="page-hero__inner">
      <div class="breadcrumb"><a href="index.php">Home</a><span>/</span>About</div>
      <div class="eyebrow page-hero__label mt-sm"><span class="rule"></span>Our Story</div>
      <h1>We plan events.<br><em class="italic">We create memories.</em></h1>
      <p>Sinta was born from a simple belief — that every milestone deserves to be celebrated with beauty, intention, and love.</p>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="about-intro">
      <div class="reveal">
        <span class="eyebrow"><span class="rule"></span>The Sinta Story</span>
        <h2 class="mt-sm">Built on passion,<br><em class="italic">grown by trust</em></h2>
        <p class="mt-md" style="font-size:var(--fz-lg)">Founded in 2016 in Bacolod City, Sinta began as a one-woman studio with a simple mission: make people feel truly celebrated.</p>
        <p class="mt-sm">What started with intimate home gatherings grew into one of the Philippines' most trusted boutique event organizers. Today, our team of dedicated specialists has planned over 1,200 events — from whisper-quiet anniversaries to grand gala nights with 400 guests.</p>
        <p class="mt-sm">The name <em>Sinta</em> — the Hiligaynon and Filipino word for love and affection — is our north star. Every decision we make is guided by that word.</p>
        <div style="display:flex;gap:3rem;margin-top:2.5rem;padding-top:2rem;border-top:1px solid var(--border)">
          <div class="stat"><div class="stat__num">8<em>+</em></div><div class="stat__label">Years Established</div></div>
          <div class="stat"><div class="stat__num">1,200<em>+</em></div><div class="stat__label">Events Planned</div></div>
          <div class="stat"><div class="stat__num">50<em>+</em></div><div class="stat__label">Trusted Partners</div></div>
        </div>
      </div>
      <div class="about-visual animate-float"></div>
    </div>
  </div>
</section>

<section class="section--sm" style="background:var(--cream);">
  <div class="container">
    <div class="sec-head sec-head--c">
      <span class="eyebrow"><span class="rule"></span>What We Stand For</span>
      <h2 class="mt-sm">Our values guide<br><em class="italic">everything we do</em></h2>
    </div>
    <div class="values-grid stagger">
      <?php $vals=[
        ['🤝','Trust & Transparency','We communicate clearly, price fairly, and keep every promise we make to our clients.'],
        ['🌺','Meticulous Detail','From the centrepiece to the countdown, no detail is too small to deserve our full attention.'],
        ['💡','Creative Courage','We push creative boundaries to design events that are truly unique — not cookie-cutter.'],
        ['💛','Genuine Care','We treat every client\'s celebration as if it were our own. That\'s the Sinta difference.'],
        ['⚡','Flawless Execution','On the day, we move with calm efficiency so you can be fully present in the moment.'],
        ['🌱','Responsible Practice','We partner with sustainable vendors and minimize waste without compromising beauty.'],
      ]; foreach($vals as $v): ?>
      <div class="value-card">
        <div class="value-card__icon"><?= $v[0] ?></div>
        <div class="value-card__title"><?= $v[1] ?></div>
        <p style="font-size:var(--fz-sm); color: var(--mid);"><?= $v[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="sec-head sec-head--c">
      <span class="eyebrow"><span class="rule"></span>The Team</span>
      <h2 class="mt-sm">Meet the people behind<br><em class="italic">your perfect day</em></h2>
    </div>
    <div class="team-grid stagger">
      <?php $team=[
        ['https://randomuser.me/api/portraits/women/68.jpg','Isabella Reyes','Founder & Creative Director','Eight years of turning visions into reality. Isabella leads with heart and relentless passion.'],
        ['https://randomuser.me/api/portraits/women/32.jpg','Carmela Santos','Head Event Coordinator','The calm in every storm. Carmela runs logistics with seamless precision.'],
        ['https://randomuser.me/api/portraits/men/32.jpg','Marcus Dela Torre','Floral & Design Lead','A self-taught artist who transforms spaces into living, breathing masterpieces.'],
        ['https://randomuser.me/api/portraits/women/44.jpg','Sofia Aguila','Culinary Partnerships','Sofia curates our catering partners to ensure every plate is as beautiful as it is delicious.'],
      ]; foreach($team as $t): ?>
      <div class="team-card">
        <div class="team-card__avatar" style="background-image: url('<?= $t[0] ?>'); background-size: cover;"></div>
        <div class="team-card__name"><?= $t[1] ?></div>
        <div class="team-card__role"><?= $t[2] ?></div>
        <p class="team-card__bio" style="font-size:0.78rem; color:var(--mid); margin-top:0.5rem;"><?= $t[3] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section--sm" style="background:var(--cream);">
  <div class="container">
    <div class="sec-head">
      <span class="eyebrow"><span class="rule"></span>Our Partners</span>
      <h2 class="mt-sm" style="font-size:2rem">We work with<br><em class="italic">the very best</em></h2>
      <p class="mt-sm" style="color:var(--mid);">Every vendor in our network is personally vetted and shares our commitment to excellence.</p>
    </div>
    <div class="partner-row stagger">
      <?php $partners=['Grand Ballroom Bacolod','Calea Pastries & Coffee','Casa de Flores','Studio Luz Photography','The Vow Events Place','Liana\'s Catering','Azul Private Events','Riverside Garden Venue','Boholano Woodcraft','Negrense Sounds & Lights']; foreach($partners as $p): ?>
      <span class="partner-chip"><?= $p ?></span>
      <?php endforeach; ?>
    </div>
    <a href="contact.php" class="btn btn--outline-accent btn--lg mt-lg" style="margin-top:2rem; display: inline-block;">Become a Partner</a>
  </div>
</section>

<section class="section--sm">
  <div class="container">
    <div class="cta__band" style="background:var(--dark); border-radius:var(--radius-xl); padding:4rem; display:flex; align-items:center; justify-content:space-between; gap:2rem; flex-wrap:wrap;">
      <div>
        <span class="eyebrow" style="color:var(--gold);"><span class="rule" style="background:var(--gold);"></span>Start Today</span>
        <h2 style="color:var(--white); margin-top:0.5rem; max-width:420px;">Let's create something <em class="italic" style="color:var(--gold);">beautiful</em> together</h2>
      </div>
      <div style="display:flex; gap:1rem; flex-wrap:wrap;">
        <a href="booking.php" class="btn btn--gold btn--lg">Book Your Event</a>
        <a href="contact.php" class="btn btn--outline-light btn--lg">Get in Touch</a>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">
    <div class="footer__top">
      <div><a href="index.php" class="footer__logo">Sint<em>a</em></a><p class="footer__desc">Crafting extraordinary event experiences across the Philippines.</p><div class="footer__social"><a href="#">fb</a><a href="#">ig</a><a href="#">tw</a></div></div>
      <div class="footer__col"><div class="footer__col-title">Occasions</div><ul><li><a href="#">Weddings</a></li><li><a href="#">Debuts</a></li><li><a href="#">Corporate</a></li></ul></div>
      <div class="footer__col"><div class="footer__col-title">Company</div><ul><li><a href="about.php">About</a></li><li><a href="bundles.php">Bundles</a></li><li><a href="contact.php">Contact</a></li></ul></div>
      <div class="footer__col"><div class="footer__col-title">Newsletter</div><div class="footer__input-wrap"><input type="email" placeholder="your@email.com"><button>→</button></div></div>
    </div>
    <div class="footer__bottom"><span>© <?= date('Y') ?> Sinta. All rights reserved.</span><div class="footer__bottom-links"><a href="#">Privacy</a><a href="#">Terms</a></div></div>
  </div>
</footer>

<script>
  const reveals = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('active');
    });
  }, { threshold: 0.1 });
  reveals.forEach(el => observer.observe(el));
</script>
</body>
</html>
<?php $page = 'plans';
$id = $_GET['id'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Details — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
  <style>
    /* Event Detail Specific Styles */
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      font-size: 0.78rem;
      color: #6B6463;
      margin-bottom: 1.5rem;
    }
    .breadcrumb a { color: #8A7650; display: flex; align-items: center; gap: 0.4rem; text-decoration: none; }
    
    .evd-main {
      max-width: 1100px;
      margin: 0 auto;
      padding: 2.5rem 2rem 5rem;
    }
    
    .evd-hero {
      border-radius: 20px;
      overflow: hidden;
      position: relative;
      margin-bottom: 2rem;
      height: 300px;
    }
    .evd-hero__img {
      width: 100%; height: 100%;
      background-size: cover;
      background-position: center;
      position: absolute;
      inset: 0;
      transition: transform 0.6s ease;
    }
    .evd-hero:hover .evd-hero__img { transform: scale(1.03); }
    .evd-hero__overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,0.72) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);
      display: flex;
      align-items: flex-end;
    }
    .evd-hero__content {
      padding: 2rem 2.5rem;
      color: #fff;
    }
    .evd-hero__content h1 { font-size: clamp(1.6rem, 3vw, 2.4rem); margin: 0.5rem 0; color: white; }
    .evd-hero__meta {
      display: flex;
      flex-wrap: wrap;
      gap: 1.25rem;
      font-size: 0.8rem;
    }
    .badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.7rem;
      font-weight: 600;
    }
    .badge--green { background: #e8f5e9; color: #2e7d32; }
    
    .evd-grid {
      display: grid;
      grid-template-columns: 1fr 360px;
      gap: 1.5rem;
      align-items: start;
    }
    
    .card {
      background: white;
      border: 1px solid #E2D9C8;
      border-radius: 16px;
      overflow: hidden;
      margin-bottom: 1.25rem;
    }
    .evd-card__head {
      padding: 1rem 1.4rem;
      border-bottom: 1px solid #E2D9C8;
      background: #F5F0E8;
    }
    .evd-card__head h4 {
      display: flex; align-items: center; gap: 0.5rem;
      font-size: 0.88rem; font-weight: 600; margin: 0;
    }
    .evd-card__head h4 i { color: #8A7650; }
    .evd-card__body { padding: 1.4rem; }
    
    .evd-pkg-label {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.15rem;
      margin-bottom: 1rem;
    }
    .evd-inclusions { list-style: none; padding: 0; margin: 0; }
    .evd-inclusions li { display: flex; align-items: center; gap: 0.55rem; padding: 0.4rem 0; font-size: 0.85rem; }
    .evd-inclusions li i { color: #8A7650; font-size: 0.75rem; }
    
    .evd-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 2rem; }
    .evd-info-item span { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; color: #6B6463; display: block; margin-bottom: 0.15rem; }
    .evd-info-item strong { font-size: 0.9rem; font-weight: 500; }
    
    .evd-timeline { display: flex; flex-direction: column; gap: 0; }
    .evd-tl-item {
      display: flex;
      gap: 1rem;
      align-items: flex-start;
      padding-bottom: 1.25rem;
      position: relative;
    }
    .evd-tl-dot {
      width: 10px; height: 10px;
      border-radius: 50%;
      background: #D4CAB5;
      flex-shrink: 0;
      margin-top: 0.35rem;
    }
    .evd-tl-item--done .evd-tl-dot { background: #8A7650; }
    .evd-tl-item strong { display: block; font-size: 0.85rem; }
    .evd-tl-item p { font-size: 0.8rem; color: #6B6463; margin: 0; }
    
    .evd-summary-rows { margin-bottom: 1rem; }
    .evd-summary-row { display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.82rem; }
    .evd-summary-total {
      display: flex; justify-content: space-between;
      padding-top: 1rem;
      border-top: 1px solid #E2D9C8;
      font-weight: bold;
    }
    
    .evd-payment-progress { margin-bottom: 1rem; }
    .evd-payment-bar { height: 6px; background: #F5F0E8; border-radius: 100px; overflow: hidden; margin-bottom: 0.5rem; }
    .evd-payment-fill { height: 100%; background: #8A7650; border-radius: 100px; }
    .evd-payment-labels { display: flex; justify-content: space-between; font-size: 0.72rem; color: #6B6463; }
    
    .evd-pay-row { display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.82rem; }
    
    .evd-coord { display: flex; align-items: center; gap: 0.85rem; }
    .evd-coord img { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; }
    .evd-coord strong { display: block; font-size: 0.9rem; }
    .evd-coord span { font-size: 0.72rem; color: #6B6463; }
    
    .btn {
      display: inline-block;
      padding: 0.7rem 1.5rem;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s;
      text-align: center;
    }
    .btn--gold { background: #8A7650; color: white; }
    .btn--ghost { background: transparent; border: 1px solid #E2D9C8; color: #6B6463; }
    
    @media (max-width: 900px) {
      .evd-grid { grid-template-columns: 1fr; }
      .evd-main { padding: 2rem 1rem 4rem; }
      .evd-hero { height: 220px; }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<div class="app-shell">
  <main class="evd-main">

    <nav class="breadcrumb">
      <a href="/SINTA/public/index.php?route=plans"><i class="fas fa-arrow-left"></i> My Plans</a>
      <span>/</span>
      <span>Santos Wedding</span>
    </nav>

    <div class="evd-hero">
      <div class="evd-hero__img" style="background-image:url('https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=1400&h=500&fit=crop')"></div>
      <div class="evd-hero__overlay">
        <div class="evd-hero__content">
          <span class="badge badge--green">Confirmed</span>
          <h1>Santos Wedding</h1>
          <div class="evd-hero__meta">
            <span><i class="fas fa-calendar"></i> August 12, 2025</span>
            <span><i class="fas fa-location-dot"></i> Bacolod City</span>
            <span><i class="fas fa-users"></i> ~120 Guests</span>
          </div>
        </div>
      </div>
    </div>

    <div class="evd-grid">
      <div class="evd-left">
        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-box"></i> Selected Package</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-pkg-label">Classic Wedding Package</div>
            <ul class="evd-inclusions">
              <li><i class="fas fa-check-circle"></i> Venue Setup & Styling</li>
              <li><i class="fas fa-check-circle"></i> Catering — 100 pax Buffet</li>
              <li><i class="fas fa-check-circle"></i> Photography (8 hrs)</li>
              <li><i class="fas fa-check-circle"></i> Same-Day Edit Video</li>
              <li><i class="fas fa-check-circle"></i> Professional Host / Emcee</li>
              <li><i class="fas fa-check-circle"></i> Flowers & Centrepieces</li>
              <li><i class="fas fa-check-circle"></i> Full Event Coordination</li>
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-circle-info"></i> Event Information</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-info-grid">
              <div class="evd-info-item"><span>Event Type</span><strong>Wedding</strong></div>
              <div class="evd-info-item"><span>Date</span><strong>August 12, 2025</strong></div>
              <div class="evd-info-item"><span>Time</span><strong>4:00 PM</strong></div>
              <div class="evd-info-item"><span>Venue</span><strong>The Ruins, Talisay City</strong></div>
              <div class="evd-info-item"><span>Guest Count</span><strong>120 pax</strong></div>
              <div class="evd-info-item"><span>Theme</span><strong>Romantic Garden</strong></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-timeline"></i> Program Flow</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-timeline">
              <div class="evd-tl-item evd-tl-item--done"><div class="evd-tl-dot"></div><div><strong>4:00 PM</strong><p>Guest Arrival & Cocktails</p></div></div>
              <div class="evd-tl-item evd-tl-item--done"><div class="evd-tl-dot"></div><div><strong>5:00 PM</strong><p>Ceremony</p></div></div>
              <div class="evd-tl-item"><div class="evd-tl-dot"></div><div><strong>6:00 PM</strong><p>Reception & Program</p></div></div>
              <div class="evd-tl-item"><div class="evd-tl-dot"></div><div><strong>7:30 PM</strong><p>Dinner Buffet Opens</p></div></div>
              <div class="evd-tl-item"><div class="evd-tl-dot"></div><div><strong>9:00 PM</strong><p>Dancing & Celebration</p></div></div>
              <div class="evd-tl-item"><div class="evd-tl-dot"></div><div><strong>11:00 PM</strong><p>Event Ends</p></div></div>
            </div>
          </div>
        </div>
      </div>

      <div class="evd-right">
        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-receipt"></i> Order Summary</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-summary-rows">
              <div class="evd-summary-row"><span>Classic Wedding Package</span><span>₱150,000</span></div>
              <div class="evd-summary-row"><span>Add-on: Drone Coverage</span><span>₱8,000</span></div>
              <div class="evd-summary-row"><span>Add-on: Photo Booth</span><span>₱12,000</span></div>
              <div class="evd-summary-row"><span>Subtotal</span><span>₱170,000</span></div>
              <div class="evd-summary-row"><span>Service Fee (3%)</span><span>₱5,100</span></div>
            </div>
            <div class="evd-summary-total"><span>Total</span><strong>₱175,100</strong></div>
          </div>
        </div>

        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-credit-card"></i> Payment Status</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-payment-progress">
              <div class="evd-payment-bar">
                <div class="evd-payment-fill" style="width: 57%"></div>
              </div>
              <div class="evd-payment-labels">
                <span>Paid: ₱100,000</span>
                <span>Remaining: ₱75,100</span>
              </div>
            </div>
            <div class="evd-pay-row"><span><i class="fas fa-circle-check"></i> Deposit (50%)</span><span class="badge badge--green">Paid</span></div>
            <div class="evd-pay-row"><span><i class="fas fa-clock"></i> Balance (Due Aug 1)</span><span>Pending</span></div>
            <a href="#" class="btn btn--gold" style="width:100%;margin-top:1rem; display: inline-block; text-align: center;">Pay Balance</a>
          </div>
        </div>

        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-headset"></i> Your Coordinator</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-coord">
              <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Coordinator">
              <div>
                <strong>Ana Garcia</strong>
                <span>Senior Event Coordinator</span>
              </div>
            </div>
            <a href="/SINTA/public/index.php?route=messages" class="btn btn--ghost" style="width:100%;margin-top:1rem; display: inline-block; text-align: center;">
              <i class="fas fa-message"></i> Send Message
            </a>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  const nav = document.querySelector('.app-nav');
  if (nav) {
    window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 10));
  }
</script>
</body>
</html>
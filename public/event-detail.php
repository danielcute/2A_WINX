<?php session_start(); $page = 'plans';
$id = $_GET['id'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Details — Sinta</title>
  <link rel="stylesheet" href="assets/css/app.css">
  <link rel="stylesheet" href="assets/css/event-detail.css">
</head>
<body>

<?php include 'public/app-nav.php'; ?>

<div class="app-shell">
  <main class="evd-main">

    <!-- Breadcrumb -->
    <nav class="breadcrumb anim-fadeup">
      <a href="plans.php"><i class="fas fa-arrow-left"></i> My Plans</a>
      <span>/</span>
      <span>Santos Wedding</span>
    </nav>

    <!-- Hero Banner -->
    <div class="evd-hero anim-fadeup delay-1">
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

    <!-- Body Grid -->
    <div class="evd-grid">
      <!-- Left: Details -->
      <div class="evd-left">

        <!-- Package Info -->
        <div class="card evd-card anim-fadeup delay-2">
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

        <!-- Event Info -->
        <div class="card evd-card anim-fadeup delay-3">
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

        <!-- Timeline -->
        <div class="card evd-card anim-fadeup delay-4">
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

      <!-- Right: Summary & Payment -->
      <div class="evd-right">

        <!-- Order Summary -->
        <div class="card evd-card evd-summary anim-fadeup delay-2">
          <div class="evd-card__head">
            <h4><i class="fas fa-receipt"></i> Order Summary</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-summary-rows">
              <div class="evd-summary-row"><span>Classic Wedding Package</span><span>₱150,000</span></div>
              <div class="evd-summary-row"><span>Add-on: Drone Coverage</span><span>₱8,000</span></div>
              <div class="evd-summary-row"><span>Add-on: Photo Booth</span><span>₱12,000</span></div>
              <div class="evd-summary-row evd-summary-row--sub"><span>Subtotal</span><span>₱170,000</span></div>
              <div class="evd-summary-row evd-summary-row--sub"><span>Service Fee (3%)</span><span>₱5,100</span></div>
            </div>
            <div class="evd-summary-total"><span>Total</span><strong>₱175,100</strong></div>
          </div>
        </div>

        <!-- Payment Status -->
        <div class="card evd-card anim-fadeup delay-3">
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
            <div class="evd-payment-items">
              <div class="evd-pay-row"><span><i class="fas fa-circle-check" style="color:var(--green)"></i> Deposit (50%)</span><span class="badge badge--green">Paid</span></div>
              <div class="evd-pay-row"><span><i class="fas fa-clock" style="color:var(--amber)"></i> Balance (Due Aug 1)</span><span class="badge badge--amber">Pending</span></div>
            </div>
            <a href="#" class="btn btn--gold" style="width:100%;margin-top:1rem;">Pay Balance</a>
          </div>
        </div>

        <!-- Contact Coordinator -->
        <div class="card evd-card anim-fadeup delay-4">
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
            <a href="messages.php" class="btn btn--ghost" style="width:100%;margin-top:1rem;">
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
  window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 10));
</script>
</body>
</html>
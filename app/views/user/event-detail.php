<?php
$page = 'plans';
$id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$plan = null;
if ($id > 0) {
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(dirname(__DIR__)));
    }
    require_once ROOT_PATH . '/app/models/Plan.php';
    $planModel = new Plan();
    $plan = $planModel->findById($id);
}

$planTitle = 'Santos Wedding';
$planStatus = 'confirmed';
$planDate = 'August 12, 2025';
$planVenue = 'Bacolod City';
$planGuests = '120 Guests';
$planType = 'Wedding';
$planTime = '4:00 PM';
$planTheme = 'Romantic Garden';
$planSummaryItems = [
    'Venue Setup & Styling',
    'Catering — 100 pax Buffet',
    'Photography (8 hrs)',
    'Same-Day Edit Video',
    'Professional Host / Emcee',
    'Flowers & Centrepieces',
    'Full Event Coordination'
];
$heroImageUrl = '/SINTA/public/assets/img/event-placeholder.jpg';
$programLines = [];
$orderRows = [];
$serviceFee = 0;
$totalPrice = 0;
$packageLabel = 'Selected Package';
$planTypeLabel = 'Event';
$planStatusLabel = 'Pending';
$planStatusClass = 'badge--warning';
if ($plan) {
    $planTitle = htmlspecialchars($plan['event_name'] ?: 'Untitled Event');
    $planStatus = htmlspecialchars($plan['status'] ?: 'pending');
    $planDate = $plan['event_date'] ? date('F j, Y', strtotime($plan['event_date'])) : 'TBD';
    $planVenue = htmlspecialchars($plan['venue'] ?: 'TBD');
    $planGuests = !empty($plan['guest_count']) ? htmlspecialchars($plan['guest_count'] . ' Guests') : 'TBD';
    $planType = htmlspecialchars($plan['occasion_name'] ?: ($plan['package_name'] ?: 'Event'));
    $planTime = htmlspecialchars($plan['event_time'] ?: 'TBD');
    $planTheme = htmlspecialchars($plan['theme'] ?: 'TBD');
    $eventData = json_decode(stripslashes($plan['events']), true);
    $planSummaryItems = [];
    $planItems = [];
    $programLines = [];
    $orderRows = [];
    $subtotal = 0;
    $packageNameFromEvent = '';

    if (is_array($eventData)) {
        // Get package name from events JSON
        if (isset($eventData['packageName']) && !empty($eventData['packageName'])) {
            $packageNameFromEvent = htmlspecialchars($eventData['packageName']);
        }
        
        // Extract selected packages/items from customize
        if (isset($eventData['items']) && is_array($eventData['items']) && !empty($eventData['items'])) {
            foreach ($eventData['items'] as $item) {
                if (!empty($item['name'])) {
                    $planSummaryItems[] = htmlspecialchars($item['name']);
                    $price = isset($item['price']) ? (float)$item['price'] : 0;
                    $subtotal += $price;
                    $orderRows[] = [
                        'label' => htmlspecialchars((!empty($item['category']) ? $item['category'] . ': ' : '') . $item['name']),
                        'price' => $price,
                    ];
                }
            }
            $planItems = $eventData['items'];
        }
        
        // Extract program flow - only if user explicitly entered it during checkout
        if (isset($eventData['programFlow'])) {
            $programFlowData = $eventData['programFlow'];
            if (is_string($programFlowData) && !empty(trim($programFlowData))) {
                $programLines = array_filter(array_map('trim', explode("\n", $programFlowData)));
            }
        }
    }

    // If no items were found in JSON, show event basics as selected items
    if (empty($planSummaryItems)) {
        // Build summary from available data (excluding venue as it's shown in Event Information)
        if (!empty($plan['theme'])) {
            $planSummaryItems[] = 'Theme: ' . htmlspecialchars($plan['theme']);
        }
        // Show package type if available
        if (!empty($plan['package_name'])) {
            $planSummaryItems[] = 'Package: ' . htmlspecialchars($plan['package_name']);
        }
        if (empty($planSummaryItems)) {
            $planSummaryItems[] = 'Event details will be confirmed by the Sinta team.';
        }
    } else {
        // If we have items, we don't need to add venue separately as it's in Event Information
    }

    $serviceFee = round($subtotal * 0.03);
    $totalPrice = $subtotal + $serviceFee;
    
    // If total_price from database doesn't match calculated total, use database value (it's the source of truth)
    if (!empty($plan['total_price'])) {
        $totalPrice = (float)$plan['total_price'];
    }
    // Use package name from events JSON if available, otherwise use database package_name
    $packageLabel = !empty($packageNameFromEvent) ? $packageNameFromEvent : htmlspecialchars($plan['package_name'] ?: 'Selected Package');
    $planTypeLabel = htmlspecialchars($plan['occasion_name'] ?: ($plan['package_name'] ?: ($plan['event_name'] ?: 'Event')));
    $statusLabelMap = ['pending' => 'Pending', 'approved' => 'Confirmed', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'rejected' => 'Rejected', 'canceled' => 'Canceled'];
    $statusBadgeMap = ['pending' => 'badge--warning', 'approved' => 'badge--green', 'confirmed' => 'badge--green', 'completed' => 'badge--info', 'rejected' => 'badge--danger', 'canceled' => 'badge--danger'];
    $planStatusLabel = $statusLabelMap[$planStatus] ?? ucfirst($planStatus);
    $planStatusClass = $statusBadgeMap[$planStatus] ?? 'badge--warning';
    
    // Auto-confirmation logic: check if plan should be confirmed
    require_once ROOT_PATH . '/app/models/PlanAutoConfirmation.php';
    $autoConfirm = new PlanAutoConfirmation();
    $planStatusInfo = $autoConfirm->getPlanStatusInfo($id);
    
    // Update cancellation info for display
    $canCancelPlan = false;
    $minutesRemaining = 0;
    if ($planStatusInfo && $planStatusInfo['can_cancel']) {
        $canCancelPlan = true;
        $minutesRemaining = $planStatusInfo['minutes_remaining'];
    }
    
    // Update status display if auto-confirmed
    if ($planStatusInfo && $planStatusInfo['status'] !== $planStatus) {
        $planStatus = $planStatusInfo['status'];
        $planStatusLabel = $statusLabelMap[$planStatus] ?? ucfirst($planStatus);
        $planStatusClass = $statusBadgeMap[$planStatus] ?? 'badge--warning';
    }

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
    $heroImageUrl = '/SINTA/public/assets/img/event-placeholder.jpg';
    foreach ($eventImageMap as $keyword => $url) {
        if ($keyword && strpos($eventText, $keyword) !== false) {
            $heroImageUrl = $url;
            break;
        }
    }
}
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

    /* Toast Notifications */
    .toast {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      color: white;
      font-weight: 600;
      z-index: 3000;
      animation: slideInToast 0.3s ease;
      max-width: 300px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    .toast.success {
      background: #2e7d32;
    }
    .toast.error {
      background: #c62828;
    }
    .toast.info {
      background: var(--primary);
    }
    @keyframes slideInToast {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    @media (max-width: 640px) {
      .toast {
        bottom: 1rem;
        right: 1rem;
        left: 1rem;
        max-width: none;
      }
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
      <span><?= htmlspecialchars($planTitle) ?></span>
    </nav>

    <div class="evd-hero">
      <div class="evd-hero__img" style="background-image:url('<?= htmlspecialchars($heroImageUrl) ?>')"></div>
      <div class="evd-hero__overlay">
        <div class="evd-hero__content">
          <span class="badge <?= htmlspecialchars($planStatusClass) ?>"><?= htmlspecialchars($planStatusLabel) ?></span>
          <h1 id="eventTitle"><?= htmlspecialchars($planTitle) ?></h1>
          <div class="evd-hero__meta">
            <span><i class="fas fa-calendar"></i> <span id="heroDate"><?= htmlspecialchars($planDate) ?></span></span>
            <span><i class="fas fa-location-dot"></i> <span id="heroVenue"><?= htmlspecialchars($planVenue) ?></span></span>
            <span><i class="fas fa-users"></i> <span id="heroGuests"><?= htmlspecialchars($planGuests) ?></span></span>
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
            <div class="evd-pkg-label"><?= $packageLabel ?></div>
            <ul class="evd-inclusions">
              <?php foreach ($planSummaryItems as $item): ?>
                <li><i class="fas fa-check-circle"></i> <?= $item ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-circle-info"></i> Event Information</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-info-grid">
              <div class="evd-info-item"><span>Event Type</span><strong id="eventTypeValue"><?= $planTypeLabel ?></strong></div>
              <div class="evd-info-item"><span>Date</span><strong id="eventDateValue"><?= htmlspecialchars($planDate) ?></strong></div>
              <div class="evd-info-item"><span>Time</span><strong id="eventTimeValue"><?= htmlspecialchars($planTime) ?></strong></div>
              <div class="evd-info-item"><span>Venue</span><strong id="eventVenueValue"><?= htmlspecialchars($planVenue) ?></strong></div>
              <div class="evd-info-item"><span>Guest Count</span><strong id="eventGuestValue"><?= htmlspecialchars($planGuests) ?></strong></div>
              <div class="evd-info-item"><span>Theme</span><strong id="eventThemeValue"><?= htmlspecialchars($planTheme) ?></strong></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-timeline"></i> Program Flow</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-timeline" id="programFlowTimeline">
              <?php if (!empty($programLines)): ?>
                <?php foreach ($programLines as $index => $line): ?>
                  <?php
                    // Handle both "time - description" format and simple item names
                    $parts = explode(' - ', $line, 2);
                    $hasTime = count($parts) > 1;
                    $time = $hasTime ? trim($parts[0]) : '';
                    $description = trim($parts[$hasTime ? 1 : 0]);
                    
                    $isDone = $index < 2 ? ' evd-tl-item--done' : '';
                  ?>
                  <div class="evd-tl-item<?= $isDone ?>">
                    <div class="evd-tl-dot"></div>
                    <div><strong><?= htmlspecialchars($hasTime ? $time : $description) ?></strong><?php if ($hasTime): ?><p><?= htmlspecialchars($description) ?></p><?php endif; ?></div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="evd-tl-item">
                  <div class="evd-tl-dot"></div>
                  <div><strong>To be added</strong><p>Your program flow will appear once you confirm checkout.</p></div>
                </div>
              <?php endif; ?>
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
            <div class="evd-summary-rows" id="orderSummaryRows">
              <?php if (!empty($orderRows)): ?>
                <?php foreach ($orderRows as $row): ?>
                  <div class="evd-summary-row"><span><?= $row['label'] ?></span><span>₱<?= number_format($row['price'], 0) ?></span></div>
                <?php endforeach; ?>
                <div class="evd-summary-row"><span>Subtotal</span><span>₱<?= number_format($subtotal, 0) ?></span></div>
                <div class="evd-summary-row"><span>Service Fee (3%)</span><span>₱<?= number_format($serviceFee, 0) ?></span></div>
              <?php else: ?>
                <div class="evd-summary-row"><span>Event Total</span><span>₱<?= number_format($plan['total_price'] ?? 0, 0) ?></span></div>
              <?php endif; ?>
            </div>
            <div class="evd-summary-total"><span>Total</span><strong id="orderSummaryTotal">₱<?= number_format($totalPrice > 0 ? $totalPrice : ($plan['total_price'] ?? 0), 0) ?></strong></div>
          </div>
        </div>

        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-credit-card"></i> Payment Status</h4>
          </div>
          <div class="evd-card__body">
            <?php 
              // Calculate payment status based on plan data
              $depositAmount = round($totalPrice * 0.5);
              $balanceAmount = $totalPrice - $depositAmount;
              $balanceDueDate = $plan['event_date'] ? date('F j, Y', strtotime($plan['event_date'] . ' -1 day')) : 'TBD';
              $paymentStatus = $plan['payment_status'] ?? 'pending';
              $isConfirmed = $planStatus === 'confirmed' || $planStatus === 'approved';
              $isDepositPaid = $paymentStatus === 'paid';
              
              // Calculate paid and remaining amounts
              $paidAmount = $isDepositPaid ? $depositAmount : 0;
              $remainingAmount = $totalPrice - $paidAmount;
              $paymentPercentage = $totalPrice > 0 ? round(($paidAmount / $totalPrice) * 100) : 0;
            ?>
            <div class="evd-payment-progress">
              <div class="evd-payment-bar">
                <div class="evd-payment-fill" style="width: <?= $paymentPercentage ?>%"></div>
              </div>
              <div class="evd-payment-labels">
                <span>Paid: ₱<?= number_format($paidAmount, 0) ?></span>
                <span>Remaining: ₱<?= number_format($remainingAmount, 0) ?></span>
              </div>
            </div>
            <div class="evd-pay-row">
              <span><i class="fas fa-<?= $isDepositPaid ? 'circle-check' : 'hourglass-end' ?>"></i> Deposit (50%)</span>
              <span class="badge badge--<?= $isDepositPaid ? 'green' : 'warning' ?>">
                <?= $isDepositPaid ? 'Paid' : ($isConfirmed ? 'Due Now' : 'Not Required Yet') ?>
              </span>
            </div>
            <div class="evd-pay-row">
              <span><i class="fas fa-clock"></i> Balance (Due <?= $balanceDueDate ?>)</span>
              <span class="badge badge--<?= ($isDepositPaid || !$isConfirmed) ? 'warning' : 'info' ?>">
                <?= $isDepositPaid ? 'Due Soon' : 'Pending' ?>
              </span>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
              <?php if ($isConfirmed && !$isDepositPaid): ?>
                <a href="#" class="btn btn--gold" style="flex: 1; text-align: center;"><i class="fas fa-credit-card"></i> Pay Deposit (₱<?= number_format($depositAmount, 0) ?>)</a>
              <?php elseif ($isDepositPaid): ?>
                <a href="#" class="btn btn--gold" style="flex: 1; text-align: center;"><i class="fas fa-credit-card"></i> Pay Balance (₱<?= number_format($balanceAmount, 0) ?>)</a>
              <?php else: ?>
                <button class="btn btn--gold" style="flex: 1; text-align: center; opacity: 0.5; cursor: not-allowed;" disabled title="Booking must be confirmed first"><i class="fas fa-credit-card"></i> Payment</button>
              <?php endif; ?>
              <?php if ($canCancelPlan): ?>
                <button onclick="cancelEventPlan(event, <?= $id ?>, '<?= htmlspecialchars($planTitle) ?>')" class="btn btn--outline" style="flex: 1;" title="Cancel within <?= $minutesRemaining ?> minutes. Cancel before 30 minutes to skip deposit payment requirement.">
                  <i class="fas fa-ban"></i> Cancel (<?= $minutesRemaining ?>m)
                </button>
              <?php endif; ?>
            </div>
            <?php if ($planStatus === 'pending'): ?>
              <div style="background: rgba(255, 193, 7, 0.1); border-left: 4px solid #FFC107; padding: 0.75rem 1rem; margin-top: 1rem; border-radius: 4px;">
                <p style="font-size: 0.8rem; color: #F57F17; margin: 0;"><i class="fas fa-info-circle"></i> This booking will auto-confirm in <?= $minutesRemaining ?> minutes. After confirmation, a 50% deposit payment will be required.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="evd-card__head">
            <h4><i class="fas fa-headset"></i> Your Coordinator</h4>
          </div>
          <div class="evd-card__body">
            <div class="evd-coord">
              <img src="/SINTA/public/assets/img/logo.png" alt="SINTA Coordinator">
              <div>
                <strong>SINTA Event Team</strong>
                <span>Official Event Coordinator & Organizer</span>
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
  // Toast notification function
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }

  const nav = document.querySelector('.app-nav');
  if (nav) {
    window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 10));
  }
  
  // Cancel event plan
  function cancelEventPlan(event, planId, eventName) {
    event.preventDefault();
    
    // Check current cancellation status
    fetch('/SINTA/public/api-plan.php?action=check_cancellation&plan_id=' + planId)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.can_cancel) {
          if (confirm(`Are you sure you want to cancel "${eventName}"?\n\nTime remaining to cancel: ${data.minutes_remaining} minutes\n\nThis action cannot be undone.`)) {
            // Proceed with cancellation
            const formData = new FormData();
            formData.append('action', 'cancel_plan');
            formData.append('plan_id', planId);
            
            fetch('/SINTA/public/api-plan.php', {
              method: 'POST',
              body: formData
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showToast('Plan cancelled successfully! Redirecting...', 'success');
                setTimeout(() => {
                  window.location.href = '/SINTA/public/index.php?route=plans';
                }, 1500);
              } else {
                showToast(data.message || 'Failed to cancel plan', 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              showToast('An error occurred while cancelling the plan', 'error');
            });
          }
        } else {
          showToast(data.reason || 'This plan cannot be cancelled', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while checking cancellation status');
      });
  }
</script>
</body>
</html>
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
  <link rel="stylesheet" href="/assets/css/global.css">
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
    
    .plan-card__summary,
    .plan-card__program {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: wrap;
      color: var(--text-muted);
      font-size: 0.8rem;
      margin-top: 0.75rem;
      line-height: 1.4;
    }
    .plan-card__summary i,
    .plan-card__program i {
      color: var(--primary);
      font-size: 0.75rem;
    }
    .plan-card__summary span,
    .plan-card__program span {
      display: inline-block;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .plan-card__program span {
      width: 100%;
    }
    
    .plan-card__program-label {
      font-weight: 600;
      color: var(--primary);
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.12em;
    }
    
    .plan-card__link {
      font-size: 0.75rem;
      color: var(--primary);
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.3rem;
    }
    
    .btn-delete-plan {
      background: transparent;
      color: #f44336;
      border: 1.5px solid #f44336;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.2s ease;
    }
    
    .btn-delete-plan:hover {
      background: rgba(244, 67, 54, 0.1);
      border-color: #d32f2f;
      color: #d32f2f;
      transform: translateY(-2px);
    }

    .btn-cancel-plan {
      background: transparent;
      color: #ff9800;
      border: 1.5px solid #ff9800;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.2s ease;
    }

    .btn-cancel-plan:hover {
      background: rgba(255, 152, 0, 0.1);
      border-color: #f57c00;
      color: #f57c00;
      transform: translateY(-2px);
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
  <main class="plans-main">
    
    <div class="plans-header animate-fade-up">
      <div>
        <div class="eyebrow"><span class="rule"></span> Your Events</div>
        <h1>My <em>Plans</em></h1>
        <p class="plans-header__sub">Track all your upcoming and past celebrations in one place.</p>
      </div>
      <a href="/index.php?route=occasions" class="btn btn--primary"><i class="fas fa-plus"></i> New Event</a>
    </div>
    
    <?php
      $badgeMap = ['confirmed'=>'badge--success','approved'=>'badge--success','canceled'=>'badge--danger','pending'=>'badge--warning','completed'=>'badge--info','rejected'=>'badge--danger'];
      $labelMap = ['confirmed'=>'Confirmed','approved'=>'Confirmed','canceled'=>'Canceled','pending'=>'Pending','completed'=>'Completed','rejected'=>'Rejected'];
      $counts = ['all' => 0, 'confirmed' => 0, 'approved' => 0, 'canceled' => 0, 'pending' => 0, 'completed' => 0, 'rejected' => 0];
      foreach ($plans as $plan) {
          $status = $plan['status'] ?? 'pending';
          if (!isset($counts[$status])) {
              $status = 'pending';
          }
          $counts['all']++;
          $counts[$status]++;
      }
    ?>
    <div class="plans-tabs animate-fade-up delay-1">
      <button class="plans-tab active" onclick="filterPlans('all',this)">All <span class="plans-tab__count"><?= $counts['all'] ?></span></button>
      <button class="plans-tab" onclick="filterPlans('confirmed',this)">Confirmed <span class="plans-tab__count"><?= $counts['confirmed'] ?></span></button>
      <button class="plans-tab" onclick="filterPlans('canceled',this)">Canceled <span class="plans-tab__count"><?= $counts['canceled'] ?></span></button>
      <button class="plans-tab" onclick="filterPlans('pending',this)">Pending <span class="plans-tab__count"><?= $counts['pending'] ?></span></button>
      <button class="plans-tab" onclick="filterPlans('completed',this)">Completed <span class="plans-tab__count"><?= $counts['completed'] ?></span></button>
    </div>
    
    <div class="plans-grid stagger" id="plansGrid">
      <?php if (empty($plans)): ?>
        <div class="plan-card" style="grid-column: span 3; text-align: center; padding: 3rem;">
          <h3>No plans found</h3>
          <p>Once you complete checkout, your bookings will appear here.</p>
        </div>
      <?php else: ?>
        <?php foreach ($plans as $plan): ?>
          <?php
            $status = $plan['status'] ?? 'pending';
            if (!isset($badgeMap[$status])) {
                $status = 'pending';
            }
            $eventData = json_decode($plan['events'], true);
            $itemNames = [];
            if (is_array($eventData) && isset($eventData['items']) && is_array($eventData['items'])) {
                foreach ($eventData['items'] as $item) {
                    if (!empty($item['name'])) {
                        $itemNames[] = $item['name'];
                    }
                }
            }
            $summary = implode(', ', array_slice($itemNames, 0, 3));
            if (count($itemNames) > 3) {
                $summary .= ' + ' . (count($itemNames) - 3) . ' more';
            }
            $programLines = [];
            if (is_array($eventData) && !empty($eventData['programFlow'])) {
                $programLines = array_filter(array_map('trim', explode("\n", $eventData['programFlow'])));
            }
            $programSnippet = '';
            if (!empty($programLines)) {
                $programSnippet = implode(' · ', array_slice($programLines, 0, 2));
            }
            $eventName = htmlspecialchars($plan['event_name'] ?: 'Untitled Event');
            $type = htmlspecialchars(($plan['occasion_name'] ?? 'Event') ?: 'Event');
            $date = $plan['event_date'] ? date('M j, Y', strtotime($plan['event_date'])) : 'TBD';
            $location = htmlspecialchars($plan['venue'] ?: 'TBD');
            $price = '₱' . number_format((float)$plan['total_price'], 0);
            $eventText = strtolower(trim(($plan['occasion_name'] ?? '') . ' ' . ($plan['package_name'] ?? '') . ' ' . ($plan['event_name'] ?? '')));
            $eventImageMap = [
                'wedding' => '/assets/img/wedding3.jpg',
                'debut' => '/assets/img/debut.jpg',
                'birthday' => '/assets/img/birthday2.jpg',
                'corporate' => '/assets/img/corporate2.jpg',
                'anniversary' => '/assets/img/anniversary.jpg',
                'beach' => '/assets/img/beach.jpg',
                'garden' => '/assets/img/garden.jpg',
            ];
            $imageUrl = '/assets/img/event-placeholder.jpg';
            foreach ($eventImageMap as $keyword => $url) {
                if ($keyword && strpos($eventText, $keyword) !== false) {
                    $imageUrl = $url;
                    break;
                }
            }
          ?>
          <a href="/index.php?route=event-detail&id=<?= $plan['plan_id'] ?>" class="plan-card" data-status="<?= htmlspecialchars($status) ?>">
            <div class="plan-card__img" style="background-image:url('<?= $imageUrl ?>')">
              <span class="badge <?= $badgeMap[$status] ?>"><?= $labelMap[$status] ?></span>
              <?php if ($status === 'confirmed' && ($plan['payment_status'] ?? 'pending') === 'pending'): ?>
                <span class="badge badge--warning" style="position: absolute; top: 50%; right: 1rem; margin-top: 1.5rem; background: #FF6B6B; color: white; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.7rem;"><i class="fas fa-exclamation-circle"></i> Payment Due: 50% Deposit</span>
              <?php endif; ?>
            </div>
            <div class="plan-card__body">
              <span class="plan-card__type"><?= $type ?></span>
              <h3><?= $eventName ?></h3>
              <div class="plan-card__meta">
                <span><i class="fas fa-calendar"></i> <?= $date ?></span>
                <span><i class="fas fa-location-dot"></i> <?= $location ?></span>
              </div>
              <?php if ($summary): ?>
                <div class="plan-card__summary">
                  <i class="fas fa-list"></i>
                  <span><?= htmlspecialchars($summary) ?></span>
                </div>
              <?php endif; ?>
              <?php if ($programSnippet): ?>
                <div class="plan-card__program">
                  <span class="plan-card__program-label">Timeline</span>
                  <i class="fas fa-stream"></i>
                  <span><?= htmlspecialchars($programSnippet) ?></span>
                </div>
              <?php endif; ?>
              <div class="plan-card__footer">
                <span class="plan-card__price"><?= $price ?></span>
                <div style="display: flex; align-items: center; gap: 1rem;">
                  <?php if ($status === 'pending' && ($plan['can_cancel'] ?? false)): ?>
                    <button 
                      class="btn-cancel-plan" 
                      onclick="cancelPlan(event, <?= $plan['plan_id'] ?>, '<?= htmlspecialchars($eventName) ?>')"
                      title="Cancel this pending plan (<?= ($plan['minutes_remaining'] ?? 0) ?> minutes remaining)"
                    >
                      <i class="fas fa-ban"></i> Cancel
                    </button>
                  <?php endif; ?>
                  <span class="plan-card__link">View Details <i class="fas fa-arrow-right"></i></span>
                </div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
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

function filterPlans(status, btn) {
  document.querySelectorAll('.plans-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.plan-card').forEach(card => {
    card.style.display = (status === 'all' || card.dataset.status === status) ? '' : 'none';
  });
}

function deletePlan(event, planId, eventName) {
  event.preventDefault();
  event.stopPropagation();
  
  if (confirm(`Are you sure you want to delete "${eventName}"? This action cannot be undone.`)) {
    fetch('/index.php?route=delete-plan', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ plan_id: planId })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('Plan deleted successfully!', 'success');
        setTimeout(() => location.reload(), 500);
      } else {
        showToast(data.message || 'Failed to delete plan', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('An error occurred while deleting the plan', 'error');
    });
  }
}

function cancelPlan(event, planId, eventName) {
  event.preventDefault();
  event.stopPropagation();
  
  // First check if cancellation is still available (within 30 minutes)
  fetch('/api-plan.php?action=check_cancellation&plan_id=' + planId)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Check cancellation response:', data);
      
      if (!data.success) {
        showToast(data.message || 'Could not check cancellation status', 'error');
        return;
      }
      
      if (data.can_cancel) {
        if (confirm(`Are you sure you want to cancel "${eventName}"?\n\nTime remaining: ${data.minutes_remaining} minutes`)) {
          // Proceed with cancellation
          const formData = new FormData();
          formData.append('action', 'cancel_plan');
          formData.append('plan_id', planId);
          
          fetch('/api-plan.php', {
            method: 'POST',
            body: formData
          })
          .then(response => {
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then(data => {
            console.log('Cancel response:', data);
            if (data.success) {
              showToast('Plan cancelled successfully!', 'success');
              setTimeout(() => location.reload(), 500);
            } else {
              showToast(data.message || 'Failed to cancel plan', 'error');
            }
          })
          .catch(error => {
            console.error('Cancellation error:', error);
            showToast('An error occurred while cancelling the plan: ' + error.message, 'error');
          });
        }
      } else {
        showToast(data.message || 'This plan cannot be cancelled', 'error');
      }
    })
    .catch(error => {
      console.error('Cancellation status check error:', error);
      showToast('An error occurred while checking cancellation status: ' + error.message, 'error');
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
<?php 
session_start();

// Check if admin is logged in - if not, redirect to signin.php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: signin.php');
    exit;
}

$page_title = 'Dashboard'; // Change per page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Sinta</title>
  <link rel="stylesheet" href="assets/css/global.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Dashboard specific styles */
    .admin-content {
      padding: 2rem;
    }
    .stats-grid { 
      display: grid; 
      grid-template-columns: repeat(4, 1fr); 
      gap: 1.5rem; 
      margin-bottom: 2rem; 
    }
    .stat-card { 
      background: white; 
      border: 1px solid var(--border); 
      border-radius: 20px; 
      padding: 1.5rem; 
      display: flex; 
      align-items: center; 
      gap: 1rem; 
    }
    .stat-icon { 
      width: 50px; 
      height: 50px; 
      background: var(--primary-pale); 
      border-radius: 15px; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      color: var(--primary); 
      font-size: 1.3rem; 
    }
    .stat-info h3 { 
      font-size: 1.8rem; 
      margin: 0; 
    }
    .stat-info p { 
      color: var(--gray); 
      margin: 0; 
      font-size: 0.8rem; 
    }
    .recent-table { 
      width: 100%; 
      background: white; 
      border-radius: 20px; 
      overflow: hidden; 
      border: 1px solid var(--border); 
    }
    .recent-table th, 
    .recent-table td { 
      padding: 1rem; 
      text-align: left; 
      border-bottom: 1px solid var(--border); 
    }
    .recent-table th { 
      background: var(--cream); 
    }
    .badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .badge--primary { background: var(--primary-pale); color: var(--primary); }
  </style>
</head>
<body>

<?php include 'admin-nav.php'; ?>

<div class="admin-content">
  <div class="stats-grid">
    <?php
    // Initialize session arrays if not set
    if (!isset($_SESSION['packages'])) $_SESSION['packages'] = [];
    if (!isset($_SESSION['bookings'])) $_SESSION['bookings'] = [];
    if (!isset($_SESSION['testimonials'])) $_SESSION['testimonials'] = [];
    ?>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-box"></i></div>
      <div class="stat-info">
        <h3><?= count($_SESSION['packages'] ?? []) ?></h3>
        <p>Total Packages</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-calendar"></i></div>
      <div class="stat-info">
        <h3><?= count($_SESSION['bookings'] ?? []) ?></h3>
        <p>Total Bookings</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-star"></i></div>
      <div class="stat-info">
        <h3><?= count($_SESSION['testimonials'] ?? []) ?></h3>
        <p>Testimonials</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-users"></i></div>
      <div class="stat-info">
        <h3>1,200+</h3>
        <p>Happy Clients</p>
      </div>
    </div>
  </div>
  
  <h3 style="margin-bottom: 1rem;">Recent Bookings</h3>
  <table class="recent-table">
    <thead>
      <tr><th>ID</th><th>Customer</th><th>Package</th><th>Event Date</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php 
      $recent = array_slice($_SESSION['bookings'] ?? [], 0, 5);
      if (empty($recent)): ?>
        <tr>
          <td colspan="5" style="text-align: center; padding: 2rem; color: var(--gray);">
            No bookings yet
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($recent as $book): ?>
        <tr>
          <td><?= $book['id'] ?? 'N/A' ?></td>
          <td><?= htmlspecialchars($book['customer'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($book['package'] ?? 'N/A') ?></td>
          <td><?= $book['event_date'] ?? 'N/A' ?></td>
          <td><span class="badge badge--primary"><?= $book['status'] ?? 'pending' ?></span></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  追赶
</div>

<script>
document.getElementById('mobileToggle')?.addEventListener('click', function() {
  document.getElementById('adminSidebar').classList.toggle('open');
});
</script>
</body>
</html>
<?php 
// Session already started in index.php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

$page_title = 'Dashboard';

// Initialize session arrays if not set
if (!isset($_SESSION['packages'])) {
    $_SESSION['packages'] = [
        ['id' => 1, 'name' => 'Classic Wedding', 'occasion' => 'wedding', 'price' => 150000, 'description' => 'Perfect for those who want a beautifully organized event without complexity.'],
        ['id' => 2, 'name' => 'Elegant Wedding', 'occasion' => 'wedding', 'price' => 250000, 'description' => 'Elevated experience with premium vendors and extended services.'],
        ['id' => 3, 'name' => 'Premium Wedding', 'occasion' => 'wedding', 'price' => 450000, 'description' => 'The ultimate luxury experience with bespoke services.'],
        ['id' => 4, 'name' => 'Classic Birthday', 'occasion' => 'birthday', 'price' => 45000, 'description' => 'Fun and festive celebration with all the essentials.'],
        ['id' => 5, 'name' => 'Deluxe Birthday', 'occasion' => 'birthday', 'price' => 85000, 'description' => 'Extra special celebration with premium entertainment.'],
        ['id' => 6, 'name' => 'Corporate Gala', 'occasion' => 'corporate', 'price' => 250000, 'description' => 'Professional and elegant event for company celebrations.'],
    ];
}

if (!isset($_SESSION['bookings'])) {
    $_SESSION['bookings'] = [
        ['id' => 1001, 'customer' => 'Maria Santos', 'email' => 'maria@email.com', 'package' => 'Classic Wedding', 'event_date' => '2025-08-12', 'total' => 150000, 'status' => 'confirmed', 'created_at' => '2025-01-15'],
        ['id' => 1002, 'customer' => 'John Reyes', 'email' => 'john@email.com', 'package' => 'Deluxe Birthday', 'event_date' => '2025-10-03', 'total' => 85000, 'status' => 'pending', 'created_at' => '2025-01-20'],
        ['id' => 1003, 'customer' => 'Anna Lopez', 'email' => 'anna@email.com', 'package' => 'Elegant Wedding', 'event_date' => '2025-09-15', 'total' => 250000, 'status' => 'confirmed', 'created_at' => '2025-02-10'],
    ];
}

if (!isset($_SESSION['testimonials'])) {
    $_SESSION['testimonials'] = [
        ['id' => 1, 'author' => 'Isabella Rodriguez', 'rating' => 5, 'quote' => 'The most magical day of our lives. Sinta made every detail absolutely perfect.', 'occasion' => 'Wedding', 'date' => '2024-03-15'],
        ['id' => 2, 'author' => 'Marcus Tan', 'rating' => 5, 'quote' => 'Professional, creative, and handled every detail without a hitch.', 'occasion' => 'Corporate', 'date' => '2024-06-20'],
    ];
}

$totalPackages = count($_SESSION['packages']);
$totalBookings = count($_SESSION['bookings']);
$totalTestimonials = count($_SESSION['testimonials']);
$recentBookings = array_slice($_SESSION['bookings'], 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Sinta</title>
  <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'admin-nav.php'; ?>

<!-- Dashboard Content -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
  <div class="stat-card" style="background: white; border: 1px solid #E2D9C8; border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
    <div class="stat-icon" style="width: 50px; height: 50px; background: rgba(138,118,80,0.12); border-radius: 15px; display: flex; align-items: center; justify-content: center; color: #8A7650;"><i class="fas fa-box"></i></div>
    <div class="stat-info"><h3 style="font-size: 1.8rem; margin: 0;"><?= $totalPackages ?></h3><p style="color: #6B6463; margin: 0;">Total Packages</p></div>
  </div>
  <div class="stat-card" style="background: white; border: 1px solid #E2D9C8; border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
    <div class="stat-icon" style="width: 50px; height: 50px; background: rgba(138,118,80,0.12); border-radius: 15px; display: flex; align-items: center; justify-content: center; color: #8A7650;"><i class="fas fa-calendar"></i></div>
    <div class="stat-info"><h3 style="font-size: 1.8rem; margin: 0;"><?= $totalBookings ?></h3><p style="color: #6B6463; margin: 0;">Total Bookings</p></div>
  </div>
  <div class="stat-card" style="background: white; border: 1px solid #E2D9C8; border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
    <div class="stat-icon" style="width: 50px; height: 50px; background: rgba(138,118,80,0.12); border-radius: 15px; display: flex; align-items: center; justify-content: center; color: #8A7650;"><i class="fas fa-star"></i></div>
    <div class="stat-info"><h3 style="font-size: 1.8rem; margin: 0;"><?= $totalTestimonials ?></h3><p style="color: #6B6463; margin: 0;">Testimonials</p></div>
  </div>
  <div class="stat-card" style="background: white; border: 1px solid #E2D9C8; border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
    <div class="stat-icon" style="width: 50px; height: 50px; background: rgba(138,118,80,0.12); border-radius: 15px; display: flex; align-items: center; justify-content: center; color: #8A7650;"><i class="fas fa-users"></i></div>
    <div class="stat-info"><h3 style="font-size: 1.8rem; margin: 0;">1,200+</h3><p style="color: #6B6463; margin: 0;">Happy Clients</p></div>
  </div>
</div>

<h3 style="margin-bottom: 1rem;">Recent Bookings</h3>
<table class="recent-table" style="width: 100%; background: white; border-radius: 20px; overflow: hidden; border: 1px solid #E2D9C8;">
  <thead>
    <tr><th style="padding: 1rem; text-align: left; background: #F5F0E8;">ID</th><th style="padding: 1rem; text-align: left; background: #F5F0E8;">Customer</th><th style="padding: 1rem; text-align: left; background: #F5F0E8;">Package</th><th style="padding: 1rem; text-align: left; background: #F5F0E8;">Event Date</th><th style="padding: 1rem; text-align: left; background: #F5F0E8;">Status</th></tr>
  </thead>
  <tbody>
    <?php if (empty($recentBookings)): ?>
      <tr><td colspan="5" style="text-align: center; padding: 2rem;">No bookings yet</td></tr>
    <?php else: ?>
      <?php foreach ($recentBookings as $book): ?>
        <tr>
          <td style="padding: 1rem; border-bottom: 1px solid #E2D9C8;"><?= $book['id'] ?></td>
          <td style="padding: 1rem; border-bottom: 1px solid #E2D9C8;"><?= htmlspecialchars($book['customer']) ?></td>
          <td style="padding: 1rem; border-bottom: 1px solid #E2D9C8;"><?= htmlspecialchars($book['package']) ?></td>
          <td style="padding: 1rem; border-bottom: 1px solid #E2D9C8;"><?= $book['event_date'] ?></td>
          <td style="padding: 1rem; border-bottom: 1px solid #E2D9C8;"><span class="badge" style="background: rgba(138,118,80,0.12); color: #8A7650; padding: 0.25rem 0.75rem; border-radius: 20px;"><?= $book['status'] ?></span></td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<script>
document.getElementById('mobileToggle')?.addEventListener('click', function() {
  document.getElementById('adminSidebar').classList.toggle('open');
});
</script>

</main>
</div>
</body>
</html>
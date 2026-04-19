<?php 
// Session already started in index.php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

$page_title = 'Dashboard';

// Get real data from database
require_once ROOT_PATH . '/config/database.php';
$db = Database::getInstance()->getConnection();

// Get stats
$packages_result = $db->query("SELECT COUNT(*) as count FROM packages_tbl");
$total_packages = $packages_result->fetch_assoc()['count'] ?? 0;

// Use checkout_tbl instead of bookings_tbl
$bookings_result = $db->query("SELECT COUNT(*) as count FROM checkout_tbl");
$total_bookings = $bookings_result->fetch_assoc()['count'] ?? 0;

$testimonials_result = $db->query("SELECT COUNT(*) as count FROM testimonials_tbl");
$total_testimonials = $testimonials_result->fetch_assoc()['count'] ?? 0;

$users_result = $db->query("SELECT COUNT(*) as count FROM users_tbl WHERE role = 'user'");
$total_users = $users_result->fetch_assoc()['count'] ?? 0;

// Get recent bookings from checkout_tbl
$recent_bookings = $db->query("SELECT c.*, u.first_name, u.last_name, u.email FROM checkout_tbl c LEFT JOIN users_tbl u ON c.user_id = u.user_id ORDER BY c.checkout_id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Sinta</title>
  <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .dashboard-container {
      padding: 2rem 0;
    }

    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 3rem;
    }

    .stat-card {
      background: linear-gradient(135deg, #fff 0%, #f9f7f7 100%);
      border: 2px solid #E2D9C8;
      border-radius: 20px;
      padding: 1.75rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100px;
      height: 100px;
      background: radial-gradient(circle, rgba(138, 118, 80, 0.08) 0%, transparent 70%);
      border-radius: 50%;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(138, 118, 80, 0.1);
      border-color: #8A7650;
    }

    .stat-content {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      position: relative;
      z-index: 1;
    }

    .stat-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, rgba(138, 118, 80, 0.15) 0%, rgba(138, 118, 80, 0.08) 100%);
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #8A7650;
      font-size: 1.5rem;
      flex-shrink: 0;
    }

    .stat-info h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
      margin: 0 0 0.25rem 0;
      color: #2C2820;
      font-weight: 600;
    }

    .stat-info p {
      font-size: 0.85rem;
      color: #8B7355;
      margin: 0;
      font-weight: 500;
    }

    /* Section Header */
    .section-header {
      margin-bottom: 2rem;
    }

    .section-header h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.8rem;
      color: #2C2820;
      margin: 0 0 0.5rem 0;
    }

    .section-header p {
      color: #8B7355;
      margin: 0;
      font-size: 0.9rem;
    }

    /* Recent Bookings Table */
    .recent-bookings {
      background: white;
      border: 2px solid #E2D9C8;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
    }

    .recent-bookings table {
      width: 100%;
      border-collapse: collapse;
    }

    .recent-bookings thead tr {
      background: linear-gradient(90deg, #F5F0E8 0%, #F9F7F7 100%);
      border-bottom: 2px solid #E2D9C8;
    }

    .recent-bookings th {
      padding: 1.2rem 1.5rem;
      text-align: left;
      font-weight: 600;
      color: #2C2820;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .recent-bookings tbody tr {
      border-bottom: 1px solid #F0EBE3;
      transition: all 0.2s ease;
    }

    .recent-bookings tbody tr:hover {
      background: #FAFAF8;
    }

    .recent-bookings td {
      padding: 1.2rem 1.5rem;
      color: #2C2820;
      font-size: 0.9rem;
    }

    .booking-id {
      font-weight: 600;
      color: #8A7650;
    }

    .status-badge {
      display: inline-block;
      padding: 0.4rem 1rem;
      border-radius: 25px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status-confirmed {
      background: rgba(76, 175, 80, 0.12);
      color: #2e7d32;
    }

    .status-pending {
      background: rgba(255, 193, 7, 0.12);
      color: #f57f17;
    }

    .status-cancelled {
      background: rgba(244, 67, 54, 0.12);
      color: #c62828;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem 1.5rem;
      color: #8B7355;
    }

    .empty-state i {
      font-size: 3rem;
      color: #E2D9C8;
      margin-bottom: 1rem;
      display: block;
    }

    /* Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 3rem;
    }

    .action-btn {
      background: white;
      border: 2px solid #E2D9C8;
      border-radius: 15px;
      padding: 1.5rem;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      color: #2C2820;
    }

    .action-btn:hover {
      border-color: #8A7650;
      background: linear-gradient(135deg, #fff 0%, #f9f7f7 100%);
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(138, 118, 80, 0.15);
    }

    .action-btn i {
      font-size: 2rem;
      color: #8A7650;
      display: block;
      margin-bottom: 0.75rem;
    }

    .action-btn span {
      display: block;
      font-weight: 600;
      font-size: 0.95rem;
    }
  </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

<div class="dashboard-container">
  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-content">
        <div class="stat-icon">
          <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
          <h3><?= $total_packages ?></h3>
          <p>Total Packages</p>
        </div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-content">
        <div class="stat-icon">
          <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
          <h3><?= $total_bookings ?></h3>
          <p>Total Bookings</p>
        </div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-content">
        <div class="stat-icon">
          <i class="fas fa-star"></i>
        </div>
        <div class="stat-info">
          <h3><?= $total_testimonials ?></h3>
          <p>Testimonials</p>
        </div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-content">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
          <h3><?= $total_users ?></h3>
          <p>Active Users</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="section-header">
    <h2>Quick Actions</h2>
    <p>Manage your platform</p>
  </div>
  <div class="quick-actions">
    <a href="/SINTA/public/index.php?route=admin-packages" class="action-btn">
      <i class="fas fa-plus-circle"></i>
      <span>Manage Packages</span>
    </a>
    <a href="/SINTA/public/index.php?route=admin-occasions" class="action-btn">
      <i class="fas fa-calendar-alt"></i>
      <span>Manage Occasions</span>
    </a>
    <a href="/SINTA/public/index.php?route=admin-bookings" class="action-btn">
      <i class="fas fa-list-check"></i>
      <span>View Bookings</span>
    </a>
    <a href="/SINTA/public/index.php?route=admin-messages" class="action-btn">
      <i class="fas fa-envelope-open"></i>
      <span>Read Messages</span>
    </a>
  </div>

  <!-- Recent Bookings -->
  <div class="section-header">
    <h2>Recent Bookings</h2>
    <p>Latest booking activity</p>
  </div>
  <div class="recent-bookings">
    <table>
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Customer</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($recent_bookings->num_rows === 0): ?>
          <tr>
            <td colspan="5" class="empty-state">
              <i class="fas fa-inbox"></i>
              <p>No bookings yet. Check back soon!</p>
            </td>
          </tr>
        <?php else: ?>
          <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
            <tr>
              <td class="booking-id">#<?= str_pad($booking['checkout_id'] ?? 0, 5, '0', STR_PAD_LEFT) ?></td>
              <td><?= htmlspecialchars(($booking['first_name'] ?? 'N/A') . ' ' . ($booking['last_name'] ?? '')) ?></td>
              <td>₱<?= number_format($booking['total_amount'] ?? 0, 2) ?></td>
              <td>
                <span class="status-badge status-<?= strtolower($booking['status'] ?? 'pending') ?>">
                  <?= htmlspecialchars($booking['status'] ?? 'pending') ?>
                </span>
              </td>
              <td><?= htmlspecialchars($booking['email'] ?? 'N/A') ?></td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.getElementById('mobileToggle')?.addEventListener('click', function() {
  document.getElementById('adminSidebar').classList.toggle('open');
});
</script>
</body>
</html>

</main>
</div>
</body>
</html>
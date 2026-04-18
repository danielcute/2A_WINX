<?php
// admin-dashboard-modern.php - Modern Minimalist Dashboard

// Calculate statistics (add your own database queries here)
$stats = [
    'total_bookings' => 156,
    'pending_bookings' => 12,
    'completed_bookings' => 144,
    'total_revenue' => 45280,
    'total_packages' => 24,
    require_once ROOT_PATH . '/app/models/Message.php';
    $messageModel = new Message();
    'pending_messages' => $messageModel->getUnreadCount(),
];

$recent_bookings = [
    ['id' => 'BK001', 'client' => 'John Doe', 'package' => 'Wedding Gold', 'date' => '2024-01-15', 'amount' => 5000, 'status' => 'confirmed'],
    ['id' => 'BK002', 'client' => 'Jane Smith', 'package' => 'Birthday Standard', 'date' => '2024-01-18', 'amount' => 2000, 'status' => 'pending'],
    ['id' => 'BK003', 'client' => 'Mike Johnson', 'package' => 'Corporate Platinum', 'date' => '2024-01-20', 'amount' => 8500, 'status' => 'confirmed'],
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sinta</title>
    <link rel="stylesheet" href="/SINTA/public/assets/css/admin-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            margin-top: 2rem;
        }

        .recent-bookings {
            margin-top: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .quick-actions {
            display: flex;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation from admin-nav.php included here -->
    <?php include 'admin-nav-new.php'; ?>

    <!-- DASHBOARD CONTENT -->
    <div class="admin-content">
        <!-- Dashboard Header -->
        <div class="section-header">
            <h1 class="section-title">Dashboard</h1>
            <p class="section-subtitle">Welcome to your admin panel. Here's an overview of your business.</p>
        </div>

        <!-- Key Statistics -->
        <div class="dashboard-grid">
            <!-- Total Bookings Card -->
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--primary">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-card__content">
                    <div class="stat-card__label">Total Bookings</div>
                    <div class="stat-card__value"><?php echo number_format($stats['total_bookings']); ?></div>
                    <div class="stat-card__change stat-card__change--positive">
                        <i class="fas fa-arrow-up"></i> 12% from last month
                    </div>
                </div>
            </div>

            <!-- Pending Bookings Card -->
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card__content">
                    <div class="stat-card__label">Pending Bookings</div>
                    <div class="stat-card__value"><?php echo $stats['pending_bookings']; ?></div>
                    <div class="stat-card__change">
                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Awaiting confirmation
                    </div>
                </div>
            </div>

            <!-- Total Revenue Card -->
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-card__content">
                    <div class="stat-card__label">Total Revenue</div>
                    <div class="stat-card__value">$<?php echo number_format($stats['total_revenue']); ?></div>
                    <div class="stat-card__change stat-card__change--positive">
                        <i class="fas fa-arrow-up"></i> 8.5% growth
                    </div>
                </div>
            </div>

            <!-- Unread Messages Card -->
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--info">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-card__content">
                    <div class="stat-card__label">Unread Messages</div>
                    <div class="stat-card__value"><?php echo $stats['pending_messages']; ?></div>
                    <a href="/SINTA/public/index.php?route=admin-messages" style="color: var(--primary); text-decoration: none; font-size: 0.85rem;">
                        View all <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Section -->
        <div class="card recent-bookings">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem;">Recent Bookings</h2>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Latest client bookings</p>
                </div>
                <a href="/SINTA/public/index.php?route=admin-bookings" class="btn btn--sm btn--secondary">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Client Name</th>
                            <th>Package</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bookings as $booking): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($booking['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($booking['client']); ?></td>
                                <td><?php echo htmlspecialchars($booking['package']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['date'])); ?></td>
                                <td><strong>$<?php echo number_format($booking['amount']); ?></strong></td>
                                <td>
                                    <span class="badge badge--<?php echo $booking['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn--sm btn--secondary" style="padding: 0.35rem 0.75rem;">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="action-buttons">
            <a href="/SINTA/public/index.php?route=admin-packages" class="btn btn--primary">
                <i class="fas fa-plus"></i> Add Package
            </a>
            <a href="/SINTA/public/index.php?route=admin-bookings" class="btn btn--secondary">
                <i class="fas fa-calendar"></i> Manage Bookings
            </a>
            <a href="/SINTA/public/index.php?route=admin-messages" class="btn btn--secondary">
                <i class="fas fa-envelope"></i> View Messages
            </a>
            <a href="/SINTA/public/index.php?route=admin-logout" class="btn btn--secondary">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <script>
        // Update page title in header
        document.addEventListener('DOMContentLoaded', function() {
            const pageTitle = document.querySelector('#page-title');
            if (pageTitle) {
                pageTitle.textContent = 'Dashboard';
            }
        });
    </script>
</body>
</html>

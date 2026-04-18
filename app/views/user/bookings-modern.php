<?php
/**
 * USER BOOKINGS PAGE - Modern Minimalist
 * Location: app/views/user/bookings-modern.php
 */

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

require_once ROOT_PATH . '/app/models/Booking.php';
require_once ROOT_PATH . '/app/models/Package.php';

$pageTitle = 'My Bookings';
$bookingModel = new Booking();
$packageModel = new Package();
$userId = $_SESSION['user_id'];

// Fetch user's bookings
$db = Database::getInstance()->getConnection();
$query = "SELECT b.*, p.name as package_name, p.price as package_price
          FROM checkout_tbl b
          LEFT JOIN packages_tbl p ON b.package_id = p.package_id
          WHERE b.user_id = $userId
          ORDER BY b.date DESC";

$result = $db->query($query);
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// Group by status
$confirmed = array_filter($bookings, fn($b) => $b['status'] === 'confirmed');
$pending = array_filter($bookings, fn($b) => $b['status'] === 'pending');
$cancelled = array_filter($bookings, fn($b) => $b['status'] === 'cancelled');

?>
<?php include 'header-modern.php'; ?>

<main>
    <div class="container" style="padding: 3rem 1.5rem;">
        <!-- PAGE HEADER -->
        <div class="section-header">
            <h1 class="section-title"><i class="fas fa-calendar-check"></i> My Bookings</h1>
            <p class="section-subtitle">Manage and track all your event bookings</p>
        </div>

        <!-- TAB NAVIGATION -->
        <div style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid var(--border);">
            <button class="tab-btn active" onclick="switchTab('all')" style="padding: 1rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; font-weight: 600; transition: var(--transition);">
                All Bookings (<?php echo count($bookings); ?>)
            </button>
            <button class="tab-btn" onclick="switchTab('confirmed')" style="padding: 1rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; font-weight: 600; transition: var(--transition);">
                Confirmed (<?php echo count($confirmed); ?>)
            </button>
            <button class="tab-btn" onclick="switchTab('pending')" style="padding: 1rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; font-weight: 600; transition: var(--transition);">
                Pending (<?php echo count($pending); ?>)
            </button>
        </div>

        <!-- ALL BOOKINGS TAB -->
        <div id="tab-all" class="tab-content">
            <?php if (empty($bookings)): ?>
                <div style="text-align: center; padding: 3rem 1rem;">
                    <i class="fas fa-inbox" style="font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                    <h3 style="color: var(--text-secondary); margin-bottom: 0.5rem;">No bookings yet</h3>
                    <p style="color: var(--text-light); margin-bottom: 1.5rem;">Start planning your next event by browsing our packages</p>
                    <a href="/SINTA/public/index.php?route=packages" class="btn btn--primary">
                        <i class="fas fa-arrow-right"></i> Browse Packages
                    </a>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="card" style="border-left: 4px solid <?php 
                            echo $booking['status'] === 'confirmed' ? 'var(--success)' : 
                                 ($booking['status'] === 'pending' ? 'var(--warning)' : 'var(--danger)');
                        ?>; padding: 1.5rem;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1.5rem;">
                                <!-- Left Column -->
                                <div>
                                    <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($booking['package_name']); ?>
                                    </h3>
                                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                        <span class="badge badge--<?php echo $booking['status']; ?>">
                                            <?php echo strtoupper($booking['status']); ?>
                                        </span>
                                        <span class="badge badge--<?php echo $booking['payment_method'] ?? 'info'; ?>">
                                            <?php echo $booking['payment_method'] ?? 'Pending'; ?>
                                        </span>
                                    </div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                        <div><strong>Booking ID:</strong> BK<?php echo str_pad($booking['checkout_id'], 5, '0', STR_PAD_LEFT); ?></div>
                                        <div><strong>Booked on:</strong> <?php echo date('M d, Y', strtotime($booking['date'])); ?></div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Total Amount</div>
                                    <div style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">
                                        $<?php echo number_format($booking['total_amount']); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                        <div>Deposit: $<?php echo number_format($booking['deposit_amount'] ?? 0); ?></div>
                                        <div>Balance: $<?php echo number_format($booking['total_amount'] - ($booking['deposit_amount'] ?? 0)); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- ACTION BUTTONS -->
                            <div style="display: flex; gap: 0.75rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                                <button class="btn btn--secondary btn--sm" onclick="viewBookingDetails(<?php echo $booking['checkout_id']; ?>)">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <button class="btn btn--primary btn--sm" onclick="proceedToPayment(<?php echo $booking['checkout_id']; ?>)">
                                        <i class="fas fa-credit-card"></i> Complete Payment
                                    </button>
                                    <button class="btn btn--danger btn--sm" onclick="cancelBooking(<?php echo $booking['checkout_id']; ?>)">
                                        <i class="fas fa-trash"></i> Cancel
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn--secondary btn--sm" onclick="contactSupport(<?php echo $booking['checkout_id']; ?>)">
                                        <i class="fas fa-envelope"></i> Contact Support
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- CONFIRMED TAB -->
        <div id="tab-confirmed" class="tab-content" style="display: none;">
            <?php if (empty($confirmed)): ?>
                <div style="text-align: center; padding: 3rem 1rem;">
                    <i class="fas fa-check-circle" style="font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-secondary);">No confirmed bookings yet</p>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($confirmed as $booking): ?>
                        <div class="card" style="border-left: 4px solid var(--success); padding: 1.5rem;">
                            <h3 style="font-weight: 700; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($booking['package_name']); ?>
                            </h3>
                            <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">
                                Booking ID: BK<?php echo str_pad($booking['checkout_id'], 5, '0', STR_PAD_LEFT); ?> | 
                                Date: <?php echo date('M d, Y', strtotime($booking['date'])); ?>
                            </div>
                            <div style="font-size: 1.3rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">
                                $<?php echo number_format($booking['total_amount']); ?>
                            </div>
                            <button class="btn btn--secondary btn--sm" onclick="viewBookingDetails(<?php echo $booking['checkout_id']; ?>)">
                                View Details
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- PENDING TAB -->
        <div id="tab-pending" class="tab-content" style="display: none;">
            <?php if (empty($pending)): ?>
                <div style="text-align: center; padding: 3rem 1rem;">
                    <i class="fas fa-hourglass" style="font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-secondary);">No pending bookings</p>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($pending as $booking): ?>
                        <div class="card" style="border-left: 4px solid var(--warning); padding: 1.5rem;">
                            <h3 style="font-weight: 700; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($booking['package_name']); ?>
                            </h3>
                            <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">
                                <span class="badge badge--warning">Action Required</span>
                                Booking ID: BK<?php echo str_pad($booking['checkout_id'], 5, '0', STR_PAD_LEFT); ?>
                            </div>
                            <button class="btn btn--primary btn--sm" onclick="proceedToPayment(<?php echo $booking['checkout_id']; ?>)">
                                Complete Payment
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    function switchTab(tab) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(el => el.style.color = 'var(--text-secondary)');
        
        // Show selected tab
        document.getElementById('tab-' + tab).style.display = 'block';
        event.target.style.color = 'var(--primary)';
        event.target.style.borderBottomColor = 'var(--primary)';
    }

    function viewBookingDetails(bookingId) {
        alert('Booking details for ID: ' + bookingId + ' (Feature coming soon)');
    }

    function proceedToPayment(bookingId) {
        window.location.href = '/SINTA/public/index.php?route=checkout&booking_id=' + bookingId;
    }

    function cancelBooking(bookingId) {
        if (confirm('Are you sure you want to cancel this booking?')) {
            // Send cancel request
            fetch('/SINTA/public/api/bookings/cancel', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({booking_id: bookingId})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    function contactSupport(bookingId) {
        window.location.href = '/SINTA/public/index.php?route=messages?booking_id=' + bookingId;
    }
</script>

<?php include 'footer.php'; ?>
</body>
</html>

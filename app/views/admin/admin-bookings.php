<?php 
require_once dirname(__DIR__, 2) . '/models/Booking.php';
require_once dirname(__DIR__, 2) . '/models/User.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

$page_title = 'Booking Management';
$bookingModel = new Booking();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];
    
    switch ($action) {
        case 'update_status':
            if ($bookingModel->updateStatus((int)$_POST['id'], $_POST['status'])) {
                $response = ['success' => true, 'message' => 'Status updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update status'];
            }
            break;
            
        case 'delete':
            if ($bookingModel->delete((int)$_POST['id'])) {
                $response = ['success' => true, 'message' => 'Booking deleted successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to delete booking'];
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}

$bookings = $bookingModel->getAll();
$stats = $bookingModel->getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Booking Management | Sinta</title>
    <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 20px; padding: 1rem; text-align: center; border: 1px solid var(--border); }
        .stat-card h3 { font-size: 1.8rem; margin: 0; color: var(--primary); }
        .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: 24px; overflow: hidden; }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
        .admin-table th { background: var(--cream); }
        .status-select { padding: 0.3rem 0.6rem; border-radius: 20px; border: 1px solid var(--border); }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: #333; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; z-index: 3000; animation: slideIn 0.3s ease; }
        .toast.success { background: #2e7d32; }
        .toast.error { background: #c62828; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

<div class="admin-container">
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $stats['total'] ?? 0 ?></h3>
            <p>Total Bookings</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['pending'] ?? 0 ?></h3>
            <p>Pending</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['paid'] ?? 0 ?></h3>
            <p>Paid</p>
        </div>
        <div class="stat-card">
            <h3>₱<?= number_format($stats['total_revenue'] ?? 0, 0) ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>📋 Booking <em>Management</em></h1>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Package</th>
                <th>Total (₱)</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="bookingsTableBody">
            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem;">No bookings yet</td>
                </tr>
            <?php else: ?>
                <?php foreach ($bookings as $book): ?>
                    <tr id="booking-row-<?= $book['checkout_id'] ?>">
                        <td><?= $book['checkout_id'] ?></td>
                        <td><?= htmlspecialchars($book['first_name'] . ' ' . ($book['last_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($book['email'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($book['package_name'] ?? 'Custom Package') ?></td>
                        <td><?= number_format($book['total_amount'], 0) ?></td>
                        <td>
                            <select class="status-select" onchange="updateStatus(<?= $book['checkout_id'] ?>, this.value)">
                                <option value="pending" <?= $book['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= $book['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="completed" <?= $book['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="failed" <?= $book['status'] == 'failed' ? 'selected' : '' ?>>Failed</option>
                            </select>
                        </td>
                        <td><?= date('M d, Y', strtotime($book['date'])) ?></td>
                        <td>
                            <button class="btn btn--ghost btn-sm" onclick="deleteBooking(<?= $book['checkout_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function updateStatus(id, status) {
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&id=${id}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (!data.success) {
            location.reload();
        }
    });
}

function deleteBooking(id) {
    if (confirm('Delete this booking?')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                document.getElementById(`booking-row-${id}`)?.remove();
            }
        });
    }
}
</script>
</body>
</html>
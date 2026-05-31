<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}
require_once ROOT_PATH . '/app/models/Booking.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/PlanAutoConfirmation.php';

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin' && empty($_SESSION['admin_logged_in']))) {
    header('Location: ' . BASE_URL . '/index.php?route=signin');
    exit;
}

$page = 'admin-bookings';
$page_title = 'Booking Management';

// Handle AJAX requests BEFORE doing any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    // Set JSON header and send response
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $bookingModel = new Booking();
        $action = $_POST['action'] ?? '';
        $response = ['success' => false, 'message' => 'Invalid action'];
        
        switch ($action) {
            case 'update_status':
                $bookingId = (int)($_POST['id'] ?? 0);
                $status = $_POST['status'] ?? '';
                
                if (!$bookingId) {
                    $response = ['success' => false, 'message' => 'Invalid booking ID'];
                } elseif (!$status) {
                    $response = ['success' => false, 'message' => 'Invalid status'];
                } else {
                    $canUpdate = $bookingModel->canUpdateStatus($bookingId);
                    
                    if (!$canUpdate['can_update']) {
                        $response = ['success' => false, 'message' => $canUpdate['reason']];
                    } else {
                        $updateResult = $bookingModel->updateStatus($bookingId, $status);
                        if ($updateResult) {
                            $response = ['success' => true, 'message' => 'Status updated successfully'];
                        } else {
                            error_log("Booking update failed for ID $bookingId");
                            $response = ['success' => false, 'message' => 'Failed to update status. Please try again.'];
                        }
                    }
                }
                break;
                
            case 'delete':
                $bookingId = (int)($_POST['id'] ?? 0);
                
                if (!$bookingId) {
                    $response = ['success' => false, 'message' => 'Invalid booking ID'];
                } else {
                    $canDelete = $bookingModel->canDelete($bookingId);
                    
                    if (!$canDelete['can_delete']) {
                        $response = ['success' => false, 'message' => $canDelete['reason']];
                    } else {
                        $deleteResult = $bookingModel->delete($bookingId);
                        if ($deleteResult) {
                            $response = ['success' => true, 'message' => 'Booking deleted successfully'];
                        } else {
                            error_log("Booking delete failed for ID $bookingId");
                            $response = ['success' => false, 'message' => 'Failed to delete booking. Please try again.'];
                        }
                    }
                }
                break;
        }
        
        // Clear any buffered output before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        echo json_encode($response);
    } catch (Exception $e) {
        error_log("Admin bookings error: " . $e->getMessage());
        http_response_code(500);
        if (ob_get_level()) {
            ob_clean();
        }
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
    exit;
}

// Only load these for page display
$bookingModel = new Booking();
$autoConfirm = new PlanAutoConfirmation();

$bookings = $bookingModel->getAll();

// Auto-confirm pending plans and update their status
foreach ($bookings as &$booking) {
    $planStatusInfo = $autoConfirm->getPlanStatusInfo($booking['checkout_id']);
    if ($planStatusInfo) {
        $booking['status'] = $planStatusInfo['status'];
    }
}

$stats = $bookingModel->getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Booking Management | Sinta</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        .bookings-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .bookings-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: #2C2820; margin: 0; display: flex; align-items: center; gap: 1rem; letter-spacing: -0.03em; font-weight: 200; }
        .bookings-header h1 em { color: #8A7650; font-style: italic; font-weight: 400; }
        .animated-icon { display: inline-flex; color: #8A7650; animation: pulse 1.4s ease-in-out infinite; font-size: 1.6rem; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.08); } }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 20px; padding: 1.5rem; text-align: center; border: 2px solid #E2D9C8; transition: all 0.3s ease; }
        .stat-card:hover { border-color: #8A7650; box-shadow: 0 10px 30px rgba(138, 118, 80, 0.15); }
        .stat-card h3 { font-size: 1.8rem; margin: 0 0 0.5rem; color: #8A7650; font-weight: 700; }
        .stat-card p { color: #8B7355; margin: 0; font-weight: 600; }
        .table-wrapper { overflow-x: auto; border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); max-width: 100%; }
        .admin-table { width: 100%; border-collapse: collapse; background: white; min-width: 1100px; }
        .admin-table th, .admin-table td { padding: 0.7rem 0.6rem; text-align: left; border-bottom: 1px solid #827660; font-size: 0.85rem; }
        .admin-table th { background: #b5a584; font-weight: 600; color: #2C2820; white-space: nowrap; }
        .admin-table td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .admin-table td:nth-child(1) { width: 50px; }
        .admin-table td:nth-child(2) { max-width: 110px; }
        .admin-table td:nth-child(3) { max-width: 130px; }
        .admin-table td:nth-child(4) { max-width: 120px; }
        .admin-table td:nth-child(5) { max-width: 100px; }
        .admin-table td:nth-child(10) { min-width: 130px; white-space: normal; }
        .booking-actions { display: flex; gap: 0.3rem; flex-wrap: wrap; }
        .booking-actions .btn { font-size: 0.75rem; padding: 0.4rem 0.6rem; white-space: nowrap; }
        .btn-delete-custom { color: #f44336; border-color: #f44336; }
        .btn-delete-custom:hover { background: rgba(244, 67, 54, 0.15); color: #d32f2f; border-color: #d32f2f; }
        .status-select { padding: 0.5rem 0.75rem; border-radius: 8px; border: 2px solid #E2D9C8; background: white; color: #2C2820; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-size: 0.9rem; }
        .status-select:hover { border-color: #8A7650; }
        .status-select:focus { outline: none; border-color: #8A7650; box-shadow: 0 0 0 3px rgba(138, 118, 80, 0.1); }
        .status-select:disabled { background: #F5F0E8; color: #A39B8B; border-color: #D4CAB8; cursor: not-allowed; opacity: 0.7; }
        .status-select:disabled:hover { border-color: #D4CAB8; }
        .completed-indicator { display: block; color: #8A7650; margin-top: 0.25rem; font-weight: 600; font-size: 0.75rem; }
        .btn-delete-custom:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-delete-custom:disabled:hover { background: transparent; color: #f44336; border-color: #f44336; }
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
            <h3><?= $stats['confirmed'] ?? 0 ?></h3>
            <p>Confirmed</p>
        </div>
        <div class="stat-card">
            <h3>₱<?= number_format($stats['total_revenue'] ?? 0, 0) ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    
    <div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Event</th>
                <th>Event Date</th>
                <th>Total (₱)</th>
                <th>Paid (₱)</th>
                <th>Payment Status</th>
                <th>Booking Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="bookingsTableBody">
            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 2rem;">No bookings yet</td>
                </tr>
            <?php else: ?>
                <?php foreach ($bookings as $book): ?>
                    <tr id="booking-row-<?= $book['checkout_id'] ?>">
                        <td><?= $book['checkout_id'] ?></td>
                        <td><?= htmlspecialchars($book['first_name'] . ' ' . ($book['last_name'] ?? '')) ?></td>
                        <td title="<?= htmlspecialchars($book['email'] ?? 'N/A') ?>" style="cursor: help;"><?= substr(htmlspecialchars($book['email'] ?? 'N/A'), 0, 18) ?><?= strlen($book['email'] ?? '') > 18 ? '...' : '' ?></td>
                        <td title="<?= htmlspecialchars($book['event_name'] ?? 'Custom Event') ?>" style="cursor: help;"><?= substr(htmlspecialchars($book['event_name'] ?? 'Custom Event'), 0, 20) ?><?= strlen($book['event_name'] ?? '') > 20 ? '...' : '' ?></td>
                        <td><?= !empty($book['event_date']) ? date('M d, Y', strtotime($book['event_date'])) : 'TBD' ?></td>
                        <td><?= number_format($book['total_amount'], 0) ?></td>
                        <td><?= number_format($book['total_paid'] ?? 0, 0) ?></td>
                        <td>
                            <?php 
                                $paymentPercent = ($book['total_amount'] > 0) ? round(($book['total_paid'] ?? 0) / $book['total_amount'] * 100) : 0;
                                if ($paymentPercent == 100) {
                                    $paymentBadgeClass = 'badge--green';
                                    $paymentText = 'Fully Paid';
                                } elseif ($paymentPercent >= 50) {
                                    $paymentBadgeClass = 'badge--warning';
                                    $paymentText = '50% Paid';
                                } else {
                                    $paymentBadgeClass = 'badge--danger';
                                    $paymentText = 'Unpaid';
                                }
                            ?>
                            <span class="badge <?= $paymentBadgeClass ?>" style="display: inline-block; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                <?= $paymentText ?>
                            </span>
                        </td>
                        <td>
                            <select class="status-select" 
                                    id="status-select-<?= $book['checkout_id'] ?>"
                                    onchange="updateStatus(<?= $book['checkout_id'] ?>, this.value)" 
                                    <?= $book['status'] == 'completed' ? 'disabled' : '' ?> 
                                    title="<?= $book['status'] == 'completed' ? 'This booking is completed and cannot be changed' : '' ?>">
                                <option value="pending" <?= $book['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $book['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="completed" <?= $book['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="canceled" <?= $book['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                            </select>
                            <?php if ($book['status'] == 'completed'): ?>
                                <small style="display: block; color: #8A7650; margin-top: 0.25rem; font-weight: 600; font-size: 0.7rem;">
                                    <i class="fas fa-lock"></i> Settled
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="booking-actions">
                                <button class="btn btn--ghost btn--sm btn-delete-custom" 
                                        id="delete-btn-<?= $book['checkout_id'] ?>"
                                        onclick="deleteBooking(<?= $book['checkout_id'] ?>)"
                                        <?= $book['status'] == 'completed' ? 'disabled' : '' ?>
                                        title="<?= $book['status'] == 'completed' ? 'Cannot delete completed bookings' : '' ?>"
                                        style="<?= $book['status'] == 'completed' ? 'opacity: 0.5; cursor: not-allowed;' : '' ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
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
    // Get current status from the select element
    const selectElement = document.getElementById(`status-select-${id}`);
    const currentStatus = selectElement.getAttribute('data-previous-status') || selectElement.value;
    
    // Check if the booking is already completed
    if (selectElement.disabled) {
        showToast('This booking is completed and cannot be changed', 'error');
        selectElement.value = currentStatus;
        return;
    }
    
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: `action=update_status&id=${id}&status=${status}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            // If status changed to completed, disable the select and delete button
            if (status === 'completed') {
                selectElement.disabled = true;
                selectElement.title = 'This booking is completed and cannot be changed';
                const deleteBtn = document.getElementById(`delete-btn-${id}`);
                if (deleteBtn) {
                    deleteBtn.disabled = true;
                    deleteBtn.style.opacity = '0.5';
                    deleteBtn.style.cursor = 'not-allowed';
                    deleteBtn.title = 'Cannot delete completed bookings';
                }
                // Add the settled indicator
                const smallTag = selectElement.nextElementSibling;
                if (!smallTag || !smallTag.textContent.includes('Settled')) {
                    const settledIndicator = document.createElement('small');
                    settledIndicator.style.display = 'block';
                    settledIndicator.style.color = '#8A7650';
                    settledIndicator.style.marginTop = '0.25rem';
                    settledIndicator.style.fontWeight = '600';
                    settledIndicator.innerHTML = '<i class="fas fa-lock"></i> Settled';
                    selectElement.parentNode.insertBefore(settledIndicator, selectElement.nextSibling);
                }
            }
            // Reload page to reflect the updated status
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(error => {
        showToast('An error occurred: ' + error.message, 'error');
        console.error('Error:', error);
        selectElement.value = currentStatus;
    });
}

function deleteBooking(id) {
    const deleteBtn = document.getElementById(`delete-btn-${id}`);
    
    // Check if booking is completed
    if (deleteBtn && deleteBtn.disabled) {
        showToast('Cannot delete completed bookings', 'error');
        return;
    }
    
    if (confirm('Delete this booking?')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'same-origin',
            body: `action=delete&id=${id}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                document.getElementById(`booking-row-${id}`)?.remove();
            }
        })
        .catch(error => {
            showToast('An error occurred: ' + error.message, 'error');
            console.error('Error:', error);
        });
    }
}
</script>
<?php include 'admin-footer.php'; ?>
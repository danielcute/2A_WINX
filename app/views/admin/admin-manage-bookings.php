<?php
/**
 * Admin Booking Management Page
 * Manage all customer bookings - CREATE, READ, UPDATE, DELETE
 */

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

require_once ROOT_PATH . '/app/controllers/BookingController.php';
require_once ROOT_PATH . '/config/database.php';

$bookingController = new BookingController();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    // Handle form submissions for CRUD
    $action = $_POST['booking_action'] ?? '';
    
    switch ($action) {
        case 'create':
            $user_id = intval($_POST['user_id'] ?? 0);
            $package_id = intval($_POST['package_id'] ?? null) ?: null;
            $event_type = trim($_POST['event_type'] ?? '');
            $event_date = trim($_POST['event_date'] ?? '');
            $guest_count = intval($_POST['guest_count'] ?? 0);
            $total_price = floatval($_POST['total_price'] ?? 0);
            $status = trim($_POST['status'] ?? 'pending');
            
            if (!$user_id) {
                $error = 'Please select a customer';
                break;
            }
            
            $result = $bookingController->create([
                'user_id' => $user_id,
                'package_id' => $package_id,
                'event_type' => $event_type,
                'event_date' => $event_date,
                'guest_count' => $guest_count,
                'total_price' => $total_price,
                'status' => $status
            ]);
            
            if ($result['success']) {
                $message = 'Booking created successfully!';
            } else {
                $error = 'Failed to create booking: ' . ($result['error'] ?? 'Unknown error');
            }
            break;
            
        case 'update':
            $booking_id = intval($_POST['booking_id'] ?? 0);
            if (!$booking_id) {
                $error = 'Invalid booking ID';
                break;
            }
            
            $update_data = [];
            if (isset($_POST['event_type'])) $update_data['event_type'] = trim($_POST['event_type']);
            if (isset($_POST['event_date'])) $update_data['event_date'] = trim($_POST['event_date']);
            if (isset($_POST['guest_count'])) $update_data['guest_count'] = intval($_POST['guest_count']);
            if (isset($_POST['total_price'])) $update_data['total_price'] = floatval($_POST['total_price']);
            if (isset($_POST['status'])) $update_data['status'] = trim($_POST['status']);
            
            $result = $bookingController->update($booking_id, $update_data);
            if ($result['success']) {
                $message = 'Booking updated successfully!';
            } else {
                $error = 'Failed to update booking: ' . ($result['error'] ?? 'Unknown error');
            }
            break;
            
        case 'delete':
            $booking_id = intval($_POST['booking_id'] ?? 0);
            if (!$booking_id) {
                $error = 'Invalid booking ID';
                break;
            }
            
            $result = $bookingController->delete($booking_id);
            if ($result['success']) {
                $message = 'Booking deleted successfully!';
            } else {
                $error = 'Failed to delete booking: ' . ($result['error'] ?? 'Unknown error');
            }
            break;
    }
}

// Get all bookings and stats
$bookings = $bookingController->getAll();
$stats = $bookingController->getStats();

// Get all users and packages for dropdowns
$users_result = $db->query("SELECT user_id, first_name, last_name, email FROM users_tbl WHERE role = 'user' ORDER BY first_name");
$packages_result = $db->query("SELECT package_id, package_name, price FROM packages_tbl ORDER BY package_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Booking Management | SINTA</title>
    <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 1rem; 
            margin-bottom: 2rem; 
        }
        
        .stat-card { 
            background: white; 
            border-radius: 15px; 
            padding: 1.5rem; 
            border: 2px solid #f0e6d6;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover { 
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 { 
            font-size: 1.8rem; 
            margin: 0;
            color: var(--primary);
            font-weight: bold;
        }
        
        .stat-card p { 
            margin: 0.5rem 0 0 0; 
            color: #666;
            font-size: 0.9rem;
        }
        
        .action-buttons { 
            display: flex; 
            gap: 0.5rem;
            margin: 1rem 0;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: #f0e6d6;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e8d6c6;
        }
        
        .btn-danger {
            background: #d32f2f;
            color: white;
        }
        
        .btn-danger:hover {
            background: #b71c1c;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal.show { display: block; }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #f0e6d6;
            padding-bottom: 1rem;
        }
        
        .modal-header h2 {
            margin: 0;
            color: var(--primary);
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f5f0;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            font-weight: 600;
            color: var(--primary);
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

<div class="admin-container">
    <h1>📋 Booking <em>Management</em></h1>
    
    <?php if ($message): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">✗ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Stats -->
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
            <h3>₱<?= number_format($stats['total_revenue'] ?? 0, 2) ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i class="fas fa-plus"></i> Create New Booking
        </button>
    </div>
    
    <!-- Bookings Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Event Type</th>
                    <th>Event Date</th>
                    <th>Guests</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($bookings) && !isset($bookings['error'])): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?= $booking['booking_id'] ?></td>
                            <td><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></td>
                            <td><?= htmlspecialchars($booking['email']) ?></td>
                            <td><?= htmlspecialchars($booking['event_type']) ?></td>
                            <td><?= date('M d, Y', strtotime($booking['event_date'])) ?></td>
                            <td><?= $booking['guest_count'] ?></td>
                            <td>₱<?= number_format($booking['total_price'], 2) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-secondary" onclick="openEditModal(<?= htmlspecialchars(json_encode($booking)) ?>)">
                                    Edit
                                </button>
                                <button class="btn btn-danger" onclick="deleteBooking(<?= $booking['booking_id'] ?>)">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem;">
                            No bookings found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Create New Booking</h2>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        
        <form id="bookingForm" method="POST">
            <input type="hidden" name="booking_id" id="booking_id">
            <input type="hidden" name="booking_action" id="booking_action" value="create">
            
            <div class="form-group">
                <label for="user_id">Customer *</label>
                <select name="user_id" id="user_id" required>
                    <option value="">Select a customer...</option>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <option value="<?= $user['user_id'] ?>">
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> (<?= $user['email'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="package_id">Package</label>
                <select name="package_id" id="package_id">
                    <option value="">Select a package...</option>
                    <?php $packages_result->data_seek(0); while ($package = $packages_result->fetch_assoc()): ?>
                        <option value="<?= $package['package_id'] ?>">
                            <?= htmlspecialchars($package['package_name']) ?> (₱<?= number_format($package['price'], 2) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="event_type">Event Type *</label>
                <input type="text" name="event_type" id="event_type" placeholder="e.g., Wedding, Birthday, Corporate" required>
            </div>
            
            <div class="form-group">
                <label for="event_date">Event Date *</label>
                <input type="date" name="event_date" id="event_date" required>
            </div>
            
            <div class="form-group">
                <label for="guest_count">Number of Guests *</label>
                <input type="number" name="guest_count" id="guest_count" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="total_price">Total Price *</label>
                <input type="number" name="total_price" id="total_price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" required>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Save Booking
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').innerText = 'Create New Booking';
    document.getElementById('booking_action').value = 'create';
    document.getElementById('booking_id').value = '';
    document.getElementById('bookingForm').reset();
    document.getElementById('bookingModal').classList.add('show');
}

function openEditModal(booking) {
    document.getElementById('modalTitle').innerText = 'Edit Booking';
    document.getElementById('booking_action').value = 'update';
    document.getElementById('booking_id').value = booking.booking_id;
    document.getElementById('user_id').value = booking.user_id;
    document.getElementById('package_id').value = booking.package_id || '';
    document.getElementById('event_type').value = booking.event_type;
    document.getElementById('event_date').value = booking.event_date;
    document.getElementById('guest_count').value = booking.guest_count;
    document.getElementById('total_price').value = booking.total_price;
    document.getElementById('status').value = booking.status;
    document.getElementById('bookingModal').classList.add('show');
}

function closeModal() {
    document.getElementById('bookingModal').classList.remove('show');
}

function deleteBooking(bookingId) {
    if (!confirm('Are you sure you want to delete this booking?')) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="booking_action" value="delete">
        <input type="hidden" name="booking_id" value="${bookingId}">
    `;
    document.body.appendChild(form);
    form.submit();
}

window.onclick = function(event) {
    const modal = document.getElementById('bookingModal');
    if (event.target == modal) {
        modal.classList.remove('show');
    }
}
</script>
</body>
</html>

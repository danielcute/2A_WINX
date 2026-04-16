<?php 
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
$page_title = 'Booking Management';

// Initialize bookings in session
if (!isset($_SESSION['bookings'])) {
    $_SESSION['bookings'] = [
        ['id' => 1001, 'customer' => 'Maria Santos', 'email' => 'maria@email.com', 'package' => 'Classic Wedding', 'event_date' => '2025-08-12', 'total' => 150000, 'status' => 'confirmed', 'created_at' => '2025-01-15'],
        ['id' => 1002, 'customer' => 'John Reyes', 'email' => 'john@email.com', 'package' => 'Deluxe Birthday', 'event_date' => '2025-10-03', 'total' => 85000, 'status' => 'pending', 'created_at' => '2025-01-20'],
    ];
}

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $newId = max(array_column($_SESSION['bookings'], 'id') ?: [1000]) + 1;
                $_SESSION['bookings'][] = [
                    'id' => $newId,
                    'customer' => $_POST['customer'],
                    'email' => $_POST['email'],
                    'package' => $_POST['package'],
                    'event_date' => $_POST['event_date'],
                    'total' => (int)$_POST['total'],
                    'status' => $_POST['status'],
                    'created_at' => date('Y-m-d')
                ];
                break;
                
            case 'update':
                foreach ($_SESSION['bookings'] as &$book) {
                    if ($book['id'] == $_POST['id']) {
                        $book['customer'] = $_POST['customer'];
                        $book['email'] = $_POST['email'];
                        $book['package'] = $_POST['package'];
                        $book['event_date'] = $_POST['event_date'];
                        $book['total'] = (int)$_POST['total'];
                        $book['status'] = $_POST['status'];
                        break;
                    }
                }
                break;
                
            case 'delete':
                $_SESSION['bookings'] = array_filter($_SESSION['bookings'], fn($b) => $b['id'] != $_POST['id']);
                $_SESSION['bookings'] = array_values($_SESSION['bookings']);
                break;
                
            case 'update_status':
                foreach ($_SESSION['bookings'] as &$book) {
                    if ($book['id'] == $_POST['id']) {
                        $book['status'] = $_POST['status'];
                        break;
                    }
                }
                break;
        }
    }
    header('Location: admin-bookings.php');
    exit;
}

$bookings = $_SESSION['bookings'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Booking Management | Sinta</title>
  <link rel="stylesheet" href="assets/css/global.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .admin-container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
    .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: 24px; overflow: hidden; }
    .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
    .admin-table th { background: var(--cream); }
    .status-select { padding: 0.3rem 0.6rem; border-radius: 20px; border: 1px solid var(--border); }
    .form-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 2000; }
    .form-modal.active { display: flex; }
    .form-modal-content { background: white; border-radius: 28px; padding: 2rem; max-width: 500px; width: 90%; }
    .form-group { margin-bottom: 1rem; }
    .form-group input, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 12px; }
    .btn-group { display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.5rem; }
  </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

<div class="admin-container">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>📋 Booking <em>Management</em></h1>
    <button class="btn btn--primary" onclick="openModal('create')">+ Create New Booking</button>
  </div>
  
  <table class="admin-table">
    <thead>
      <tr><th>ID</th><th>Customer</th><th>Email</th><th>Package</th><th>Event Date</th><th>Total (₱)</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if (empty($bookings)): ?>
        <tr><td colspan="8" style="text-align: center; padding: 2rem;">No bookings yet</td></tr>
      <?php else: ?>
        <?php foreach ($bookings as $book): ?>
        <tr>
          <td><?= $book['id'] ?></td>
          <td><?= htmlspecialchars($book['customer']) ?></td>
          <td><?= htmlspecialchars($book['email']) ?></td>
          <td><?= htmlspecialchars($book['package']) ?></td>
          <td><?= $book['event_date'] ?></td>
          <td><?= number_format($book['total']) ?></td>
          <td>
            <select class="status-select" onchange="updateStatus(<?= $book['id'] ?>, this.value)">
              <option value="pending" <?= $book['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="confirmed" <?= $book['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
              <option value="completed" <?= $book['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>
          </td>
          <td>
            <button class="btn btn--primary btn-sm" onclick="openModal('update', <?= htmlspecialchars(json_encode($book)) ?>)">Edit</button>
            <button class="btn btn--ghost btn-sm" onclick="deleteBooking(<?= $book['id'] ?>)">Delete</button>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Form -->
<div id="bookingModal" class="form-modal">
  <div class="form-modal-content">
    <h3 id="modalTitle">Create Booking</h3>
    <form method="POST" id="bookingForm">
      <input type="hidden" name="action" id="formAction"><input type="hidden" name="id" id="bookingId">
      <div class="form-group"><label>Customer Name</label><input type="text" name="customer" id="bookCustomer" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" id="bookEmail" required></div>
      <div class="form-group"><label>Package</label><input type="text" name="package" id="bookPackage" required></div>
      <div class="form-group"><label>Event Date</label><input type="date" name="event_date" id="bookDate" required></div>
      <div class="form-group"><label>Total (₱)</label><input type="number" name="total" id="bookTotal" required></div>
      <div class="form-group"><label>Status</label><select name="status" id="bookStatus"><option value="pending">Pending</option><option value="confirmed">Confirmed</option><option value="completed">Completed</option></select></div>
      <div class="btn-group"><button type="submit" class="btn btn--primary">Save</button><button type="button" class="btn btn--ghost" onclick="closeModal()">Cancel</button></div>
    </form>
  </div>
</div>

<script>
function openModal(action, data = null) {
  const modal = document.getElementById('bookingModal');
  modal.classList.add('active');
  document.getElementById('formAction').value = action;
  if (action === 'create') {
    document.getElementById('modalTitle').innerText = 'Create New Booking';
    document.getElementById('bookingForm').reset();
    document.getElementById('bookingId').value = '';
  } else if (action === 'update' && data) {
    document.getElementById('modalTitle').innerText = 'Update Booking';
    document.getElementById('bookingId').value = data.id;
    document.getElementById('bookCustomer').value = data.customer;
    document.getElementById('bookEmail').value = data.email;
    document.getElementById('bookPackage').value = data.package;
    document.getElementById('bookDate').value = data.event_date;
    document.getElementById('bookTotal').value = data.total;
    document.getElementById('bookStatus').value = data.status;
  }
}
function closeModal() { document.getElementById('bookingModal').classList.remove('active'); }
function deleteBooking(id) { if(confirm('Delete this booking?')){ let f=document.createElement('form');f.method='POST';f.innerHTML=`<input name="action" value="delete"><input name="id" value="${id}">`;document.body.appendChild(f);f.submit();} }
function updateStatus(id, status) { let f=document.createElement('form');f.method='POST';f.innerHTML=`<input name="action" value="update_status"><input name="id" value="${id}"><input name="status" value="${status}">`;document.body.appendChild(f);f.submit(); }
</script>
</body>
</html>
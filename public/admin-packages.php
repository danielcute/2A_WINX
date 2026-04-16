<?php 
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
$page_title = 'Package Management';

// Initialize packages array in session (simulating database)
if (!isset($_SESSION['packages'])) {
    $_SESSION['packages'] = [
        ['id' => 1, 'name' => 'Classic Wedding', 'occasion' => 'wedding', 'price' => 150000, 'description' => 'Perfect for those who want a beautifully organized event without complexity.'],
        ['id' => 2, 'name' => 'Elegant Wedding', 'occasion' => 'wedding', 'price' => 250000, 'description' => 'Elevated experience with premium vendors and extended services.'],
        ['id' => 3, 'name' => 'Classic Birthday', 'occasion' => 'birthday', 'price' => 45000, 'description' => 'Fun and festive celebration with all the essentials.'],
    ];
}

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $newId = count($_SESSION['packages']) + 1;
                $_SESSION['packages'][] = [
                    'id' => $newId,
                    'name' => $_POST['name'],
                    'occasion' => $_POST['occasion'],
                    'price' => (int)$_POST['price'],
                    'description' => $_POST['description']
                ];
                break;
                
            case 'update':
                foreach ($_SESSION['packages'] as &$pkg) {
                    if ($pkg['id'] == $_POST['id']) {
                        $pkg['name'] = $_POST['name'];
                        $pkg['occasion'] = $_POST['occasion'];
                        $pkg['price'] = (int)$_POST['price'];
                        $pkg['description'] = $_POST['description'];
                        break;
                    }
                }
                break;
                
            case 'delete':
                $_SESSION['packages'] = array_filter($_SESSION['packages'], fn($p) => $p['id'] != $_POST['id']);
                $_SESSION['packages'] = array_values($_SESSION['packages']);
                break;
        }
    }
    header('Location: admin-packages.php');
    exit;
}

$packages = $_SESSION['packages'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Package Management | Sinta</title>
  <link rel="stylesheet" href="assets/css/global.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .admin-container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
    .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
    .admin-table th { background: var(--cream); font-weight: 600; }
    .btn-sm { padding: 0.3rem 0.8rem; font-size: 0.75rem; }
    .form-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 2000; }
    .form-modal.active { display: flex; }
    .form-modal-content { background: white; border-radius: 28px; padding: 2rem; max-width: 500px; width: 90%; }
    .form-group { margin-bottom: 1rem; }
    .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 12px; }
    .btn-group { display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.5rem; }
  </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

<div class="admin-container">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>📦 Package <em>Management</em></h1>
    <button class="btn btn--primary" onclick="openModal('create')">+ Create New Package</button>
  </div>
  
  <table class="admin-table">
    <thead>
      <tr><th>ID</th><th>Package Name</th><th>Occasion</th><th>Price (₱)</th><th>Description</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if (empty($packages)): ?>
        <tr><td colspan="6" style="text-align: center; padding: 2rem;">No packages yet</td></tr>
      <?php else: ?>
        <?php foreach ($packages as $pkg): ?>
        <tr>
          <td><?= $pkg['id'] ?></td>
          <td><?= htmlspecialchars($pkg['name']) ?></td>
          <td><?= ucfirst($pkg['occasion']) ?></td>
          <td><?= number_format($pkg['price']) ?></td>
          <td><?= htmlspecialchars(substr($pkg['description'], 0, 50)) ?>...</td>
          <td>
            <button class="btn btn--primary btn-sm" onclick="openModal('update', <?= htmlspecialchars(json_encode($pkg)) ?>)">Edit</button>
            <button class="btn btn--ghost btn-sm" onclick="deletePackage(<?= $pkg['id'] ?>)">Delete</button>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
</div>

<!-- Modal Form -->
<div id="packageModal" class="form-modal">
  <div class="form-modal-content">
    <h3 id="modalTitle">Create Package</h3>
    <form method="POST" id="packageForm">
      <input type="hidden" name="action" id="formAction">
      <input type="hidden" name="id" id="packageId">
      <div class="form-group"><label>Package Name</label><input type="text" name="name" id="pkgName" required></div>
      <div class="form-group">
        <label>Occasion</label>
        <select name="occasion" id="pkgOccasion" required>
          <option value="wedding">Wedding</option>
          <option value="birthday">Birthday</option>
          <option value="corporate">Corporate</option>
          <option value="anniversary">Anniversary</option>
        </select>
      </div>
      <div class="form-group"><label>Price (₱)</label><input type="number" name="price" id="pkgPrice" required></div>
      <div class="form-group"><label>Description</label><textarea name="description" id="pkgDesc" rows="3" required></textarea></div>
      <div class="btn-group"><button type="submit" class="btn btn--primary">Save</button><button type="button" class="btn btn--ghost" onclick="closeModal()">Cancel</button></div>
    </form>
  </div>
</div>

<script>
function openModal(action, data = null) {
  const modal = document.getElementById('packageModal');
  document.getElementById('formAction').value = action;
  if (action === 'create') {
    document.getElementById('modalTitle').innerText = 'Create New Package';
    document.getElementById('packageForm').reset();
    document.getElementById('packageId').value = '';
  } else if (action === 'update' && data) {
    document.getElementById('modalTitle').innerText = 'Update Package';
    document.getElementById('packageId').value = data.id;
    document.getElementById('pkgName').value = data.name;
    document.getElementById('pkgOccasion').value = data.occasion;
    document.getElementById('pkgPrice').value = data.price;
    document.getElementById('pkgDesc').value = data.description;
  }
  modal.classList.add('active');
}
function closeModal() { document.getElementById('packageModal').classList.remove('active'); }
function deletePackage(id) {
  if (confirm('Delete this package permanently?')) {
    const form = document.createElement('form'); form.method = 'POST';
    form.innerHTML = `<input name="action" value="delete"><input name="id" value="${id}">`;
    document.body.appendChild(form); form.submit();
  }
}
</script>
</body>
</html>
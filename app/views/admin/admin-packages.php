<?php 
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}
require_once ROOT_PATH . '/app/models/Package.php';
require_once ROOT_PATH . '/app/models/Occasion.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /index.php?route=signin');
    exit;
}

$page = 'admin-packages';
$page_title = 'Package Management';
$packageModel = new Package();
$occasionModel = new Occasion();
$occasions = $occasionModel->getAll();

// Handle AJAX (unchanged)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];
    switch ($action) {
        case 'create':
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'occasion_id' => (int)$_POST['occasion_id'],
                'price' => (float)$_POST['price'],
                'inclusions' => isset($_POST['inclusions']) ? json_decode($_POST['inclusions'], true) : [],
                'images' => isset($_POST['images']) ? json_decode($_POST['images'], true) : []
            ];
            if ($packageModel->create($data)) $response = ['success' => true, 'message' => 'Package created successfully'];
            else $response = ['success' => false, 'message' => 'Failed to create package'];
            break;
        case 'update':
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'occasion_id' => (int)$_POST['occasion_id'],
                'price' => (float)$_POST['price'],
                'inclusions' => isset($_POST['inclusions']) ? json_decode($_POST['inclusions'], true) : [],
                'images' => isset($_POST['images']) ? json_decode($_POST['images'], true) : []
            ];
            if ($packageModel->update((int)$_POST['id'], $data)) $response = ['success' => true, 'message' => 'Package updated successfully'];
            else $response = ['success' => false, 'message' => 'Failed to update package'];
            break;
        case 'delete':
            if ($packageModel->delete((int)$_POST['id'])) $response = ['success' => true, 'message' => 'Package deleted successfully'];
            else $response = ['success' => false, 'message' => 'Failed to delete package'];
            break;
        case 'get':
            $package = $packageModel->findById((int)$_POST['id']);
            if ($package) $response = ['success' => true, 'data' => $package];
            else $response = ['success' => false, 'message' => 'Package not found'];
            break;
    }
    echo json_encode($response);
    exit;
}

$packages = $packageModel->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Package Management | Sinta</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Floating Action Button */
        .fab-add {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            border-radius: 28px;
            background: linear-gradient(135deg, #8A7650, #6b5a40);
            color: white;
            border: none;
            box-shadow: 0 6px 14px rgba(0,0,0,0.2), 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            transition: all 0.2s ease;
            z-index: 100;
        }
        .fab-add:hover {
            transform: scale(1.08);
            background: linear-gradient(135deg, #9c8a64, #7e6b4c);
            box-shadow: 0 10px 20px rgba(0,0,0,0.25);
        }
        /* Package grid & cards */
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.8rem;
            margin-top: 1rem;
        }
        .package-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #E2D9C8;
            overflow: hidden;
            transition: all 0.25s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .package-card:hover {
            transform: translateY(-6px);
            border-color: #8A7650;
            box-shadow: 0 20px 30px -12px rgba(138,118,80,0.2);
        }
        .package-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0ebe2;
        }
        .package-info {
            padding: 1.2rem 1.2rem 1.5rem;
        }
        .package-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2C2820;
            margin-bottom: 0.3rem;
            font-family: 'Cormorant Garamond', serif;
        }
        .package-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #8A7650;
            margin: 0.5rem 0;
        }
        .package-desc {
            color: #6B6463;
            font-size: 0.85rem;
            line-height: 1.4;
            margin-bottom: 1rem;
        }
        .package-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        /* Modal remains same as before but we keep it functional */
        .form-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 2000; }
        .form-modal.active { display: flex; }
        .form-modal-content { background: white; border-radius: 28px; padding: 2rem; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid #E2D9C8; border-radius: 12px; }
        .inclusion-item { display: flex; gap: 0.5rem; margin-bottom: 0.5rem; }
        .image-upload-area { border: 2px dashed #E2D9C8; border-radius: 12px; padding: 1rem; text-align: center; cursor: pointer; margin-top: 0.5rem; }
        .image-preview { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem; }
        .image-preview-item { position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; }
        .image-preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .image-preview-item .remove { position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: #333; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; z-index: 3000; animation: slideIn 0.3s ease; }
        .toast.success { background: #2e7d32; }
        .toast.error { background: #c62828; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body>
<?php include ROOT_PATH . '/app/views/admin/admin-nav.php'; ?>
<div style="padding: 1rem 2rem;">
    <div class="packages-grid">
        <?php if (empty($packages)): ?>
            <div style="text-align: center; grid-column: 1/-1; padding: 3rem;">No packages yet. Click the + button to create one.</div>
        <?php else: ?>
            <?php foreach ($packages as $pkg): ?>
                <div class="package-card">
                    <img src="<?= htmlspecialchars($pkg['image'] ?? '/assets/img/placeholder.jpg') ?>" class="package-image" alt="<?= htmlspecialchars($pkg['name']) ?>">
                    <div class="package-info">
                        <div class="package-name"><?= htmlspecialchars($pkg['name']) ?></div>
                        <div class="package-price">₱<?= number_format($pkg['price'], 0) ?></div>
                        <div class="package-desc"><?= htmlspecialchars(substr($pkg['description'] ?? '', 0, 80)) ?>...</div>
                        <div class="package-actions">
                            <button class="btn btn--primary btn--sm btn-animation" onclick="editPackage(<?= $pkg['package_id'] ?>)"><i class="fas fa-edit"></i> Edit</button>
                            <button class="btn btn--ghost btn--sm btn-delete-custom btn-animation" onclick="deletePackage(<?= $pkg['package_id'] ?>)"><i class="fas fa-trash"></i> Delete</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Floating Action Button -->
<button class="fab-add" onclick="openModal('create')"><i class="fas fa-plus"></i></button>

<!-- Modal (unchanged functionality) -->
<div id="packageModal" class="form-modal">
    <div class="form-modal-content">
        <h3 id="modalTitle">Create Package</h3>
        <form id="packageForm">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id" id="packageId">
            <div class="form-group"><label>Package Name *</label><input type="text" name="name" id="pkgName" required></div>
            <div class="form-group"><label>Occasion *</label><select name="occasion_id" id="pkgOccasion" required><option value="">Select Occasion</option><?php foreach ($occasions as $occ): ?><option value="<?= $occ['occasion_id'] ?>"><?= htmlspecialchars($occ['events']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Price (₱) *</label><input type="number" name="price" id="pkgPrice" required></div>
            <div class="form-group"><label>Description</label><textarea name="description" id="pkgDesc" rows="3"></textarea></div>
            <div class="form-group"><label>Inclusions</label><div id="inclusionsContainer"><div class="inclusion-item"><input type="text" class="inclusion-input" placeholder="e.g., Full Event Coordination"><button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button></div></div><button type="button" class="btn btn--ghost btn--sm" onclick="addInclusion()">+ Add Inclusion</button></div>
            <div class="form-group"><label>Images</label><div class="image-upload-area" onclick="document.getElementById('imageInput').click()"><i class="fas fa-cloud-upload-alt"></i><p>Click to upload images</p></div><input type="file" id="imageInput" multiple accept="image/*" style="display: none;" onchange="handleImageUpload(this)"><div id="imagePreviewContainer" class="image-preview"></div></div>
            <div class="btn-group" style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;"><button type="submit" class="btn btn--primary">Save Package</button><button type="button" class="btn btn--ghost" onclick="closeModal()">Cancel</button></div>
        </form>
    </div>
</div>

<script>
let currentImages = [];
function addInclusion() { const container = document.getElementById('inclusionsContainer'); const div = document.createElement('div'); div.className = 'inclusion-item'; div.innerHTML = `<input type="text" class="inclusion-input" placeholder="e.g., Full Event Coordination"><button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button>`; container.appendChild(div); }
function removeInclusion(btn) { btn.parentElement.remove(); }
function handleImageUpload(input) { const files = input.files; const previewContainer = document.getElementById('imagePreviewContainer'); for (let file of files) { const reader = new FileReader(); reader.onload = function(e) { const imageData = e.target.result; currentImages.push(imageData); const div = document.createElement('div'); div.className = 'image-preview-item'; div.innerHTML = `<img src="${imageData}" alt="Preview"><div class="remove" onclick="removeImage(this, '${imageData}')">✕</div>`; previewContainer.appendChild(div); }; reader.readAsDataURL(file); } }
function removeImage(element, imageData) { element.parentElement.remove(); const index = currentImages.indexOf(imageData); if (index > -1) currentImages.splice(index, 1); }
function showToast(message, type) { const toast = document.createElement('div'); toast.className = `toast ${type}`; toast.textContent = message; document.body.appendChild(toast); setTimeout(() => toast.remove(), 3000); }
function openModal(action, data = null) { const modal = document.getElementById('packageModal'); document.getElementById('formAction').value = action; currentImages = []; document.getElementById('imagePreviewContainer').innerHTML = ''; if (action === 'create') { document.getElementById('modalTitle').innerText = 'Create New Package'; document.getElementById('packageForm').reset(); document.getElementById('packageId').value = ''; document.getElementById('inclusionsContainer').innerHTML = `<div class="inclusion-item"><input type="text" class="inclusion-input" placeholder="e.g., Full Event Coordination"><button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button></div>`; } else if (action === 'update' && data) { document.getElementById('modalTitle').innerText = 'Update Package'; document.getElementById('packageId').value = data.package_id; document.getElementById('pkgName').value = data.name; document.getElementById('pkgOccasion').value = data.occasion_id; document.getElementById('pkgPrice').value = data.price; document.getElementById('pkgDesc').value = data.description || ''; const container = document.getElementById('inclusionsContainer'); container.innerHTML = ''; if (data.inclusions && data.inclusions.length) { data.inclusions.forEach(item => { const div = document.createElement('div'); div.className = 'inclusion-item'; div.innerHTML = `<input type="text" class="inclusion-input" value="${escapeHtml(item)}"><button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button>`; container.appendChild(div); }); } else { container.innerHTML = `<div class="inclusion-item"><input type="text" class="inclusion-input" placeholder="e.g., Full Event Coordination"><button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button></div>`; } if (data.images && data.images.length) { const previewContainer = document.getElementById('imagePreviewContainer'); previewContainer.innerHTML = ''; data.images.forEach(img => { const div = document.createElement('div'); div.className = 'image-preview-item'; div.innerHTML = `<img src="${img}" alt="Package Image"><div class="remove" onclick="removeImage(this, '${img}')">✕</div>`; previewContainer.appendChild(div); currentImages.push(img); }); } } modal.classList.add('active'); }
function closeModal() { document.getElementById('packageModal').classList.remove('active'); }
function editPackage(id) { fetch('', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=get&id=${id}` }).then(response => response.json()).then(data => { if (data.success) openModal('update', data.data); else showToast(data.message, 'error'); }); }
function deletePackage(id) { if (confirm('Delete this package permanently?')) { fetch('', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=delete&id=${id}` }).then(response => response.json()).then(data => { showToast(data.message, data.success ? 'success' : 'error'); if (data.success) setTimeout(() => location.reload(), 1500); }); } }
function escapeHtml(text) { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }
document.getElementById('packageForm').addEventListener('submit', function(e) { e.preventDefault(); const inclusions = Array.from(document.querySelectorAll('.inclusion-input')).map(input => input.value.trim()).filter(value => value !== ''); const formData = new FormData(); formData.append('action', document.getElementById('formAction').value); formData.append('id', document.getElementById('packageId').value); formData.append('name', document.getElementById('pkgName').value); formData.append('occasion_id', document.getElementById('pkgOccasion').value); formData.append('price', document.getElementById('pkgPrice').value); formData.append('description', document.getElementById('pkgDesc').value); formData.append('inclusions', JSON.stringify(inclusions)); formData.append('images', JSON.stringify(currentImages)); fetch('', { method: 'POST', body: formData }).then(response => response.json()).then(data => { showToast(data.message, data.success ? 'success' : 'error'); if (data.success) setTimeout(() => location.reload(), 1500); }); });
document.getElementById('packageModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
</script>
<?php include 'admin-footer.php'; ?>
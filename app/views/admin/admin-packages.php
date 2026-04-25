<?php 
require_once dirname(__DIR__, 2) . '/models/Package.php';
require_once dirname(__DIR__, 2) . '/models/Occasion.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

$page_title = 'Package Management';
$packageModel = new Package();
$occasionModel = new Occasion();
$occasions = $occasionModel->getAll();

// Handle AJAX requests
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
            
            if ($packageModel->create($data)) {
                $response = ['success' => true, 'message' => 'Package created successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to create package'];
            }
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
            
            if ($packageModel->update((int)$_POST['id'], $data)) {
                $response = ['success' => true, 'message' => 'Package updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update package'];
            }
            break;
            
        case 'delete':
            if ($packageModel->delete((int)$_POST['id'])) {
                $response = ['success' => true, 'message' => 'Package deleted successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to delete package'];
            }
            break;
            
        case 'get':
            $package = $packageModel->findById((int)$_POST['id']);
            if ($package) {
                $response = ['success' => true, 'data' => $package];
            } else {
                $response = ['success' => false, 'message' => 'Package not found'];
            }
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
    <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
        .admin-table th { background: var(--cream); font-weight: 600; }
        .package-action-buttons { display: flex; gap: 0.75rem; }
        .package-action-buttons .btn {
            flex: 1;
            min-width: 130px;
        }
        .btn-animation {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .animated-icon { display: inline-flex; color: var(--primary); animation: pulse 1.4s ease-in-out infinite; }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
        .form-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 2000; }
        .form-modal.active { display: flex; }
        .form-modal-content { background: white; border-radius: 28px; padding: 2rem; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.85rem; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 12px; }
        .inclusions-list { margin-top: 0.5rem; }
        .inclusion-item { display: flex; gap: 0.5rem; margin-bottom: 0.5rem; }
        .inclusion-item input { flex: 1; }
        .btn-group { display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.5rem; }
        .image-upload-area { border: 2px dashed var(--border); border-radius: 12px; padding: 1rem; text-align: center; cursor: pointer; margin-top: 0.5rem; }
        .image-preview { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem; }
        .image-preview-item { position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; }
        .image-preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .image-preview-item .remove { position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; }
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

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1><i class="fas fa-box-open animated-icon"></i> Package <em>Management</em></h1>
        <button class="btn btn--primary btn--sm" onclick="openModal('create')"><i class="fas fa-plus"></i> Create New Package</button>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Package Name</th>
                <th>Occasion</th>
                <th>Price (₱)</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="packagesTableBody">
            <?php if (empty($packages)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">No packages yet</td>
                </tr>
            <?php else: ?>
                <?php foreach ($packages as $pkg): ?>
                    <tr>
                        <td><?= $pkg['package_id'] ?></td>
                        <td><?= htmlspecialchars($pkg['name']) ?></td>
                        <td><?= htmlspecialchars($pkg['occasion_name'] ?? 'N/A') ?></td>
                        <td><?= number_format($pkg['price'], 0) ?></td>
                        <td><?= htmlspecialchars(substr($pkg['description'] ?? '', 0, 50)) ?>...</td>
                        <td>
                            <div class="package-action-buttons">
                                <button class="btn btn--primary btn--sm btn-animation" onclick="editPackage(<?= $pkg['package_id'] ?>)"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn--ghost btn--sm btn-delete-custom btn-animation" onclick="deletePackage(<?= $pkg['package_id'] ?>)"><i class="fas fa-trash"></i> Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Form -->
<div id="packageModal" class="form-modal">
    <div class="form-modal-content">
        <h3 id="modalTitle">Create Package</h3>
        <form id="packageForm">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id" id="packageId">
            
            <div class="form-group">
                <label>Package Name *</label>
                <input type="text" name="name" id="pkgName" required>
            </div>
            
            <div class="form-group">
                <label>Occasion *</label>
                <select name="occasion_id" id="pkgOccasion" required>
                    <option value="">Select Occasion</option>
                    <?php foreach ($occasions as $occ): ?>
                        <option value="<?= $occ['occasion_id'] ?>"><?= htmlspecialchars($occ['events']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Price (₱) *</label>
                <input type="number" name="price" id="pkgPrice" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="pkgDesc" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Package Inclusions</label>
                <div id="inclusionsContainer">
                    <div class="inclusion-item">
                        <input type="text" class="inclusion-input" placeholder="e.g., Full Event Coordination">
                        <button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button>
                    </div>
                </div>
                <button type="button" class="btn btn--ghost btn--sm" onclick="addInclusion()">+ Add Inclusion</button>
            </div>
            
            <div class="form-group">
                <label>Package Images</label>
                <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to upload images</p>
                </div>
                <input type="file" id="imageInput" multiple accept="image/*" style="display: none;" onchange="handleImageUpload(this)">
                <div id="imagePreviewContainer" class="image-preview"></div>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn--primary">Save Package</button>
                <button type="button" class="btn btn--ghost" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentImages = [];

function addInclusion() {
    const container = document.getElementById('inclusionsContainer');
    const div = document.createElement('div');
    div.className = 'inclusion-item';
    div.innerHTML = `
        <input type="text" class="inclusion-input" placeholder="e.g., Full Event Coordination">
        <button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button>
    `;
    container.appendChild(div);
}

function removeInclusion(btn) {
    btn.parentElement.remove();
}

function handleImageUpload(input) {
    const files = input.files;
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    for (let file of files) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imageData = e.target.result;
            currentImages.push(imageData);
            
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `
                <img src="${imageData}" alt="Preview">
                <div class="remove" onclick="removeImage(this, '${imageData}')">✕</div>
            `;
            previewContainer.appendChild(div);
        };
        reader.readAsDataURL(file);
    }
}

function removeImage(element, imageData) {
    element.parentElement.remove();
    const index = currentImages.indexOf(imageData);
    if (index > -1) currentImages.splice(index, 1);
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function openModal(action, data = null) {
    const modal = document.getElementById('packageModal');
    document.getElementById('formAction').value = action;
    currentImages = [];
    document.getElementById('imagePreviewContainer').innerHTML = '';
    
    if (action === 'create') {
        document.getElementById('modalTitle').innerText = 'Create New Package';
        document.getElementById('packageForm').reset();
        document.getElementById('packageId').value = '';
        document.getElementById('inclusionsContainer').innerHTML = `
            <div class="inclusion-item">
                <input type="text" class="inclusion-input" placeholder="e.g., Full Event Coordination">
                <button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button>
            </div>
        `;
    } else if (action === 'update' && data) {
        document.getElementById('modalTitle').innerText = 'Update Package';
        document.getElementById('packageId').value = data.package_id;
        document.getElementById('pkgName').value = data.name;
        document.getElementById('pkgOccasion').value = data.occasion_id;
        document.getElementById('pkgPrice').value = data.price;
        document.getElementById('pkgDesc').value = data.description || '';
        
        // Populate inclusions
        const container = document.getElementById('inclusionsContainer');
        container.innerHTML = '';
        if (data.inclusions && data.inclusions.length) {
            data.inclusions.forEach(item => {
                const div = document.createElement('div');
                div.className = 'inclusion-item';
                div.innerHTML = `
                    <input type="text" class="inclusion-input" value="${escapeHtml(item)}">
                    <button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button>
                `;
                container.appendChild(div);
            });
        } else {
            container.innerHTML = `
                <div class="inclusion-item">
                    <input type="text" class="inclusion-input" placeholder="e.g., Full Event Coordination">
                    <button type="button" class="btn btn--ghost btn--icon" onclick="removeInclusion(this)">✕</button>
                </div>
            `;
        }
        
        // Populate images if any
        if (data.images && data.images.length) {
            const previewContainer = document.getElementById('imagePreviewContainer');
            previewContainer.innerHTML = '';
            data.images.forEach(img => {
                const div = document.createElement('div');
                div.className = 'image-preview-item';
                div.innerHTML = `
                    <img src="${img}" alt="Package Image">
                    <div class="remove" onclick="removeImage(this, '${img}')">✕</div>
                `;
                previewContainer.appendChild(div);
                currentImages.push(img);
            });
        }
    }
    modal.classList.add('active');
}

function closeModal() {
    document.getElementById('packageModal').classList.remove('active');
}

function editPackage(id) {
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=get&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            openModal('update', data.data);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function deletePackage(id) {
    if (confirm('Delete this package permanently? This action cannot be undone.')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                setTimeout(() => location.reload(), 1500);
            }
        });
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('packageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const inclusions = Array.from(document.querySelectorAll('.inclusion-input'))
        .map(input => input.value.trim())
        .filter(value => value !== '');
    
    const formData = new FormData();
    formData.append('action', document.getElementById('formAction').value);
    formData.append('id', document.getElementById('packageId').value);
    formData.append('name', document.getElementById('pkgName').value);
    formData.append('occasion_id', document.getElementById('pkgOccasion').value);
    formData.append('price', document.getElementById('pkgPrice').value);
    formData.append('description', document.getElementById('pkgDesc').value);
    formData.append('inclusions', JSON.stringify(inclusions));
    formData.append('images', JSON.stringify(currentImages));
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    });
});

// Close modal on outside click
document.getElementById('packageModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
<?php include 'admin-footer.php'; ?>
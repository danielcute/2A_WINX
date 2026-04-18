<?php
/**
 * Admin Customization Management Page
 * Manage package customizations/add-ons - CREATE, READ, UPDATE, DELETE
 */

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

require_once ROOT_PATH . '/app/controllers/CustomizationController.php';
require_once ROOT_PATH . '/config/database.php';

$customizationController = new CustomizationController();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    // Handle form submissions for CRUD
    $action = $_POST['custom_action'] ?? '';
    
    switch ($action) {
        case 'create':
            $package_id = intval($_POST['package_id'] ?? 0);
            $category = trim($_POST['category'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $status = trim($_POST['status'] ?? 'active');
            
            if (!$package_id || !$category || !$name || $price < 0) {
                $error = 'Please fill in all required fields';
                break;
            }
            
            $result = $customizationController->create([
                'package_id' => $package_id,
                'category' => $category,
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'status' => $status
            ]);
            
            if ($result['success']) {
                // Handle image upload if provided
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_result = $customizationController->uploadImage($_FILES['image'], $result['customization_id']);
                    if ($image_result['success']) {
                        $customizationController->update($result['customization_id'], ['image' => $image_result['path']]);
                        $message = 'Customization created successfully with image!';
                    } else {
                        $message = 'Customization created but image upload failed: ' . $image_result['error'];
                    }
                } else {
                    $message = 'Customization created successfully!';
                }
            } else {
                $error = 'Failed to create customization: ' . ($result['error'] ?? 'Unknown error');
            }
            break;
            
        case 'update':
            $customization_id = intval($_POST['customization_id'] ?? 0);
            if (!$customization_id) {
                $error = 'Invalid customization ID';
                break;
            }
            
            $update_data = [];
            if (isset($_POST['category'])) $update_data['category'] = trim($_POST['category']);
            if (isset($_POST['name'])) $update_data['name'] = trim($_POST['name']);
            if (isset($_POST['description'])) $update_data['description'] = trim($_POST['description']);
            if (isset($_POST['price'])) $update_data['price'] = floatval($_POST['price']);
            if (isset($_POST['status'])) $update_data['status'] = trim($_POST['status']);
            
            $result = $customizationController->update($customization_id, $update_data);
            if ($result['success']) {
                // Handle image upload if provided
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_result = $customizationController->uploadImage($_FILES['image'], $customization_id);
                    if ($image_result['success']) {
                        $customizationController->update($customization_id, ['image' => $image_result['path']]);
                        $message = 'Customization updated successfully with new image!';
                    } else {
                        $message = 'Customization updated but image upload failed: ' . $image_result['error'];
                    }
                } else {
                    $message = 'Customization updated successfully!';
                }
            } else {
                $error = 'Failed to update customization: ' . ($result['error'] ?? 'Unknown error');
            }
            break;
            
        case 'delete':
            $customization_id = intval($_POST['customization_id'] ?? 0);
            if (!$customization_id) {
                $error = 'Invalid customization ID';
                break;
            }
            
            $result = $customizationController->delete($customization_id);
            if ($result['success']) {
                $message = 'Customization deleted successfully!';
            } else {
                $error = 'Failed to delete customization: ' . ($result['error'] ?? 'Unknown error');
            }
            break;
    }
}

// Get all customizations and stats
$customizations = $customizationController->getAll();
$stats = $customizationController->getStats();

// Get all packages for dropdown
$packages_result = $db->query("SELECT package_id, name as package_name FROM packages_tbl ORDER BY name");

// Get customization categories
$categories_result = $db->query("SELECT DISTINCT category FROM customizations_tbl WHERE category IS NOT NULL AND category != '' ORDER BY category");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Customization Management | SINTA</title>
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
            flex-wrap: wrap;
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
            box-sizing: border-box;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
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
            white-space: nowrap;
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
        
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        
        .category-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            background: #e3f2fd;
            color: #1565c0;
        }
        
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
        
        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #ddd;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

<div class="admin-container">
    <h1>✨ Customization <em>Management</em></h1>
    
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
            <p>Total Add-ons</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['packages_with_options'] ?? 0 ?></h3>
            <p>Packages with Options</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['active'] ?? 0 ?></h3>
            <p>Active</p>
        </div>
        <div class="stat-card">
            <h3>₱<?= number_format($stats['avg_price'] ?? 0, 2) ?></h3>
            <p>Average Price</p>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i class="fas fa-plus"></i> Add New Customization
        </button>
    </div>
    
    <!-- Customizations Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Package</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($customizations) && !isset($customizations['error'])): ?>
                    <?php foreach ($customizations as $custom): ?>
                        <tr>
                            <td>#<?= $custom['customization_id'] ?></td>
                            <td><?= htmlspecialchars($custom['package_name'] ?? 'N/A') ?></td>
                            <td><span class="category-badge"><?= htmlspecialchars($custom['category']) ?></span></td>
                            <td><?= htmlspecialchars($custom['name']) ?></td>
                            <td><?= htmlspecialchars(substr($custom['description'], 0, 50)) . (strlen($custom['description']) > 50 ? '...' : '') ?></td>
                            <td>₱<?= number_format($custom['price'], 2) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($custom['status']) ?>">
                                    <?= ucfirst($custom['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-secondary" onclick="openEditModal(<?= htmlspecialchars(json_encode($custom)) ?>)">
                                    Edit
                                </button>
                                <button class="btn btn-danger" onclick="deleteCustomization(<?= $custom['customization_id'] ?>)">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">
                            No customizations found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="customizationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Customization</h2>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        
        <form id="customizationForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="customization_id" id="customization_id">
            <input type="hidden" name="custom_action" id="custom_action" value="create">
            
            <div class="form-group">
                <label for="package_id">Package *</label>
                <select name="package_id" id="package_id" required>
                    <option value="">Select a package...</option>
                    <?php $packages_result->data_seek(0); while ($pkg = $packages_result->fetch_assoc()): ?>
                        <option value="<?= $pkg['package_id'] ?>">
                            <?= htmlspecialchars($pkg['package_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <input type="text" name="category" id="category" placeholder="e.g., Catering, Decoration, Photography" required>
            </div>
            
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" name="name" id="name" placeholder="e.g., Premium Photography Package" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Describe this customization option..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" name="price" id="price" step="0.01" min="0" placeholder="0.00" required>
            </div>
            
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" name="image" id="image" accept="image/*">
                <small>Supported formats: JPG, PNG, GIF, WEBP (Max 5MB)</small>
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Save Customization
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').innerText = 'Add New Customization';
    document.getElementById('custom_action').value = 'create';
    document.getElementById('customization_id').value = '';
    document.getElementById('customizationForm').reset();
    document.getElementById('customizationModal').classList.add('show');
}

function openEditModal(custom) {
    document.getElementById('modalTitle').innerText = 'Edit Customization';
    document.getElementById('custom_action').value = 'update';
    document.getElementById('customization_id').value = custom.customization_id;
    document.getElementById('package_id').value = custom.package_id;
    document.getElementById('category').value = custom.category;
    document.getElementById('name').value = custom.name;
    document.getElementById('description').value = custom.description;
    document.getElementById('price').value = custom.price;
    document.getElementById('status').value = custom.status;
    document.getElementById('customizationModal').classList.add('show');
}

function closeModal() {
    document.getElementById('customizationModal').classList.remove('show');
}

function deleteCustomization(customizationId) {
    if (!confirm('Are you sure you want to delete this customization?')) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="custom_action" value="delete">
        <input type="hidden" name="customization_id" value="${customizationId}">
    `;
    document.body.appendChild(form);
    form.submit();
}

window.onclick = function(event) {
    const modal = document.getElementById('customizationModal');
    if (event.target == modal) {
        modal.classList.remove('show');
    }
}
</script>
</body>
</html>

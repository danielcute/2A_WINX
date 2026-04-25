<?php
/**
 * Admin Package Management Page
 */

$page = 'admin-packages';

// Check if admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/app/controllers/AdminPackageController.php';
require_once ROOT_PATH . '/config/database.php';

$packageController = new AdminPackageController();
$db = Database::getInstance()->getConnection();

// Fetch all occasions for dropdown
$occasions = [];
$result = $db->query("SELECT occasion_id, events as name FROM occasions_tbl ORDER BY events ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $occasions[] = $row;
    }
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $name = trim($_POST['package_name'] ?? '');
                $package_name = trim($_POST['package_name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $price = floatval($_POST['price'] ?? 0);
                $occasion_id = intval($_POST['event_type'] ?? 0);
                $category = trim($_POST['category'] ?? '');
                $features = trim($_POST['features'] ?? '');
                $max_guests = intval($_POST['max_guests'] ?? 100);
                $duration_hours = intval($_POST['duration_hours'] ?? 4);
                $venue_type = trim($_POST['venue_type'] ?? '');
                $status = 'active';
                
                // Validate required fields
                if (!$name || !$description || $price <= 0 || !$occasion_id) {
                    $error = 'Please fill in all required fields (Name, Description, Price, Event Type)';
                    break;
                }
                
                $package_data = [
                    'name' => $name,
                    'package_name' => $package_name,
                    'description' => $description,
                    'price' => $price,
                    'occasion_id' => $occasion_id,
                    'event_type' => '',
                    'category' => $category,
                    'features' => $features,
                    'max_guests' => $max_guests,
                    'duration_hours' => $duration_hours,
                    'venue_type' => $venue_type,
                    'status' => $status,
                    'image' => ''
                ];
                
                // Create the package first
                $result = $packageController->create($package_data);
                
                if (!$result['success']) {
                    $error = 'Failed to create package: ' . $result['error'];
                    break;
                }
                
                $package_id = $result['package_id'];
                
                // Handle image upload after package creation
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_result = $packageController->uploadImage($_FILES['image'], $package_id);
                    if ($image_result['success']) {
                        $message = 'Package created successfully with image!';
                    } else {
                        $message = 'Package created but image upload failed: ' . $image_result['error'];
                    }
                } else {
                    $message = 'Package created successfully!';
                }
                break;
                
            case 'update':
                $package_id = intval($_POST['package_id'] ?? 0);
                if (!$package_id) {
                    $error = 'Invalid package ID';
                    break;
                }
                
                $update_data = [
                    'name' => trim($_POST['package_name'] ?? ''),
                    'package_name' => trim($_POST['package_name'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'price' => floatval($_POST['price'] ?? 0),
                    'occasion_id' => intval($_POST['event_type'] ?? 0),
                    'event_type' => '',
                    'category' => trim($_POST['category'] ?? ''),
                    'features' => trim($_POST['features'] ?? ''),
                    'max_guests' => intval($_POST['max_guests'] ?? 100),
                    'duration_hours' => intval($_POST['duration_hours'] ?? 4),
                    'venue_type' => trim($_POST['venue_type'] ?? ''),
                    'status' => 'active',
                    'image' => ''
                ];
                
                $result = $packageController->update($package_id, $update_data);
                if (!$result['success']) {
                    $error = 'Failed to update package: ' . $result['error'];
                    break;
                }
                
                // Handle image upload after package update
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_result = $packageController->uploadImage($_FILES['image'], $package_id);
                    if ($image_result['success']) {
                        $message = 'Package updated successfully with new image!';
                    } else {
                        $message = 'Package updated but image upload failed: ' . $image_result['error'];
                    }
                } else {
                    $message = 'Package updated successfully!';
                }
                break;
                
            case 'delete':
                $package_id = intval($_POST['package_id'] ?? 0);
                if (!$package_id) {
                    $error = 'Invalid package ID';
                    break;
                }
                
                $result = $packageController->delete($package_id);
                if ($result['success']) {
                    $message = 'Package deleted successfully!';
                } else {
                    $error = 'Failed to delete package: ' . $result['error'];
                }
                break;
        }
    }
}

$packages = $packageController->getAll();

// Group packages by main admin categories
$packageSections = [
    'Wedding' => [],
    'Birthday' => [],
    'Big Events / Gala' => [],
    'Other Events' => []
];

foreach ($packages as $pkg) {
    $occasion = strtolower($pkg['occasion_name'] ?? '');
    if ($occasion === 'wedding') {
        $packageSections['Wedding'][] = $pkg;
    } elseif ($occasion === 'birthday') {
        $packageSections['Birthday'][] = $pkg;
    } elseif ($occasion === 'corporate') {
        $packageSections['Big Events / Gala'][] = $pkg;
    } else {
        $packageSections['Other Events'][] = $pkg;
    }
}

// Sort packages within each section
foreach ($packageSections as $section => &$pkgs) {
    usort($pkgs, function($a, $b) {
        // Custom sort order based on package name
        $order = [
            'Classic Wedding' => 1,
            'Elegant Wedding' => 2,
            'Premium Wedding' => 3,
            'Classic Birthday' => 1,
            'Birthday Bundle A' => 2,
            'Birthday Bundle B' => 3,
            'Birthday Bundle C' => 4,
            'Corporate Gala' => 1,
            'Premium Gala Experience' => 2,
            'Luxury Gala & Awards' => 3
        ];
        
        $aOrder = $order[$a['package_name']] ?? 999;
        $bOrder = $order[$b['package_name']] ?? 999;
        
        if ($aOrder === $bOrder) {
            return $a['price'] <=> $b['price'];
        }
        
        return $aOrder <=> $bOrder;
    });
}

$sectionOrder = ['Wedding', 'Birthday', 'Big Events / Gala', 'Other Events'];
?>
<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Packages | Sinta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
    <style>
        body { background: #f5f5f5; font-family: 'DM Sans', sans-serif; }
        body.modal-open { overflow: hidden; }
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 1.9rem; color: #2C2820; margin: 0; font-weight: 700; letter-spacing: -0.03em; display: inline-flex; align-items: center; gap: 0.75rem; }
        .page-header h1 em { color: #8A7650; font-style: italic; font-weight: 400; }
        .btn {
            background: none;
            color: inherit;
            padding: 0.75rem 1.2rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn--primary { background: var(--primary); color: white; }
        .btn--primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 10px 30px rgba(138, 118, 80, 0.15); }
        .btn--ghost { background: transparent; color: var(--text-secondary); border: 1.5px solid var(--border); }
        .btn--ghost:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-2px); }
        .btn-danger { background: #f44336; color: white; border: none; }
        .btn-danger:hover { background: #d32f2f; }
        .btn-animation { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .msg-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .msg-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .section-filter { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 1.5rem; }
        .filter-pill { padding: 0.75rem 1.2rem; border-radius: 999px; border: 1px solid transparent; background: #f2efe8; color: #5f4b2f; cursor: pointer; transition: all 0.2s ease; }
        .filter-pill.active { background: #8A7650; border-color: #7e6b4c; color: white; }
        .section-title { font-size: 1.4rem; margin: 2rem 0 1rem; color: #333; border-bottom: 1px solid #e6e1d8; padding-bottom: 0.75rem; }
        .package-section { display: none; }
        .package-section.active { display: block; }
        .section-filter { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 1.5rem; }
        .filter-pill { padding: 0.75rem 1.2rem; border-radius: 999px; border: 1px solid transparent; background: #f2efe8; color: #5f4b2f; cursor: pointer; transition: all 0.2s ease; }
        .filter-pill.active { background: #8A7650; border-color: #7e6b4c; color: white; }
        .section-title { font-size: 1.4rem; margin: 2rem 0 1rem; color: #333; border-bottom: 1px solid #e6e1d8; padding-bottom: 0.75rem; }
        .package-section { display: none; }
        .package-section.active { display: block; }
        .packages-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .package-card { background: white; border: 2px solid #E2D9C8; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.3s ease; display: flex; flex-direction: column; }
        .package-card:hover { border-color: #8A7650; box-shadow: 0 15px 40px rgba(138, 118, 80, 0.2); transform: translateY(-8px); }
        .package-image { width: 100%; height: 200px; object-fit: cover; background: linear-gradient(135deg, #E2D9C8 0%, #D4C7B1 100%); }
        .package-info { padding: 1.5rem; display: flex; flex-direction: column; flex: 1; }
        .package-name { font-size: 1.2rem; font-weight: 600; color: #2C2820; margin-bottom: 0.5rem; }
        .package-price { color: #8A7650; font-size: 1.4rem; font-weight: 700; margin: 0.5rem 0; }
        .package-desc { color: #6B6463; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem; }
        .package-tag { font-size: 0.85rem; color: #8B7355; margin-bottom: 1rem; }
        .package-actions { display: flex; gap: 0.75rem; margin-top: auto; justify-content: center; align-items: center; }
        .package-actions .btn { flex: 0 1 auto; min-width: 120px; padding: 0.6rem 1.2rem; }
        .btn-delete-custom { color: #f44336; border-color: #f44336; }
        .btn-delete-custom:hover { background: rgba(244, 67, 54, 0.15); color: #d32f2f; border-color: #d32f2f; }
        .animated-icon { display: inline-flex; color: #8A7650; animation: pulse 1.4s ease-in-out infinite; font-size: 1.5rem; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.08); } }
        .btn-danger { background: #f44336; color: white; border: none; }
        .btn-danger:hover { background: #d32f2f; }
        
        /* Modal */
        .modal { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0, 0, 0, 0.7); 
            z-index: 99999; 
            align-items: center; 
            justify-content: center; 
            backdrop-filter: blur(3px);
        }
        .modal.active { 
            display: flex; 
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn { 
            from { opacity: 0; } 
            to { opacity: 1; } 
        }
        .modal-content { 
            background: white; 
            padding: 30px; 
            border-radius: 20px; 
            max-width: 600px; 
            width: 90%; 
            max-height: 90vh; 
            overflow-y: auto; 
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); 
            position: relative; 
            z-index: 100000;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { margin: 0; }
        .close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: #333; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; }
        .form-group textarea { resize: vertical; min-height: 100px; }
    </style>
</head>
<body>

<?php include __DIR__ . '/admin-nav.php'; ?>

<div class="admin-container">
    <div class="admin-page-header page-header">
        <h1 class="admin-page-title"><i class="fas fa-box-open animated-icon"></i> Manage Packages</h1>
        <button class="btn btn--primary btn--sm" onclick="openAddModal()"><i class="fas fa-plus"></i> Add New Package</button>
    </div>
    
    <?php if ($message): ?>
        <div class="msg-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="msg-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="section-filter" id="packageSectionFilter">
        <div class="filter-pill active" data-section="all" onclick="filterSection('all')">All Packages</div>
        <?php foreach ($sectionOrder as $sectionName): ?>
            <?php if (!empty($packageSections[$sectionName])): ?>
                <div class="filter-pill" data-section="<?= strtolower(str_replace([' ', '/'], ['-', ''], $sectionName)) ?>" onclick="filterSection('<?= strtolower(str_replace([' ', '/'], ['-', ''], $sectionName)) ?>')"><?= htmlspecialchars($sectionName) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php foreach ($sectionOrder as $sectionName): ?>
        <?php if (!empty($packageSections[$sectionName])): ?>
            <div class="package-section active" id="section-<?= strtolower(str_replace([' ', '/'], ['-', ''], $sectionName)) ?>">
                <div class="section-title"><?= htmlspecialchars($sectionName) ?></div>
                <div class="packages-grid">
                    <?php foreach ($packageSections[$sectionName] as $pkg): ?>
                        <div class="package-card">
                            <img src="<?= htmlspecialchars($pkg['image'] ?? '/SINTA/public/assets/img/placeholder.jpg') ?>" alt="<?= htmlspecialchars($pkg['name'] ?? 'Package') ?>" class="package-image">
                            <div class="package-info">
                                <div class="package-name"><?= htmlspecialchars($pkg['package_name'] ?? $pkg['name'] ?? 'Unnamed') ?></div>
                                <div class="package-price">₱<?= number_format($pkg['price'], 2) ?></div>
                                <div class="package-desc"><?= htmlspecialchars(substr($pkg['description'], 0, 80)) ?>...</div>
                                <div style="font-size: 0.85rem; color: #999; margin-bottom: 10px;">
                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($pkg['occasion_name'] ?? 'N/A') ?>
                                </div>
                                <div class="package-actions">
                                    <button class="btn btn--primary btn--sm btn-animation" onclick="editPackage(<?= $pkg['package_id'] ?>)"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn--ghost btn--sm btn-delete-custom btn-animation" onclick="deletePackage(<?= $pkg['package_id'] ?>)"><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- Add/Edit Modal - Moved outside admin-container for proper z-index stacking -->
<div class="modal" id="packageModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Package</h2>
            <button class="close-btn" onclick="closeModal()">✕</button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" id="packageForm">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="package_id" id="packageId">
            
            <div class="form-group">
                <label>Package Name *</label>
                <input type="text" name="package_name" required>
            </div>
            
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Price (₱) *</label>
                <input type="number" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Event Type *</label>
                <select name="event_type" required>
                    <option value="">Select Event Type</option>
                    <?php foreach ($occasions as $occ): ?>
                        <option value="<?= $occ['occasion_id'] ?>"><?= htmlspecialchars($occ['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" placeholder="e.g., Classic, Deluxe, Premium">
            </div>
            
            <div class="form-group">
                <label>Features/Inclusions</label>
                <textarea name="features" placeholder="List what's included (one per line)"></textarea>
            </div>
            
            <div class="form-group">
                <label>Max Guests</label>
                <input type="number" name="max_guests" value="100" min="1">
            </div>
            
            <div class="form-group">
                <label>Duration (hours)</label>
                <input type="number" name="duration_hours" value="4" min="1" max="24">
            </div>
            
            <div class="form-group">
                <label>Venue Type</label>
                <input type="text" name="venue_type" placeholder="e.g., Indoor, Outdoor, Hotel">
            </div>
            
            <div class="form-group">
                <label>Package Image</label>
                <input type="file" name="image" accept="image/*">
                <small>JPG, PNG, GIF, WEBP (Max 5MB)</small>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn" style="background: #999;" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn">Save Package</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Package';
    document.getElementById('formAction').value = 'create';
    document.getElementById('packageForm').reset();
    document.getElementById('packageModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function editPackage(packageId) {
    // Fetch package data via AJAX
    fetch('/SINTA/public/api-package.php?action=get_package&id=' + packageId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pkg = data.package;
                document.getElementById('modalTitle').textContent = 'Edit Package';
                document.getElementById('formAction').value = 'update';
                document.getElementById('packageId').value = pkg.package_id;
                document.querySelector('input[name="package_name"]').value = pkg.package_name || pkg.name || '';
                document.querySelector('textarea[name="description"]').value = pkg.description || '';
                document.querySelector('input[name="price"]').value = pkg.price || '';
                document.querySelector('select[name="event_type"]').value = pkg.occasion_id || '';
                document.querySelector('input[name="category"]').value = pkg.category || '';
                document.querySelector('textarea[name="features"]').value = pkg.features || '';
                document.querySelector('input[name="max_guests"]').value = pkg.max_guests || 100;
                document.querySelector('input[name="duration_hours"]').value = pkg.duration_hours || 4;
                document.querySelector('input[name="venue_type"]').value = pkg.venue_type || '';
                document.getElementById('packageModal').classList.add('active');
                document.body.classList.add('modal-open');
            } else {
                alert('Failed to load package data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading package data');
        });
}

function deletePackage(packageId) {
    if (confirm('Are you sure you want to delete this package?')) {
        // Create hidden form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="package_id" value="${packageId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function closeModal() {
    document.getElementById('packageModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

function filterSection(section) {
    const sections = document.querySelectorAll('.package-section');
    const pills = document.querySelectorAll('.filter-pill');

    pills.forEach(pill => {
        pill.classList.toggle('active', pill.dataset.section === section);
    });

    sections.forEach(sec => {
        if (section === 'all') {
            sec.classList.add('active');
        } else {
            sec.classList.toggle('active', sec.id === `section-${section}`);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    filterSection('all');
});
</script>

</body>
</html>

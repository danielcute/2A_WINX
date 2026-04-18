<?php
// admin-packages-modern.php - Modern Minimalist Packages Management

$packages = [
    ['id' => 'PKG001', 'name' => 'Wedding Gold', 'category' => 'Wedding', 'price' => 5000, 'features' => 12, 'status' => 'active', 'bookings' => 8],
    ['id' => 'PKG002', 'name' => 'Birthday Standard', 'category' => 'Birthday', 'price' => 2000, 'features' => 7, 'status' => 'active', 'bookings' => 15],
    ['id' => 'PKG003', 'name' => 'Corporate Platinum', 'category' => 'Corporate', 'price' => 8500, 'features' => 18, 'status' => 'active', 'bookings' => 5],
    ['id' => 'PKG004', 'name' => 'Anniversary Deluxe', 'category' => 'Anniversary', 'price' => 3500, 'features' => 10, 'status' => 'active', 'bookings' => 3],
    ['id' => 'PKG005', 'name' => 'Engagement Basic', 'category' => 'Engagement', 'price' => 1500, 'features' => 5, 'status' => 'inactive', 'bookings' => 0],
];

$categories = ['Wedding', 'Birthday', 'Corporate', 'Anniversary', 'Engagement'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages Management - Admin</title>
    <link rel="stylesheet" href="/SINTA/public/assets/css/admin-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .package-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .package-card:hover {
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .package-card__header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .package-card__name {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .package-card__category {
            font-size: 0.85rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .package-card__body {
            padding: 1.5rem;
            flex: 1;
        }

        .package-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .package-info__item {
            text-align: center;
        }

        .package-info__label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .package-info__value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .package-card__price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .package-card__price span {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 600;
            margin-right: 0.25rem;
        }

        .package-card__features {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .package-card__status {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }

        .package-card__footer {
            padding: 0 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 0.75rem;
        }

        .package-card__footer .btn {
            flex: 1;
            font-size: 0.85rem;
            padding: 0.6rem;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .packages-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .header-actions {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 640px) {
            .packages-grid {
                grid-template-columns: 1fr;
            }

            .package-card__footer {
                flex-direction: column;
            }
        }

        .table-view {
            display: none;
        }

        .view-toggle {
            display: flex;
            gap: 0.5rem;
        }

        .view-toggle .btn {
            padding: 0.6rem 1rem;
        }
    </style>
</head>
<body>
    <?php include 'admin-nav-new.php'; ?>

    <div class="admin-content">
        <!-- Section Header -->
        <div class="section-header">
            <h1 class="section-title">Packages Management</h1>
            <p class="section-subtitle">Create and manage your event packages</p>
        </div>

        <!-- Alert for demo -->
        <div class="alert alert--success">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>5 Active Packages</strong>
                <p>All your packages are published and available for booking.</p>
            </div>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <label style="font-weight: 600; color: var(--text-secondary); margin-right: 0.5rem;">View:</label>
                <div class="view-toggle">
                    <button class="btn btn--sm btn--primary" onclick="showGridView()">
                        <i class="fas fa-th"></i> Grid
                    </button>
                    <button class="btn btn--sm btn--secondary" onclick="showTableView()">
                        <i class="fas fa-list"></i> List
                    </button>
                </div>
            </div>
            <a href="#" class="btn btn--primary" onclick="openPackageModal(event)">
                <i class="fas fa-plus"></i> New Package
            </a>
        </div>

        <!-- Grid View -->
        <div class="packages-grid" id="gridView">
            <?php foreach ($packages as $package): ?>
                <div class="package-card">
                    <div class="package-card__header">
                        <div class="package-card__name"><?php echo htmlspecialchars($package['name']); ?></div>
                        <div class="package-card__category"><?php echo htmlspecialchars($package['category']); ?></div>
                    </div>
                    <div class="package-card__body">
                        <div class="package-card__price">
                            <span>$</span><?php echo number_format($package['price']); ?>
                        </div>
                        
                        <div class="package-info">
                            <div class="package-info__item">
                                <div class="package-info__label">Features</div>
                                <div class="package-info__value"><?php echo $package['features']; ?></div>
                            </div>
                            <div class="package-info__item">
                                <div class="package-info__label">Bookings</div>
                                <div class="package-info__value"><?php echo $package['bookings']; ?></div>
                            </div>
                        </div>

                        <div class="package-card__status">
                            <span class="badge badge--<?php echo $package['status'] === 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($package['status']); ?>
                            </span>
                            <span style="color: var(--text-secondary); font-size: 0.9rem;">Package ID: <?php echo htmlspecialchars($package['id']); ?></span>
                        </div>
                    </div>
                    <div class="package-card__footer">
                        <button class="btn btn--sm btn--secondary" onclick="viewPackage('<?php echo htmlspecialchars($package['id']); ?>')">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn--sm btn--secondary" onclick="editPackage('<?php echo htmlspecialchars($package['id']); ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn--sm btn--danger" onclick="deletePackage('<?php echo htmlspecialchars($package['id']); ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Table View -->
        <div class="table-view card" id="tableView">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Package Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Features</th>
                            <th>Bookings</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $package): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($package['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($package['category']); ?></td>
                                <td><strong>$<?php echo number_format($package['price']); ?></strong></td>
                                <td><?php echo $package['features']; ?> features</td>
                                <td><?php echo $package['bookings']; ?> bookings</td>
                                <td>
                                    <span class="badge badge--<?php echo $package['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($package['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn btn--sm btn--secondary" onclick="viewPackage('<?php echo htmlspecialchars($package['id']); ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn--sm btn--secondary" onclick="editPackage('<?php echo htmlspecialchars($package['id']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn--sm btn--danger" onclick="deletePackage('<?php echo htmlspecialchars($package['id']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Package Modal -->
    <div class="modal-overlay" id="packageModal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="modal-title">Package Details</h2>
                <button class="modal-close" onclick="closeModal('packageModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label form-label--required">Package Name</label>
                    <input type="text" class="form-input" placeholder="e.g., Wedding Gold">
                </div>
                <div class="form-group">
                    <label class="form-label form-label--required">Category</label>
                    <select class="form-select">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label form-label--required">Price ($)</label>
                        <input type="number" class="form-input" placeholder="5000">
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label--required">Number of Features</label>
                        <input type="number" class="form-input" placeholder="12">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" placeholder="Describe this package..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select">
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn--secondary" onclick="closeModal('packageModal')">Cancel</button>
                <button class="btn btn--primary">Save Package</button>
            </div>
        </div>
    </div>

    <script>
        function openPackageModal(e) {
            e.preventDefault();
            document.getElementById('packageModal').classList.add('active');
        }

        function viewPackage(id) {
            document.getElementById('packageModal').classList.add('active');
        }

        function editPackage(id) {
            document.getElementById('packageModal').classList.add('active');
        }

        function deletePackage(id) {
            if (confirm('Are you sure you want to delete this package?')) {
                // Delete logic here
                alert('Package deleted successfully');
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function showGridView() {
            document.getElementById('gridView').style.display = 'grid';
            document.getElementById('tableView').style.display = 'none';
        }

        function showTableView() {
            document.getElementById('gridView').style.display = 'none';
            document.getElementById('tableView').style.display = 'block';
        }

        // Close modal when clicking outside
        document.getElementById('packageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('packageModal');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const pageTitle = document.querySelector('#page-title');
            if (pageTitle) {
                pageTitle.textContent = 'Packages';
            }
        });
    </script>
</body>
</html>

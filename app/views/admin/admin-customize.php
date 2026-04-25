<?php
// Check admin authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php?route=admin-login");
    exit;
}

require_once ROOT_PATH . '/app/models/User.php';
$user = new User();
$admin = $user->findById($_SESSION['user_id']);

if (!$admin || $admin['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?route=home");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customizations - Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .header-section h1 {
            margin: 0;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.9rem;
            color: #2C2820;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            letter-spacing: -0.03em;
        }
        .header-section h1 em {
            color: #8A7650;
            font-style: italic;
            font-weight: 400;
        }


        .btn-edit:hover {
            background: #0b7dda;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
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

        .filter-section {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-section select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }

        .table-container {
            overflow-x: auto;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state__icon {
            font-size: 3rem;
            color: #8A7650;
            margin-bottom: 1rem;
        }

        .empty-state p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        /* Category Sections */
        .category-section {
            margin-bottom: 3rem;
        }

        .category-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            color: #8A7650;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E2D9C8;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .category-title i {
            font-size: 2rem;
            transition: all 0.3s ease;
            animation: iconPulse 2s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Options Grid */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Option Card */
        .option-card {
            background: white;
            border: 2px solid #E2D9C8;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .option-card:hover {
            border-color: #8A7650;
            box-shadow: 0 8px 24px rgba(138, 118, 80, 0.15);
            transform: translateY(-4px);
        }

        .option-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #E2D9C8 0%, #D4C7B1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .option-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder-image {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(138,118,80,0.1), rgba(138,118,80,0.05));
            font-size: 3rem;
            color: #8A7650;
        }

        .option-content {
            padding: 1.25rem;
            flex: 1;
        }

        .option-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            color: #2C2820;
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }

        .option-desc {
            font-size: 0.85rem;
            color: #6B6463;
            margin: 0 0 0.75rem 0;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .option-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #8A7650;
            margin-bottom: 0.5rem;
        }

        .option-status {
            margin-bottom: 1rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .option-actions {
            padding: 1rem 1.25rem;
            border-top: 1px solid #E2D9C8;
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            align-items: center;
        }

        .option-actions .btn--primary,
        .option-actions .btn-delete-custom {
            flex: 0 1 auto;
            min-width: 120px;
            padding: 0.6rem 1.2rem;
        }

        .header-section .btn {
            flex: 0;
            width: auto;
        }

        .btn-delete-custom {
            color: #f44336;
            border-color: #f44336;
        }

        .btn-delete-custom:hover {
            background: rgba(244, 67, 54, 0.15);
            color: #d32f2f;
            border-color: #d32f2f;
        }

        /* Floating Action Button */
        .btn--fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8A7650 0%, #6b5a40 100%);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(138, 118, 80, 0.3);
            transition: all 0.3s ease;
            animation: fabFloat 3s ease-in-out infinite;
            z-index: 99;
        }

        .btn--fab:hover {
            transform: scale(1.15);
            box-shadow: 0 8px 24px rgba(138, 118, 80, 0.4);
            animation: none;
        }

        .btn--fab:active {
            transform: scale(0.95);
        }

        @keyframes fabFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Header button enhancement */
        .header-section .btn {
            background: linear-gradient(135deg, #8A7650 0%, #6b5a40 100%);
            transition: all 0.3s ease;
            animation: slideInRight 0.6s ease-out;
        }

        .header-section .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(138, 118, 80, 0.25);
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Modal Styles */
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

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
            color: #2C2820;
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-header h2 i {
            color: #8A7650;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
            color: #999;
            transition: all 0.2s ease;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            color: #333;
            transform: rotate(90deg);
        }

        .modal .form-group {
            margin-bottom: 15px;
        }

        .modal .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .modal .form-group input,
        .modal .form-group select,
        .modal .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }

        .modal .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .modal .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #E2D9C8;
        }

        .modal .form-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .modal .form-actions button[type="submit"] {
            background: linear-gradient(135deg, #8A7650 0%, #6B5E4A 100%);
            color: white;
        }

        .modal .form-actions button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(138, 118, 80, 0.3);
        }

        .modal .form-actions button[type="button"] {
            background: #d4ccc0;
            color: #2C2820;
        }

        .modal .form-actions button[type="button"]:hover {
            background: #c8bfb4;
        }

        body.modal-open {
            overflow: hidden;
        }</head>
<body>
    <?php include ROOT_PATH . '/app/views/admin/admin-nav.php'; ?>

    <div class="admin-container">
        <div class="admin-page-header header-section">
            <h1 class="admin-page-title"><i class="fas fa-cog animated-icon"></i> Manage Customizations</h1>
            <button onclick="openCustomizationModal()" class="btn btn--primary btn--sm"><i class="fas fa-plus"></i> Add New Customization</button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✓ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ✗ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($options)): ?>
            <!-- Group options by category -->
            <?php $grouped = []; 
            foreach ($options as $opt) {
                $cat = $opt['category'];
                if (!isset($grouped[$cat])) $grouped[$cat] = [];
                $grouped[$cat][] = $opt;
            }
            ?>
            
            <?php foreach (['Theme', 'Venue', 'Catering', 'Extras'] as $category): ?>
                <?php if (isset($grouped[$category])): ?>
                    <div class="category-section">
                        <h2 class="category-title">
                            <i class="fas <?php 
                            if ($category === 'Theme') echo 'fa-palette';
                            elseif ($category === 'Venue') echo 'fa-map-marker-alt';
                            elseif ($category === 'Catering') echo 'fa-utensils';
                            else echo 'fa-sparkles';
                            ?>"></i>
                            <?= htmlspecialchars($category) ?>
                        </h2>
                        
                        <div class="options-grid">
                            <?php foreach ($grouped[$category] as $option): ?>
                                <div class="option-card">
                                    <!-- Image -->
                                    <div class="option-image">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image">
                                                <i class="fas <?php 
                                                    if ($category === 'Theme') echo 'fa-palette';
                                                    elseif ($category === 'Venue') echo 'fa-map-marker-alt';
                                                    elseif ($category === 'Catering') echo 'fa-utensils';
                                                    else echo 'fa-sparkles';
                                                ?>"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="option-content">
                                        <h3 class="option-name"><?php echo htmlspecialchars($option['name']); ?></h3>
                                        <?php if (!empty($option['description'])): ?>
                                            <p class="option-desc"><?php echo htmlspecialchars($option['description']); ?></p>
                                        <?php endif; ?>
                                        <div class="option-price">₱<?php echo number_format($option['price'], 0); ?></div>
                                        <div class="option-status">
                                            <span class="status-badge <?php echo $option['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $option['is_active'] ? '<i class="fas fa-check-circle"></i> Active' : '<i class="fas fa-times-circle"></i> Inactive'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="option-actions">
                                        <a href="<?php echo BASE_URL; ?>/index.php?route=admin-customize-edit&id=<?php echo $option['option_id']; ?>" class="btn btn--primary btn--sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="fas fa-inbox"></i></div>
                <p>No customization options found</p>
                <button onclick="openCustomizationModal()" class="btn btn--primary btn--sm"><i class="fas fa-plus"></i> Create First Customization</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal for Add Customization - Moved outside admin-container for proper z-index stacking -->
    <div class="modal" id="customizationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Add New Customization</h2>
                <button type="button" class="close-btn" onclick="closeCustomizationModal()">✕</button>
            </div>

            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=admin-customize-create" enctype="multipart/form-data" id="customizationForm">
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="Theme">Theme</option>
                        <option value="Venue">Venue</option>
                        <option value="Catering">Catering</option>
                        <option value="Extras">Extras</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Option Name *</label>
                    <input type="text" id="name" name="name" placeholder="e.g., Gold Decorations, Premium Catering Menu" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Describe this customization option..."></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price (₱) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" placeholder="0.00" required value="0">
                </div>

                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="is_active" name="is_active" checked style="width: auto;">
                        Active (available for users)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="closeCustomizationModal()">✕ Cancel</button>
                    <button type="submit">✓ Save Customization</button>
                </div>
            </form>
        </div>
    </div>

    <button onclick="openCustomizationModal()" class="btn--fab" title="Add New Customization">
        <i class="fas fa-plus"></i>
    </button>

    <script>
        function openCustomizationModal() {
            document.getElementById('customizationModal').classList.add('active');
            document.body.classList.add('modal-open');
        }

        function closeCustomizationModal() {
            document.getElementById('customizationModal').classList.remove('active');
            document.body.classList.remove('modal-open');
            document.getElementById('customizationForm').reset();
        }

        function confirmDelete(optionId) {
            if (confirm('Are you sure you want to delete this customization option? This action cannot be undone.')) {
                window.location.href = '<?php echo BASE_URL; ?>/index.php?route=admin-customize-delete&id=' + optionId;
            }
        }

        // Format price input
        const priceInput = document.getElementById('price');
        if (priceInput) {
            priceInput.addEventListener('blur', function() {
                this.value = parseFloat(this.value || 0).toFixed(2);
            });
        }
    </script>
</body>
</html>

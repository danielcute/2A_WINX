<?php
// Authentication and $wardrobesByCategory / $allCategories are provided by WardrobeController::index()
$page = 'admin-wardrobe';
$page_title = 'Manage Wardrobes';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Manage Wardrobes - Admin</title>
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
        }

        .btn-add {
            background: #8A7650;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-add:hover {
            background: #6B5A3E;
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

        .category-section {
            margin-bottom: 40px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .category-header {
            background: #f5f0e8;
            padding: 15px 20px;
            border-bottom: 2px solid #ddd;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .category-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: #8A7650;
            font-weight: 600;
        }

        .wardrobe-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
        }

        .wardrobe-table thead {
            background: #f9f7f3;
            border-bottom: 2px solid #ddd;
        }

        .wardrobe-table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #2C2820;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .wardrobe-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .wardrobe-table tbody tr:hover {
            background: #faf8f4;
        }

        /* Mobile action dropdown menu */
        .action-menu-btn {
            background: #8A7650;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            display: none;
            align-items: center;
            gap: 0.3rem;
        }

        .action-menu-btn:hover {
            background: #6B5A3E;
        }

        .action-menu {
            position: relative;
            display: inline-block;
        }

        .action-dropdown {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            padding: 8px 0;
            z-index: 100;
            border-radius: 4px;
            right: 0;
        }

        .action-dropdown.active {
            display: block;
        }

        .action-dropdown a,
        .action-dropdown button {
            color: #333;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background-color 0.2s;
        }

        .action-dropdown a:hover,
        .action-dropdown button:hover {
            background-color: #f0f0f0;
        }

        .action-dropdown a.danger,
        .action-dropdown button.danger {
            color: #dc3545;
        }

        .action-dropdown a.danger:hover,
        .action-dropdown button.danger:hover {
            background-color: #ffe0e0;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .admin-container {
                padding: 15px;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-add {
                width: 100%;
                justify-content: center;
                margin-top: 10px;
            }

            .wardrobe-table {
                font-size: 0.85rem;
            }

            .wardrobe-table td, .wardrobe-table th {
                padding: 8px 10px;
            }

            /* Hide traditional action buttons on tablet/mobile */
            .action-buttons {
                display: none;
            }

            /* Show action menu button on tablet/mobile */
            .action-menu-btn {
                display: flex;
            }

            .wardrobe-desc {
                max-width: 150px;
            }

            .wardrobe-image-cell {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .wardrobe-table {
                font-size: 0.75rem;
                display: block;
                width: 100%;
                overflow-x: visible;
            }

            .wardrobe-table thead {
                display: none;
            }

            .wardrobe-table tbody,
            .wardrobe-table tr,
            .wardrobe-table td {
                display: block;
                width: 100%;
            }

            .wardrobe-table tr {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 10px;
                background: white;
            }

            .wardrobe-table td {
                text-align: right;
                padding: 10px 0;
                position: relative;
                padding-left: 100px;
                min-height: 20px;
            }

            .wardrobe-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                font-weight: 600;
                text-align: left;
                color: #8A7650;
                width: 90px;
            }

            .wardrobe-table .wardrobe-image-cell {
                display: block;
                text-align: center;
                padding-left: 0;
                margin-bottom: 10px;
            }

            .wardrobe-table .wardrobe-image-cell:before {
                display: none;
            }

            .wardrobe-table td[data-label="Actions"] {
                padding-left: 0;
                text-align: center;
            }

            .wardrobe-table td[data-label="Actions"]:before {
                display: none;
            }

            .action-menu-btn {
                width: 100%;
                justify-content: center;
            }

            .action-dropdown {
                right: auto;
                left: 50%;
                transform: translateX(-50%);
                min-width: 180px;
            }
        }

        @media (max-width: 480px) {
            .category-header h2 {
                font-size: 1rem;
            }

            .wardrobe-table tr {
                margin-bottom: 12px;
                padding: 8px;
            }

            .wardrobe-table td {
                padding: 8px 0;
                padding-left: 80px;
            }

            .wardrobe-table td:before {
                width: 75px;
                font-size: 0.75rem;
            }

            .wardrobe-name {
                font-size: 0.9rem;
                font-weight: 700;
            }

            .price-badge {
                font-size: 0.75rem;
            }

            .action-menu-btn {
                padding: 10px;
                font-size: 0.9rem;
            }

            .action-dropdown a,
            .action-dropdown button {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }

        .wardrobe-name {
            font-weight: 600;
            color: #2C2820;
        }

        .wardrobe-desc {
            color: #666;
            font-size: 0.9rem;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .price-badge {
            background: #e8f4f8;
            color: #006085;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #999;
        }

        .empty-state-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .wardrobe-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .wardrobe-image-cell {
            text-align: center;
        }

        .no-image {
            width: 50px;
            height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            border-radius: 4px;
            color: #999;
            font-size: 1.5rem;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ddd;
        }

        .modal-header h2 {
            margin: 0;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.7rem;
            color: #2C2820;
            font-weight: 1000;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: #2C2820;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .modal-form-group {
            display: flex;
            flex-direction: column;
        }

        .modal-form-group label {
            font-weight: 600;
            color: #2C2820;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .modal-form-group input,
        .modal-form-group select,
        .modal-form-group textarea {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px; /* Prevents auto-zoom on mobile devices */
            font-family: inherit;
        }

        .modal-form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .modal-form-group input:focus,
        .modal-form-group select:focus,
        .modal-form-group textarea:focus {
            outline: none;
            border-color: #8A7650;
            box-shadow: 0 0 0 3px rgba(138, 118, 80, 0.1);
        }

        .modal-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .image-preview-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .current-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .new-image-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 5px;
            border: 1px solid #8A7650;
        }

        .modal-form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-btn-primary {
            background: #8A7650;
            color: white;
        }

        .modal-btn-primary:hover {
            background: #6B5A3E;
        }

        .modal-btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .modal-btn-secondary:hover {
            background: #e0e0e0;
        }

        .modal-btn-danger {
            background: #dc3545;
            color: white;
            margin-left: auto;
        }

        .modal-btn-danger:hover {
            background: #c82333;
        }

        .small-text {
            color: #666;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .modal-form-row {
                grid-template-columns: 1fr;
            }

            .modal-form-actions {
                flex-direction: column;
            }

            .modal-btn {
                width: 100%;
                justify-content: center;
            }

            .modal-btn-danger {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php include VIEW_PATH . '/admin/admin-nav.php'; ?>

    <div class="admin-container">
        <div class="header-section">
            <h1>
                <i class="fas fa-tuxedo"></i>
                Manage <em>Wardrobes</em>
            </h1>
            <button onclick="openAddModal()" class="btn-add" style="border: none; cursor: pointer;">
                <i class="fas fa-plus"></i> Add Wardrobe
            </button>
        </div>


        <?php if (!empty($wardrobesByCategory)): ?>
            <?php foreach ($wardrobesByCategory as $category => $wardrobes): ?>
                <div class="category-section">
                    <div class="category-header">
                        <h2><?php echo htmlspecialchars($category); ?></h2>
                        <span style="background: #8A7650; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; margin-left: auto;">
                            <?php echo count($wardrobes); ?> items
                        </span>
                    </div>

                    <table class="wardrobe-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">Image</th>
                                <th style="width: 15%;">Name</th>
                                <th style="width: 15%;">Description</th>
                                <th style="width: 10%;">Rental Price</th>
                                <th style="width: 10%;">Stock</th>
                                <th style="width: 10%;">Duration</th>
                                <th style="width: 10%;">Sizes</th>
                                <th style="width: 4%;">Category</th>
                                <th style="width: 18%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wardrobes as $wardrobe): ?>
                                <tr>
                                    <td class="wardrobe-image-cell" data-label="Image">
                                        <?php if (!empty($wardrobe['has_image']) && !empty($wardrobe['image_type'])): ?>
                                            <img src="<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe-image&id=<?php echo $wardrobe['wardrobe_id']; ?>" 
                                                 alt="<?php echo htmlspecialchars($wardrobe['name']); ?>" 
                                                 class="wardrobe-thumbnail"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="wardrobe-name" data-label="Name"><?php echo htmlspecialchars($wardrobe['name']); ?></td>
                                    <td class="wardrobe-desc" data-label="Description" title="<?php echo htmlspecialchars($wardrobe['description'] ?? ''); ?>">
                                        <?php echo htmlspecialchars(substr($wardrobe['description'] ?? '', 0, 50)); ?>...
                                    </td>
                                    <td data-label="Price">
                                        <span class="price-badge">₱<?php echo number_format($wardrobe['rental_price'], 2); ?></span>
                                    </td>
                                    <td data-label="Stock" style="text-align: center; font-weight: 600; color: #8A7650;">
                                        <?php echo $wardrobe['availability_count']; ?> pcs
                                    </td>
                                    <td data-label="Duration" style="text-align: center;">
                                        <?php echo $wardrobe['rental_duration_days']; ?> day(s)
                                    </td>
                                    <td data-label="Sizes" style="font-size: 0.85rem;">
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                            <?php 
                                                $sizes = array_map('trim', explode(',', $wardrobe['sizes_available'] ?? ''));
                                                foreach ($sizes as $size) {
                                                    echo '<span style="background: #f0f0f0; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">' . htmlspecialchars($size) . '</span>';
                                                }
                                            ?>
                                        </div>
                                    </td>
                                    <td data-label="Category" style="text-align: center; font-size: 1.2rem; color: #8A7650;">
                                        <?php 
                                            $categoryIcons = [
                                                'Gown' => 'fas fa-person-dress',
                                                'Suit' => 'fas fa-person-hiking',
                                                'Shoes' => 'fas fa-shoe-prints',
                                                'Accessories' => 'fas fa-ring',
                                                'Bag' => 'fas fa-bag-shopping',
                                                'Hat' => 'fas fa-hat-wizard',
                                                'Veil' => 'fas fa-fan',
                                                'Cape' => 'fas fa-book',
                                                'Jewelry' => 'fas fa-gem',
                                                'Tie' => 'fas fa-face-smile',
                                                'default' => 'fas fa-shirt'
                                            ];
                                            $icon = $categoryIcons[$wardrobe['category']] ?? $categoryIcons['default'];
                                        ?>
                                        <i class="<?php echo $icon; ?>" title="<?php echo htmlspecialchars($wardrobe['category']); ?>"></i>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="openEditModal(<?php echo $wardrobe['wardrobe_id']; ?>)" title="Edit this wardrobe">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn-delete" onclick="deleteWardrobe(<?php echo $wardrobe['wardrobe_id']; ?>, '<?php echo htmlspecialchars($wardrobe['name']); ?>')" title="Delete this wardrobe">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                        <div class="action-menu">
                                            <button type="button" class="action-menu-btn" onclick="toggleActionMenu(this)" title="More actions">
                                                <i class="fas fa-ellipsis-v"></i> Actions
                                            </button>
                                            <div class="action-dropdown">
                                                <button type="button" onclick="openEditModal(<?php echo $wardrobe['wardrobe_id']; ?>); closeActionDropdown(this)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="danger" onclick="deleteWardrobe(<?php echo $wardrobe['wardrobe_id']; ?>, '<?php echo htmlspecialchars($wardrobe['name']); ?>'); closeActionDropdown(this)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <p>No wardrobes yet. <a href="<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe-add" style="color: #8A7650;">Add your first wardrobe</a></p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Action menu dropdown functions
        function toggleActionMenu(btn) {
            const dropdown = btn.nextElementSibling;
            dropdown.classList.toggle('active');
            
            // Close other dropdowns
            document.querySelectorAll('.action-dropdown.active').forEach(d => {
                if (d !== dropdown) d.classList.remove('active');
            });
            
            event.stopPropagation();
        }

        function closeActionDropdown(element) {
            const dropdown = element.closest('.action-menu').querySelector('.action-dropdown');
            dropdown.classList.remove('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.action-menu')) {
                document.querySelectorAll('.action-dropdown.active').forEach(d => {
                    d.classList.remove('active');
                });
            }
        });

        // Add Wardrobe Modal Functions
        function openAddModal() {
            const modal = document.getElementById('addModal');
            const form = document.getElementById('addWardrobeForm');
            
            // Reset form
            form.innerHTML = '';
            
            // Show loading state briefly then populate form
            setTimeout(() => {
                populateAddForm();
            }, 100);
            
            modal.classList.add('active');
        }

        function populateAddForm() {
            const form = document.getElementById('addWardrobeForm');
            
            form.innerHTML = `
                <div class="modal-form-group">
                    <label for="add_category">Category *</label>
                    <select id="add_category" name="category" required>
                        <option value="">-- Select a category --</option>
                        <option value="Wedding">Wedding</option>
                        <option value="Birthday">Birthday</option>
                        <option value="Corporate Gala">Corporate Gala</option>
                        <option value="Debut">Debut</option>
                        <option value="Anniversary">Anniversary</option>
                        <option value="Other Events">Other Events</option>
                    </select>
                </div>
                
                <div class="modal-form-group">
                    <label for="add_name">Wardrobe Name *</label>
                    <input type="text" id="add_name" name="name" required maxlength="150" placeholder="Enter wardrobe name">
                </div>
                
                <div class="modal-form-group">
                    <label for="add_description">Description</label>
                    <textarea id="add_description" name="description" placeholder="Enter description"></textarea>
                </div>
                
                <div class="modal-form-row">
                    <div class="modal-form-group">
                        <label for="add_rental_price">Rental Price (₱) *</label>
                        <input type="number" id="add_rental_price" name="rental_price" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="modal-form-group">
                        <label for="add_availability_count">Available Stock *</label>
                        <input type="number" id="add_availability_count" name="availability_count" value="1" min="1" required>
                    </div>
                </div>
                
                <div class="modal-form-row">
                    <div class="modal-form-group">
                        <label for="add_rental_duration_days">Rental Duration (Days) *</label>
                        <input type="number" id="add_rental_duration_days" name="rental_duration_days" value="1" min="1" required>
                    </div>
                    <div class="modal-form-group">
                        <label for="add_sizes_available">Available Sizes *</label>
                        <input type="text" id="add_sizes_available" name="sizes_available" required placeholder="e.g., XS, S, M, L, XL">
                        <small class="small-text">Separate sizes with commas. Stock count shown from total available count above.</small>
                    </div>
                </div>
                
                <div class="modal-form-group">
                    <label for="add_wardrobe_image">Wardrobe Image</label>
                    <input type="file" id="add_wardrobe_image" name="wardrobe_image" accept="image/*">
                    <small class="small-text">Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
                    <div id="addImagePreview" style="margin-top: 15px;"></div>
                </div>
                
                <div class="modal-form-actions">
                    <button type="button" class="modal-btn modal-btn-secondary" onclick="closeAddModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="modal-btn modal-btn-primary">
                        <i class="fas fa-plus"></i> Add Wardrobe
                    </button>
                </div>
            `;
            
            // Add event listeners
            document.getElementById('add_wardrobe_image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                const preview = document.getElementById('addImagePreview');
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = '<p style="color: #666; font-size: 0.9rem; margin: 5px 0;">Image Preview:</p><img src="' + e.target.result + '" class="new-image-preview">';
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = '';
                }
            });
            
            form.addEventListener('submit', submitAddForm);
        }

        function submitAddForm(e) {
            e.preventDefault();
            
            const form = document.getElementById('addWardrobeForm');
            const formData = new FormData();
            
            // Add form fields
            formData.append('category', form.querySelector('select[name="category"]').value);
            formData.append('name', form.querySelector('input[name="name"]').value);
            formData.append('description', form.querySelector('textarea[name="description"]').value);
            formData.append('rental_price', form.querySelector('input[name="rental_price"]').value);
            formData.append('availability_count', form.querySelector('input[name="availability_count"]').value);
            formData.append('rental_duration_days', form.querySelector('input[name="rental_duration_days"]').value);
            formData.append('sizes_available', form.querySelector('input[name="sizes_available"]').value);
            
            // Add file if selected
            const imageFile = document.getElementById('add_wardrobe_image').files[0];
            if (imageFile) {
                if (imageFile.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB');
                    return;
                }
                
                if (!imageFile.type.startsWith('image/')) {
                    alert('Please upload a valid image file');
                    return;
                }
                
                formData.append('wardrobe_image', imageFile);
            }
            
            fetch('<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe-add', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error status: ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('Wardrobe added successfully!');
                        closeAddModal();
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to add wardrobe'));
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response text:', text);
                    alert('Error: Invalid response from server. Check console for details.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred while adding the wardrobe: ' + error.message);
            });
        }

        function closeAddModal() {
            const modal = document.getElementById('addModal');
            modal.classList.remove('active');
            document.getElementById('addWardrobeForm').innerHTML = '';
        }

        function deleteWardrobe(wardrobeId, wardrobeName) {
            if (confirm('Are you sure you want to delete "' + wardrobeName + '"?')) {
                const formData = new FormData();
                formData.append('wardrobe_id', wardrobeId);

                fetch('<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe-delete', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Wardrobe deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete wardrobe'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the wardrobe');
                });
            }
        }

        // Modal Functions
        function openEditModal(wardrobeId) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editWardrobeForm');
            
            // Show loading state
            form.innerHTML = '<p style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
            modal.classList.add('active');
            
// Fetch wardrobe data
            console.log('Fetching wardrobe data for ID:', wardrobeId);
            fetch('<?php echo BASE_URL; ?>/public/api-wardrobe.php?action=get&id=' + wardrobeId, {
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok && response.status === 401) {
                        throw new Error('Session expired');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Wardrobe data:', data);
                    if (data.success) {
                        populateEditForm(data.data);
                    } else {
                        form.innerHTML = '<p style="color: red;">Error loading wardrobe data: ' + (data.message || 'Unknown error') + '</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    form.innerHTML = '<p style="color: red;">Error loading wardrobe data: ' + error.message + '</p>';
                });
        }

        function populateEditForm(wardrobe) {
            const form = document.getElementById('editWardrobeForm');
            
            let imagePreview = '';
            if (wardrobe.image && wardrobe.image_type) {
                imagePreview = `
                    <div class="image-preview-container">
                        <p style="color: #666; font-size: 0.9rem; margin: 5px 0;">Current Image:</p>
                        <img src="data:${wardrobe.image_type};base64,${wardrobe.image}" alt="${wardrobe.name}" class="current-image">
                    </div>
                `;
            }
            
            form.innerHTML = `
                <input type="hidden" name="wardrobe_id" value="${wardrobe.wardrobe_id}">
                
                <div class="modal-form-group">
                    <label for="edit_category">Category *</label>
                    <select id="edit_category" name="category" required>
                        <option value="">-- Select a category --</option>
                        <option value="Wedding" ${wardrobe.category === 'Wedding' ? 'selected' : ''}>Wedding</option>
                        <option value="Birthday" ${wardrobe.category === 'Birthday' ? 'selected' : ''}>Birthday</option>
                        <option value="Corporate Gala" ${wardrobe.category === 'Corporate Gala' ? 'selected' : ''}>Corporate Gala</option>
                        <option value="Debut" ${wardrobe.category === 'Debut' ? 'selected' : ''}>Debut</option>
                        <option value="Anniversary" ${wardrobe.category === 'Anniversary' ? 'selected' : ''}>Anniversary</option>
                        <option value="Other Events" ${wardrobe.category === 'Other Events' ? 'selected' : ''}>Other Events</option>
                    </select>
                </div>
                
                <div class="modal-form-group">
                    <label for="edit_name">Wardrobe Name *</label>
                    <input type="text" id="edit_name" name="name" value="${escapeHtml(wardrobe.name)}" required maxlength="150">
                </div>
                
                <div class="modal-form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description">${escapeHtml(wardrobe.description || '')}</textarea>
                </div>
                
                <div class="modal-form-row">
                    <div class="modal-form-group">
                        <label for="edit_rental_price">Rental Price (₱) *</label>
                        <input type="number" id="edit_rental_price" name="rental_price" value="${wardrobe.rental_price}" step="0.01" min="0" required>
                    </div>
                    <div class="modal-form-group">
                        <label for="edit_availability_count">Available Stock *</label>
                        <input type="number" id="edit_availability_count" name="availability_count" value="${wardrobe.availability_count}" min="1" required>
                    </div>
                </div>
                
                <div class="modal-form-row">
                    <div class="modal-form-group">
                        <label for="edit_rental_duration_days">Rental Duration (Days) *</label>
                        <input type="number" id="edit_rental_duration_days" name="rental_duration_days" value="${wardrobe.rental_duration_days}" min="1" required>
                    </div>
                    <div class="modal-form-group">
                        <label for="edit_sizes_available">Available Sizes *</label>
                        <input type="text" id="edit_sizes_available" name="sizes_available" value="${escapeHtml(wardrobe.sizes_available)}" required>
                        <small class="small-text">Separate sizes with commas. Stock count shown from total available count above.</small>
                    </div>
                </div>
                
                <div class="modal-form-group">
                    <label for="edit_wardrobe_image">Wardrobe Image</label>
                    <input type="file" id="edit_wardrobe_image" name="wardrobe_image" accept="image/*">
                    <small class="small-text">Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
                    ${imagePreview}
                    <div id="editImagePreview" style="margin-top: 15px;"></div>
                </div>
                
                <div class="modal-form-actions">
                    <button type="button" class="modal-btn modal-btn-secondary" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="modal-btn modal-btn-danger" onclick="deleteWardrobeModal(${wardrobe.wardrobe_id}, '${escapeHtml(wardrobe.name)}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button type="submit" class="modal-btn modal-btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            `;
            
            // Add event listeners
            document.getElementById('edit_wardrobe_image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                const preview = document.getElementById('editImagePreview');
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = '<p style="color: #666; font-size: 0.9rem; margin: 5px 0;">New Image Preview:</p><img src="' + e.target.result + '" class="new-image-preview">';
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = '';
                }
            });
            
            form.addEventListener('submit', submitEditForm);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
        }

        function submitEditForm(e) {
            e.preventDefault();
            
            const form = document.getElementById('editWardrobeForm');
            const formData = new FormData();
            
            // Get wardrobe ID
            const wardrobeId = form.querySelector('input[name="wardrobe_id"]').value;
            formData.append('wardrobe_id', wardrobeId);
            
            // Add form fields
            formData.append('category', form.querySelector('select[name="category"]').value);
            formData.append('name', form.querySelector('input[name="name"]').value);
            formData.append('description', form.querySelector('textarea[name="description"]').value);
            formData.append('rental_price', form.querySelector('input[name="rental_price"]').value);
            formData.append('availability_count', form.querySelector('input[name="availability_count"]').value);
            formData.append('rental_duration_days', form.querySelector('input[name="rental_duration_days"]').value);
            formData.append('sizes_available', form.querySelector('input[name="sizes_available"]').value);
            
            // Add file if selected
            const imageFile = document.getElementById('edit_wardrobe_image').files[0];
            if (imageFile) {
                if (imageFile.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB');
                    return;
                }
                
                if (!imageFile.type.startsWith('image/')) {
                    alert('Please upload a valid image file');
                    return;
                }
                
                formData.append('wardrobe_image', imageFile);
                console.log('Adding image file:', imageFile.name, imageFile.type, imageFile.size);
            }
            
            console.log('Submitting form data...');
            fetch('<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe-update', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(response => {
                console.log('Response status:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error('HTTP error status: ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('Wardrobe updated successfully!');
                        closeEditModal();
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update wardrobe'));
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response text:', text);
                    alert('Error: Invalid response from server. Check console for details.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred while updating the wardrobe: ' + error.message);
            });
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('active');
            document.getElementById('editWardrobeForm').innerHTML = '';
        }

        function deleteWardrobeModal(wardrobeId, wardrobeName) {
            if (confirm('Are you sure you want to delete "' + wardrobeName + '"? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('wardrobe_id', wardrobeId);

                fetch('<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe-delete', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Wardrobe deleted successfully!');
                        closeEditModal();
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete wardrobe'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the wardrobe');
                });
            }
        }

        // Close modal when clicking outside
        // Ensure DOM is ready before attaching event listeners to modals
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                const editModal = document.getElementById('editModal');
                const addModal = document.getElementById('addModal');
                
                if (editModal) {
                    editModal.addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeEditModal();
                        }
                    });
                }
                
                if (addModal) {
                    addModal.addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeAddModal();
                        }
                    });
                }
            });
        } else {
            const editModal = document.getElementById('editModal');
            const addModal = document.getElementById('addModal');
            
            if (editModal) {
                editModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeEditModal();
                    }
                });
            }
            
            if (addModal) {
                addModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeAddModal();
                    }
                });
            }
        }
    </script>

    <!-- Add Wardrobe Modal -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Add Wardrobe</h2>
                <button type="button" class="modal-close" onclick="closeAddModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addWardrobeForm"></form>
        </div>
    </div>

    <!-- Edit Wardrobe Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Wardrobe</h2>
                <button type="button" class="modal-close" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editWardrobeForm"></form>
        </div>
    </div>
</body>
</html>
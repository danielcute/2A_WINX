<?php
// Check admin authentication
if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin' && empty($_SESSION['admin_logged_in']))) {
    header('Location: ' . BASE_URL . '/index.php?route=signin');
    exit;
}

require_once ROOT_PATH . '/app/models/User.php';
$user = new User();
$admin = $user->findById($_SESSION['user_id']);

require_once ROOT_PATH . '/app/models/Customization.php';
$customizationModel = new Customization();
if (!isset($options) || empty($options)) {
    $options = $customizationModel->getAllOptions();
}

if (!$admin || $admin['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?route=home");
    exit;
}

$page = 'admin-customize';
$page_title = 'Manage Customizations';
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

        @media (max-width: 600px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
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
            background: #e08414;
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
            
            // Helper function to create JSON-safe option data (includes base64 image data)
            function getCleanOptionData($option) {
                $imageData = null;
                $imageType = null;
                
                if (!empty($option['image'])) {
                    $imageData = base64_encode($option['image']);
                    $imageType = $option['image_type'] ?? 'image/jpeg';
                }
                
                return [
                    'option_id' => $option['option_id'],
                    'name' => $option['name'],
                    'description' => $option['description'] ?? '',
                    'price' => $option['price'],
                    'is_active' => $option['is_active'],
                    'category' => $option['category'],
                    'colors_json' => $option['colors_json'] ?? null,
                    'image' => $imageData,
                    'image_type' => $imageType
                ];
            }
            ?>
            
            <!-- VENUE DECORATION SECTION -->
            <?php if (isset($grouped['Theme']) || isset($grouped['Color Combinations']) || isset($grouped['Venue'])): ?>
                <div class="category-section">
                    <h2 class="category-title">
                        <i class="fas fa-home"></i>
                        Venue Decoration
                    </h2>
                    
                    <!-- Theme -->
                    <?php if (isset($grouped['Theme'])): ?>
                        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: #6B5A40; margin: 1.5rem 0 1rem 0;">
                            <i class="fas fa-palette"></i> Theme
                        </h3>
                        <div class="options-grid">
                            <?php foreach ($grouped['Theme'] as $option): ?>
                                <div class="option-card">
                                    <div class="option-image">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image"><i class="fas fa-palette"></i></div>
                                        <?php endif; ?>
                                    </div>
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
                                    <div class="option-actions">
                                        <button onclick="openEditModal(this)" data-option='<?php echo json_encode(getCleanOptionData($option)); ?>' class="btn btn--primary btn--sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Color Combinations -->
                    <?php if (isset($grouped['Color Combinations'])): ?>
                        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: #6B5A40; margin: 1.5rem 0 1rem 0;">
                            <i class="fas fa-droplet"></i> Color Palette
                        </h3>
                        <div class="options-grid">
                            <?php
                            // Sort color combinations: preset/existing first, then organizer choice, then other
                            $colorOptions = $grouped['Color Combinations'];
                            usort($colorOptions, function($a, $b) {
                                $nameA = $a['name'];
                                $nameB = $b['name'];
                                
                                $orderA = 0;
                                $orderB = 0;
                                
                                if ($nameA === 'Organizer Choice') $orderA = 2;
                                elseif ($nameA === 'Other') $orderA = 3;
                                else $orderA = 1;
                                
                                if ($nameB === 'Organizer Choice') $orderB = 2;
                                elseif ($nameB === 'Other') $orderB = 3;
                                else $orderB = 1;
                                
                                return $orderA - $orderB;
                            });
                            ?>
                            <?php foreach ($colorOptions as $option): ?>
                                <div class="option-card">
                                    <div class="option-image">
                                        <?php 
                                        $showColorBars = false;
                                        if (!empty($option['colors_json'])) {
                                            $colors = json_decode($option['colors_json'], true);
                                            if (is_array($colors) && !empty($colors)) {
                                                $showColorBars = true;
                                                echo '<div style="display: flex; width: 100%; height: 100%; gap: 0;">';
                                                foreach ($colors as $color) {
                                                    echo '<div style="flex: 1; background-color: ' . htmlspecialchars($color) . ';"></div>';
                                                }
                                                echo '</div>';
                                            }
                                        }
                                        if (!$showColorBars && !empty($option['image'])): 
                                    ?>
                                            <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                    <?php elseif (!$showColorBars): ?>
                                            <div class="placeholder-image"><i class="fas fa-fill-drip"></i></div>
                                    <?php endif; ?>
                                    </div>
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
                                    <div class="option-actions">
                                        <button onclick="openEditModal(this)" data-option='<?php echo json_encode(getCleanOptionData($option)); ?>' class="btn btn--primary btn--sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Venue -->
                    <?php if (isset($grouped['Venue'])): ?>
                        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: #6B5A40; margin: 1.5rem 0 1rem 0;">
                            <i class="fas fa-map-marker-alt"></i> Venue
                        </h3>
                        <div class="options-grid">
                            <?php foreach ($grouped['Venue'] as $option): ?>
                                <div class="option-card">
                                    <div class="option-image">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image"><i class="fas fa-map-marker-alt"></i></div>
                                        <?php endif; ?>
                                    </div>
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
                                    <div class="option-actions">
                                        <button onclick="openEditModal(this)" data-option='<?php echo json_encode(getCleanOptionData($option)); ?>' class="btn btn--primary btn--sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- FOOD SECTION -->
            <?php if (isset($grouped['Food']) || isset($grouped['Catering']) || isset($grouped['Pastries']) || isset($grouped['Beverages'])): ?>
                <div class="category-section">
                    <h2 class="category-title">
                        <i class="fas fa-utensils"></i>
                        Food & Beverages
                    </h2>
                    
                    <!-- Food -->
                    <?php if (isset($grouped['Food'])): ?>
                        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: #6B5A40; margin: 1.5rem 0 1rem 0;">
                            <i class="fas fa-drumstick-bite"></i> Food Stations
                        </h3>
                        <div class="options-grid">
                            <?php foreach ($grouped['Food'] as $option): ?>
                                <div class="option-card">
                                    <div class="option-image">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image"><i class="fas fa-drumstick-bite"></i></div>
                                        <?php endif; ?>
                                    </div>
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
                                    <div class="option-actions">
                                        <button onclick="openEditModal(this)" data-option='<?php echo json_encode(getCleanOptionData($option)); ?>' class="btn btn--primary btn--sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Sweets -->
                    <?php if (isset($grouped['Sweets'])): ?>
                        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: #6B5A40; margin: 1.5rem 0 1rem 0;">
                            <i class="fas fa-candy-cane"></i> Sweets Station
                        </h3>
                        <div class="options-grid">
                            <?php foreach ($grouped['Sweets'] as $option): ?>
                                <div class="option-card">
                                    <div class="option-image">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image"><i class="fas fa-candy-cane"></i></div>
                                        <?php endif; ?>
                                    </div>
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
                                    <div class="option-actions">
                                        <button onclick="openEditModal(this)" data-option='<?php echo json_encode(getCleanOptionData($option)); ?>' class="btn btn--primary btn--sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Catering -->
                    <?php if (isset($grouped['Catering'])): ?>
                        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: #6B5A40; margin: 1.5rem 0 1rem 0;">
                            <i class="fas fa-utensils"></i> Catering
                        </h3>
                        <div class="options-grid">
                            <?php foreach ($grouped['Catering'] as $option): ?>
                                <div class="option-card">
                                    <div class="option-image">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image"><i class="fas fa-utensils"></i></div>
                                        <?php endif; ?>
                                    </div>
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
                                    <div class="option-actions">
                                        <button onclick="openEditModal(this)" data-option='<?php echo json_encode(getCleanOptionData($option)); ?>' class="btn btn--primary btn--sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Pastries -->
                    <?php if (isset($grouped['Pastries'])): ?>
                        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: #6B5A40; margin: 1.5rem 0 1rem 0;">
                            <i class="fas fa-birthday-cake"></i> Pastries & Cakes
                        </h3>
                        <div class="options-grid">
                            <?php foreach ($grouped['Pastries'] as $option): ?>
                                <div class="option-card">
                                    <div class="option-image">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image"><i class="fas fa-birthday-cake"></i></div>
                                        <?php endif; ?>
                                    </div>
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
                                    <div class="option-actions">
                                        <button onclick="openEditModal(this)" data-option='<?php echo json_encode(getCleanOptionData($option)); ?>' class="btn btn--primary btn--sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Beverages -->
                    <?php if (isset($grouped['Beverages'])): ?>
                        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: #6B5A40; margin: 1.5rem 0 1rem 0;">
                            <i class="fas fa-wine-glass"></i> Beverages
                        </h3>
                        <div class="options-grid">
                            <?php foreach ($grouped['Beverages'] as $option): ?>
                                <div class="option-card">
                                    <div class="option-image">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image"><i class="fas fa-wine-glass"></i></div>
                                        <?php endif; ?>
                                    </div>
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
                                    <div class="option-actions">
                                        <button onclick="openEditModal(this)" data-option='<?php echo json_encode(getCleanOptionData($option)); ?>' class="btn btn--primary btn--sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- ADD-ONS SECTION -->
            <?php 
            $addOns = [];
            if (isset($grouped['Add-ons'])) $addOns = array_merge($addOns, $grouped['Add-ons']);
            if (isset($grouped['Extras'])) $addOns = array_merge($addOns, $grouped['Extras']);
            
            if (!empty($addOns)): 
            ?>
                <div class="category-section">
                    <h2 class="category-title">
                        <i class="fas fa-sparkles"></i>
                        Add-Ons
                    </h2>
                    
                    <div class="options-grid">
                        <?php foreach ($addOns as $option): ?>
                            <div class="option-card">
                                <div class="option-image">
                                    <?php if (!empty($option['image'])): ?>
                                        <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image"><i class="fas fa-sparkles"></i></div>
                                    <?php endif; ?>
                                </div>
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
                                <div class="option-actions">
                                    <button onclick="openEditModal(this)" data-option='<?php echo json_encode(getCleanOptionData($option)); ?>' class="btn btn--primary btn--sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn btn--ghost btn--sm btn-delete-custom">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="fas fa-inbox"></i></div>
                <p>No customization options found</p>
                <button onclick="openCustomizationModal()" class="btn btn--primary btn--sm"><i class="fas fa-plus"></i> Create First Customization</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Modal for Customization Options -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Customization Option</h2>
                <button type="button" class="close-btn" onclick="closeEditModal()">✕</button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="option_id" id="editOptionId">
                <input type="hidden" name="category" id="editOptionCategory">
                
                <div class="form-group">
                    <label for="editOptionName">Option Name *</label>
                    <input type="text" id="editOptionName" name="name" required placeholder="e.g., Garden Venue">
                </div>
                
                <div class="form-group">
                    <label for="editOptionDesc">Description</label>
                    <textarea id="editOptionDesc" name="description" placeholder="Add a detailed description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="editOptionPrice">Price (₱) *</label>
                    <input type="number" id="editOptionPrice" name="price" required step="100" min="0" placeholder="0">
                </div>
                
                <div class="form-group">
                    <label for="editOptionImage">Image</label>
                    <div id="currentImageContainer" style="margin-bottom: 10px; display: none;">
                        <img id="currentImagePreview" style="max-width: 100%; max-height: 200px; border-radius: 8px; border: 2px solid #E2D9C8;">
                        <p style="font-size: 12px; color: #666; margin-top: 5px;">Current image</p>
                    </div>
                    <input type="file" id="editOptionImage" name="image" accept="image/*">
                    <small>Upload a new image to replace (optional)</small>
                </div>

                <!-- Color Palette Images Section -->
                <div id="colorImagesSection" style="display: none; padding: 1rem; background: #f9f7f3; border-radius: 8px; border-left: 4px solid #8A7650; margin: 1rem 0;">
                    <h4 style="margin-top: 0; margin-bottom: 0.5rem; color: #2C2820; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-images"></i> Color Images
                    </h4>
                    <p style="font-size: 0.8rem; color: #666; margin: 0 0 0.75rem;">Upload one image per color</p>
                    <div id="colorImagesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem; padding: 0.75rem; background: white; border-radius: 6px; border: 1px solid #E2D9C8;"></div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="editOptionActive" name="is_active" value="1">
                        Active
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn--ghost" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn--primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toastNotification" style="position: fixed; bottom: 30px; right: 30px; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 16px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 10000; display: none; animation: slideInRight 0.3s ease-out; max-width: 400px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span id="toastIcon">✓</span>
            <span id="toastMessage"></span>
        </div>
    </div>

    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        #toastNotification.error {
            background: linear-gradient(135deg, #f44336 0%, #da190b 100%);
        }

        #toastNotification.success {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        }

        #toastNotification.hide {
            animation: slideOutRight 0.3s ease-out;
        }

        @keyframes cameraPulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.7;
                transform: scale(1.1);
            }
        }

        @keyframes cameraZoom {
            0%, 100% {
                r: 3px;
                opacity: 0.8;
            }
            50% {
                r: 4px;
                opacity: 1;
            }
        }

        @keyframes checkmark {
            0% {
                stroke-dashoffset: 50;
                stroke-dasharray: 50;
                opacity: 0;
                transform: scale(0.5);
            }
            50% {
                opacity: 1;
            }
            100% {
                stroke-dashoffset: 0;
                stroke-dasharray: 50;
                opacity: 1;
                transform: scale(1);
            }
        }

        .animated-camera-icon {
            display: inline-block;
            margin-right: 4px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 1024px) {
            .admin-container {
                padding: 1.2rem;
            }

            .header-section {
                padding-bottom: 1.2rem;
                margin-bottom: 1.5rem;
            }

            .header-section h1 {
                font-size: 1.6rem;
            }

            .options-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 1.2rem;
                margin-bottom: 1.5rem;
            }

            .category-title {
                font-size: 1.5rem;
                margin-bottom: 1.2rem;
            }

            .option-image {
                height: 180px;
            }
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }

            .header-section {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                padding-bottom: 1.2rem;
                margin-bottom: 1.5rem;
                border-bottom: 2px solid #e0e0e0;
            }

            .header-section h1 {
                font-size: 1.3rem;
                gap: 0.75rem;
            }

            .header-section .btn {
                width: 100%;
                justify-content: center;
                padding: 1rem;
                font-size: 0.9rem;
                border-radius: 16px;
            }

            .options-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
                margin-bottom: 1.5rem;
            }

            .category-title {
                font-size: 1.2rem;
                margin-bottom: 1rem;
                padding-bottom: 0.75rem;
            }

            .option-card {
                border-radius: 14px;
            }

            .option-image {
                height: 160px;
                font-size: 2.5rem;
            }

            .option-content {
                padding: 1rem;
            }

            .option-name {
                font-size: 1rem;
            }

            .option-desc {
                font-size: 0.8rem;
            }

            .option-price {
                font-size: 1.1rem;
            }

            .option-actions {
                padding: 0.75rem 1rem;
            }

            .option-actions .btn--primary,
            .option-actions .btn-delete-custom {
                min-width: 100px;
                padding: 0.5rem 1rem;
                font-size: 0.75rem;
            }

            .btn--fab {
                bottom: 1.5rem;
                right: 1.5rem;
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .modal-content {
                padding: 1.5rem;
                width: 90%;
                max-width: 500px;
            }

            .modal-header h2 {
                font-size: 1.3rem;
            }

            .close-btn {
                width: 28px;
                height: 28px;
                font-size: 1.5rem;
            }

            .form-group {
                margin-bottom: 1.2rem;
            }

            .form-group label {
                font-size: 0.9rem;
                margin-bottom: 0.4rem;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                font-size: 0.9rem;
                padding: 0.6rem 0.8rem;
            }
        }

        @media (max-width: 600px) {
            .admin-container {
                padding: 0.75rem;
            }

            .header-section {
                padding-bottom: 1rem;
                margin-bottom: 1.2rem;
                gap: 0.75rem;
            }

            .header-section h1 {
                font-size: 1.1rem;
                gap: 0.5rem;
                margin: 0;
            }

            .header-section .btn {
                padding: 0.9rem 1.2rem;
                font-size: 0.85rem;
                border-radius: 14px;
            }

            .options-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
                margin-bottom: 1.2rem;
            }

            .category-title {
                font-size: 1rem;
                margin-bottom: 0.75rem;
                padding-bottom: 0.5rem;
            }

            .category-title i {
                font-size: 1.3rem;
            }

            .option-card {
                border-radius: 12px;
            }

            .option-image {
                height: 140px;
                font-size: 2rem;
            }

            .option-content {
                padding: 0.75rem;
            }

            .option-name {
                font-size: 0.95rem;
                margin-bottom: 0.4rem;
            }

            .option-desc {
                font-size: 0.75rem;
                margin-bottom: 0.5rem;
            }

            .option-price {
                font-size: 1rem;
                margin-bottom: 0.4rem;
            }

            .status-badge {
                padding: 0.3rem 0.6rem;
                font-size: 0.7rem;
            }

            .option-actions {
                padding: 0.6rem 0.75rem;
                gap: 0.5rem;
            }

            .option-actions .btn--primary,
            .option-actions .btn-delete-custom {
                min-width: 80px;
                padding: 0.45rem 0.8rem;
                font-size: 0.7rem;
            }

            .btn--fab {
                bottom: 1rem;
                right: 1rem;
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }

            .modal {
                z-index: 99998;
            }

            .modal-content {
                padding: 1.2rem;
                width: 95%;
                max-width: 450px;
                z-index: 99999;
            }

            .modal-header {
                margin-bottom: 1rem;
            }

            .modal-header h2 {
                font-size: 1.1rem;
                gap: 0.5rem;
            }

            .modal-header h2 i {
                font-size: 1.2rem;
            }

            .close-btn {
                width: 26px;
                height: 26px;
                font-size: 1.3rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-group label {
                font-size: 0.8rem;
                margin-bottom: 0.3rem;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                font-size: 0.85rem;
                padding: 0.5rem 0.7rem;
                border-radius: 8px;
            }

            .form-group textarea {
                min-height: 80px;
            }

            .category-summary {
                font-size: 0.8rem;
            }
        }
    </style>

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
                        <optgroup label="Venue Decoration">
                            <option value="Theme">Theme</option>
                            <option value="Color Combinations">Color Combinations</option>
                            <option value="Venue">Venue</option>
                        </optgroup>
                        <optgroup label="Food & Beverages">
                            <option value="Food">Food Stations</option>
                            <option value="Sweets">Sweets Station</option>
                            <option value="Catering">Catering</option>
                            <option value="Pastries">Pastries & Cakes</option>
                            <option value="Beverages">Beverages</option>
                        </optgroup>
                        <optgroup label="Add-Ons">
                            <option value="Add-ons">Add-Ons</option>
                        </optgroup>
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
        // Toast Notification Function
        function showNotification(message, type = 'success', duration = 3000) {
            const toast = document.getElementById('toastNotification');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');
            
            toastMessage.textContent = message;
            toast.className = type;
            
            if (type === 'error') {
                toastIcon.textContent = '✕';
            } else {
                toastIcon.textContent = '✓';
            }
            
            toast.style.display = 'flex';
            toast.classList.remove('hide');
            
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 300);
            }, duration);
        }

        function openEditModal(buttonElement) {
            // Get the data from the data-option attribute
            const optionDataString = buttonElement.getAttribute('data-option');
            
            try {
                const optionData = JSON.parse(optionDataString);
                const optionId = optionData.option_id;
                
                document.getElementById('editOptionId').value = optionId || '';
                document.getElementById('editOptionCategory').value = optionData.category || '';
                document.getElementById('editOptionName').value = optionData.name || '';
                document.getElementById('editOptionDesc').value = optionData.description || '';
                document.getElementById('editOptionPrice').value = optionData.price || 0;
                document.getElementById('editOptionActive').checked = optionData.is_active == 1;
                
                // Clear the image preview
                document.getElementById('currentImageContainer').style.display = 'none';
                document.getElementById('editOptionImage').value = '';
                
                // Display the current image if available
                if (optionData.image && optionData.image_type) {
                    const imagePreview = document.getElementById('currentImagePreview');
                    imagePreview.src = `data:${optionData.image_type};base64,${optionData.image}`;
                    document.getElementById('currentImageContainer').style.display = 'block';
                }

                // Handle color palette images for Color Combinations
                const colorImagesSection = document.getElementById('colorImagesSection');
                const container = document.getElementById('colorImagesContainer');
                
                if (optionData.category === 'Color Combinations') {
                    colorImagesSection.style.display = 'block';
                    
                    // Parse colors_json if available
                    if (optionData.colors_json) {
                        try {
                            const colors = JSON.parse(optionData.colors_json);
                            if (Array.isArray(colors)) {
                                renderColorImageInputs(colors, container);
                            } else {
                                container.innerHTML = '<p style="grid-column: 1/-1; color: #999; text-align: center;">Invalid colors format</p>';
                            }
                        } catch (e) {
                            container.innerHTML = '<p style="grid-column: 1/-1; color: #999; text-align: center;">No colors set</p>';
                        }
                    } else {
                        container.innerHTML = '<p style="grid-column: 1/-1; color: #999; text-align: center;">No colors set</p>';
                    }
                } else {
                    colorImagesSection.style.display = 'none';
                    container.innerHTML = '';
                }
                
                document.getElementById('editModal').classList.add('active');
                document.body.classList.add('modal-open');
            } catch(error) {
                console.error('Error parsing option data:', error);
                showNotification('Error loading option data. Please try again.', 'error');
            }
        }

        function renderColorImageInputs(colors, container) {
            container.innerHTML = '';
            colors.forEach((hex, index) => {
                const colorBox = document.createElement('div');
                colorBox.style.cssText = `
                    border: 2px solid #E2D9C8;
                    border-radius: 6px;
                    padding: 0.75rem;
                    text-align: center;
                    background: white;
                    cursor: pointer;
                    transition: all 0.3s ease;
                `;
                colorBox.onmouseover = function() {
                    this.style.boxShadow = '0 2px 8px rgba(138, 118, 80, 0.2)';
                };
                colorBox.onmouseout = function() {
                    this.style.boxShadow = 'none';
                };

                const inputId = `color_image_${index}`;
                colorBox.innerHTML = `
                    <div style="margin-bottom: 0.5rem;">
                        <div class="color-swatch-${index}" style="
                            width: 100%;
                            height: 60px;
                            background-color: ${hex};
                            border-radius: 4px;
                            border: 1px solid rgba(0,0,0,0.1);
                            margin-bottom: 0.4rem;
                        "></div>
                        <p style="margin: 0.3rem 0 0; font-weight: 600; color: #2C2820; font-size: 0.75rem;">${hex}</p>
                    </div>
                    <input type="hidden" name="colors[]" value="${hex}">
                    <input type="file" id="${inputId}" name="color_images[]" accept="image/*" style="display: none;">
                    <button type="button" class="upload-color-btn" data-index="${index}" style="
                        width: 100%;
                        padding: 0.4rem 0.5rem;
                        background: linear-gradient(135deg, #8A7650 0%, #6B5E4A 100%);
                        color: white;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                        font-weight: 600;
                        font-size: 0.7rem;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 32px;
                    "><svg class="animated-camera-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: cameraPulse 2s ease-in-out infinite;">
                        <path d="M14.5 4h-5L7 2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2h-3l-2.5-2z"></path>
                        <circle cx="12" cy="13" r="3" style="animation: cameraZoom 2s ease-in-out infinite;"></circle>
                    </svg></button>
                `;

                container.appendChild(colorBox);

                const uploadBtn = colorBox.querySelector('.upload-color-btn');
                const fileInput = colorBox.querySelector(`#${inputId}`);
                
                uploadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fileInput.click();
                });

                fileInput.addEventListener('change', function(e) {
                    if (e.target.files && e.target.files[0]) {
                        const file = e.target.files[0];
                        const reader = new FileReader();
                        
                        reader.onload = function(event) {
                            const colorSwatch = colorBox.querySelector(`.color-swatch-${index}`);
                            colorSwatch.style.backgroundImage = `url('${event.target.result}')`;
                            colorSwatch.style.backgroundSize = 'cover';
                            colorSwatch.style.backgroundPosition = 'center';
                            uploadBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: checkmark 0.6s ease-out;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                            uploadBtn.style.background = 'linear-gradient(135deg, #50C878 0%, #2d7a3f 100%)';
                        };
                        
                        reader.readAsDataURL(file);
                    }
                });
            });
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
            document.body.classList.remove('modal-open');
            document.getElementById('editForm').reset();
            document.getElementById('currentImageContainer').style.display = 'none';
        }

        // Handle edit form submission
        document.getElementById('editForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const optionId = document.getElementById('editOptionId').value;
            const formData = new FormData(this);
            
            fetch('<?php echo BASE_URL; ?>/index.php?route=admin-customize-update', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Customization option updated successfully!', 'success');
                    closeEditModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification('Error: ' + (data.message || 'Failed to update'), 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        });

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
<?php include 'admin-footer.php'; ?>

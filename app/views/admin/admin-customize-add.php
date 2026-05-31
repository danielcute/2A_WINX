<?php
// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/index.php?route=signin');
    exit;
}

require_once ROOT_PATH . '/app/models/User.php';
$user = new User();
$admin = $user->findById($_SESSION['user_id']);

if (!$admin || $admin['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?route=home");
    exit;
}

// Predefined main categories for SINTA customize
$mainCategories = ['Theme', 'Color Combinations', 'Venue', 'Food', 'Sweets', 'Catering', 'Pastries', 'Beverages', 'Add-ons'];
$categories = $categories ?? $mainCategories;
// Ensure only main categories are shown
$allCategories = array_intersect($mainCategories, array_merge($categories, $mainCategories));
$allCategories = array_values(array_unique($allCategories));
sort($allCategories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customization - Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .form-container {
            max-width: 550px;
            width: 90%;
            max-height: 90vh;
            padding: 2.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow-y: auto;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container h1 {
            margin-top: 0;
            margin-bottom: 2rem;
            color: #2C2820;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-container h1 .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            background: linear-gradient(135deg, #8A7650 0%, #6B5E4A 100%);
            border-radius: 12px;
            color: white;
            font-size: 1.3rem;
            animation: iconBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes iconBounce {
            0% {
                transform: scale(0) rotate(-45deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.1) rotate(5deg);
            }
            100% {
                transform: scale(1) rotate(0);
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2C2820;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid #E2D9C8;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: inherit;
            box-sizing: border-box;
            transition: all 0.3s ease;
            background-color: #fafaf8;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8A7650;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(138, 118, 80, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .checkbox-group input {
            width: auto;
            cursor: pointer;
            accent-color: #8A7650;
        }

        .checkbox-group label {
            margin: 0;
            display: flex;
            align-items: center;
            font-weight: 500;
            color: #2C2820;
        }

        .form-actions {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
            justify-content: center;
            padding-top: 1.5rem;
            border-top: 1px solid #E2D9C8;
        }

        .form-actions button,
        .form-actions a {
            padding: 0.85rem 2.5rem;
            border: none;
            border-radius: 30px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 130px;
        }

        .form-actions button[type="submit"] {
            background: linear-gradient(135deg, #8A7650 0%, #6B5E4A 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(138, 118, 80, 0.3);
        }

        .form-actions button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(138, 118, 80, 0.45);
        }

        .form-actions button[type="submit"]:active {
            transform: translateY(-1px);
        }

        .form-actions .cancel-btn {
            background: #d4ccc0;
            color: #2C2820;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .form-actions .cancel-btn:hover {
            background: #c8bfb4;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .form-actions .cancel-btn:active {
            transform: translateY(0);
        }


        .price-info {
            font-size: 0.8rem;
            color: #8B7B6F;
            margin-top: 0.4rem;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #E2D9C8;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #D4CCC0;
        }
    </style>
</head>
<body>
    <?php include ROOT_PATH . '/app/views/admin/admin-nav.php'; ?>

    <div class="modal-overlay">
        <div class="form-container">
            <h1><span class="icon">+</span> Add New Customization Option</h1>

            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=admin-customize-create" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($allCategories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Option Name *</label>
                <input type="text" id="name" name="name" placeholder="e.g., Gold Decorations, Premium Catering Menu" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Describe this customization option..."></textarea>
                <div class="price-info">Optional: provide details about this option</div>
            </div>

            <div class="form-group">
                <label for="price">Price (₱) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" placeholder="0.00" required value="0">
            </div>

            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <div class="price-info">Upload an image for this customization option (optional)</div>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    <label for="is_active">Active (available for users)</label>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin-customize" class="cancel-btn">✕ Cancel</a>
                <button type="submit">✓ Save Customization</button>
            </div>
        </form>
        </div>
    </div>

    <script>
        // Format price input
        const priceInput = document.getElementById('price');
        if (priceInput) {
            priceInput.addEventListener('blur', function() {
                this.value = parseFloat(this.value || 0).toFixed(2);
            });
        }

        // Prevent closing modal on overlay click during form edit
        const modalOverlay = document.querySelector('.modal-overlay');
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                // Do nothing - user must click Cancel button
            }
        });
    </script>
</body>
</html>

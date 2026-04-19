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

// Predefined categories
$defaultCategories = ['Decorations', 'Catering', 'Entertainment', 'Photography', 'Venue Setup', 'Lighting', 'Sound System', 'Floral Arrangements', 'Seating', 'Other'];
$allCategories = array_merge($categories ?? [], $defaultCategories);
$allCategories = array_unique($allCategories);
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
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-container h1 {
            margin-top: 0;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
        }

        .checkbox-group input {
            width: auto;
            margin-right: 10px;
        }

        .checkbox-group label {
            margin: 0;
            display: flex;
            align-items: center;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn-submit {
            flex: 1;
            background: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }

        .btn-submit:hover {
            background: #45a049;
        }

        .btn-cancel {
            flex: 1;
            background: #999;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
        }

        .btn-cancel:hover {
            background: #777;
        }

        .price-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include ROOT_PATH . '/app/views/admin/admin-nav.php'; ?>

    <div class="form-container">
        <h1>➕ Add New Customization Option</h1>

        <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=admin-customize-create">
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">-- Select or Type Category --</option>
                    <?php foreach ($allCategories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="price-info">Select an existing category or type a new one</div>
            </div>

            <div class="form-group">
                <label for="name">Option Name *</label>
                <input type="text" id="name" name="name" placeholder="e.g., Gold Decorations, Premium Catering Menu" required>
                <div class="price-info">Descriptive name for this customization option</div>
            </div>

            <div class="form-group">
                <label for="price">Price (₱) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" placeholder="0.00" required value="0">
                <div class="price-info">Price in Philippine Pesos</div>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    <label for="is_active">Active (available for users)</label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">✓ Create Customization</button>
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin-customize" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        // Allow custom category input
        const categorySelect = document.getElementById('category');
        
        categorySelect.addEventListener('change', function() {
            if (this.value === '' && this.options[0].value === '') {
                const customValue = prompt('Enter new category name:');
                if (customValue) {
                    this.value = customValue;
                }
            }
        });

        // Format price input
        const priceInput = document.getElementById('price');
        priceInput.addEventListener('blur', function() {
            this.value = parseFloat(this.value || 0).toFixed(2);
        });
    </script>
</body>
</html>

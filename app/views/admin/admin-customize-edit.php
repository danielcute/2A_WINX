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

// Set page info for navigation
$page = 'admin-customize';
$page_title = 'Edit Customization';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customization - Admin</title>
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
            margin-bottom: 0.5rem;
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
            background: linear-gradient(135deg, #8a6750 0%, #6B5E4A 100%);
            border-radius: 12px;
            color: white;
            font-size: 1.3rem;
            animation: iconSpin 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes iconSpin {
            0% {
                transform: scale(0) rotateZ(-45deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.1) rotateZ(10deg);
            }
            100% {
                transform: scale(1) rotateZ(0);
            }
        }

        .option-id {
            font-size: 0.75rem;
            color: #8B7B6F;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
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
            <h1><span class="icon">✏️</span> Edit Customization Option</h1>
            <div class="option-id">Option ID: #<?php echo $option['option_id']; ?></div>

            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=admin-customize-update" enctype="multipart/form-data">
                <input type="hidden" name="option_id" value="<?php echo $option['option_id']; ?>">

            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($allCategories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                            <?php echo ($cat === $option['category']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Option Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($option['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Describe this customization option..."><?php echo htmlspecialchars($option['description'] ?? ''); ?></textarea>
                <div class="price-info">Provide details about this option</div>
            </div>

            <div class="form-group">
                <label for="price">Price (₱) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo number_format($option['price'], 2, '.', ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="image">Image</label>
                <?php if (!empty($option['image'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="data:<?php echo htmlspecialchars($option['image_type'] ?? 'image/jpeg'); ?>;base64,<?php echo base64_encode($option['image']); ?>" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        <p style="font-size: 12px; color: #666; margin-top: 5px;">Current image</p>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <div class="price-info">Upload a new image to replace (optional)</div>
            </div>

            <!-- Colors Field for Color Combinations -->
            <?php if ($option['category'] === 'Color Combinations'): ?>
                <div style="padding: 1.5rem; background: #f9f7f3; border-radius: 12px; border-left: 4px solid #8A7650; margin: 1.5rem 0;">
                    <h3 style="margin-top: 0; margin-bottom: 1rem; color: #2C2820; font-size: 1.1rem; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-palette"></i> Color Palette Images
                    </h3>
                    
                    <div class="form-group">
                        <label for="colors_json" style="color: #2C2820; font-weight: 700;">Color Palette (JSON)</label>
                        <p style="font-size: 0.85rem; color: #666; margin: 0.5rem 0 1rem;">Enter colors as a JSON array of hex codes. Upload an image for each color below.</p>
                        <textarea id="colors_json" name="colors_json" placeholder='["#B76E79", "#FFC0CB", "#FFD700"]' style="font-family: monospace; font-size: 0.85rem; min-height: 100px; border: 2px solid #E2D9C8; background: white;">
<?php 
if (!empty($option['colors_json'])) {
    $colors = json_decode($option['colors_json'], true);
    if (is_array($colors)) {
        echo json_encode($colors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
?>
                        </textarea>
                        <div class="price-info" style="margin-top: 0.75rem;">Example: ["#B76E79", "#FFC0CB", "#FFD700", "#006994", "#50C878"]</div>
                    </div>

                    <!-- Color Images Gallery -->
                    <div class="form-group">
                        <label style="color: #2C2820; font-weight: 700; margin-bottom: 1rem; display: block;">
                            <i class="fas fa-images"></i> Color Images (Upload one image per color)
                        </label>
                        <div id="colorImagesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; padding: 1rem; background: white; border-radius: 8px; border: 1px solid #E2D9C8;">
                            <!-- Color images will be added here -->
                        </div>
                    </div>

                    <!-- Visual Color Preview -->
                    <div class="form-group">
                        <label style="color: #2C2820; font-weight: 700; margin-bottom: 0.75rem; display: block;">
                            <i class="fas fa-eye"></i> Current Color Preview
                        </label>
                        <div id="currentColorPreview" style="display: flex; gap: 0; border-radius: 8px; overflow: hidden; height: 80px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: white;">
                            <?php 
                            if (!empty($option['colors_json'])) {
                                $colors = json_decode($option['colors_json'], true);
                                if (is_array($colors) && !empty($colors)) {
                                    foreach ($colors as $color) {
                                        echo '<div style="flex: 1; background-color: ' . htmlspecialchars($color) . '; border-right: 1px solid rgba(255,255,255,0.5); position: relative;" title="' . htmlspecialchars($color) . '"></div>';
                                    }
                                } else {
                                    echo '<div style="flex: 1; display: flex; align-items: center; justify-content: center; color: #999; background: #f5f0e8;">No colors set</div>';
                                }
                            } else {
                                echo '<div style="flex: 1; display: flex; align-items: center; justify-content: center; color: #999; background: #f5f0e8;">No colors set</div>';
                            }
                            ?>
                        </div>
                        <p style="font-size: 0.75rem; color: #999; margin-top: 0.5rem;">This is how the colors will appear to users</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_active" name="is_active" <?php echo $option['is_active'] ? 'checked' : ''; ?>>
                    <label for="is_active">Active (available for users)</label>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin-customize" class="cancel-btn">✕ Cancel</a>
                <button type="submit">✓ Save Changes</button>
            </div>
            </form>
        </div>
    </div>

    <script>
        // Format price input
        const priceInput = document.getElementById('price');
        priceInput.addEventListener('blur', function() {
            this.value = parseFloat(this.value || 0).toFixed(2);
        });

        // Handle color images for Color Combinations
        const colorsJsonField = document.getElementById('colors_json');
        const container = document.getElementById('colorImagesContainer');
        
        if (container && colorsJsonField) {
            // Render color image inputs
            function renderColorImages() {
                if (!colorsJsonField.value.trim()) {
                    container.innerHTML = '<p style="grid-column: 1/-1; color: #999; text-align: center; padding: 2rem;">Enter colors in JSON format above to add images</p>';
                    return;
                }

                try {
                    const colors = JSON.parse(colorsJsonField.value);
                    container.innerHTML = '';

                    colors.forEach((hex, index) => {
                        const colorBox = document.createElement('div');
                        colorBox.style.cssText = `
                            border: 2px solid #E2D9C8;
                            border-radius: 8px;
                            padding: 1rem;
                            text-align: center;
                            background: white;
                            transition: all 0.3s ease;
                            cursor: pointer;
                        `;
                        colorBox.onmouseover = function() {
                            this.style.boxShadow = '0 4px 12px rgba(138, 118, 80, 0.2)';
                        };
                        colorBox.onmouseout = function() {
                            this.style.boxShadow = 'none';
                        };

                        const inputId = `color_image_${index}`;

                        colorBox.innerHTML = `
                            <div style="margin-bottom: 0.75rem;">
                                <div class="color-swatch-${index}" style="
                                    width: 100%;
                                    height: 80px;
                                    background-color: ${hex};
                                    border-radius: 6px;
                                    margin-bottom: 0.5rem;
                                    border: 2px solid rgba(0,0,0,0.1);
                                "></div>
                                <p style="margin: 0.5rem 0 0; font-weight: 600; color: #2C2820; font-size: 0.9rem;">${hex}</p>
                            </div>
                            <input type="hidden" name="colors[]" value="${hex}">
                            <input type="file" id="${inputId}" name="color_images[]" accept="image/*" style="
                                display: none;
                            ">
                            <button type="button" class="upload-color-btn" data-index="${index}" style="
                                width: 100%;
                                padding: 0.6rem;
                                background: linear-gradient(135deg, #8A7650 0%, #6B5E4A 100%);
                                color: white;
                                border: none;
                                border-radius: 6px;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 0.85rem;
                                transition: all 0.3s ease;
                            ">📸 Upload Image</button>
                        `;

                        container.appendChild(colorBox);

                        // Add event listener to upload button
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
                                    uploadBtn.textContent = '✓ Image Uploaded';
                                    uploadBtn.style.background = 'linear-gradient(135deg, #50C878 0%, #2d7a3f 100%)';
                                };
                                
                                reader.readAsDataURL(file);
                            }
                        });
                    });
                } catch (e) {
                    container.innerHTML = '<p style="grid-column: 1/-1; color: #d32f2f; text-align: center; padding: 2rem;">Invalid JSON format</p>';
                }
            }

            // Initial render
            renderColorImages();

            // Re-render when colors JSON changes
            colorsJsonField.addEventListener('change', function() {
                renderColorImages();
                updateColorPreview();
            });
            colorsJsonField.addEventListener('blur', function() {
                try {
                    const parsed = JSON.parse(this.value);
                    if (!Array.isArray(parsed)) {
                        alert('Colors must be a JSON array of hex codes. Example: ["#B76E79", "#FFC0CB"]');
                        return;
                    }
                    this.value = JSON.stringify(parsed, null, 2);
                    renderColorImages();
                    updateColorPreview();
                } catch (e) {
                    if (this.value.trim()) {
                        alert('Invalid JSON format. Please check your input.\n\nExample: ["#B76E79", "#FFC0CB", "#FFD700"]');
                    }
                }
            });
            colorsJsonField.addEventListener('keyup', function() {
                updateColorPreview();
            });
        }

        function updateColorPreview() {
            const textarea = document.getElementById('colors_json');
            const preview = document.getElementById('currentColorPreview');
            
            if (!preview || !textarea || !textarea.value.trim()) return;

            try {
                const colors = JSON.parse(textarea.value);
                if (!Array.isArray(colors)) return;

                preview.innerHTML = '';
                if (colors.length === 0) {
                    preview.innerHTML = '<div style="flex: 1; display: flex; align-items: center; justify-content: center; color: #999; background: #f5f0e8;">No colors set</div>';
                } else {
                    colors.forEach(color => {
                        const div = document.createElement('div');
                        div.style.cssText = `flex: 1; background-color: ${color}; border-right: 1px solid rgba(255,255,255,0.5); position: relative;`;
                        div.title = color;
                        preview.appendChild(div);
                    });
                }
            } catch (e) {
                // Invalid JSON, just skip the preview
            }
        }

        // Validate form on submit
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const colorsJsonField = document.getElementById('colors_json');
                if (colorsJsonField && colorsJsonField.value.trim()) {
                    try {
                        JSON.parse(colorsJsonField.value);
                    } catch (err) {
                        e.preventDefault();
                        alert('Invalid JSON in colors field. Please fix the format.');
                        return false;
                    }
                }
            });
        }
    </script>
<?php include ROOT_PATH . '/app/views/admin/admin-footer.php'; ?>
</body>
</html>

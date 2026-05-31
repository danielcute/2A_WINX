<?php
// Authentication and $allCategories are provided by WardrobeController::addForm()
$page = 'admin-wardrobe-add';
$page_title = 'Add New Wardrobe';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Add Wardrobe - Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-section {
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

        .back-link {
            color: #8A7650;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2C2820;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8A7650;
            box-shadow: 0 0 0 3px rgba(138, 118, 80, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #8A7650;
            color: white;
        }

        .btn-primary:hover {
            background: #6B5A3E;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .form-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 25px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .required {
            color: #dc3545;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .admin-container {
                padding: 15px;
            }

            .header-section h1 {
                font-size: 1.5rem;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 12px;
                font-size: 16px;
            }

            .form-card {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .admin-container {
                padding: 10px;
            }

            .header-section {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }

            .header-section h1 {
                font-size: 1.25rem;
                gap: 0.5rem;
            }

            .header-section h1 i {
                font-size: 1.25rem;
            }

            .back-link {
                font-size: 0.85rem;
            }

            .form-group label {
                font-size: 0.9rem;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 16px;
            }

            #imagePreview img {
                max-width: 150px;
                max-height: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include VIEW_PATH . '/admin/admin-nav.php'; ?>

    <div class="admin-container">
        <div class="header-section">
            <h1>
                <i class="fas fa-plus"></i>
                Add New <em>Wardrobe</em>
            </h1>
            <a href="<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Wardrobes
            </a>
        </div>

        <div class="form-card">
            <form id="addWardrobeForm">
                <div class="form-group">
                    <label for="category">
                        Category <span class="required">*</span>
                    </label>
                    <select id="category" name="category" required>
                        <option value="">-- Select a category --</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>">
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">
                        Wardrobe Name <span class="required">*</span>
                    </label>
                    <input type="text" id="name" name="name" placeholder="e.g., Classic Bride Gown" required maxlength="150">
                </div>

                <div class="form-group">
                    <label for="description">
                        Description
                    </label>
                    <textarea id="description" name="description" placeholder="Describe this wardrobe item..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rental_price">
                            Rental Price (₱) <span class="required">*</span>
                        </label>
                        <input type="number" id="rental_price" name="rental_price" placeholder="0.00" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="availability_count">
                            Available Stock <span class="required">*</span>
                        </label>
                        <input type="number" id="availability_count" name="availability_count" placeholder="1" min="1" value="1" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rental_duration_days">
                            Rental Duration (Days) <span class="required">*</span>
                        </label>
                        <input type="number" id="rental_duration_days" name="rental_duration_days" placeholder="1" min="1" value="1" required>
                    </div>

                    <div class="form-group">
                        <label for="sizes_available">
                            Available Sizes <span class="required">*</span>
                        </label>
                        <input type="text" id="sizes_available" name="sizes_available" placeholder="e.g., XS,S,M,L,XL" value="Standard" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="wardrobe_image">
                        Wardrobe Image
                    </label>
                    <input type="file" id="wardrobe_image" name="wardrobe_image" accept="image/*">
                    <small style="color: #666; display: block; margin-top: 5px;">Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
                    <div id="imagePreview" style="margin-top: 15px;"></div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Wardrobe
                    </button>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Image preview
        document.getElementById('wardrobe_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #ddd;">';
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        document.getElementById('addWardrobeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const imageFile = document.getElementById('wardrobe_image').files[0];
            
            // Validate image if provided
            if (imageFile) {
                if (imageFile.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB');
                    return;
                }
                
                if (!imageFile.type.startsWith('image/')) {
                    alert('Please upload a valid image file');
                    return;
                }
            }

            fetch('<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe-add', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Wardrobe added successfully!');
                    window.location.href = '<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe';
                } else {
                    alert('Error: ' + (data.message || 'Failed to add wardrobe'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the wardrobe');
            });
        });
    </script>
</body>
</html>
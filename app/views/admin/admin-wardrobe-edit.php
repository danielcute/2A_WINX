<?php
// Start session and check admin authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$page = 'admin-wardrobe-edit';
$page_title = 'Edit Wardrobe';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Edit Wardrobe - Admin</title>
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

        .btn-danger {
            background: #dc3545;
            color: white;
            margin-left: auto;
        }

        .btn-danger:hover {
            background: #c82333;
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

            .btn-danger {
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
                <i class="fas fa-edit"></i>
                Edit <em><?php echo htmlspecialchars($wardrobe['name']); ?></em>
            </h1>
            <a href="<?php echo APP_URL; ?>/admin-wardrobe" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Wardrobes
            </a>
        </div>

        <div class="form-card">
            <form id="editWardrobeForm">
                <input type="hidden" name="wardrobe_id" value="<?php echo $wardrobe['wardrobe_id']; ?>">

                <div class="form-group">
                    <label for="category">
                        Category <span class="required">*</span>
                    </label>
                    <select id="category" name="category" required>
                        <option value="">-- Select a category --</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($cat === $wardrobe['category']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">
                        Wardrobe Name <span class="required">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($wardrobe['name']); ?>" placeholder="e.g., Classic Bride Gown" required maxlength="150">
                </div>

                <div class="form-group">
                    <label for="description">
                        Description
                    </label>
                    <textarea id="description" name="description" placeholder="Describe this wardrobe item..."><?php echo htmlspecialchars($wardrobe['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rental_price">
                            Rental Price (₱) <span class="required">*</span>
                        </label>
                        <input type="number" id="rental_price" name="rental_price" value="<?php echo $wardrobe['rental_price']; ?>" placeholder="0.00" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="availability_count">
                            Available Stock <span class="required">*</span>
                        </label>
                        <input type="number" id="availability_count" name="availability_count" value="<?php echo $wardrobe['availability_count']; ?>" min="1" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rental_duration_days">
                            Rental Duration (Days) <span class="required">*</span>
                        </label>
                        <input type="number" id="rental_duration_days" name="rental_duration_days" value="<?php echo $wardrobe['rental_duration_days']; ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="sizes_available">
                            Available Sizes <span class="required">*</span>
                        </label>
                        <input type="text" id="sizes_available" name="sizes_available" value="<?php echo htmlspecialchars($wardrobe['sizes_available']); ?>" placeholder="e.g., XS,S,M,L,XL" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="wardrobe_image">
                        Wardrobe Image
                    </label>
                    <input type="file" id="wardrobe_image" name="wardrobe_image" accept="image/*">
                    <small style="color: #666; display: block; margin-top: 5px;">Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
                    <?php if (!empty($wardrobe['image'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="color: #666; font-size: 0.9rem; margin: 5px 0;">Current Image:</p>
                            <img src="data:<?php echo htmlspecialchars($wardrobe['image_type']); ?>;base64,<?php echo base64_encode($wardrobe['image']); ?>" 
                                 style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #ddd;">
                        </div>
                    <?php endif; ?>
                    <div id="imagePreview" style="margin-top: 15px;"></div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Wardrobe
                    </button>
                    <a href="<?php echo APP_URL; ?>/admin-wardrobe" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="button" class="btn btn-danger" onclick="deleteWardrobe()">
                        <i class="fas fa-trash"></i> Delete
                    </button>
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
                    preview.innerHTML = '<p style="color: #666; font-size: 0.9rem; margin: 5px 0;">New Image Preview:</p><img src="' + e.target.result + '" style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #ddd;">';
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        document.getElementById('editWardrobeForm').addEventListener('submit', function(e) {
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

            console.log('Submitting wardrobe update:', { wardrobeId: formData.get('wardrobe_id'), hasImage: !!imageFile });

            fetch('<?php echo APP_URL; ?>/admin-wardrobe-update', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Wardrobe update response status:', response.status);
                if (!response.ok && response.status === 401) {
                    alert('Session expired. Please login again.');
                    window.location.href = '<?php echo APP_URL; ?>/index.php?route=signin';
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
                console.log('Wardrobe update response:', data);
                if (data.success) {
                    alert('Wardrobe updated successfully!');
                    window.location.href = '<?php echo APP_URL; ?>/admin-wardrobe';
                } else {
                    alert('Error: ' + (data.message || 'Failed to update wardrobe'));
                }
            })
            .catch(error => {
                console.error('Wardrobe update error:', error);
                alert('An error occurred while updating the wardrobe');
            });
        });

        function deleteWardrobe() {
            if (confirm('Are you sure you want to delete this wardrobe? This action cannot be undone.')) {
                const wardrobeId = document.querySelector('input[name="wardrobe_id"]').value;
                const formData = new FormData();
                formData.append('wardrobe_id', wardrobeId);

                console.log('Deleting wardrobe:', wardrobeId);

                fetch('<?php echo APP_URL; ?>/admin-wardrobe-delete', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('Delete response status:', response.status);
                    if (!response.ok && response.status === 401) {
                        alert('Session expired. Please login again.');\n                        window.location.href = '<?php echo APP_URL; ?>/index.php?route=signin';
                        return null;
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data) return;
                    console.log('Delete response:', data);
                    if (data.success) {
                        alert('Wardrobe deleted successfully!');\n                        window.location.href = '<?php echo APP_URL; ?>/admin-wardrobe';
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete wardrobe'));\n                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    alert('An error occurred while deleting the wardrobe');\n                });
            }
        }
    </script>
</body>
</html>

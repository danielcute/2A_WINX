<?php
// Authentication is already checked by index.php
// Set page title for use in admin-nav.php
$page = 'admin-occasions';
$page_title = 'Occasions Management';

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/controllers/OccasionController.php';
require_once ROOT_PATH . '/app/models/Customization.php';

$controller = new OccasionController();
$occasions = $controller->getAll();
$customizationModel = new Customization();
$allCustomizationOptions = $customizationModel->getAllOptions();

// Filter to only main categories
$mainCategories = ['Theme', 'Venue', 'Catering', 'Extras'];
$customizationOptions = array_filter($allCustomizationOptions, function($opt) use ($mainCategories) {
    return in_array($opt['category'], $mainCategories);
});
$customizationOptions = array_values($customizationOptions);

$categoryCounts = [];
foreach ($customizationOptions as $option) {
    $categoryCounts[$option['category']] = ($categoryCounts[$option['category']] ?? 0) + 1;
}

$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Occasions Management | Sinta Admin</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .occasions-container {
            padding: 2rem 0;
            max-width: 1300px;
            margin: 0 auto;
            width: 100%;
        }

        .occasions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .occasions-header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.9rem;
            color: #2C2820;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 700;
            letter-spacing: -0.03em;
        }
        .occasions-header h1 em {
            color: #8A7650;
            font-style: italic;
            font-weight: 400;
        }


        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.12);
            border-left: 4px solid #4caf50;
            color: #2e7d32;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.12);
            border-left: 4px solid #f44336;
            color: #c62828;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .category-summary {
            margin-bottom: 1.75rem;
            background: #fff9f1;
            border: 1px solid #f1dfca;
            border-radius: 18px;
            padding: 1.25rem 1.5rem;
        }

        .category-summary h3 {
            margin: 0 0 1rem;
            color: #8A7650;
            font-size: 1.1rem;
            letter-spacing: 0.02em;
            font-weight: 600;
        }

        .category-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .category-card {
            background: white;
            border: 1px solid #E2D9C8;
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .category-card__title {
            font-size: 0.95rem;
            color: #2C2820;
            font-weight: 700;
        }

        .category-card__count {
            font-size: 1.2rem;
            color: #8A7650;
            font-weight: 700;
        }

        .category-summary-note {
            margin: 0;
            color: #6B6463;
            font-size: 0.9rem;
        }

        .category-summary-note a {
            color: #8A7650;
            text-decoration: none;
            font-weight: 600;
        }

        .category-summary-note a:hover {
            text-decoration: underline;
        }

        /* Occasions Grid */
        .occasions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .occasion-card {
            background: white;
            border: 2px solid #E2D9C8;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .occasion-card:hover {
            border-color: #8A7650;
            box-shadow: 0 15px 40px rgba(138, 118, 80, 0.2);
            transform: translateY(-8px);
        }

        .occasion-image-container {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #E2D9C8 0%, #D4C7B1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8A7650;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }

        .occasion-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .occasion-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem 1.5rem 0 1.5rem;
        }

        .occasion-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(138, 118, 80, 0.15) 0%, rgba(138, 118, 80, 0.08) 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8A7650;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .occasion-info h3 {
            font-size: 1.2rem;
            color: #2C2820;
            margin: 0 0 0.25rem 0;
            font-weight: 600;
        }

        .occasion-info p {
            color: #8B7355;
            margin: 0;
            font-size: 0.8rem;
        }

        .occasion-description {
            color: #6B6463;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .occasion-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            padding: 1rem;
            background: #F5F0E8;
            border-radius: 15px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item strong {
            display: block;
            font-size: 1.3rem;
            color: #8A7650;
            margin-bottom: 0.25rem;
        }

        .stat-item small {
            color: #8B7355;
            font-size: 0.75rem;
        }

        .occasion-actions {
            display: flex;
            gap: 0.75rem;
        }

        .occasion-actions .btn {
            flex: 1;
            min-width: 130px;
        }

        .btn-animation {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
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

        .animated-icon {
            display: inline-flex;
            color: #8A7650;
            animation: pulse 1.4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #E2D9C8;
        }

        .modal-header h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            color: #2C2820;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #8B7355;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            color: #2C2820;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2C2820;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #E2D9C8;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8A7650;
            box-shadow: 0 0 0 3px rgba(138, 118, 80, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #E2D9C8;
        }

        .btn-secondary {
            background: #E2D9C8;
            color: #2C2820;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border: 2px dashed #E2D9C8;
            border-radius: 20px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #E2D9C8;
            display: block;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #8B7355;
            margin: 0;
        }
    </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

    <div class="admin-content-wrapper">
    <div class="occasions-container">
    <!-- Header -->
    <div class="admin-page-header occasions-header">
        <h1 class="admin-page-title"><i class="fas fa-calendar-day animated-icon"></i> Manage Occasions</h1>
        <button class="btn btn--primary btn--sm" id="addOccasionBtn">
            <i class="fas fa-plus"></i> Add Occasion
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($success_message) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($error_message) ?></span>
        </div>
    <?php endif; ?>

    <!-- Customization Categories Overview -->
    <?php if (!empty($categoryCounts)): ?>
        <div class="category-summary">
            <h3>Customization categories in database</h3>
            <div class="category-summary-grid">
                <?php foreach ($categoryCounts as $category => $count): ?>
                    <div class="category-card">
                        <div class="category-card__title"><?= htmlspecialchars($category) ?></div>
                        <div class="category-card__count"><?= $count ?> option<?= $count !== 1 ? 's' : '' ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="category-summary-note">These categories are available for the Customize page and can be managed in <a href="<?= BASE_URL ?>/index.php?route=admin-customize">Customize management</a>.</p>
        </div>
    <?php endif; ?>

    <!-- Occasions Grid -->
    <?php if (!empty($occasions)): ?>
        <div class="occasions-grid">
            <?php foreach ($occasions as $occasion): ?>
                <div class="occasion-card">
                    <!-- Image Container -->
                    <div class="occasion-image-container">
                        <?php if (isset($occasion['image']) && $occasion['image']): ?>
                            <img id="occImg_<?= $occasion['occasion_id'] ?>" alt="<?= htmlspecialchars($occasion['events']) ?>" style="display: none;">
                            <script>
fetch('<?= BASE_URL ?>/api-occasion.php?image=<?= $occasion['occasion_id'] ?>')
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success && data.image) {
                                            const img = document.getElementById('occImg_<?= $occasion['occasion_id'] ?>');
                                            img.src = data.image;
                                            img.style.display = 'block';
                                        }
                                    })
        .catch(err => console.error('Error loading image:', err));
                            </script>
                            <i class="fas fa-image" style="display:block;"></i>
                        <?php else: ?>
                            <i class="fas fa-calendar-alt"></i>
                        <?php endif; ?>
                    </div>


                    <!-- Content -->
                    <div style="padding: 1.5rem 1.5rem 0 1.5rem;">
                        <div class="occasion-header">
                            <div class="occasion-info">
                                <h3><?= htmlspecialchars($occasion['events'] ?? '') ?></h3>
                                <p>Occasion</p>
                            </div>
                        </div>

                        <p class="occasion-description" style="margin: 1rem 0; color: #6B6463; font-size: 0.9rem; line-height: 1.5;">
                            <?= htmlspecialchars($occasion['descriptions'] ?? 'No description provided') ?>
                        </p>

                        <div class="occasion-stats" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; padding: 1rem; background: #F5F0E8; border-radius: 15px; margin-top: 1rem;">
                            <div class="stat-item" style="text-align: center; grid-column: span 2;">
                                <strong style="display: block; font-size: 1.3rem; color: #8A7650; margin-bottom: 0.25rem;"><?= $occasion['packages_count'] ?? 0 ?></strong>
                                <small style="color: #8B7355; font-size: 0.75rem;">Associated Packages</small>
                            </div>
                        </div>

                        <div class="occasion-actions" style="margin-top: 1rem; padding-bottom: 1.5rem;">
                            <button class="btn btn--primary btn--sm btn-animation" onclick="editOccasion(<?= $occasion['occasion_id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn--ghost btn--sm btn-animation btn-delete-custom" onclick="deleteOccasion(<?= $occasion['occasion_id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>No occasions found. Create your first occasion!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="occasionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Occasion</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="occasionForm">
            <input type="hidden" id="occasionId" name="occasion_id">
            
            <div class="form-group">
                <label for="occasionName">Occasion Name *</label>
                <input type="text" id="occasionName" name="events" placeholder="e.g., Wedding, Birthday, Corporate" required>
            </div>

            <div class="form-group">
                <label for="occasionDescription">Description</label>
                <textarea id="occasionDescription" name="descriptions" placeholder="Describe this occasion type..."></textarea>
            </div>

            <div class="form-group">
                <label for="occasionImage">Image</label>
                <input type="file" id="occasionImage" name="image" accept="image/*">
                <small>Optional: Upload an image for this occasion</small>
                <div id="imagePreview" style="margin-top: 10px; display: none;">
                    <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                    <button type="button" onclick="clearImagePreview()" class="btn btn--secondary btn--sm">Clear</button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn--primary">Save Occasion</button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('occasionModal');
const addOccasionBtn = document.getElementById('addOccasionBtn');
const occasionForm = document.getElementById('occasionForm');

// Add occasion button
addOccasionBtn.addEventListener('click', () => {
    document.getElementById('modalTitle').textContent = 'Add Occasion';
    occasionForm.reset();
    document.getElementById('occasionId').value = '';
    modal.classList.add('show');
});

// Close modal
function closeModal() {
    modal.classList.remove('show');
}

// Close modal on background click
modal.addEventListener('click', (e) => {
    if (e.target === modal) {
        closeModal();
    }
});

// Edit occasion
function editOccasion(id) {
    document.getElementById('modalTitle').textContent = 'Edit Occasion';
    document.getElementById('occasionId').value = id;
    
    // Fetch occasion data via AJAX
fetch('<?= BASE_URL ?>/api-occasion.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const occasion = data.data;
                document.getElementById('occasionName').value = occasion.events || '';
                document.getElementById('occasionDescription').value = occasion.descriptions || '';
                
                // Load image if it exists
                if (occasion.has_image) {
                    fetch('/api-occasion.php?image=' + id)
                        .then(res => res.json())
                        .then(imgData => {
                            if (imgData.success) {
                                document.getElementById('previewImg').src = imgData.image;
                                document.getElementById('imagePreview').style.display = 'block';
                            }
                        });
                } else {
                    document.getElementById('imagePreview').style.display = 'none';
                }
                
                modal.classList.add('show');
            }
        })
        .catch(error => console.error('Error:', error));
}

// Delete occasion
function deleteOccasion(id) {
    if (confirm('Are you sure you want to delete this occasion? This will affect all associated packages.')) {
        console.log('Deleting occasion:', id);
        
        fetch('/api-occasion.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ occasion_id: id }),
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            if (!response.ok && response.status === 401) {
                alert('Session expired. Please login again.');\n                window.location.href = '/index.php?route=signin';
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return;
            console.log('Delete response:', data);
            if (data.success) {
                alert('Occasion deleted successfully!');\n                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete'));\n            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('Error deleting occasion: ' + error.message);\n        });
    }
}

// Image preview
document.getElementById('occasionImage')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewImg').src = event.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

function clearImagePreview() {
    document.getElementById('occasionImage').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('previewImg').src = '';
}

// Form submission
occasionForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const formData = new FormData(occasionForm);
    const occasion_id = formData.get('occasion_id');
    
    // For updates with FormData, we'll send as POST with action parameter
    // since PUT doesn't work well with FormData in all browsers
    const url = occasion_id ? '/api-occasion.php?action=update' : '/api-occasion.php';
    
    console.log('Submitting occasion form:', { occasion_id, url, hasImage: !!formData.get('image') });
    
    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Occasion response status:', response.status);
        if (!response.ok && response.status === 401) {
            alert('Session expired. Please login again.');
            window.location.href = '/index.php?route=signin';
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (!data) return;
        console.log('Occasion response:', data);
        if (data.success) {
            alert('Occasion saved successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save'));
        }
    })
    .catch(error => {
        console.error('Occasion submission error:', error);
        alert('Error: ' + error.message);
    });
});

// Mobile toggle
document.getElementById('mobileToggle')?.addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.toggle('open');
});
</script>
    </div>
    </div>
    </main>
    </div>

<?php include 'admin-footer.php'; ?>
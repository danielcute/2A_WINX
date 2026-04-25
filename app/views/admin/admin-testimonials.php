<?php 
require_once dirname(__DIR__, 2) . '/models/Testimonial.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

$page_title = 'Testimonial Management';
$testimonialModel = new Testimonial();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];
    
    switch ($action) {
        case 'create':
            $data = [
                'user_id' => $_POST['user_id'] ?? null,
                'package_id' => $_POST['package_id'] ?? null,
                'rating' => (int)$_POST['rating'],
                'comment' => $_POST['comment'],
                'status' => 'approved'
            ];
            
            if ($testimonialModel->create($data)) {
                $response = ['success' => true, 'message' => 'Testimonial added successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to add testimonial'];
            }
            break;
            
        case 'update_status':
            if ($testimonialModel->update((int)$_POST['id'], ['status' => $_POST['status']])) {
                $response = ['success' => true, 'message' => 'Status updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update status'];
            }
            break;
            
        case 'delete':
            if ($testimonialModel->delete((int)$_POST['id'])) {
                $response = ['success' => true, 'message' => 'Testimonial deleted successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to delete testimonial'];
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}

$testimonials = $testimonialModel->getAll(false);
$pendingCount = $testimonialModel->getPendingCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Testimonial Management | Sinta</title>
    <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1000px; margin: 0 auto; padding: 2rem; }
        .testimonial-card { background: white; border: 1px solid var(--border); border-radius: 24px; padding: 1.5rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: flex-start; }
        .testimonial-content { flex: 1; }
        .testimonial-stars { color: var(--primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.15rem; }
        .testimonial-stars i { color: var(--primary); animation: pulse 1.4s ease-in-out infinite; }
        .testimonial-stars .star-empty { color: #d4c5b6; }
        .testimonial-quote { font-style: italic; color: var(--dark); margin-bottom: 0.5rem; }
        .testimonial-author { font-weight: 600; color: var(--primary); }
        .status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; margin-top: 0.5rem; }
        .status-badge.approved { background: #e8f5e9; color: #2e7d32; }
        .status-badge.pending { background: #fff3e0; color: #ef6c00; }
        .btn-animation { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; }
        .btn-delete-custom { color: #f44336; border-color: #f44336; }
        .btn-delete-custom:hover { background: rgba(244, 67, 54, 0.15); color: #d32f2f; border-color: #d32f2f; }
        .animated-icon { display: inline-flex; color: #8A7650; animation: pulse 1.4s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.08); } }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: #333; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; z-index: 3000; animation: slideIn 0.3s ease; }
        .toast.success { background: #2e7d32; }
        .toast.error { background: #c62828; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

    <div class="admin-page-header" style="margin-bottom: 2rem;">
        <h1 class="admin-page-title"><i class="fas fa-star animated-icon"></i> Testimonial <em>Management</em></h1>
        <div>
            <?php if ($pendingCount > 0): ?>
                <span class="badge" style="background: #ef6c00; color: white; margin-right: 1rem;"><?= $pendingCount ?> Pending Approval</span>
            <?php endif; ?>
            <button class="btn btn--primary btn--sm" onclick="openAddModal()"><i class="fas fa-plus"></i> Add Testimonial</button>
        </div>
    </div>
    
    <div id="testimonialsList">
        <?php if (empty($testimonials)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--gray);">No testimonials yet</div>
        <?php else: ?>
            <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card" id="testimonial-<?= $t['review_id'] ?>">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <?php for ($i = 0; $i < $t['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                            <?php for ($i = 0; $i < 5 - $t['rating']; $i++): ?>
                                <i class="far fa-star star-empty"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-quote">"<?= htmlspecialchars($t['comment']) ?>"</p>
                        <div class="testimonial-author">— <?= htmlspecialchars($t['first_name'] . ' ' . ($t['last_name'] ?? '')) ?></div>
                        <?php if ($t['package_name']): ?>
                            <small style="color: var(--gray);">Event: <?= htmlspecialchars($t['package_name']) ?></small>
                        <?php endif; ?>
                        <div>
                            <span class="status-badge <?= $t['status'] ?>"><?= ucfirst($t['status'] ?? 'pending') ?></span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($t['status'] !== 'approved'): ?>
                            <button class="btn btn--primary btn--sm btn-animation" onclick="updateStatus(<?= $t['review_id'] ?>, 'approved')"><i class="fas fa-check"></i> Approve</button>
                        <?php endif; ?>
                        <button class="btn btn--ghost btn--sm btn-delete-custom btn-animation" onclick="deleteTestimonial(<?= $t['review_id'] ?>)"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<!-- Add Modal -->
<div id="addModal" class="form-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 2000;">
    <div style="background: white; border-radius: 28px; padding: 2rem; max-width: 500px; width: 90%;">
        <h3>Add New Testimonial</h3>
        <form id="addTestimonialForm">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Author Name</label>
                <input type="text" name="author" id="testAuthor" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 12px;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Rating (1-5)</label>
                <select name="rating" id="testRating" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 12px;">
                    <option value="5">5 ★★★★★</option>
                    <option value="4">4 ★★★★☆</option>
                    <option value="3">3 ★★★☆☆</option>
                    <option value="2">2 ★★☆☆☆</option>
                    <option value="1">1 ★☆☆☆☆</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Testimonial Text</label>
                <textarea name="comment" id="testQuote" rows="3" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 12px;"></textarea>
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="submit" class="btn btn--primary">Add Testimonial</button>
                <button type="button" class="btn btn--ghost" onclick="closeAddModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    document.getElementById('addTestimonialForm').reset();
}

function updateStatus(id, status) {
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&id=${id}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            location.reload();
        }
    });
}

function deleteTestimonial(id) {
    if (confirm('Delete this testimonial?')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                document.getElementById(`testimonial-${id}`)?.remove();
            }
        });
    }
}

document.getElementById('addTestimonialForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('author', document.getElementById('testAuthor').value);
    formData.append('rating', document.getElementById('testRating').value);
    formData.append('comment', document.getElementById('testQuote').value);
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    });
});

document.getElementById('addModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddModal();
});
</script>
<?php include 'admin-footer.php'; ?>
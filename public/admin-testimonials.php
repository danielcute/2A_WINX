<?php 
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
$page_title = 'Testimonial Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Testimonial Management | Sinta</title>
  <link rel="stylesheet" href="assets/css/global.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .admin-container { max-width: 1000px; margin: 0 auto; padding: 2rem; }
    .testimonial-card { background: white; border: 1px solid var(--border); border-radius: 24px; padding: 1.5rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: flex-start; }
    .testimonial-content { flex: 1; }
    .testimonial-stars { color: var(--primary); margin-bottom: 0.5rem; }
    .testimonial-quote { font-style: italic; color: var(--dark); margin-bottom: 0.5rem; }
    .testimonial-author { font-weight: 600; color: var(--primary); }
    .btn-sm { padding: 0.3rem 0.8rem; font-size: 0.75rem; }
    .form-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 2000; }
    .form-modal.active { display: flex; }
    .form-modal-content { background: white; border-radius: 28px; padding: 2rem; max-width: 500px; width: 90%; }
    .form-group { margin-bottom: 1rem; }
    .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 12px; }
    .btn-group { display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.5rem; }
  </style>
</head>
<body>
<?php include 'admin-nav.php'; ?>

<div class="admin-container">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>⭐ Testimonial <em>Management</em></h1>
    <button class="btn btn--primary" onclick="openModal('create')">+ Add Testimonial</button>
  </div>
  
  <?php
  // Initialize testimonials in session
  if (!isset($_SESSION['testimonials'])) {
      $_SESSION['testimonials'] = [
          ['id' => 1, 'author' => 'Isabella Rodriguez', 'rating' => 5, 'quote' => 'The most magical day of our lives. Sinta made every detail absolutely perfect.', 'occasion' => 'Wedding', 'date' => '2024-03-15'],
          ['id' => 2, 'author' => 'Marcus Tan', 'rating' => 5, 'quote' => 'Professional, creative, and handled every detail without a hitch.', 'occasion' => 'Corporate', 'date' => '2024-06-20'],
      ];
  }

  // CRUD Operations
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (isset($_POST['action'])) {
          switch ($_POST['action']) {
              case 'create':
                  $newId = count($_SESSION['testimonials']) + 1;
                  $_SESSION['testimonials'][] = [
                      'id' => $newId,
                      'author' => $_POST['author'],
                      'rating' => (int)$_POST['rating'],
                      'quote' => $_POST['quote'],
                      'occasion' => $_POST['occasion'],
                      'date' => date('Y-m-d')
                  ];
                  break;
                  
              case 'update':
                  foreach ($_SESSION['testimonials'] as &$t) {
                      if ($t['id'] == $_POST['id']) {
                          $t['author'] = $_POST['author'];
                          $t['rating'] = (int)$_POST['rating'];
                          $t['quote'] = $_POST['quote'];
                          $t['occasion'] = $_POST['occasion'];
                          break;
                      }
                  }
                  break;
                  
              case 'delete':
                  $_SESSION['testimonials'] = array_filter($_SESSION['testimonials'], fn($t) => $t['id'] != $_POST['id']);
                  $_SESSION['testimonials'] = array_values($_SESSION['testimonials']);
                  break;
          }
      }
      header('Location: admin-testimonials.php');
      exit;
  }

  $testimonials = $_SESSION['testimonials'];
  ?>
  
  <?php if (empty($testimonials)): ?>
    <div style="text-align: center; padding: 3rem; color: var(--gray);">No testimonials yet</div>
  <?php else: ?>
    <?php foreach ($testimonials as $t): ?>
    <div class="testimonial-card">
      <div class="testimonial-content">
        <div class="testimonial-stars"><?= str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']) ?></div>
        <p class="testimonial-quote">"<?= htmlspecialchars($t['quote']) ?>"</p>
        <div class="testimonial-author">— <?= htmlspecialchars($t['author']) ?></div>
        <small style="color: var(--gray);"><?= $t['occasion'] ?> · <?= $t['date'] ?></small>
      </div>
      <div style="display: flex; gap: 0.5rem;">
        <button class="btn btn--primary btn-sm" onclick="openModal('update', <?= htmlspecialchars(json_encode($t)) ?>)">Edit</button>
        <button class="btn btn--ghost btn-sm" onclick="deleteTestimonial(<?= $t['id'] ?>)">Delete</button>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Modal -->
<div id="testimonialModal" class="form-modal">
  <div class="form-modal-content">
    <h3 id="modalTitle">Add Testimonial</h3>
    <form method="POST">
      <input type="hidden" name="action" id="formAction">
      <input type="hidden" name="id" id="testimonialId">
      <div class="form-group"><label>Author Name</label><input type="text" name="author" id="testAuthor" required></div>
      <div class="form-group"><label>Rating (1-5)</label><select name="rating" id="testRating"><option>1</option><option>2</option><option>3</option><option>4</option><option selected>5</option></select></div>
      <div class="form-group"><label>Quote</label><textarea name="quote" id="testQuote" rows="3" required></textarea></div>
      <div class="form-group"><label>Occasion</label><input type="text" name="occasion" id="testOccasion" required></div>
      <div class="btn-group"><button type="submit" class="btn btn--primary">Save</button><button type="button" class="btn btn--ghost" onclick="closeModal()">Cancel</button></div>
    </form>
  </div>
</div>

<script>
function openModal(action, data = null) {
  const modal = document.getElementById('testimonialModal');
  modal.classList.add('active');
  document.getElementById('formAction').value = action;
  if (action === 'create') {
    document.getElementById('modalTitle').innerText = 'Add New Testimonial';
    document.getElementById('testimonialId').value = '';
    document.getElementById('testAuthor').value = '';
    document.getElementById('testQuote').value = '';
    document.getElementById('testOccasion').value = '';
    document.getElementById('testRating').value = '5';
  } else if (action === 'update' && data) {
    document.getElementById('modalTitle').innerText = 'Update Testimonial';
    document.getElementById('testimonialId').value = data.id;
    document.getElementById('testAuthor').value = data.author;
    document.getElementById('testRating').value = data.rating;
    document.getElementById('testQuote').value = data.quote;
    document.getElementById('testOccasion').value = data.occasion;
  }
}
function closeModal() { document.getElementById('testimonialModal').classList.remove('active'); }
function deleteTestimonial(id) { if(confirm('Delete this testimonial?')){ let f=document.createElement('form');f.method='POST';f.innerHTML=`<input name="action" value="delete"><input name="id" value="${id}">`;document.body.appendChild(f);f.submit();} }
</script>
</body>
</html>
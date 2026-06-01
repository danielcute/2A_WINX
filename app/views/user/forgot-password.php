<?php
$page = 'forgot-password';
$error = $_SESSION['forgot_error'] ?? null;
$success = $_SESSION['forgot_success'] ?? null;
unset($_SESSION['forgot_error'], $_SESSION['forgot_success']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
    }
    require_once ROOT_PATH . '/app/models/User.php';
    $userModel = new User();
    $email = trim($_POST['email']);
    $user = $userModel->findByEmail($email);
    // Always show success to prevent email enumeration
    $_SESSION['forgot_success'] = 'If that email is registered, you will receive a password reset link shortly. Please contact the admin directly at sinta@example.com for immediate assistance.';
    header('Location: ' . BASE_URL . '/index.php?route=forgot-password');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root { --primary: #8A7650; --border: #E2D9C8; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: linear-gradient(135deg, #f5f0e8 0%, #fff 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'DM Sans', sans-serif; padding: 1rem; }
    .card { background: white; border-radius: 24px; padding: 2.5rem; max-width: 420px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid var(--border); }
    .icon { width: 72px; height: 72px; background: rgba(138,118,80,0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: var(--primary); margin: 0 auto 1.5rem; }
    h1 { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; text-align: center; margin-bottom: 0.5rem; color: #2C2820; }
    p { color: #6B6463; text-align: center; margin-bottom: 2rem; font-size: 0.9rem; line-height: 1.6; }
    label { display: block; font-size: 0.75rem; font-weight: 600; color: #6B6463; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
    input { width: 100%; padding: 0.9rem 1rem; border: 2px solid var(--border); border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: border-color 0.2s; margin-bottom: 1.25rem; }
    input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(138,118,80,0.12); }
    .btn { width: 100%; padding: 0.9rem; background: var(--primary); color: white; border: none; border-radius: 999px; font-size: 0.85rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; transition: all 0.2s; }
    .btn:hover { background: #6B5A3E; transform: translateY(-2px); }
    .alert { padding: 0.75rem 1rem; border-radius: 12px; margin-bottom: 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; }
    .alert--success { background: #f0fdf4; color: #16a34a; }
    .alert--error { background: #fef2f2; color: #dc2626; }
    .back { text-align: center; margin-top: 1.25rem; }
    .back a { color: #6B6463; text-decoration: none; font-size: 0.85rem; }
    .back a:hover { color: var(--primary); }
  </style>
</head>
<body>
<div class="card">
  <div class="icon"><i class="fas fa-key"></i></div>
  <h1>Forgot Password</h1>
  <p>Enter your email address and we'll help you reset your password.</p>
  <?php if ($success): ?>
    <div class="alert alert--success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert--error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <label>Email Address</label>
    <input type="email" name="email" placeholder="your@email.com" required>
    <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
  </form>
  <div class="back"><a href="/index.php?route=signin"><i class="fas fa-arrow-left"></i> Back to Sign In</a></div>
</div>
</body>
</html>

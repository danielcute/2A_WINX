<?php
$page = 'reset-password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root { --primary: #8A7650; --border: #E2D9C8; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: linear-gradient(135deg, #f5f0e8 0%, #fff 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'DM Sans', sans-serif; padding: 1rem; }
    .card { background: white; border-radius: 24px; padding: 2.5rem; max-width: 420px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid var(--border); text-align: center; }
    .icon { width: 72px; height: 72px; background: rgba(138,118,80,0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: var(--primary); margin: 0 auto 1.5rem; }
    h1 { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; margin-bottom: 1rem; color: #2C2820; }
    p { color: #6B6463; margin-bottom: 1.5rem; font-size: 0.9rem; line-height: 1.6; }
    .btn { display: inline-block; padding: 0.9rem 2rem; background: var(--primary); color: white; border-radius: 999px; text-decoration: none; font-size: 0.85rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; transition: all 0.2s; }
    .btn:hover { background: #6B5A3E; }
  </style>
</head>
<body>
<div class="card">
  <div class="icon"><i class="fas fa-lock-open"></i></div>
  <h1>Reset Password</h1>
  <p>Password reset links are not yet automated. Please contact the Sinta admin team directly to reset your password, or use the forgot password form to get assistance.</p>
  <a href="/index.php?route=forgot-password" class="btn"><i class="fas fa-arrow-left"></i> Back to Forgot Password</a>
</div>
</body>
</html>

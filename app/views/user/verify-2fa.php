<?php
$page = 'verify-2fa';
$error = isset($_SESSION['two_fa_error']) ? $_SESSION['two_fa_error'] : '';
unset($_SESSION['two_fa_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Two-Factor Authentication — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/global.css">
  <style>
    body {
      background: linear-gradient(135deg, #f5f0e8 0%, #fff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'DM Sans', sans-serif;
    }
    .auth-container {
      max-width: 450px;
      width: 100%;
      margin: 2rem;
    }
    .auth-card {
      background: white;
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      border: 1px solid #E2D9C8;
      padding: 2.5rem;
    }
    .twofa-icon {
      width: 80px;
      height: 80px;
      background: var(--primary-pale);
      color: var(--primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      margin: 0 auto 1.5rem;
    }
    h1 {
      font-size: 1.8rem;
      margin-bottom: 0.5rem;
      font-family: 'Cormorant Garamond', serif;
      text-align: center;
      color: var(--text-primary);
    }
    p {
      color: var(--text-muted);
      text-align: center;
      margin-bottom: 2rem;
      line-height: 1.6;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    .form-group label {
      display: block;
      font-size: 0.75rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--text-secondary);
    }
    .form-group input {
      width: 100%;
      padding: 1rem;
      border: 2px solid var(--border);
      border-radius: 12px;
      font-size: 1.2rem;
      text-align: center;
      letter-spacing: 0.3em;
      transition: all 0.2s ease;
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(138,118,80,0.12);
    }
    .error-message {
      background: #fee2e2;
      color: #dc2626;
      padding: 0.75rem 1rem;
      border-radius: 12px;
      margin-bottom: 1rem;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
      padding: 0.8rem 1.6rem;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.85rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      text-decoration: none;
      border-radius: 999px;
      transition: all 0.3s ease;
      cursor: pointer;
      border: none;
      width: 100%;
    }
    .btn--primary {
      background: var(--primary);
      color: white;
    }
    .btn--primary:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .btn--ghost {
      background: transparent;
      color: var(--text-muted);
      border: 1px solid var(--border);
    }
    .btn--ghost:hover {
      border-color: var(--primary);
      color: var(--primary);
    }
    .back-link {
      text-align: center;
      margin-top: 1.5rem;
    }
    .back-link a {
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.85rem;
    }
    .back-link a:hover {
      color: var(--primary);
    }
    .method-toggle {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
      border-bottom: 2px solid var(--border);
    }
    .method-toggle button {
      flex: 1;
      padding: 0.75rem;
      border: none;
      background: none;
      cursor: pointer;
      font-weight: 600;
      color: var(--text-muted);
      border-bottom: 3px solid transparent;
      transition: all 0.2s;
      font-size: 0.9rem;
    }
    .method-toggle button.active {
      color: var(--primary);
      border-bottom-color: var(--primary);
    }
    .verify-method {
      display: none;
    }
    .verify-method.active {
      display: block;
      animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  </style>
</head>
<body>

<div class="auth-container">
  <div class="auth-card">
    <div class="twofa-icon">
      <i class="fas fa-shield-halved"></i>
    </div>
    <h1>Two-Factor Authentication</h1>
    <p>Verify your identity to complete login</p>
    
    <?php if ($error): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    
    <!-- Verification Method Toggle -->
    <div class="method-toggle">
      <button type="button" class="active" onclick="switchMethod(event, 'code')" style="font-size: 0.85rem;">
        <i class="fas fa-mobile-alt"></i> 6-Digit Code
      </button>
      <button type="button" onclick="switchMethod(event, 'key')" style="font-size: 0.85rem;">
        <i class="fas fa-key"></i> Setup Key
      </button>
    </div>
    
    <!-- 6-Digit Code Method -->
    <form method="POST" action="/index.php?route=verify-2fa" id="codeForm" class="verify-method active">
      <input type="hidden" name="action" value="verify_2fa">
      
      <div class="form-group">
        <label>Enter 6-digit code from your authenticator app</label>
        <input type="text" name="code" id="twofaCode" placeholder="000000" maxlength="6" autocomplete="off" required>
      </div>
      
      <button type="submit" class="btn btn--primary">
        <i class="fas fa-check"></i> Verify
      </button>
    </form>
    
    <!-- Manual Setup Key Method -->
    <form method="POST" action="/index.php?route=verify-2fa" id="keyForm" class="verify-method">
      <input type="hidden" name="action" value="verify_2fa">
      
      <div class="form-group">
        <label>Enter your manual setup key</label>
        <input type="text" name="setup_key" id="setupKey" placeholder="Enter your setup key" autocomplete="off" required>
      </div>
      
      <small style="color: var(--text-muted); display: block; margin-bottom: 1rem;">
        <i class="fas fa-info-circle"></i> Use this if you can't access your authenticator app. This should be the key you saved when setting up 2FA.
      </small>
      
      <button type="submit" class="btn btn--primary">
        <i class="fas fa-check"></i> Verify
      </button>
    </form>
    
    <div class="back-link">
      <a href="/index.php?route=signin">
        <i class="fas fa-arrow-left"></i> Back to Sign In
      </a>
    </div>
  </div>
</div>

<script>
function switchMethod(event, method) {
  // Update button states
  document.querySelectorAll('.method-toggle button').forEach(btn => {
    btn.classList.remove('active');
  });
  event.target.closest('button').classList.add('active');
  
  // Toggle form visibility
  document.querySelectorAll('.verify-method').forEach(form => {
    form.classList.remove('active');
  });
  
  if (method === 'code') {
    document.getElementById('codeForm').classList.add('active');
  } else {
    document.getElementById('keyForm').classList.add('active');
  }
}

document.getElementById('twofaCode').addEventListener('input', function(e) {
  // Only allow numbers
  this.value = this.value.replace(/\D/g, '');
});

document.getElementById('twofaCode').addEventListener('keyup', function(e) {
  // Auto-submit when 6 digits entered
  if (this.value.length === 6) {
    this.form.submit();
  }
});
</script>

</body>
</html>

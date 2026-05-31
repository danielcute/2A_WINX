<?php
$page = 'setup-2fa';
$error = isset($_SESSION['two_fa_error']) ? $_SESSION['two_fa_error'] : '';
$success = isset($_SESSION['two_fa_success']) ? $_SESSION['two_fa_success'] : '';
unset($_SESSION['two_fa_error']);
unset($_SESSION['two_fa_success']);

// Get stored data from session
$email = $_SESSION['temp_user_email'] ?? $_SESSION['user_email'] ?? 'user@example.com';
$secret = $_SESSION['temp_2fa_secret'] ?? '';

// Generate QR code URL using QRServer API (Google Charts was deprecated)
$qrCodeUrl = '';
if ($secret) {
    $otpauthUrl = 'otpauth://totp/Sinta:' . urlencode($email) . '?secret=' . $secret . '&issuer=Sinta&algorithm=SHA1&digits=6&period=30';
    $qrCodeUrl = 'https://quickchart.io/qr?text=' . urlencode($otpauthUrl) . '&size=200';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setup 2-Factor Authentication — Sinta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #8A7650;
      --primary-dark: #6B5A40;
      --primary-pale: rgba(138, 118, 80, 0.15);
      --gold: #8A7650;
      --gold-pale: rgba(138, 118, 80, 0.15);
      --text-primary: #2C2820;
      --text-secondary: #5A5550;
      --text-muted: #8B887F;
      --border: #E2D9C8;
      --white: #FFFFFF;
      --error: #DC2626;
      --success: #16A34A;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #f5f0e8 0%, #fff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'DM Sans', sans-serif;
      padding: 1rem;
    }
    
    .setup-container {
      max-width: 480px;
      width: 100%;
    }
    
    .setup-card {
      background: var(--white);
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.12);
      border: 1px solid var(--border);
    }
    
    .setup-header {
      background: var(--primary-pale);
      padding: 2rem 2rem 1.5rem;
      text-align: center;
    }
    
    .setup-icon {
      width: 72px;
      height: 72px;
      background: var(--primary);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.75rem;
      margin: 0 auto 1rem;
    }
    
    .setup-header h1 {
      font-size: 1.6rem;
      font-family: 'Cormorant Garamond', serif;
      color: var(--text-primary);
      margin-bottom: 0.5rem;
    }
    
    .setup-header p {
      color: var(--text-muted);
      font-size: 0.9rem;
      line-height: 1.5;
    }
    
    .setup-body {
      padding: 2rem;
    }
    
    .qr-section {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .qr-container {
      background: white;
      padding: 1.5rem;
      border-radius: 16px;
      border: 2px solid var(--border);
      display: inline-block;
      margin-bottom: 1.5rem;
    }
    
    .qr-container img {
      width: 180px;
      height: 180px;
      display: block;
    }
    
    .qr-instructions {
      text-align: left;
      background: var(--gold-pale);
      border-radius: 12px;
      padding: 1.25rem;
      margin-bottom: 1.5rem;
    }
    
    .qr-instructions h3 {
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .qr-instructions ol {
      margin: 0;
      padding-left: 1.25rem;
      color: var(--text-secondary);
      font-size: 0.9rem;
      line-height: 1.7;
    }
    
    .qr-instructions li {
      margin-bottom: 0.5rem;
    }
    
    .secret-section {
      background: #f8f7f5;
      border-radius: 12px;
      padding: 1.25rem;
      margin-bottom: 1.5rem;
      text-align: center;
    }
    
    .secret-section label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-muted);
      margin-bottom: 0.5rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .secret-key {
      font-family: 'DM Mono', 'Courier New', monospace;
      font-size: 1.1rem;
      letter-spacing: 0.15em;
      color: var(--text-primary);
      background: white;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid var(--border);
      word-break: break-all;
    }
    
    .form-group {
      margin-bottom: 1.25rem;
    }
    
    .form-group label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 0.5rem;
    }
    
    .form-group input {
      width: 100%;
      padding: 0.9rem 1rem;
      border: 2px solid var(--border);
      border-radius: 12px;
      font-size: 1.1rem;
      text-align: center;
      letter-spacing: 0.25em;
      transition: all 0.2s ease;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(138,118,80,0.15);
    }
    
    .form-group input::placeholder {
      letter-spacing: 0.1em;
      color: #ccc;
    }
    
    .error-message {
      background: #fef2f2;
      color: var(--error);
      padding: 0.75rem 1rem;
      border-radius: 12px;
      margin-bottom: 1rem;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .success-message {
      background: #f0fdf4;
      color: var(--success);
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
      gap: 0.5rem;
      padding: 0.9rem 1.5rem;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.85rem;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      text-decoration: none;
      border-radius: 999px;
      transition: all 0.25s ease;
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
      box-shadow: 0 8px 24px rgba(138,118,80,0.25);
    }
    
    .btn--ghost {
      background: transparent;
      color: var(--text-muted);
      border: 1px solid var(--border);
      margin-top: 0.75rem;
    }
    
    .btn--ghost:hover {
      border-color: var(--primary);
      color: var(--primary);
    }
    
    .skip-link {
      text-align: center;
      margin-top: 1.25rem;
    }
    
    .skip-link a {
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.85rem;
    }
    
    .skip-link a:hover {
      color: var(--primary);
    }
    
    .setup-tab-btn {
      position: relative;
    }
    
    .setup-tab-btn.active {
      color: var(--primary);
      border-bottom-color: var(--primary) !important;
    }
    
    .setup-method-section {
      animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @media (max-width: 480px) {
      .setup-body {
        padding: 1.5rem;
      }
      
      .qr-container img {
        width: 150px;
        height: 150px;
      }
    }
  </style>
</head>
<body>

<div class="setup-container">
  <div class="setup-card">
    <div class="setup-header">
      <div class="setup-icon">
        <i class="fas fa-shield-halved"></i>
      </div>
      <h1>Set Up Two-Factor Authentication</h1>
      <p>Add an extra layer of security to your account by scanning this QR code with your authenticator app.</p>
    </div>
    
    <div class="setup-body">
      <?php if ($error): ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="success-message">
          <i class="fas fa-check-circle"></i>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>
      
      <div class="qr-section">
        <!-- Setup Method Toggle -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border);">
          <button type="button" class="setup-tab-btn active" onclick="switchSetupMethod('qr')" style="flex: 1; padding: 0.75rem; border: none; background: none; cursor: pointer; font-weight: 600; color: var(--text-secondary); border-bottom: 3px solid transparent; transition: all 0.2s;">
            <i class="fas fa-qrcode"></i> Scan QR Code
          </button>
          <button type="button" class="setup-tab-btn" onclick="switchSetupMethod('manual')" style="flex: 1; padding: 0.75rem; border: none; background: none; cursor: pointer; font-weight: 600; color: var(--text-muted); border-bottom: 3px solid transparent; transition: all 0.2s;">
            <i class="fas fa-keyboard"></i> Enter Key Manually
          </button>
        </div>
        
        <!-- QR Code Method -->
        <div id="qrMethod" class="setup-method-section">
          <div class="qr-container">
            <?php if ($qrCodeUrl): ?>
              <img src="<?= htmlspecialchars($qrCodeUrl) ?>" alt="QR Code" id="qrCode">
            <?php else: ?>
              <div style="width: 180px; height: 180px; display: flex; align-items: center; justify-content: center; color: #ccc;">
                <i class="fas fa-ban" style="font-size: 3rem;"></i>
              </div>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Manual Entry Method -->
        <div id="manualMethod" class="setup-method-section" style="display: none;">
          <div class="secret-section">
            <label>Your Setup Key</label>
            <div style="display: flex; gap: 0.5rem; align-items: stretch;">
              <div class="secret-key" id="secretKey" style="cursor: pointer; user-select: all; flex: 1;">
                <?= htmlspecialchars($secret) ?>
              </div>
              <button type="button" class="btn btn--primary" onclick="copySecret()" style="width: auto; padding: 0.9rem 1rem; border-radius: 8px;">
                <i class="fas fa-copy"></i>
              </button>
            </div>
            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
              <i class="fas fa-info-circle"></i> Copy this key and paste it into your authenticator app
            </small>
          </div>
        </div>
      </div>
      
      <div class="qr-instructions">
        <h3>How to set up:</h3>
        <ol>
          <li>Download <strong>Google Authenticator</strong> (or any authenticator app) on your phone</li>
          <li>Open the app and tap <strong>+</strong> to add a new account</li>
          <li>Choose your setup method above:
            <ul>
              <li><strong>Scan QR Code:</strong> Select "Scan barcode" in your app and scan the QR code</li>
              <li><strong>Enter Key Manually:</strong> Select "Enter a setup key" in your app and paste the key</li>
            </ul>
          </li>
          <li>Enter the 6-digit code below to verify</li>
        </ol>
      </div>
      
<form method="POST" action="/index.php?route=setup-2fa" id="setupForm">
        <input type="hidden" name="action" value="enable_2fa">
        
        <div class="form-group">
          <label>Enter 6-digit verification code</label>
          <input type="text" name="code" id="twofaCode" placeholder="000000" maxlength="6" autocomplete="off" required>
        </div>
        
        <button type="submit" class="btn btn--primary">
          <i class="fas fa-check"></i> Enable 2FA
        </button>
      </form>
      


<script>
function switchSetupMethod(method) {
  // Update button states
  document.querySelectorAll('.setup-tab-btn').forEach(btn => btn.classList.remove('active'));
  event.target.closest('.setup-tab-btn').classList.add('active');
  
  // Toggle section visibility
  const qrSection = document.getElementById('qrMethod');
  const manualSection = document.getElementById('manualMethod');
  
  if (method === 'qr') {
    qrSection.style.display = 'block';
    manualSection.style.display = 'none';
  } else {
    qrSection.style.display = 'none';
    manualSection.style.display = 'block';
  }
}

function copySecret() {
  const secretKey = document.getElementById('secretKey').textContent;
  navigator.clipboard.writeText(secretKey).then(() => {
    alert('Secret key copied to clipboard!');
  }).catch(() => {
    // Fallback for older browsers
    const el = document.getElementById('secretKey');
    const range = document.createRange();
    range.selectNodeContents(el);
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    document.execCommand('copy');
    alert('Secret key copied to clipboard!');
  });
}

document.getElementById('twofaCode').addEventListener('input', function(e) {
  // Only allow numbers
  this.value = this.value.replace(/\D/g, '');
});

document.getElementById('twofaCode').addEventListener('keyup', function(e) {
  // Auto-submit when 6 digits entered
  if (this.value.length === 6) {
    document.getElementById('setupForm').submit();
  }
});

// Handle form submission
document.getElementById('setupForm').addEventListener('submit', function(e) {
  const code = document.getElementById('twofaCode').value;
  if (code.length !== 6) {
    e.preventDefault();
    alert('Please enter the 6-digit code from your authenticator app');
    return false;
  }
});
</script>

</body>
</html>

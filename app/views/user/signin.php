<?php 
$page = 'signin';
$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
$success = isset($_SESSION['signup_success']) ? $_SESSION['signup_success'] : '';
unset($_SESSION['login_error']);
unset($_SESSION['signup_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — Sinta</title>
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
      max-width: 1200px;
      width: 100%;
      margin: 2rem;
    }
    .auth-card {
      display: grid;
      grid-template-columns: 1fr 1fr;
      background: white;
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      border: 1px solid #E2D9C8;
    }
    .auth-brand {
      background-image: url('/assets/img/signinimg.jpg');
      background-size: cover;
      background-position: center;
      padding: 3rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      color: white;
      position: relative;
    }
    .auth-brand::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 0;
    }
    .auth-brand > div, .auth-brand .event-tags {
      position: relative;
      z-index: 1;
    }
    .auth-brand .logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 3rem;
    }
    .auth-brand .logo img {
      width: 40px;
      height: 40px;
      object-fit: contain;
      border-radius: 10px;
      background: white;
      padding: 5px;
    }
    .auth-brand .logo span {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.8rem;
      font-weight: 500;
      color: white;
    }
    .auth-quote {
      margin-bottom: 3rem;
    }
    .auth-quote i {
      font-size: 2rem;
      opacity: 0.9;
      margin-bottom: 1rem;
      color: white;
    }
    .auth-quote p {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.3rem;
      line-height: 1.5;
      font-style: italic;
      color: white;
    }
    .auth-stats {
      display: flex;
      gap: 2rem;
    }
    .auth-stat__number {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.8rem;
      font-weight: 600;
      color: white;
    }
    .auth-stat__label {
      font-size: 0.7rem;
      opacity: 0.8;
      text-transform: uppercase;
      color: rgba(255,255,255,0.9);
    }
    .event-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 3rem;
    }
    .event-tags span {
      padding: 0.4rem 1rem;
      background: rgba(255,255,255,0.2);
      border-radius: 60px;
      font-size: 0.75rem;
      backdrop-filter: blur(4px);
      color: white;
    }
    .auth-form {
      padding: 3rem;
    }
    .auth-form h1 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      font-family: 'Cormorant Garamond', serif;
    }
    .auth-form p {
      color: #6B6463;
      margin-bottom: 2rem;
    }
    .social-buttons {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .social-btn {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.75rem;
      border: 1px solid #E2D9C8;
      border-radius: 12px;
      background: white;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .social-btn:hover {
      border-color: #8A7650;
      color: #8A7650;
    }
    .divider {
      text-align: center;
      margin: 1.5rem 0;
      position: relative;
    }
    .divider::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      width: 100%;
      height: 1px;
      background: #E2D9C8;
    }
    .divider span {
      background: white;
      padding: 0 1rem;
      position: relative;
      color: #6B6463;
      font-size: 0.8rem;
    }
    .form-group {
      margin-bottom: 1.25rem;
    }
    .form-group label {
      display: block;
      font-size: 0.75rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #6B6463;
    }
    .form-group input {
      width: 100%;
      padding: 0.85rem;
      border: 1px solid #E2D9C8;
      border-radius: 12px;
      font-size: 0.9rem;
      transition: all 0.2s ease;
    }
    .form-group input:focus {
      outline: none;
      border-color: #8A7650;
      box-shadow: 0 0 0 3px rgba(138,118,80,0.12);
    }
    .form-options {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    .checkbox {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .checkbox input {
      width: 16px;
      height: 16px;
      accent-color: #8A7650;
    }
    .checkbox label {
      font-size: 0.85rem;
      color: #6B6463;
    }
    .forgot-link {
      font-size: 0.85rem;
      color: #8A7650;
      text-decoration: none;
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
    }
    .btn--primary {
      background: #8A7650;
      color: white;
    }
    .btn--primary:hover {
      background: #6B5A3E;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .btn--full {
      width: 100%;
    }
    .btn--lg {
      padding: 1rem 2.2rem;
      font-size: 0.9rem;
    }
    .auth-footer {
      text-align: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid #E2D9C8;
    }
    .auth-footer a {
      color: #8A7650;
      text-decoration: none;
      font-weight: 500;
    }
    @media (max-width: 900px) {
      .auth-card {
        grid-template-columns: 1fr;
      }
      .auth-brand {
        padding: 2rem;
      }
      .auth-form {
        padding: 2rem;
      }
    }
  </style>
</head>
<body>

<div class="auth-container">
  <div class="auth-card animate-fade-up">
    
    <div class="auth-brand">
      <div>
        <div class="logo">
          <img src="/assets/img/logo.png" alt="Sinta">
          <span>Sinta</span>
        </div>
        <div class="auth-quote">
          <i class="fas fa-quote-left"></i>
          <p>Every great event begins with a single moment. Let us help you create yours.</p>
        </div>
        <div class="auth-stats">
          <div class="auth-stat">
            <div class="auth-stat__number">1,200+</div>
            <div class="auth-stat__label">Events Planned</div>
          </div>
          <div class="auth-stat">
            <div class="auth-stat__number">98%</div>
            <div class="auth-stat__label">Satisfaction</div>
          </div>
          <div class="auth-stat">
            <div class="auth-stat__number">8 Yrs</div>
            <div class="auth-stat__label">Excellence</div>
          </div>
        </div>
      </div>
      <div class="event-tags">
        <span>Weddings</span>
        <span>Debuts</span>
        <span>Birthdays</span>
        <span>Corporate</span>
        <span>Gala Nights</span>
      </div>
    </div>
    
    <div class="auth-form">
      <h1>Welcome back</h1>
      <p>Sign in to continue planning your perfect event</p>
      
      <div class="social-buttons">
        <button class="social-btn" onclick="alert('Google login demo')">
          <i class="fab fa-google"></i> Google
        </button>
        <button class="social-btn" onclick="alert('Facebook login demo')">
          <i class="fab fa-facebook-f"></i> Facebook
        </button>
      </div>
      
      <div class="divider"><span>or sign in with email</span></div>
      
      <?php if ($success): ?>
        <div style="background: #d1fae5; color: #047857; padding: 0.75rem 1rem; border-radius: 12px; margin-bottom: 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
<form id="signinForm" onsubmit="return handleSignin(event)">
        <input type="hidden" name="action" value="login">
        <div class="form-group">
          <label>Email address</label>
          <input type="email" name="email" id="signinEmail" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <div style="position: relative;">
            <input type="password" name="password" id="signinPassword" placeholder="Enter your password" required>
            <i class="fas fa-eye" id="toggleSigninPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #8A7650;"></i>
          </div>
        </div>
        <div class="form-options">
          <label class="checkbox">
            <input type="checkbox" name="remember"> <span>Remember me</span>
          </label>
          <a href="#" class="forgot-link">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn--primary btn--full btn--lg" id="signinBtn">Sign In</button>
      </form>
      
      <!-- Hidden error container for AJAX responses -->
      <div id="signinError" class="error-message" style="display: none;"></div>
      
      <div class="auth-footer">
        <p>Don't have an account? <a href="/index.php?route=signup">Create one free →</a></p>
      </div>
    </div>
    
  </div>
</div>

<script>
// Password visibility toggle for Signin
document.getElementById('toggleSigninPassword').addEventListener('click', function() {
  const passwordInput = document.getElementById('signinPassword');
  const icon = this;
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    passwordInput.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
});

// AJAX Signin handler - no page refresh on invalid credentials
async function handleSignin(event) {
  event.preventDefault();
  
  const form = document.getElementById('signinForm');
  const errorDiv = document.getElementById('signinError');
  const btn = document.getElementById('signinBtn');
  
  const email = document.getElementById('signinEmail').value;
  const password = document.getElementById('signinPassword').value;
  
  // Hide any existing error
  errorDiv.style.display = 'none';
  
  // Show loading state
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
  
  try {
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('email', email);
    formData.append('password', password);
    
    const response = await fetch('index.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    
    // Try to get response text first
    const responseText = await response.text();
    
    // Check if redirected (success) - if so, responseText might be empty or redirect page
    if (response.redirected || response.url.includes('homepage') || response.url.includes('admin-dashboard')) {
      window.location.href = response.url;
      return;
    }
    
    // Try to parse as JSON
    try {
      const data = JSON.parse(responseText);
      
      if (data && data.success === false) {
        // Login failed - show error without refresh
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
        errorDiv.style.display = 'flex';
        
        // Clear password field
        document.getElementById('signinPassword').value = '';
        
        // Shake animation
        form.style.animation = 'shake 0.5s';
        setTimeout(() => form.style.animation = '', 500);
        
        // Focus on password field for retry
        document.getElementById('signinPassword').focus();
        return;
      }
    } catch (e) {
      // Not JSON - check if response contains login error redirect
      if (responseText.includes('login_error') || responseText.includes('Invalid email or password')) {
        // Server side rendered error - extract message
        const match = responseText.match(/login_error.*?>([^<]+)</);
        const message = match ? match[1] : 'Invalid email or password';
        
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message.trim();
        errorDiv.style.display = 'flex';
        
        // Clear password field
        document.getElementById('signinPassword').value = '';
        
        // Shake animation
        form.style.animation = 'shake 0.5s';
        setTimeout(() => form.style.animation = '', 500);
        return;
      }
    }
    
    // Fallback - if response seems like HTML (redirect to homepage), go there
    if (responseText.includes('<!DOCTYPE') || responseText.includes('<html')) {
      window.location.href = '/index.php?route=homepage';
      return;
    }
    
    // Unknown response - show error
    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Invalid email or password';
    errorDiv.style.display = 'flex';
    document.getElementById('signinPassword').value = '';
    form.style.animation = 'shake 0.5s';
    setTimeout(() => form.style.animation = '', 500);
    
  } catch (error) {
    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.';
    errorDiv.style.display = 'flex';
  } finally {
    btn.disabled = false;
    btn.innerHTML = 'Sign In';
  }
}

// Add shake animation
const style = document.createElement('style');
style.textContent = `
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
  }
`;
document.head.appendChild(style);
</script>

</body>
</html>
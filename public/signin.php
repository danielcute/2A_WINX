<?php $page = 'signin'; ?>
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
  <link rel="stylesheet" href="assets/css/global.css">
  <style>
    body {
      background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .auth-container {
      max-width: 1200px;
      width: 100%;
      margin: 2rem;
    }
    
    .auth-card {
      display: grid;
      grid-template-columns: 1fr 1fr;
      background: var(--bg-card);
      border-radius: var(--radius-2xl);
      overflow: hidden;
      box-shadow: var(--shadow-lg);
      border: 1px solid var(--border);
    }
    
    /* Brand Side - WITH BACKGROUND IMAGE (replaces brown gradient) */
    .auth-brand {
      background-image: url('assets/img/signinimg.jpg');
      background-size: cover;
      background-position: center;
      padding: 3rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      color: white;
      position: relative;
    }
    
    /* Dark overlay to make text readable */
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
    
    .auth-brand > div,
    .auth-brand .event-tags {
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
      font-family: var(--serif);
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
      font-family: var(--serif);
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
      font-family: var(--serif);
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
    
    /* Form Side */
    .auth-form {
      padding: 3rem;
    }
    
    .auth-form h1 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    
    .auth-form p {
      color: var(--text-muted);
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
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      background: var(--bg-primary);
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .social-btn:hover {
      border-color: var(--primary);
      color: var(--primary);
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
      background: var(--border);
    }
    
    .divider span {
      background: var(--bg-card);
      padding: 0 1rem;
      position: relative;
      color: var(--text-muted);
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
      color: var(--text-muted);
    }
    
    .form-group input {
      width: 100%;
      padding: 0.85rem;
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      font-size: 0.9rem;
      transition: all 0.2s ease;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-pale);
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
      accent-color: var(--primary);
    }
    
    .checkbox label {
      font-size: 0.85rem;
      color: var(--text-muted);
    }
    
    .forgot-link {
      font-size: 0.85rem;
      color: var(--primary);
      text-decoration: none;
    }
    
    .forgot-link:hover {
      text-decoration: underline;
    }
    
    .auth-footer {
      text-align: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border);
    }
    
    .auth-footer a {
      color: var(--primary);
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
    
    <!-- Brand Side - With Background Image -->
    <div class="auth-brand">
      <div>
        <div class="logo">
          <img src="assets/img/logo.png" alt="Sinta">
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
    
    <!-- Form Side -->
    <div class="auth-form">
      <h1>Welcome back</h1>
      <p>Sign in to continue planning your perfect event</p>
      
      <div class="social-buttons">
        <button class="social-btn" onclick="socialLogin('google')">
          <i class="fab fa-google"></i> Google
        </button>
        <button class="social-btn" onclick="socialLogin('facebook')">
          <i class="fab fa-facebook-f"></i> Facebook
        </button>
      </div>
      
      <div class="divider"><span>or sign in with email</span></div>
      
      <form id="signinForm">
        <div class="form-group">
          <label>Email address</label>
          <input type="email" id="email" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" id="password" placeholder="Enter your password" required>
        </div>
        <div class="form-options">
          <label class="checkbox">
            <input type="checkbox"> <span>Remember me</span>
          </label>
          <a href="#" class="forgot-link">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn--primary btn--full btn--lg">Sign In</button>
      </form>
      
      <div class="auth-footer">
        <p>Don't have an account? <a href="signup.php">Create one free →</a></p>
      </div>
    </div>
    
  </div>
</div>

<script>
function socialLogin(provider) {
  alert(`Signing in with ${provider}...`);
}

document.getElementById('signinForm')?.addEventListener('submit', (e) => {
  e.preventDefault();
  const email = document.getElementById('email').value;
  alert(`Welcome back! Signing in as ${email}`);
  window.location.href = 'homepage.php';
});
</script>
</body>
</html>
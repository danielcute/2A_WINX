<?php 

$page = 'signup';
$errors = isset($_SESSION['signup_errors']) ? $_SESSION['signup_errors'] : [];
$single_error = $_SESSION['signup_error'] ?? '';
if ($single_error && empty($errors)) {
    $errors = [$single_error];
}
$formData = isset($_SESSION['signup_form_data']) ? $_SESSION['signup_form_data'] : [];
unset($_SESSION['signup_errors']);
unset($_SESSION['signup_error']);
unset($_SESSION['signup_form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account — Sinta</title>
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
      background-image: url('/assets/img/signupimg.jpg');
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
    .auth-features {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      margin-bottom: 3rem;
    }
    .auth-feature {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .auth-feature i {
      font-size: 1.2rem;
      color: white;
    }
    .auth-feature strong {
      color: white;
    }
    .event-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 2rem;
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
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      margin-bottom: 1.25rem;
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
    .form-group input.error {
      border-color: #dc2626;
    }
    .error-text {
      color: #dc2626;
      font-size: 0.7rem;
      margin-top: 0.25rem;
      display: block;
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
    .password-strength {
      margin-top: 0.5rem;
    }
    .strength-bars {
      display: flex;
      gap: 0.3rem;
      margin-bottom: 0.3rem;
    }
    .strength-bar {
      flex: 1;
      height: 4px;
      background: #E2D9C8;
      border-radius: 2px;
      transition: all 0.2s ease;
    }
    .strength-bar.active {
      background: #8A7650;
    }
    .strength-text {
      font-size: 0.7rem;
      color: #6B6463;
    }
    .checkbox {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.75rem;
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
    .checkbox a {
      color: #8A7650;
      text-decoration: none;
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
      .form-row {
        grid-template-columns: 1fr;
        gap: 0;
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
          <p>Join thousands of happy clients who trust us to bring their visions to life.</p>
        </div>
        <div class="auth-features">
          <div class="auth-feature">
            <i class="fas fa-check-circle"></i>
            <div>
              <strong>Free to join</strong>
              <div style="font-size: 0.8rem;">No commitment, cancel anytime</div>
            </div>
          </div>
          <div class="auth-feature">
            <i class="fas fa-check-circle"></i>
            <div>
              <strong>Personalized planning</strong>
              <div style="font-size: 0.8rem;">Get packages tailored to your needs</div>
            </div>
          </div>
          <div class="auth-feature">
            <i class="fas fa-check-circle"></i>
            <div>
              <strong>24/7 support</strong>
              <div style="font-size: 0.8rem;">Dedicated coordinator for every event</div>
            </div>
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
      <h1>Create account</h1>
      <p>Join Sinta and start planning unforgettable events</p>
      
      <div class="social-buttons">
        <button class="social-btn" onclick="alert('Google signup demo')">
          <i class="fab fa-google"></i> Google
        </button>
        <button class="social-btn" onclick="alert('Facebook signup demo')">
          <i class="fab fa-facebook-f"></i> Facebook
        </button>
      </div>
      
      <div class="divider"><span>or sign up with email</span></div>
      
      <?php if (!empty($errors)): ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          <ul style="margin: 0; padding-left: 1rem;">
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="/index.php?route=signup" id="signupForm">
        <input type="hidden" name="action" value="signup">
        <div class="form-row">
          <div class="form-group">
            <label>First name *</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label>Last name *</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label>Email address *</label>
          <input type="email" name="email" id="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
          <span class="error-text" id="emailError"></span>
        </div>
        <div class="form-group">
          <label>Mobile number *</label>
          <input type="tel" name="phone" value="<?= htmlspecialchars($formData['phone'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Birthday *</label>
          <input type="date" name="birthday" value="<?= htmlspecialchars($formData['birthday'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Address *</label>
          <input type="text" name="address" placeholder="Street address, city, province" value="<?= htmlspecialchars($formData['address'] ?? '') ?>" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Password *</label>
            <div style="position: relative;">
              <input type="password" name="password" id="password" required onkeyup="checkPasswordStrength()">
              <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #8A7650;"></i>
            </div>
          </div>
          <div class="form-group">
            <label>Confirm password *</label>
            <div style="position: relative;">
              <input type="password" name="confirm_password" id="confirmPassword" required onkeyup="checkPasswordMatch()">
              <i class="fas fa-eye" id="toggleConfirmPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #8A7650;"></i>
            </div>
            <span class="error-text" id="passwordMatchError"></span>
          </div>
        </div>
        <div class="password-strength" id="passwordStrength">
          <div class="strength-bars">
            <div class="strength-bar"></div>
            <div class="strength-bar"></div>
            <div class="strength-bar"></div>
            <div class="strength-bar"></div>
          </div>
          <span class="strength-text">Password strength</span>
        </div>
        <div class="checkbox">
          <input type="checkbox" id="terms" required>
          <label>I agree to Sinta's <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a> *</label>
        </div>
        <div class="checkbox">
          <input type="checkbox" name="newsletter" checked>
          <label>Send me event inspiration and exclusive offers</label>
        </div>
        <button type="submit" class="btn btn--primary btn--full btn--lg" id="submitBtn">Create Account</button>
      </form>
      
      <div class="auth-footer">
        <p>Already have an account? <a href="/index.php?route=signin">Sign in →</a></p>
      </div>
    </div>
    
  </div>
</div>

<script>
let passwordValid = false;
let termsChecked = false;

function checkPasswordStrength() {
  const password = document.getElementById('password').value;
  const bars = document.querySelectorAll('.strength-bar');
  let strength = 0;
  
  if (password.length >= 8) strength++;
  if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
  if (password.match(/[0-9]/)) strength++;
  if (password.match(/[^a-zA-Z0-9]/)) strength++;
  
  bars.forEach((bar, index) => {
    if (index < strength) {
      bar.classList.add('active');
    } else {
      bar.classList.remove('active');
    }
  });
  
  const texts = ['Very weak', 'Weak', 'Good', 'Strong'];
  document.querySelector('.strength-text').textContent = texts[strength - 1] || 'Password strength';
  
  passwordValid = strength >= 2;
  checkFormValidity();
}

function checkPasswordMatch() {
  const password = document.getElementById('password').value;
  const confirm = document.getElementById('confirmPassword').value;
  const errorSpan = document.getElementById('passwordMatchError');
  
  if (password !== confirm) {
    errorSpan.textContent = 'Passwords do not match';
    return false;
  } else {
    errorSpan.textContent = '';
    return true;
  }
}

function checkEmailAvailability() {
  const email = document.getElementById('email').value;
  const errorSpan = document.getElementById('emailError');
  
  if (email && email.includes('@')) {
    // Optional: Add AJAX check for email availability
    // For now, just clear error
    errorSpan.textContent = '';
  }
}

function checkFormValidity() {
  const termsCheckbox = document.getElementById('terms');
  const submitBtn = document.getElementById('submitBtn');
  const passwordMatch = checkPasswordMatch();
  
  if (termsCheckbox.checked && passwordValid && passwordMatch) {
    submitBtn.disabled = false;
  } else {
    submitBtn.disabled = true;
  }
}

document.getElementById('terms').addEventListener('change', function() {
  checkFormValidity();
});

document.getElementById('password').addEventListener('keyup', function() {
  checkPasswordMatch();
  checkFormValidity();
});

document.getElementById('confirmPassword').addEventListener('keyup', function() {
  checkPasswordMatch();
  checkFormValidity();
});

document.getElementById('email').addEventListener('blur', checkEmailAvailability);

// Initial check
checkFormValidity();

// Prevent form submission if validation fails
document.getElementById('signupForm').addEventListener('submit', function(e) {
  if (!document.getElementById('terms').checked) {
    e.preventDefault();
    alert('Please agree to the Terms of Service and Privacy Policy');
    return false;
  }
  if (!checkPasswordMatch()) {
    e.preventDefault();
    alert('Passwords do not match');
    return false;
  }
  if (document.getElementById('password').value.length < 6) {
    e.preventDefault();
    alert('Password must be at least 6 characters');
    return false;
  }
});

// Password visibility toggle for Password field
document.getElementById('togglePassword').addEventListener('click', function() {
  const passwordInput = document.getElementById('password');
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

// Password visibility toggle for Confirm Password field
document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const icon = this;
  
  if (confirmPasswordInput.type === 'password') {
    confirmPasswordInput.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    confirmPasswordInput.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
});
</script>

</body>
</html>
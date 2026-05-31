<?php
/**
 * Auth Controller
 * Handles authentication operations (login, logout, signup)
 */

if (!defined('ROOT_PATH')) {
    // Check if app folder exists at current level (production) or parent level (local)
    $appDir = dirname(dirname(__DIR__));
    if (is_dir($appDir . '/app')) {
        define('ROOT_PATH', $appDir);
    } else {
        // Go up 3 levels from controllers folder
        define('ROOT_PATH', $appDir);
    }
}
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Display login form
     */
    public function signin() {
        if (isset($_SESSION['user_logged_in']) || isset($_SESSION['admin_logged_in'])) {
            header('Location: /index.php?route=' . (isset($_SESSION['admin_logged_in']) ? 'admin-dashboard' : 'homepage'));
            exit;
        }
        
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        
        require ROOT_PATH . '/app/views/user/signin.php';
    }
    
/**
     * Handle login form submission
     */
    public function handleLogin($email, $password) {
        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Email and password are required';
            return false;
        }
        
        $user = $this->userModel->authenticate($email, $password);
        
if ($user) {
            // Check if 2FA is enabled - but DON'T force it immediately
            // Allow user to login normally, they can verify 2FA from their settings if they want
            $twoFactorEnabled = $this->userModel->isTwoFactorEnabled($user['user_id']);
            
            if ($twoFactorEnabled) {
                // Store pending 2FA verification flag but allow login anyway
                // User can verify from profile settings if they choose
                $_SESSION['pending_2fa_verification'] = true;
            }
            
            // Complete login normally - 2FA is now optional
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['user_last_name'] = $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['user_birthday'] = $user['birthday'];
            $_SESSION['user_address'] = $user['address'];
            $_SESSION['user_avatar'] = $user['image'] ?? null;
            $_SESSION['user_role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
                header('Location: /index.php?route=admin-dashboard');
            } else {
                $_SESSION['user_logged_in'] = true;
                header('Location: /index.php?route=homepage');
            }
            exit;
        } else {
            $_SESSION['login_error'] = 'Invalid email or password';
            return false;
        }
    }
    
/**
     * Display 2FA verification page (optional - for manual verification)
     */
    public function verify2FA() {
        // Allow access to verify 2FA optionally - either from login flow OR from profile settings
        $error = $_SESSION['two_fa_error'] ?? null;
        unset($_SESSION['two_fa_error']);
        
        require ROOT_PATH . '/app/views/user/verify-2fa.php';
    }
    
/**
     * Handle 2FA verification (from profile/settings when user chooses to verify)
     */
    public function handleVerify2FA($code) {
        // Must be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /index.php?route=signin');
            exit;
        }
        
        if (empty($code)) {
            $_SESSION['two_fa_error'] = 'Please enter your authentication code';
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Verify the 2FA code
        if ($this->userModel->verifyTwoFactorCode($userId, $code)) {
            // Code is valid - clear pending flag
            unset($_SESSION['pending_2fa_verification']);
            
            $_SESSION['two_fa_success'] = '2FA verification successful! You can now use your authenticator app.';
            header('Location: /index.php?route=profile&tab=security');
            exit;
        } else {
            $_SESSION['two_fa_error'] = 'Invalid authentication code. Please try again.';
            return false;
        }
    }
    
    /**
     * Setup 2FA - generate QR code for user
     */
    public function setup2FA() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /index.php?route=signin');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Check if already enabled
        if ($this->userModel->isTwoFactorEnabled($userId)) {
            $_SESSION['two_fa_error'] = 'Two-factor authentication is already enabled.';
            header('Location: /index.php?route=profile&tab=security');
            exit;
        }
        
// Generate new secret
        $secret = $this->userModel->generateTwoFactorSecret();
        
        // Store temporarily until verified
        $_SESSION['temp_2fa_secret'] = $secret;
        
        // Get user email for the QR code
        $user = $this->userModel->findById($userId);
        $email = $user['email'];
        
        // Generate QR code URL (Google Authenticator format)
        $qrCodeUrl = 'otpauth://totp/Sinta:' . urlencode($email) . '?secret=' . $secret . '&issuer=Sinta';
        
        require ROOT_PATH . '/app/views/user/setup-2fa.php';
    }
    
    /**
     * Enable 2FA after verification
     */
    public function enable2FA($code) {
        if (!isset($_SESSION['temp_2fa_secret']) || !isset($_SESSION['user_id'])) {
            header('Location: /index.php?route=profile&tab=security');
            exit;
        }
        
        if (empty($code)) {
            $_SESSION['two_fa_error'] = 'Please enter your authentication code';
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        $secret = $_SESSION['temp_2fa_secret'];
        
        // Verify the code
        if ($this->userModel->verifyTwoFactorCode($userId, $code)) {
            // Enable 2FA
            $this->userModel->enableTwoFactor($userId, $secret);
            
            // Clear temporary session
            unset($_SESSION['temp_2fa_secret']);
            
            $_SESSION['two_fa_success'] = 'Two-factor authentication has been enabled successfully!';
            header('Location: /index.php?route=profile&tab=security');
            exit;
        } else {
            $_SESSION['two_fa_error'] = 'Invalid authentication code. Please try again.';
            return false;
        }
    }
    
    /**
     * Disable 2FA
     */
    public function disable2FA() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /index.php?route=signin');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        if ($this->userModel->disableTwoFactor($userId)) {
            $_SESSION['two_fa_success'] = 'Two-factor authentication has been disabled.';
        } else {
            $_SESSION['two_fa_error'] = 'Failed to disable two-factor authentication.';
        }
        
        header('Location: /index.php?route=profile&tab=security');
        exit;
    }
    
    /**
     * Display signup form
     */
    public function signup() {
        if (isset($_SESSION['user_logged_in']) || isset($_SESSION['admin_logged_in'])) {
            header('Location: /index.php?route=' . (isset($_SESSION['admin_logged_in']) ? 'admin-dashboard' : 'homepage'));
            exit;
        }
        
        $error = $_SESSION['signup_error'] ?? null;
        $success = $_SESSION['signup_success'] ?? null;
        unset($_SESSION['signup_error']);
        unset($_SESSION['signup_success']);
        
        require ROOT_PATH . '/app/views/user/signup.php';
    }
    
    /**
     * Handle signup form submission
     */
    public function handleSignup($data) {
        $errors = [];
        
        // Validation
        if (empty($data['first_name'])) $errors[] = 'First name is required';
        if (empty($data['last_name'])) $errors[] = 'Last name is required';
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        if (empty($data['phone'])) $errors[] = 'Mobile number is required';
        if (empty($data['birthday'])) $errors[] = 'Birthday is required';
        if (empty($data['address'])) $errors[] = 'Address is required';
        if (empty($data['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        if (($data['password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            $_SESSION['signup_error'] = implode('<br>', $errors);
            return false;
        }
        
        // Check if email already exists
        if ($this->userModel->emailExists($data['email'])) {
            $_SESSION['signup_error'] = 'Email address is already registered. Please use a different email or sign in.';
            return false;
        }
        
        // Create new user
        $userId = $this->userModel->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'birthday' => $data['birthday'],
            'address' => $data['address'],
            'password' => $data['password'],
            'role' => 'user'
        ]);
        
        if ($userId) {
            $_SESSION['signup_success'] = 'Account created successfully! Please sign in with your credentials.';
            header('Location: /index.php?route=signin');
            exit;
        } else {
            $_SESSION['signup_error'] = 'Failed to create account. Please try again.';
            return false;
        }
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        session_destroy();
        header('Location: /index.php?route=landing');
        exit;
    }
}
?>
<?php
/**
 * Auth Controller
 * Handles authentication operations (login, logout, signup)
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
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
            header('Location: /SINTA/public/index.php?route=' . (isset($_SESSION['admin_logged_in']) ? 'admin-dashboard' : 'homepage'));
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
                header('Location: /SINTA/public/index.php?route=admin-dashboard');
            } else {
                $_SESSION['user_logged_in'] = true;
                header('Location: /SINTA/public/index.php?route=homepage');
            }
            exit;
        } else {
            $_SESSION['login_error'] = 'Invalid email or password';
            return false;
        }
    }
    
    /**
     * Display signup form
     */
    public function signup() {
        if (isset($_SESSION['user_logged_in']) || isset($_SESSION['admin_logged_in'])) {
            header('Location: /SINTA/public/index.php?route=' . (isset($_SESSION['admin_logged_in']) ? 'admin-dashboard' : 'homepage'));
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
            header('Location: /SINTA/public/index.php?route=signin');
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
        header('Location: /SINTA/public/index.php?route=landing');
        exit;
    }
}
?>
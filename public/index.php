<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Define base path
define('ROOT_PATH', dirname(__DIR__));
define('MODEL_PATH', ROOT_PATH . '/models/');
define('VIEW_PATH', ROOT_PATH . '/app/views/');

// Get the route parameter
$route = isset($_GET['route']) ? $_GET['route'] : 'landing';

// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    require_once ROOT_PATH . '/app/models/User.php';
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // User login from database (checks both admin and regular users)
    $userModel = new User();
    $user = $userModel->authenticate($email, $password);
    
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
        
        // Check if user is admin
        if ($user['role'] === 'admin') {
            $_SESSION['admin_logged_in'] = true;
            header('Location: /SINTA/public/index.php?route=admin-dashboard');
            exit;
        } else {
            // Regular user
            $_SESSION['user_logged_in'] = true;
            header('Location: /SINTA/public/index.php?route=homepage');
            exit;
        }
    } else {
        $_SESSION['login_error'] = 'Invalid email or password';
        header('Location: /SINTA/public/index.php?route=signin');
        exit;
    }
}

// Handle signup
// Handle signup
// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    error_log("=== SIGNUP POST RECEIVED ===");
    error_log("POST data: " . print_r($_POST, true));
    
    require_once ROOT_PATH . '/app/models/User.php';
    
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    $errors = [];
    
    // Validation
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($phone)) $errors[] = 'Mobile number is required';
    if (empty($birthday)) $errors[] = 'Birthday is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($password)) $errors[] = 'Password is required';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    
    error_log("Validation errors: " . print_r($errors, true));
    
    if (empty($errors)) {
        try {
            $userModel = new User();
            
            // Check if email already exists
            if ($userModel->emailExists($email)) {
                $errors[] = 'Email address is already registered. Please use a different email or sign in.';
                error_log("Email already exists: " . $email);
            } else {
                // Create new user
                $userId = $userModel->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                    'birthday' => $birthday,
                    'address' => $address,
                    'password' => $password,
                    'role' => 'user'
                ]);
                
                error_log("User creation result - userId: " . ($userId ? $userId : 'false'));
                
                if ($userId) {
                    // Success! Store success message and redirect to signin
                    $_SESSION['signup_success'] = 'Account created successfully! Please sign in with your email and password.';
                    
                    error_log("User created successfully, redirecting to signin page");
                    
                    // Redirect to signin page
                    header('Location: /SINTA/public/index.php?route=signin');
                    exit;
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                    error_log("User creation failed - check database");
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            error_log("Exception: " . $e->getMessage());
        }
    }
    
    // If we got here, there were errors
    error_log("Errors occurred, redirecting back to signup page");
    $_SESSION['signup_errors'] = $errors;
    $_SESSION['signup_form_data'] = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'birthday' => $birthday,
        'address' => $address
    ];
    header('Location: /SINTA/public/index.php?route=signup');
    exit;
}


if ($route === 'admin-logout') {
    session_destroy();
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}

// Simple routing with correct file names
switch($route) {
    case 'landing':
        include VIEW_PATH . '/landing/landing.php';
        break;
    case 'signin':
        include VIEW_PATH . '/user/signin.php';
        break;
    case 'signup':
        include VIEW_PATH . '/user/signup.php';
        break;
    case 'homepage':
        if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
            header('Location: /SINTA/public/index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/homepage.php';
        break;
    case 'occasions':
        include VIEW_PATH . '/user/occasions.php';
        break;
    case 'packages':
        include VIEW_PATH . '/user/packages.php';
        break;
    case 'customize':
        include VIEW_PATH . '/user/customize.php';
        break;
    case 'checkout':
        include VIEW_PATH . '/user/checkout.php';
        break;
    case 'plans':
        include VIEW_PATH . '/user/plans.php';
        break;
    case 'event-detail':
        include VIEW_PATH . '/user/event-detail.php';
        break;
    case 'messages':
        include VIEW_PATH . '/user/messages.php';
        break;
    case 'profile':
        include VIEW_PATH . '/user/profile.php';
        break;
    case 'about':
        include VIEW_PATH . '/user/about.php';
        break;
    case 'contact':
        include VIEW_PATH . '/user/contact.php';
        break;
    case 'admin-dashboard':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /SINTA/public/index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-dashboard.php';
        break;
    case 'admin-bookings':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /SINTA/public/index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-bookings.php';
        break;
    case 'admin-packages':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /SINTA/public/index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-manage-packages.php';
        break;
    case 'admin-testimonials':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /SINTA/public/index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-testimonials.php';
        break;
    case 'admin-messages':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /SINTA/public/index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-messages-real.php';
        break;
    default:
        include VIEW_PATH . '/landing/landing.php';
}
?>
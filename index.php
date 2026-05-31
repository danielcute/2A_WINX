<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Define base path - smart detection for different deployment scenarios
// When deployed to public_html, this file will be at root
// When in local dev, it's in public/ folder

// Primary detection: if we're in the public folder
if (basename(__DIR__) === 'public' || is_dir(__DIR__ . '/app')) {
    define('ROOT_PATH', is_dir(__DIR__ . '/app') ? __DIR__ : dirname(__DIR__));
} else {
    // We're at the true root (public_html)
    define('ROOT_PATH', __DIR__);
}

define('MODEL_PATH', ROOT_PATH . '/app/models');
define('VIEW_PATH', ROOT_PATH . '/app/views');

// Smart asset path detection
if (is_dir(ROOT_PATH . '/assets')) {
    define('ASSET_PATH', ROOT_PATH . '/assets');
} else {
    define('ASSET_PATH', ROOT_PATH . '/public/assets');
}

// Dynamically determine BASE_URL for better deployment support
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$base_url = $protocol . $host . ($script_name === '/' || $script_name === '\\' ? '' : $script_name);

// Remove /public from base URL if present
if (strpos($base_url, '/public') !== false) {
    $base_url = str_replace('/public', '', $base_url);
}

define('BASE_URL', rtrim($base_url, '/'));

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
        // Check if user has 2FA enabled - if so, REQUIRE verification before login
        $twoFactorEnabled = $userModel->isTwoFactorEnabled($user['user_id']);
        
        if ($twoFactorEnabled) {
            // 2FA is enabled - require verification before allowing login
            // Store temp user info until 2FA is verified
            $_SESSION['temp_user_id'] = $user['user_id'];
            $_SESSION['temp_user_name'] = $user['first_name'];
            $_SESSION['temp_user_last_name'] = $user['last_name'];
            $_SESSION['temp_email'] = $user['email'];
            $_SESSION['temp_role'] = $user['role'];
            
            // Redirect to 2FA verification page
            header('Location: ' . BASE_URL . '/index.php?route=verify-2fa');
            exit;
        } else {
            // No 2FA - proceed with login
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['user_last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect to appropriate dashboard
            $redirectUrl = ($user['role'] === 'admin') ? '/index.php?route=admin-dashboard' : '/index.php?route=homepage';
            header('Location: ' . BASE_URL . $redirectUrl);
            exit;
        }
    } else {
        $_SESSION['login_error'] = 'Invalid email or password';
        header('Location: ' . BASE_URL . '/index.php?route=signin');
        exit;
    }
}

// Handle 2FA verification
if ($route === 'verify-2fa' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once ROOT_PATH . '/app/models/User.php';
    
    $code = $_POST['code'] ?? '';
    $userModel = new User();
    
    // Verify the 2FA code
    if ($userModel->verify2FA($_SESSION['temp_user_id'], $code)) {
        // Code verified - complete the login
        $_SESSION['user_id'] = $_SESSION['temp_user_id'];
        $_SESSION['user_name'] = $_SESSION['temp_user_name'];
        $_SESSION['user_last_name'] = $_SESSION['temp_user_last_name'];
        $_SESSION['email'] = $_SESSION['temp_email'];
        $_SESSION['role'] = $_SESSION['temp_role'];
        
        // Clear temp session vars
        unset($_SESSION['temp_user_id'], $_SESSION['temp_user_name'], $_SESSION['temp_user_last_name'], $_SESSION['temp_email'], $_SESSION['temp_role']);
        
        // Redirect to appropriate dashboard
        $redirectUrl = ($_SESSION['role'] === 'admin') ? '/index.php?route=admin-dashboard' : '/index.php?route=homepage';
        header('Location: ' . BASE_URL . $redirectUrl);
        exit;
    } else {
        $_SESSION['2fa_error'] = 'Invalid verification code';
    }
}

// Route handling - determine which view to load
switch ($route) {
    // ── PUBLIC ROUTES (no auth required) ──
    case 'landing':
        require ROOT_PATH . '/app/views/landing.php';
        break;
    
    case 'signin':
        require ROOT_PATH . '/app/views/user/signin.php';
        break;
    
    case 'signup':
        require ROOT_PATH . '/app/views/user/signup.php';
        break;
    
    case 'verify-2fa':
        require ROOT_PATH . '/app/views/user/verify-2fa.php';
        break;
    
    case 'forgot-password':
        require ROOT_PATH . '/app/views/user/forgot-password.php';
        break;
    
    case 'reset-password':
        require ROOT_PATH . '/app/views/user/reset-password.php';
        break;
    
    // ── USER PROTECTED ROUTES ──
    case 'homepage':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/homepage.php';
        break;
    
    case 'packages':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/packages.php';
        break;
    
    case 'plan-detail':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/plan-detail.php';
        break;
    
    case 'customize':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/customize.php';
        break;
    
    case 'checkout':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/checkout.php';
        break;
    
    case 'wardrobe':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/wardrobe.php';
        break;
    
    case 'profile':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/profile.php';
        break;
    
    case 'feedback':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/feedback.php';
        break;
    
    case 'messages':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/messages.php';
        break;
    
    case 'occasions':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/occasions.php';
        break;
    
    case 'event-detail':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/event-detail.php';
        break;
    
    // ── ADMIN PROTECTED ROUTES ──
    case 'admin-dashboard':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-dashboard.php';
        break;
    
    case 'admin-profile':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-profile.php';
        break;
    
    case 'setup-admin':
        require ROOT_PATH . '/app/views/admin/setup-admin.php';
        break;
    
    case 'admin-packages':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-packages.php';
        break;
    
    case 'admin-manage-packages':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-manage-packages.php';
        break;
    
    case 'admin-customize':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-customize.php';
        break;
    
    case 'admin-customize-add':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-customize-add.php';
        break;
    
    case 'admin-customize-edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-customize-edit.php';
        break;
    
    case 'admin-occasions':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-occasions.php';
        break;
    
    case 'admin-notifications':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/AdminNotificationController.php';
        (new AdminNotificationController())->handle();
        break;

    case 'admin-wardrobe':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        (new WardrobeController())->index();
        break;

    case 'admin-wardrobe-add':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new WardrobeController())->addSubmit();
        } else {
            (new WardrobeController())->addForm();
        }
        break;

    case 'admin-wardrobe-edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        (new WardrobeController())->editForm();
        break;

    case 'admin-wardrobe-update':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        (new WardrobeController())->updateSubmit();
        break;

    case 'admin-wardrobe-delete':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        (new WardrobeController())->deleteSubmit();
        break;

    case 'admin-wardrobe-get':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        (new WardrobeController())->getJson();
        break;

    case 'admin-wardrobe-image':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(401);
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        (new WardrobeController())->serveImage();
        break;

    case 'admin-wardrobe-selections':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        (new WardrobeController())->selections();
        break;
    
    case 'admin-bookings':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-bookings.php';
        break;
    
    case 'admin-feedback':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-feedback.php';
        break;
    
    case 'admin-messages':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-messages.php';
        break;
    
    case 'admin-testimonials':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-testimonials.php';
        break;
    
    // ── 404 DEFAULT ──
    default:
        http_response_code(404);
        echo "<h1>Page Not Found</h1>";
        echo "<p>The page you requested does not exist.</p>";
        echo "<p><a href='" . BASE_URL . "/index.php?route=landing'>Return to home</a></p>";
        break;
}
?>
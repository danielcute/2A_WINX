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
$route = isset($_GET['route']) ? trim($_GET['route']) : 'landing';

// Handle POST signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    require_once ROOT_PATH . '/app/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->handleSignup($_POST);
    // handleSignup does redirect on success; on failure it redirects back to signup
}

// Continue with existing POST login flow
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
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['user_birthday'] = $user['birthday'] ?? '';
            $_SESSION['user_address'] = $user['address'] ?? '';
            $_SESSION['user_avatar'] = $user['image'] ?? null;
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
            } else {
                $_SESSION['user_logged_in'] = true;
            }
            
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
    if ($userModel->verifyTwoFactorCode($_SESSION['temp_user_id'], $code)) {

        // Code verified - complete the login
        $_SESSION['user_id'] = $_SESSION['temp_user_id'];
        $_SESSION['user_name'] = $_SESSION['temp_user_name'];
        $_SESSION['user_last_name'] = $_SESSION['temp_user_last_name'];
        $_SESSION['user_email'] = $_SESSION['temp_email'];
        $_SESSION['email'] = $_SESSION['temp_email'];
        $_SESSION['user_role'] = $_SESSION['temp_role'];
        $_SESSION['role'] = $_SESSION['temp_role'];
        
        // Set role-specific flags
        if ($_SESSION['role'] === 'admin') {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $_SESSION['user_logged_in'] = true;
        }
        
        // Clear temp session vars
        unset($_SESSION['temp_user_id'], $_SESSION['temp_user_name'], $_SESSION['temp_user_last_name'], $_SESSION['temp_email'], $_SESSION['temp_role']);
        
        // Redirect to appropriate dashboard
        $redirectUrl = ($_SESSION['role'] === 'admin') ? '/index.php?route=admin-dashboard' : '/index.php?route=homepage';
        header('Location: ' . BASE_URL . $redirectUrl);
        exit;
    } else {
        $_SESSION['two_fa_error'] = 'Invalid verification code';
    }
}

// Handle API endpoint routing - if route starts with 'api-', forward to the API file
if (strpos($route, 'api-') === 0) {
    $apiFile = ROOT_PATH . '/public/' . $route . '.php';
    if (file_exists($apiFile)) {
        require_once $apiFile;
        exit;
    }
}

// Route handling - determine which view to load
switch ($route) {
    // ── PUBLIC ROUTES (no auth required) ──
    case 'landing':
        require ROOT_PATH . '/app/views/landing/landing.php';
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
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/homepage.php';
        break;

    case 'plans':
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/PlanController.php';
        (new PlanController())->index();
        break;
    
    case 'packages':
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/packages.php';
        break;
    
    case 'plan-detail':
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        // Redirect to event-detail with the same id parameter
        $planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        header('Location: ' . BASE_URL . '/index.php?route=event-detail&id=' . $planId);
        exit;
        break;
    
    case 'customize':
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/customize.php';
        break;
    
    case 'checkout':
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/user/checkout.php';
        break;
    
    case 'setup-2fa':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        // Handle POST (enable 2FA)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'enable_2fa') {
            require_once ROOT_PATH . '/app/models/User.php';
            $code = $_POST['code'] ?? '';
            $userModel = new User();
            $tempSecret = $_SESSION['temp_2fa_secret'] ?? null;
            if (empty($code)) {
                $_SESSION['two_fa_error'] = 'Please enter your verification code';
            } elseif (!$tempSecret) {
                $_SESSION['two_fa_error'] = 'No 2FA secret found. Please refresh and try again.';
            } elseif ($userModel->verifyTwoFactorCode($_SESSION['user_id'], $code, $tempSecret)) {
                $userModel->enableTwoFactor($_SESSION['user_id'], $tempSecret);
                unset($_SESSION['temp_2fa_secret']);
                $_SESSION['two_fa_success'] = 'Two-factor authentication has been enabled successfully!';
                header('Location: ' . BASE_URL . '/index.php?route=profile&tab=security');
                exit;
            } else {
                $_SESSION['two_fa_error'] = 'Invalid authentication code. Please try again.';
            }
            header('Location: ' . BASE_URL . '/index.php?route=setup-2fa');
            exit;
        }
        // GET: generate secret and show setup page
        require_once ROOT_PATH . '/app/models/User.php';
        $userModel = new User();
        if ($userModel->isTwoFactorEnabled($_SESSION['user_id'])) {
            $_SESSION['two_fa_error'] = 'Two-factor authentication is already enabled.';
            header('Location: ' . BASE_URL . '/index.php?route=profile&tab=security');
            exit;
        }
        if (empty($_SESSION['temp_2fa_secret'])) {
            $_SESSION['temp_2fa_secret'] = $userModel->generateTwoFactorSecret();
        }
        require ROOT_PATH . '/app/views/user/setup-2fa.php';
        break;

    case 'disable-2fa':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/models/User.php';
        $userModel = new User();
        if ($userModel->disableTwoFactor($_SESSION['user_id'])) {
            $_SESSION['two_fa_success'] = 'Two-factor authentication has been disabled.';
        } else {
            $_SESSION['two_fa_error'] = 'Failed to disable two-factor authentication.';
        }
        header('Location: ' . BASE_URL . '/index.php?route=profile&tab=security');
        exit;
        break;

    case 'delete-plan':
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/PlanController.php';
        (new PlanController())->delete();
        break;

    case 'checkout-submit':
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CheckoutController.php';
        (new CheckoutController())->submit();
        break;

    case 'wardrobe':
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
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
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
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
        if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin' && empty($_SESSION['admin_logged_in']))) {

            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-dashboard.php';
        break;
    
    case 'admin-profile':
        if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin' && empty($_SESSION['admin_logged_in']))) {

            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-profile.php';
        break;
    
    case 'setup-admin':
        require ROOT_PATH . '/app/views/admin/setup-admin.php';
        break;
    
    case 'admin-packages':
        if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin' && empty($_SESSION['admin_logged_in']))) {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require ROOT_PATH . '/app/views/admin/admin-packages.php';
        break;
    
    case 'admin-manage-packages':
        if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin' && empty($_SESSION['admin_logged_in']))) {
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
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        (new CustomizeController())->listAll();
        break;
    
    case 'admin-customize-add':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        (new CustomizeController())->addForm();
        break;

    case 'admin-customize-create':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        (new CustomizeController())->create();
        break;
    
    case 'admin-customize-edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        (new CustomizeController())->editForm();
        break;

    case 'admin-customize-update':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        (new CustomizeController())->update();
        break;

    case 'admin-customize-delete':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        (new CustomizeController())->delete();
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

    case 'wardrobe-get-json':
    case 'admin-wardrobe-get':
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        (new WardrobeController())->getJson();
        break;

    case 'wardrobe-image':
    case 'admin-wardrobe-image':
        if (!isset($_SESSION['user_id'])) {
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
    
    case 'logout':
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?route=landing');
        exit;
        break;

    case 'admin-logout':
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?route=signin');
        exit;

    // ── 404 DEFAULT ──
    default:
        http_response_code(404);
        echo "<h1>Page Not Found</h1>";
        echo "<p>The page you requested does not exist.</p>";
        echo "<p><a href='" . BASE_URL . "/index.php?route=landing'>Return to home</a></p>";
        break;
}
?>
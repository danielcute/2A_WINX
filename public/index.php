<?php
error_reporting(E_ALL);
// Only display errors on localhost to prevent breaking JSON responses in production
$is_local = (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));
ini_set('display_errors', $is_local ? 1 : 0);
session_start();

// Define base path - with better mobile/deployment support
// Check if app folder exists at current level (production) or parent level (local)
if (is_dir(__DIR__ . '/app')) {
    define('ROOT_PATH', __DIR__);
} else {
    define('ROOT_PATH', dirname(__DIR__));
}
define('MODEL_PATH', ROOT_PATH . '/app/models');
define('VIEW_PATH', ROOT_PATH . '/app/views');
define('ASSET_PATH', ROOT_PATH . '/public/assets');

// Dynamically determine BASE_URL for better deployment support
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$base_url = $protocol . $host . ($script_name === '/' ? '' : $script_name);
if (strpos($base_url, '/public') !== false) {
    $base_url = str_replace('/public', '', $base_url);
}
define('BASE_URL', rtrim($base_url, '/'));

// Get the route parameter
$route = isset($_GET['route']) ? trim($_GET['route']) : 'landing';

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
            $_SESSION['temp_user_email'] = $user['email'];
            $_SESSION['temp_user_phone'] = $user['phone'];
            $_SESSION['temp_user_birthday'] = $user['birthday'];
            $_SESSION['temp_user_address'] = $user['address'];
            $_SESSION['temp_user_avatar'] = $user['image'] ?? null;
            $_SESSION['temp_user_role'] = $user['role'];
            $_SESSION['require_2fa'] = true;
            
            // Redirect to 2FA verification page
            header('Location: index.php?route=verify-2fa');
            exit;
        } else {
            // No 2FA - complete login normally
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
                header('Location: index.php?route=admin-dashboard');
                exit;
            } else {
                $_SESSION['user_logged_in'] = true;
                header('Location: index.php?route=homepage');
                exit;
            }
        }
    } else {
        $_SESSION['login_error'] = 'Invalid email or password';
        header('Location: index.php?route=signin');
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
                    header('Location: index.php?route=signin');
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
    header('Location: index.php?route=signup');
    exit;
}

// Handle feedback and admin feedback POST requests (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($route === 'feedback' || $route === 'admin-feedback')) {
    header('Content-Type: application/json; charset=utf-8');
    
    require_once ROOT_PATH . '/app/models/Feedback.php';
    $feedbackModel = new Feedback();
    
    try {
        if ($route === 'feedback') {
            // User feedback handling
            if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $action = $_POST['action'] ?? '';
            $userId = $_SESSION['user_id'];
            
            if ($action === 'submit_feedback') {
                $data = [
                    'user_id' => $userId,
                    'subject' => $_POST['subject'] ?? '',
                    'message' => $_POST['message'] ?? '',
                    'rating' => $_POST['rating'] ?? 0
                ];
                
                if (empty($data['subject']) || empty($data['message'])) {
                    echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
                } else {
                    $feedbackId = $feedbackModel->create($data);
                    if ($feedbackId) {
                        echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully', 'feedback_id' => $feedbackId]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to submit feedback']);
                    }
                }
            } elseif ($action === 'add_reply') {
                $feedbackId = (int)($_POST['feedback_id'] ?? 0);
                $message = $_POST['message'] ?? '';
                
                if (!$feedbackId || empty($message)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid reply data']);
                } else {
                    $feedback = $feedbackModel->findById($feedbackId);
                    if (!$feedback || $feedback['user_id'] != $userId) {
                        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                    } else {
                        $replyId = $feedbackModel->addUserReply($feedbackId, $userId, $message);
                        if ($replyId) {
                            echo json_encode(['success' => true, 'message' => 'Reply added successfully']);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Failed to add reply']);
                        }
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } elseif ($route === 'admin-feedback') {
            // Admin feedback handling
            if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $action = $_POST['action'] ?? '';
            $adminId = $_SESSION['user_id'];
            
            if ($action === 'add_reply') {
                $feedbackId = (int)($_POST['feedback_id'] ?? 0);
                $message = $_POST['message'] ?? '';
                
                if (!$feedbackId || empty($message)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid data']);
                } else {
                    $replyId = $feedbackModel->addAdminReply($feedbackId, $adminId, $message);
                    if ($replyId) {
                        echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to send reply']);
                    }
                }
            } elseif ($action === 'update_status') {
                $feedbackId = (int)($_POST['feedback_id'] ?? 0);
                $status = $_POST['status'] ?? '';
                
                if (!$feedbackId || empty($status)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid data']);
                } else {
                    if ($feedbackModel->updateStatus($feedbackId, $status)) {
                        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
                    }
                }
            } elseif ($action === 'delete') {
                $feedbackId = (int)($_POST['feedback_id'] ?? 0);
                
                if (!$feedbackId) {
                    echo json_encode(['success' => false, 'message' => 'Invalid feedback ID']);
                } else {
                    if ($feedbackModel->delete($feedbackId)) {
                        echo json_encode(['success' => true, 'message' => 'Feedback deleted successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to delete feedback']);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}


if ($route === 'admin-logout') {
    session_destroy();
    header('Location: /index.php?route=signin');
    exit;
}

// Setup admin account route
if ($route === 'setup-admin') {
    ob_end_clean(); // Clear any buffers
    $setupFile = __DIR__ . '/setup-admin.php';
    if (file_exists($setupFile)) {
        include $setupFile;
        exit;
    } else {
        echo "Setup file not found at: " . $setupFile;
        exit;
    }
}

// Simple routing with correct file names
switch($route) {
    case 'landing':
        $landingFile = VIEW_PATH . '/landing/landing.php';
        error_log("Trying to include: " . $landingFile);
        if (!file_exists($landingFile)) {
            error_log("File does not exist: " . $landingFile);
        }
        include $landingFile;
        break;
    case 'signin':
        include VIEW_PATH . '/user/signin.php';
        break;
    case 'signup':
        include VIEW_PATH . '/user/signup.php';
        break;
    case 'verify-2fa':
        // Handle 2FA verification
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_2fa') {
            require_once ROOT_PATH . '/app/models/User.php';
            $userModel = new User();
            
            $code = $_POST['code'] ?? '';
            $setupKey = $_POST['setup_key'] ?? '';
            $userId = $_SESSION['temp_user_id'] ?? null;
            
            if (!$userId) {
                header('Location: index.php?route=signin');
                exit;
            }
            
            $verified = false;
            
            // Check if using 6-digit code
            if (!empty($code)) {
                if ($userModel->verifyTwoFactorCode($userId, $code)) {
                    $verified = true;
                }
            }
            // Check if using manual setup key
            elseif (!empty($setupKey)) {
                $savedSecret = $userModel->getTwoFactorSecret($userId);
                if ($savedSecret && strtoupper(trim($setupKey)) === strtoupper(trim($savedSecret))) {
                    $verified = true;
                }
            }
            
            if (empty($code) && empty($setupKey)) {
                $_SESSION['two_fa_error'] = 'Please enter your authentication code or setup key';
                include VIEW_PATH . '/user/verify-2fa.php';
                exit;
            }
            
            // Verify the 2FA code or key
            if ($verified) {
                // Code/key is valid - complete the login
                $_SESSION['user_id'] = $_SESSION['temp_user_id'];
                $_SESSION['user_name'] = $_SESSION['temp_user_name'];
                $_SESSION['user_last_name'] = $_SESSION['temp_user_last_name'];
                $_SESSION['user_email'] = $_SESSION['temp_user_email'];
                $_SESSION['user_phone'] = $_SESSION['temp_user_phone'];
                $_SESSION['user_birthday'] = $_SESSION['temp_user_birthday'];
                $_SESSION['user_address'] = $_SESSION['temp_user_address'];
                $_SESSION['user_avatar'] = $_SESSION['temp_user_avatar'];
                $_SESSION['user_role'] = $_SESSION['temp_user_role'];
                
                // Store user role temporarily before clearing
                $userRole = $_SESSION['temp_user_role'];
                
                // Clear temporary session
                unset($_SESSION['temp_user_id']);
                unset($_SESSION['temp_user_name']);
                unset($_SESSION['temp_user_last_name']);
                unset($_SESSION['temp_user_email']);
                unset($_SESSION['temp_user_phone']);
                unset($_SESSION['temp_user_birthday']);
                unset($_SESSION['temp_user_address']);
                unset($_SESSION['temp_user_avatar']);
                unset($_SESSION['temp_user_role']);
                unset($_SESSION['require_2fa']);
                
                // Redirect based on user role
                if ($userRole === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                    header('Location: index.php?route=admin-dashboard');
                } else {
                    $_SESSION['user_logged_in'] = true;
                    header('Location: index.php?route=homepage');
                }
                exit;
            } else {
                $_SESSION['two_fa_error'] = 'Invalid authentication code. Please try again.';
                include VIEW_PATH . '/user/verify-2fa.php';
                exit;
            }
        }
        
        // Show 2FA verification form
        if (!isset($_SESSION['require_2fa']) || !$_SESSION['require_2fa']) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/verify-2fa.php';
        break;
    case 'setup-2fa':
        // Handle 2FA setup and enable
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enable_2fa') {
            require_once ROOT_PATH . '/app/models/User.php';
            $userModel = new User();
            
            $code = $_POST['code'] ?? '';
            $userId = $_SESSION['user_id'] ?? null;
            $secret = $_SESSION['temp_2fa_secret'] ?? null;
            
            if (!$userId || !$secret) {
                header('Location: index.php?route=profile&tab=security');
                exit;
            }
            
            if (empty($code)) {
                $_SESSION['two_fa_error'] = 'Please enter your authentication code';
                include VIEW_PATH . '/user/setup-2fa.php';
                exit;
            }
            
            // Verify the code BEFORE enabling - pass the temporary secret
            if ($userModel->verifyTwoFactorCode($userId, $code, $secret)) {
                // Enable 2FA
                $userModel->enableTwoFactor($userId, $secret);
                
                // Clear temporary session
                unset($_SESSION['temp_2fa_secret']);
                
                $_SESSION['two_fa_success'] = 'Two-factor authentication has been enabled successfully!';
                header('Location: index.php?route=profile&tab=security');
                exit;
            } else {
                $_SESSION['two_fa_error'] = 'Invalid authentication code. Please try again.';
                include VIEW_PATH . '/user/setup-2fa.php';
                exit;
            }
        }
        
        // Show 2FA setup form - only for logged in users
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        
        // Check if already enabled
        require_once ROOT_PATH . '/app/models/User.php';
        $userModel = new User();
        $twoFactorEnabled = $userModel->isTwoFactorEnabled($_SESSION['user_id']);
        
        if ($twoFactorEnabled) {
            $_SESSION['two_fa_error'] = 'Two-factor authentication is already enabled.';
            header('Location: index.php?route=profile&tab=security');
            exit;
        }
        
        // Generate new secret if not already in session
        if (!isset($_SESSION['temp_2fa_secret'])) {
            $secret = $userModel->generateTwoFactorSecret();
            $_SESSION['temp_2fa_secret'] = $secret;
        }
        
        include VIEW_PATH . '/user/setup-2fa.php';
        break;
    case 'disable-2fa':
        // Handle 2FA disable
        require_once ROOT_PATH . '/app/models/User.php';
        $userModel = new User();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        if ($userModel->disableTwoFactor($userId)) {
            $_SESSION['two_fa_success'] = 'Two-factor authentication has been disabled.';
        } else {
            $_SESSION['two_fa_error'] = 'Failed to disable two-factor authentication.';
        }
        
        header('Location: index.php?route=profile&tab=security');
        exit;
    case 'homepage':
        if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/homepage.php';
        break;
    case 'occasions':
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/occasions.php';
        break;
    case 'packages':
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/packages.php';
        break;
    case 'customize':
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/customize.php';
        break;
    case 'checkout':
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CheckoutController.php';
        $controller = new CheckoutController();
        $controller->index();
        break;
    case 'checkout-submit':
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CheckoutController.php';
        $controller = new CheckoutController();
        $controller->submit();
        break;
    case 'plans':
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/PlanController.php';
        $controller = new PlanController();
        $controller->index();
        break;
    case 'delete-plan':
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/PlanController.php';
        $controller = new PlanController();
        $controller->delete();
        break;
    case 'event-detail':
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/event-detail.php';
        break;
    case 'messages':
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/messages.php';
        break;
case 'profile':
        // Allow both regular users and admin users to access profile
        if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/profile.php';
        break;
    case 'about':
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/about.php';
        break;
    case 'contact':
        include VIEW_PATH . '/user/contact.php';
        break;
    case 'admin-dashboard':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-dashboard.php';
        break;
    case 'admin-bookings':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-bookings.php';
        break;
    case 'admin-packages':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-manage-packages.php';
        break;
    case 'admin-occasions':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-occasions.php';
        break;
   case 'admin-messages':
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: index.php?route=signin');
        exit;
    }
    include VIEW_PATH . '/admin/admin-messages-real.php';
    break;
    case 'admin-customize':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        $controller = new CustomizeController();
        $controller->listAll();
        break;
    case 'admin-customize-add':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        $controller = new CustomizeController();
        $controller->addForm();
        break;
    case 'admin-customize-create':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        $controller = new CustomizeController();
        $controller->create();
        break;
    case 'admin-customize-edit':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        $controller = new CustomizeController();
        $controller->editForm();
        break;
    case 'admin-customize-update':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        $controller = new CustomizeController();
        $controller->update();
        break;
    case 'admin-customize-delete':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/CustomizeController.php';
        $controller = new CustomizeController();
        $controller->delete();
        break;

    // ========== WARDROBE ROUTES ==========
    case 'wardrobe':
        if (!isset($_SESSION['user_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeController.php';
        $controller = new WardrobeController();
        $controller->index();
        break;

    case 'admin-wardrobe':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/AdminWardrobeController.php';
        $controller = new AdminWardrobeController();
        $controller->listAll();
        break;

    case 'admin-wardrobe-add':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/AdminWardrobeController.php';
        $controller = new AdminWardrobeController();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->add();
        } else {
            $controller->addForm();
        }
        break;

    case 'admin-wardrobe-edit':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/AdminWardrobeController.php';
        $controller = new AdminWardrobeController();
        $controller->editForm();
        break;

    case 'admin-wardrobe-update':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/AdminWardrobeController.php';
        $controller = new AdminWardrobeController();
        $controller->update();
        break;

    case 'admin-wardrobe-delete':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/AdminWardrobeController.php';
        $controller = new AdminWardrobeController();
        $controller->delete();
        break;
    
    case 'admin-wardrobe-selections':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-wardrobe-selections.php';
        break;
    
    case 'wardrobe-selection':
        if (!isset($_SESSION['user_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        require_once ROOT_PATH . '/app/controllers/WardrobeSelectionController.php';
        $controller = new WardrobeSelectionController();
        if (isset($_GET['action']) && $_GET['action'] === 'getByCategory') {
            $controller->getByCategory();
        } else {
            $controller->selectWardrobes();
        }
        break;
    
    // ========== END WARDROBE ROUTES ==========

    case 'feedback':
        if (isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=admin-dashboard');
            exit;
        }
        if (!isset($_SESSION['user_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/user/feedback.php';
        break;
case 'admin-feedback':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-feedback.php';
        break;
    case 'admin-profile':
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: index.php?route=signin');
            exit;
        }
        include VIEW_PATH . '/admin/admin-profile.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php?route=landing');
        exit;
        break;
    case 'admin-logout':
        session_destroy();
        header('Location: index.php?route=signin');
        exit;
        break;
    default:
        include VIEW_PATH . '/landing/landing.php';
}
?>

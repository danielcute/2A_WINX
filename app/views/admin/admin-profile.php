<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define ROOT_PATH if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/User.php';

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? null) !== 'admin' && empty($_SESSION['admin_logged_in']))) {
    header('Location: ' . BASE_URL . '/index.php?route=signin');
    exit;
}

// Fetch full admin data from database
$userModel = new User();
$adminId = $_SESSION['user_id'];
$admin = $userModel->findById($adminId);

if (!$admin) {
    $_SESSION['login_error'] = 'Admin data not found';
    header('Location: ' . BASE_URL . '/index.php?route=signin');
    exit;
}

// Prepare admin data
$admin_data = [
    'id' => $admin['user_id'],
    'first_name' => $admin['first_name'],
    'last_name' => $admin['last_name'],
    'email' => $admin['email'],
    'phone' => $admin['phone'],
    'image' => $admin['image'],
    'member_since' => $admin['created_at'],
    'role' => $admin['role']
];

// Handle avatar upload
$avatar_upload_error = '';
$avatar_upload_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $upload_dir = ROOT_PATH . '/public/assets/img/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['avatar'];
    $file_name = 'admin_' . $admin_data['id'] . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $target_file = $upload_dir . $file_name;
    $relative_path = '/assets/img/' . $file_name;
    
    // Allowed file types
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        if (!in_array($file['type'], $allowed_types)) {
            $avatar_upload_error = 'Only JPG, PNG, GIF, and WEBP images are allowed.';
        } elseif ($file['size'] > $max_size) {
            $avatar_upload_error = 'Image size must be less than 2MB.';
        } else {
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                // Delete old avatar if it exists
                if (!empty($admin_data['image']) && file_exists(ROOT_PATH . $admin_data['image'])) {
                    unlink(ROOT_PATH . $admin_data['image']);
                }
                // Update database with new image
                $userModel->update($admin_data['id'], ['image' => $relative_path]);
                $_SESSION['user_avatar'] = $relative_path;
                $admin_data['image'] = $relative_path;
                $avatar_upload_success = 'Profile picture updated successfully!';
                
                // Redirect to prevent form resubmission
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            } else {
                $avatar_upload_error = 'Failed to upload image. Please try again.';
            }
        }
    } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
        $avatar_upload_error = 'An error occurred during upload.';
    }
}

// Handle profile and security operations
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $first_name = trim($_POST['first_name'] ?? '');
                $last_name = trim($_POST['last_name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                
                if ($first_name && $last_name) {
                    $updateData = [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'phone' => $phone
                    ];
                    
                    if ($userModel->update($admin_data['id'], $updateData)) {
                        // Update session
                        $_SESSION['user_name'] = $first_name;
                        $_SESSION['user_last_name'] = $last_name;
                        $_SESSION['user_phone'] = $phone;
                        
                        $admin_data['first_name'] = $first_name;
                        $admin_data['last_name'] = $last_name;
                        $admin_data['phone'] = $phone;
                        
                        $success_message = 'Profile updated successfully!';
                    } else {
                        $error_message = 'Failed to update profile. Please try again.';
                    }
                } else {
                    $error_message = 'Please fill in all required fields.';
                }
                break;
                
            case 'update_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (!$current_password || !$new_password || !$confirm_password) {
                    $error_message = 'All password fields are required.';
                } elseif ($new_password !== $confirm_password) {
                    $error_message = 'New passwords do not match.';
                } elseif (strlen($new_password) < 6) {
                    $error_message = 'Password must be at least 6 characters.';
                } else {
                    // Verify current password
                    $db_admin = $userModel->findById($admin_data['id']);
                    if (password_verify($current_password, $db_admin['password'])) {
                        if ($userModel->updatePassword($admin_data['id'], $new_password)) {
                            $success_message = 'Password updated successfully!';
                        } else {
                            $error_message = 'Failed to update password. Please try again.';
                        }
                    } else {
                        $error_message = 'Current password is incorrect.';
                    }
                }
                break;
                
            case 'enable_2fa':
                $code = $_POST['code'] ?? '';
                $tempSecret = $_SESSION['temp_2fa_secret'] ?? null;
                
                if (empty($code)) {
                    $error_message = 'Please enter your verification code.';
                } else if (!$tempSecret) {
                    $error_message = 'No 2FA secret found. Please refresh the page and try again.';
                } else {
                    // Verify the 2FA code - pass the temporary secret
                    if ($userModel->verifyTwoFactorCode($admin_data['id'], $code, $tempSecret)) {
                        // Enable 2FA
                        $userModel->enableTwoFactor($admin_data['id'], $tempSecret);
                        unset($_SESSION['temp_2fa_secret']);
                        $success_message = 'Two-factor authentication enabled successfully!';
                    } else {
                        $error_message = 'Invalid verification code.';
                    }
                }
                break;
                
            case 'disable_2fa':
                $userModel->disableTwoFactor($admin_data['id']);
                $success_message = 'Two-factor authentication disabled.';
                break;
        }
    }
}

// Get 2FA status
$twoFactorEnabled = $userModel->isTwoFactorEnabled($admin_data['id']);

// Generate QR code for 2FA setup
$qrCodeUrl = '';
if (!$twoFactorEnabled) {
    $secret = $_SESSION['temp_2fa_secret'] ?? $userModel->generateTwoFactorSecret();
    $_SESSION['temp_2fa_secret'] = $secret;
    $email = $admin_data['email'];
    $otpauthUrl = 'otpauth://totp/' . urlencode('Sinta Admin:' . $email) . '?secret=' . $secret . '&issuer=Sinta';
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauthUrl);
}

// Check if avatar exists, otherwise use default
$avatar_path = !empty($admin_data['image']) ? $admin_data['image'] : '/assets/img/default-avatar.jpg';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin Profile — Sinta</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
    <style>
        /* Additional styles for avatar upload */
        .avatar-upload-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        .avatar-upload-modal.active {
            display: flex;
        }
        .avatar-upload-content {
            background: var(--white);
            border-radius: var(--r-xl);
            max-width: 400px;
            width: 90%;
            padding: 2rem;
            text-align: center;
            animation: fadeUp 0.3s ease;
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 1rem auto;
            overflow: hidden;
            border: 3px solid var(--gold);
        }
        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .upload-area {
            border: 2px dashed var(--border);
            border-radius: var(--r-lg);
            padding: 1.5rem;
            margin: 1rem 0;
            cursor: pointer;
            transition: all var(--tb);
        }
        .upload-area:hover {
            border-color: var(--gold);
            background: var(--gold-pale);
        }
        .upload-area i {
            font-size: 2rem;
            color: var(--gold);
            margin-bottom: 0.5rem;
        }
        #avatarInput {
            display: none;
        }
        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #fef3c7;
            border: 2px solid #f59e0b;
            color: #92400e;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .pw-wrap {
            position: relative;
        }
        .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gold);
            font-size: 0.9rem;
            padding: 4px 8px;
        }
        .field-wrap input {
            padding-right: 40px;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/admin-nav.php'; ?>

<div class="app-shell">
    <main class="profile-main">
        
        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert--success animate-fade-up">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert--error animate-fade-up">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($avatar_upload_success): ?>
            <div class="alert alert--success animate-fade-up">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($avatar_upload_success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($avatar_upload_error): ?>
            <div class="alert alert--error animate-fade-up">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($avatar_upload_error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Profile Hero Header -->
        <div class="profile-hero animate-fade-up">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar">
                    <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="<?php echo htmlspecialchars($admin_data['first_name']); ?>" id="profileAvatar">
                </div>
                <button class="profile-avatar-edit" aria-label="Change avatar" onclick="openAvatarModal()">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            <div class="profile-hero__info">
                <div class="admin-badge">
                    <i class="fas fa-crown"></i>
                    <span>Admin</span>
                </div>
                <h1 class="profile-hero__name">
                    <?php echo htmlspecialchars($admin_data['first_name'] . ' ' . $admin_data['last_name']); ?>
                </h1>
                <div class="profile-hero__meta">
                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($admin_data['email']); ?></span>
                    <span><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($admin_data['phone'] ?: 'Not provided'); ?></span>
                    <span><i class="far fa-calendar-alt"></i> Member since <?php echo date('M Y', strtotime($admin_data['member_since'])); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Tabs Navigation -->
        <div class="profile-tabs animate-fade-up">
            <button class="profile-tab active" data-tab="personal">
                <i class="fas fa-user"></i> Personal Info
            </button>
            <button class="profile-tab" data-tab="security">
                <i class="fas fa-lock"></i> Security
            </button>
        </div>
        
        <!-- Tab: Personal Info -->
        <div id="tab-personal" class="profile-pane active">
            <div class="pane-card">
                <div class="pane-card__head">
                    <h3 class="pane-card__title">Personal Information</h3>
                    <span class="pane-card__hint">Update your admin profile details</span>
                </div>
                <form method="POST" action="">
                    <div class="pane-card__body">
                        <div class="field-grid">
                            <div class="field-wrap">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($admin_data['first_name']); ?>" required>
                            </div>
                            <div class="field-wrap">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($admin_data['last_name']); ?>" required>
                            </div>
                            <div class="field-wrap">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" disabled style="background: #f5f5f5; cursor: not-allowed;">
                            </div>
                            <div class="field-wrap">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($admin_data['phone']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="pane-actions--padded">
                        <div class="pane-actions">
                            <button type="submit" name="action" value="update_profile" class="btn btn--gold">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn--ghost" onclick="resetForm(this)">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tab: Security -->
        <div id="tab-security" class="profile-pane">
            <!-- Change Password Card -->
            <div class="pane-card">
                <div class="pane-card__head">
                    <h3 class="pane-card__title">Change Password</h3>
                    <span class="pane-card__hint">Keep your account secure</span>
                </div>
                <form method="POST" action="" id="passwordForm">
                    <div class="pane-card__body">
                        <div class="field-grid">
                            <div class="field-wrap field-wrap--full">
                                <label>Current Password</label>
                                <div class="pw-wrap">
                                    <input type="password" name="current_password" id="currentPassword" placeholder="Enter current password" required>
                                    <button type="button" class="pw-toggle" onclick="togglePassword('currentPassword', this)"><i class="far fa-eye-slash"></i></button>
                                </div>
                            </div>
                            <div class="field-wrap">
                                <label>New Password</label>
                                <div class="pw-wrap">
                                    <input type="password" name="new_password" id="newPassword" placeholder="Enter new password" required minlength="6">
                                    <button type="button" class="pw-toggle" onclick="togglePassword('newPassword', this)"><i class="far fa-eye-slash"></i></button>
                                </div>
                            </div>
                            <div class="field-wrap">
                                <label>Confirm New Password</label>
                                <div class="pw-wrap">
                                    <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm new password" required minlength="6">
                                    <button type="button" class="pw-toggle" onclick="togglePassword('confirmPassword', this)"><i class="far fa-eye-slash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pane-actions--padded">
                        <div class="pane-actions">
                            <button type="submit" name="action" value="update_password" class="btn btn--gold">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Two-Factor Authentication Card -->
            <div class="pane-card">
                <div class="pane-card__head">
                    <h3 class="pane-card__title">Two-Factor Authentication</h3>
                    <span class="pane-card__hint">Add an extra layer of security to your admin account</span>
                </div>
                <div class="pane-card__body">
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: <?php echo $twoFactorEnabled ? '#f0fdf4' : '#fef3c7'; ?>; border-radius: 12px; margin-bottom: 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: <?php echo $twoFactorEnabled ? '#16a34a' : '#d97706'; ?>; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                            <i class="fas fa-shield-<?php echo $twoFactorEnabled ? 'check' : 'exclamation-triangle'; ?>"></i>
                        </div>
                        <div>
                            <strong style="color: <?php echo $twoFactorEnabled ? '#16a34a' : '#d97706'; ?>;">
                                <?php echo $twoFactorEnabled ? 'Two-Factor Authentication Enabled' : 'Two-Factor Authentication Not Enabled'; ?>
                            </strong>
                            <p style="margin: 0.25rem 0 0 0; color: #666; font-size: 0.9rem;">
                                <?php echo $twoFactorEnabled 
                                    ? 'Your admin account is protected with 2FA using Google Authenticator or similar apps.' 
                                    : 'Enable 2FA to add an extra layer of security to your admin account.'; ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($twoFactorEnabled): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="disable_2fa">
                            <button type="submit" class="btn btn--ghost" style="color: #dc2626; border-color: #dc2626;" onclick="return confirm('Are you sure you want to disable Two-Factor Authentication? Your admin account will be less secure.');">
                                <i class="fas fa-times"></i> Disable 2FA
                            </button>
                        </form>
                    <?php else: ?>
                        <!-- Setup Method Toggle -->
                        <div style="display: flex; gap: 0.5rem; margin: 1.5rem 0; border-bottom: 2px solid var(--border);">
                            <button type="button" class="admin-setup-tab-btn active" onclick="switchAdminMethod(event, 'qr')" style="flex: 1; padding: 0.75rem; border: none; background: none; cursor: pointer; font-weight: 600; color: #666; border-bottom: 3px solid transparent; transition: all 0.2s;">
                                <i class="fas fa-qrcode"></i> Scan QR Code
                            </button>
                            <button type="button" class="admin-setup-tab-btn" onclick="switchAdminMethod(event, 'manual')" style="flex: 1; padding: 0.75rem; border: none; background: none; cursor: pointer; font-weight: 600; color: #999; border-bottom: 3px solid transparent; transition: all 0.2s;">
                                <i class="fas fa-keyboard"></i> Enter Key Manually
                            </button>
                        </div>
                        
                        <!-- QR Code Method -->
                        <div id="adminQrMethod" style="text-align: center; margin: 1.5rem 0;">
                            <p style="color: #666; margin-bottom: 1rem;">Scan this QR code with your authenticator app</p>
                            <div style="display: inline-block; padding: 1rem; border: 2px solid var(--border); border-radius: 12px; background: white;">
                                <img src="<?= htmlspecialchars($qrCodeUrl) ?>" alt="QR Code" style="width: 180px; height: 180px;">
                            </div>
                        </div>
                        
                        <!-- Manual Entry Method -->
                        <div id="adminManualMethod" style="display: none;">
                            <div style="background: #f8f7f5; border-radius: 12px; padding: 1.25rem; margin: 1rem 0; text-align: left;">
                                <p style="color: #666; font-size: 0.9rem; margin: 0 0 0.75rem 0;">
                                    <strong>Your Setup Key</strong>
                                </p>
                                <div style="display: flex; gap: 0.5rem; align-items: stretch;">
                                    <div id="adminSecretKey" style="flex: 1; font-family: 'Courier New', monospace; font-size: 1rem; letter-spacing: 0.15em; color: #2c2820; background: white; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border); user-select: all; cursor: pointer;">
                                        <?php echo htmlspecialchars($_SESSION['temp_2fa_secret']); ?>
                                    </div>
                                    <button type="button" class="btn btn--gold" onclick="copyAdminSecret()" style="width: auto; padding: 0.9rem 1rem; border-radius: 8px; flex-shrink: 0;">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <small style="color: #999; display: block; margin-top: 0.5rem;">Copy this key and paste it into your authenticator app</small>
                            </div>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="enable_2fa">
                            
                            <!-- Verification Method Toggle -->
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; border-bottom: 2px solid var(--border);">
                                <button type="button" class="admin-verify-tab-btn active" onclick="switchAdminVerify(event, 'code')" style="flex: 1; padding: 0.75rem; border: none; background: none; cursor: pointer; font-weight: 600; color: #666; border-bottom: 3px solid transparent; transition: all 0.2s;">
                                    <i class="fas fa-mobile-alt"></i> 6-Digit Code
                                </button>
                                <button type="button" class="admin-verify-tab-btn" onclick="switchAdminVerify(event, 'key')" style="flex: 1; padding: 0.75rem; border: none; background: none; cursor: pointer; font-weight: 600; color: #999; border-bottom: 3px solid transparent; transition: all 0.2s;">
                                    <i class="fas fa-key"></i> Setup Key
                                </button>
                            </div>
                            
                            <!-- 6-Digit Code Input -->
                            <div id="adminCodeInput" class="admin-verify-method">
                                <div class="field-wrap field-wrap--full" style="margin-bottom: 1rem;">
                                    <label>Enter 6-digit verification code from your authenticator app</label>
                                    <input type="text" name="code" placeholder="000000" maxlength="6" pattern="\d{6}" style="text-align: center; letter-spacing: 0.3em; font-size: 1.2rem; font-weight: 600;">
                                </div>
                            </div>
                            
                            <!-- Setup Key Input -->
                            <div id="adminKeyInput" class="admin-verify-method" style="display: none;">
                                <div class="field-wrap field-wrap--full" style="margin-bottom: 1rem;">
                                    <label>Enter your manual setup key</label>
                                    <input type="text" name="setup_key" placeholder="Enter your setup key" style="text-align: center; letter-spacing: 0.1em; font-size: 1rem;">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn--gold">
                                <i class="fas fa-shield-alt"></i> Enable 2FA
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Avatar Upload Modal -->
<div id="avatarModal" class="avatar-upload-modal">
    <div class="avatar-upload-content">
        <i class="fas fa-user-circle" style="font-size: 2rem; color: var(--gold);"></i>
        <h3 style="margin: 0.5rem 0; font-family: var(--serif);">Change Profile Picture</h3>
        
        <div class="avatar-preview">
            <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Preview" id="avatarPreview">
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data" id="avatarUploadForm">
            <div class="upload-area" onclick="document.getElementById('avatarInput').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to upload from computer</p>
                <small style="color: var(--muted);">JPG, PNG, GIF up to 2MB</small>
            </div>
            <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" onchange="previewImage(this)">
        </form>
        
        <div class="modal-buttons">
            <button type="submit" form="avatarUploadForm" class="btn btn--gold">Upload</button>
            <button type="button" class="btn btn--ghost" onclick="closeAvatarModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
// Avatar Modal Functions
function openAvatarModal() {
    document.getElementById('avatarModal').classList.add('active');
}

function closeAvatarModal() {
    document.getElementById('avatarModal').classList.remove('active');
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Tab switching
document.querySelectorAll('.profile-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const tabId = tab.dataset.tab;
        document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.profile-pane').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(`tab-${tabId}`).classList.add('active');
    });
});

// Password visibility toggle
function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
        btn.innerHTML = '<i class="far fa-eye"></i>';
    } else {
        field.type = 'password';
        btn.innerHTML = '<i class="far fa-eye-slash"></i>';
    }
}

// Reset form
function resetForm(btn) {
    btn.closest('form').reset();
}

// Close modal on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAvatarModal();
    }
});

// Close modal on background click
document.getElementById('avatarModal')?.addEventListener('click', (e) => {
    if (e.target.id === 'avatarModal') {
        closeAvatarModal();
    }
});

// Copy 2FA Secret Function
function copyAdminSecret() {
    const secretKey = document.getElementById('adminSecretKey').textContent;
    navigator.clipboard.writeText(secretKey).then(() => {
        alert('Secret key copied to clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const el = document.getElementById('adminSecretKey');
        const range = document.createRange();
        range.selectNodeContents(el);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        document.execCommand('copy');
        alert('Secret key copied to clipboard!');
    });
}

// Switch between QR code and manual key entry for admin 2FA
function switchAdminMethod(event, method) {
    // Update button states
    document.querySelectorAll('.admin-setup-tab-btn').forEach(btn => {
        btn.style.color = '';
        btn.style.borderBottomColor = 'transparent';
    });
    event.target.closest('.admin-setup-tab-btn').style.color = '#8A7650';
    event.target.closest('.admin-setup-tab-btn').style.borderBottomColor = '#8A7650';
    
    // Toggle section visibility
    const qrSection = document.getElementById('adminQrMethod');
    const manualSection = document.getElementById('adminManualMethod');
    
    if (method === 'qr') {
        qrSection.style.display = 'block';
        manualSection.style.display = 'none';
    } else {
        qrSection.style.display = 'none';
        manualSection.style.display = 'block';
    }
}
</script>

<?php include 'admin-footer.php'; ?>
</body>
</html>

<?php 
$page = 'profile';
$page_title = 'Profile';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?route=signin');
    exit;
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}

// Fetch full user data from database
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Feedback.php';

$userModel = new User();
$feedbackModel = new Feedback();
$user = $userModel->findById($_SESSION['user_id']);
if (!$user) {
    $_SESSION['login_error'] = 'User data not found';
    header('Location: index.php?route=signin');
    exit;
}

// Prepare user data from session/database
$user_data = [
    'id' => $user['user_id'],
    'first_name' => $_SESSION['user_name'] ?? $user['first_name'],
    'last_name' => $_SESSION['user_last_name'] ?? $user['last_name'],
    'email' => $_SESSION['user_email'] ?? $user['email'],
    'phone' => $_SESSION['user_phone'] ?? $user['phone'],
    'birthday' => $_SESSION['user_birthday'] ?? $user['birthday'],
    'address' => $_SESSION['user_address'] ?? $user['address'],
    'city' => $user['city'] ?? 'Not set',
    'image' => $_SESSION['user_avatar'] ?? $user['image'],
    'member_since' => $user['created_at'] ?? date('Y-m-d'),
    'is_premium' => false,
    'events_count' => 0,
    'rating' => 0,
    'total_spent' => '₱0'
];

$user = $user_data;

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $first_name = trim($_POST['first_name'] ?? '');
                $last_name = trim($_POST['last_name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $birthday = trim($_POST['birthday'] ?? '');
                $city = trim($_POST['city'] ?? '');
                
                if ($first_name && $last_name && $phone) {
                    $updateData = [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'phone' => $phone,
                        'address' => $address,
                        'birthday' => $birthday,
                        'city' => $city
                    ];
                    
                    if ($userModel->update($user['id'], $updateData)) {
                        // Update session
                        $_SESSION['user_name'] = $first_name;
                        $_SESSION['user_last_name'] = $last_name;
                        $_SESSION['user_phone'] = $phone;
                        $_SESSION['user_address'] = $address;
                        $_SESSION['user_birthday'] = $birthday;
                        
                        $user['first_name'] = $first_name;
                        $user['last_name'] = $last_name;
                        $user['phone'] = $phone;
                        $user['address'] = $address;
                        $user['birthday'] = $birthday;
                        $user['city'] = $city;
                        
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
                    $db_user = $userModel->findById($user['id']);
                    if (password_verify($current_password, $db_user['password'])) {
                        if ($userModel->updatePassword($user['id'], $new_password)) {
                            $success_message = 'Password updated successfully!';
                        } else {
                            $error_message = 'Failed to update password. Please try again.';
                        }
                    } else {
                        $error_message = 'Current password is incorrect.';
                    }
                }
                break;
                
            case 'update_notifications':
                $success_message = 'Notification preferences saved!';
                break;
        }
    }
}

// Check if avatar exists, otherwise use default
$avatar_path = !empty($user['image']) ? $user['image'] : '/assets/img/default-avatar.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Profile — Sinta</title>
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
        .back-btn-mobile {
            display: none;
            align-items: center;
            gap: 0.5rem;
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .back-btn-mobile { display: flex; }
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
        .default-avatars {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 1rem 0;
            flex-wrap: wrap;
        }
        .default-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all var(--tb);
        }
        .default-avatar:hover {
            transform: scale(1.05);
        }
        .default-avatar.selected {
            border-color: var(--gold);
            box-shadow: 0 0 0 2px var(--gold-pale);
        }
        
        /* Mobile responsive improvements */
        @media (max-width: 768px) {
            .avatar-upload-content {
                max-width: 95%;
                padding: 1.5rem;
            }

            .avatar-preview {
                width: 100px;
                height: 100px;
            }

            .upload-area {
                padding: 1rem;
                margin: 0.75rem 0;
            }

            .upload-area i {
                font-size: 1.5rem;
            }

            .default-avatars {
                gap: 0.75rem;
            }

            .default-avatar {
                width: 50px;
                height: 50px;
            }

            .modal-buttons {
                gap: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .avatar-upload-content {
                max-width: 100%;
                width: 95%;
                padding: 1rem;
                border-radius: 12px;
            }

            .avatar-preview {
                width: 80px;
                height: 80px;
            }

            .upload-area {
                padding: 1rem;
                margin: 0.5rem 0;
            }

            .upload-area i {
                font-size: 1.25rem;
                margin-bottom: 0.25rem;
            }

            .modal-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }

            .modal-buttons .btn {
                width: 100%;
            }

            .default-avatars {
                gap: 0.5rem;
            }

            .default-avatar {
                width: 45px;
                height: 45px;
                font-size: 1.5rem;
            }
        }
</head>
<body>

<?php include VIEW_PATH . '/user/nav.php'; ?>

<div class="app-shell">
    <main class="profile-main">
        
        <a href="index.php?route=homepage" class="back-btn-mobile"><i class="fas fa-chevron-left"></i> Back to Home</a>

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
        
        <!-- Profile Hero Header -->
        <div class="profile-hero animate-fade-up">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar">
                    <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="<?php echo htmlspecialchars($user['first_name']); ?>" id="profileAvatar">
                </div>
                <button class="profile-avatar-edit" aria-label="Change avatar" onclick="openAvatarModal()">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            <div class="profile-hero__info">
                <?php if ($user['is_premium']): ?>
                    <div class="profile-badge-pill">
                        <i class="fas fa-crown"></i>
                        <span>Premium Client</span>
                    </div>
                <?php endif; ?>
                <h1 class="profile-hero__name">
                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                </h1>
                <div class="profile-hero__meta">
                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                    <span><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($user['phone']); ?></span>
                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['city']); ?></span>
                    <span><i class="far fa-calendar-alt"></i> Member since <?php echo date('M Y', strtotime($user['member_since'])); ?></span>
                </div>
            </div>
            <div class="profile-hero__stats">
                <div class="profile-stat">
                    <span class="profile-stat__num"><?php echo $user['events_count']; ?></span>
                    <span class="profile-stat__label">Events</span>
                </div>
                <div class="profile-stat__divider"></div>
                <div class="profile-stat">
                    <span class="profile-stat__num"><?php echo $user['rating']; ?></span>
                    <span class="profile-stat__label">Rating</span>
                </div>
                <div class="profile-stat__divider"></div>
                <div class="profile-stat">
                    <span class="profile-stat__num"><?php echo $user['total_spent']; ?></span>
                    <span class="profile-stat__label">Spent</span>
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
            <button class="profile-tab" data-tab="notifications">
                <i class="fas fa-bell"></i> Notifications
            </button>
            <button class="profile-tab" data-tab="feedback">
                <i class="fas fa-comments"></i> Feedback
            </button>
        </div>
        
        <!-- Tab: Personal Info -->
        <div id="tab-personal" class="profile-pane active">
            <div class="pane-card">
                <div class="pane-card__head">
                    <h3 class="pane-card__title">Personal Information</h3>
                    <span class="pane-card__hint">Update your personal details</span>
                </div>
                <form method="POST" action="">
                    <div class="pane-card__body">
                        <div class="field-grid">
                            <div class="field-wrap">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                            </div>
                            <div class="field-wrap">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                            </div>
                            <div class="field-wrap">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                            <div class="field-wrap">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            <div class="field-wrap">
                                <label>City</label>
                                <input type="text" name="city" value="<?php echo htmlspecialchars($user['city']); ?>">
                            </div>
                            <div class="field-wrap">
                                <label>Birthday</label>
                                <input type="date" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>">
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
            <div class="pane-card">
                <div class="pane-card__head">
                    <h3 class="pane-card__title">Two-Factor Authentication</h3>
                    <span class="pane-card__hint">Add an extra layer of security to your account</span>
                </div>
<?php
                // Check 2FA status
                $twoFactorStatus = $userModel->isTwoFactorEnabled($_SESSION['user_id']);
                $twoFaEnabled = $twoFactorStatus && isset($twoFactorStatus['two_factor_enabled']) && $twoFactorStatus['two_factor_enabled'] == 1;
                $hasPendingVerification = isset($_SESSION['pending_2fa_verification']) && $_SESSION['pending_2fa_verification'];
                ?>
                <div class="pane-card__body">
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: <?php echo $twoFaEnabled ? '#f0fdf4' : '#fef3c7'; ?>; border-radius: 12px; margin-bottom: 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: <?php echo $twoFaEnabled ? '#16a34a' : '#d97706'; ?>; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                            <i class="fas fa-shield-<?php echo $twoFaEnabled ? 'checkmark' : 'exclamation-triangle'; ?>"></i>
                        </div>
                        <div>
                            <strong style="color: <?php echo $twoFaEnabled ? '#16a34a' : '#d97706'; ?>;">
                                <?php echo $twoFaEnabled ? 'Two-Factor Authentication Enabled' : 'Two-Factor Authentication Not Enabled'; ?>
                            </strong>
                            <p style="margin: 0.25rem 0 0 0; color: #666; font-size: 0.9rem;">
                                <?php echo $twoFaEnabled 
                                    ? 'Your account is protected with 2FA using Google Authenticator or similar apps.' 
                                    : 'Enable 2FA to add an extra layer of security to your account.'; ?>
                            </p>
                            <?php if ($hasPendingVerification): ?>
                            <p style="margin: 0.5rem 0 0 0; color: #d97706; font-size: 0.85rem;">
                                <i class="fas fa-info-circle"></i> Pending verification - you can verify now or use your auth app on next login
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($twoFaEnabled): ?>
                        <!-- Optionally verify 2FA code from settings -->
                        <a href="/index.php?route=verify-2fa" class="btn btn--gold" style="margin-right: 0.5rem;">
                            <i class="fas fa-shield-alt"></i> Verify Code
                        </a>
                        <a href="/index.php?route=disable-2fa" class="btn btn--ghost" style="color: #dc2626; border-color: #dc2626;" onclick="return confirm('Are you sure you want to disable Two-Factor Authentication? Your account will be less secure.');">
                            <i class="fas fa-shield-alt"></i> Disable 2FA
                        </a>
                    <?php else: ?>
                        <a href="/index.php?route=setup-2fa" class="btn btn--gold">
                            <i class="fas fa-shield-alt"></i> Set Up 2FA
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
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
                                    <input type="password" name="current_password" id="currentPassword" placeholder="Enter current password">
                                    <button type="button" class="pw-toggle" onclick="togglePassword('currentPassword', this)"><i class="far fa-eye-slash"></i></button>
                                </div>
                            </div>
                            <div class="field-wrap">
                                <label>New Password</label>
                                <div class="pw-wrap">
                                    <input type="password" name="new_password" id="newPassword" placeholder="Enter new password" onkeyup="checkPasswordStrength()">
                                    <button type="button" class="pw-toggle" onclick="togglePassword('newPassword', this)"><i class="far fa-eye-slash"></i></button>
                                </div>
                            </div>
                            <div class="field-wrap">
                                <label>Confirm Password</label>
                                <div class="pw-wrap">
                                    <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm new password">
                                    <button type="button" class="pw-toggle" onclick="togglePassword('confirmPassword', this)"><i class="far fa-eye-slash"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="pw-strength" id="passwordStrength">
                            <div class="pw-strength__bars">
                                <div class="pw-bar"></div>
                                <div class="pw-bar"></div>
                                <div class="pw-bar"></div>
                                <div class="pw-bar"></div>
                            </div>
                            <span class="pw-strength__text">Password strength</span>
                        </div>
                    </div>
                    <div class="pane-actions--padded">
                        <div class="pane-actions">
                            <button type="submit" name="action" value="update_password" class="btn btn--gold">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Active Sessions -->
            <div class="pane-card">
                <div class="pane-card__head">
                    <h3 class="pane-card__title">Active Sessions</h3>
                    <span class="pane-card__hint">Manage devices where you're logged in</span>
                </div>
                <div class="pane-card__body pane-card__body--flush">
                    <div class="session-row">
                        <div class="session-icon"><i class="fas fa-laptop-code"></i></div>
                        <div class="session-info">
                            <strong>Chrome on Windows</strong>
                            <span>Bacolod City, Philippines • Current session</span>
                        </div>
                        <span class="badge badge--green">Active</span>
                    </div>
                    <div class="session-row">
                        <div class="session-icon"><i class="fab fa-safari"></i></div>
                        <div class="session-info">
                            <strong>Safari on iPhone</strong>
                            <span>Last active 2 hours ago</span>
                        </div>
                        <button class="btn btn--ghost btn--sm" onclick="revokeSession(this)">Revoke</button>
                    </div>
                    <div class="session-row">
                        <div class="session-icon"><i class="fab fa-chrome"></i></div>
                        <div class="session-info">
                            <strong>Chrome on MacBook</strong>
                            <span>Last active yesterday</span>
                        </div>
                        <button class="btn btn--ghost btn--sm" onclick="revokeSession(this)">Revoke</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tab: Notifications -->
        <div id="tab-notifications" class="profile-pane">
            <div class="pane-card">
                <div class="pane-card__head">
                    <h3 class="pane-card__title">Notification Preferences</h3>
                    <span class="pane-card__hint">Choose what updates you receive</span>
                </div>
                <form method="POST" action="">
                    <div class="pane-card__body pane-card__body--flush">
                        <div class="notif-row">
                            <div class="notif-row__text">
                                <strong>Booking Confirmations</strong>
                                <p>Get notified when your booking is confirmed</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="notif_booking" checked>
                                <span class="toggle__track"></span>
                            </label>
                        </div>
                        <div class="notif-row">
                            <div class="notif-row__text">
                                <strong>Payment Reminders</strong>
                                <p>Reminders for upcoming balance due dates</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="notif_payment" checked>
                                <span class="toggle__track"></span>
                            </label>
                        </div>
                        <div class="notif-row">
                            <div class="notif-row__text">
                                <strong>Event Updates</strong>
                                <p>Updates on schedule changes or vendor confirmations</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="notif_events" checked>
                                <span class="toggle__track"></span>
                            </label>
                        </div>
                        <div class="notif-row">
                            <div class="notif-row__text">
                                <strong>Promotions & Deals</strong>
                                <p>Exclusive offers and seasonal packages</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="notif_promo">
                                <span class="toggle__track"></span>
                            </label>
                        </div>
                        <div class="notif-row">
                            <div class="notif-row__text">
                                <strong>New Messages</strong>
                                <p>Notifications for new messages from coordinators</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="notif_messages" checked>
                                <span class="toggle__track"></span>
                            </label>
                        </div>
                    </div>
                    <div class="pane-actions--padded">
                        <div class="pane-actions">
                            <button type="submit" name="action" value="update_notifications" class="btn btn--gold">
                                <i class="fas fa-save"></i> Save Preferences
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tab: Feedback -->
        <div id="tab-feedback" class="profile-pane">
            <div class="pane-card">
                <div class="pane-card__head">
                    <h3 class="pane-card__title">My Feedback</h3>
                    <span class="pane-card__hint">Your feedback and admin responses</span>
                </div>
                <?php 
                $userFeedbacks = $feedbackModel->getUserFeedback($_SESSION['user_id']);
                if (empty($userFeedbacks)): 
                ?>
                    <div class="pane-card__body" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-inbox" style="font-size: 2.5rem; color: var(--border); margin-bottom: 1rem;"></i>
                        <p style="color: #8B7355;">No feedback yet. Your feedback helps us improve!</p>
                        <button type="button" class="btn btn--gold" onclick="openFeedbackModal()" style="margin-top: 1rem;">
                            <i class="fas fa-plus"></i> Submit Feedback
                        </button>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($userFeedbacks as $feedback): ?>
                            <div style="background: #f9f9f9; border-radius: 8px; padding: 1.5rem; border-left: 4px solid #8A7650;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <h4 style="margin: 0 0 0.25rem 0; color: #2C2820;"><?php echo htmlspecialchars($feedback['subject']); ?></h4>
                                        <div style="font-size: 0.85rem; color: #8B7355;">
                                            <span><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></span>
                                            <span class="feedback-status" style="display: inline-block; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.75rem; margin-left: 0.5rem; background: #D1ECF1; color: #0c5460;">
                                                <?php echo ucfirst(str_replace('_', ' ', $feedback['status'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div style="text-align: center; padding: 0.5rem 1rem; background: white; border-radius: 4px; font-size: 0.85rem; color: #8B7355;">
                                        <?php 
                                        echo ($feedback['reply_count'] > 0) ? $feedback['reply_count'] . ' response' . ($feedback['reply_count'] > 1 ? 's' : '') : 'No responses';
                                        ?>
                                    </div>
                                </div>
                                <p style="margin: 1rem 0; color: #555; line-height: 1.6;"><?php echo nl2br(htmlspecialchars(substr($feedback['message'], 0, 200))); ?><?php echo strlen($feedback['message']) > 200 ? '...' : ''; ?></p>
                                
                                <!-- Replies -->
                                <?php 
                                $replies = $feedbackModel->getReplies($feedback['feedback_id']);
                                if (!empty($replies)): 
                                ?>
                                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #E2D9C8;">
                                        <h5 style="margin: 0 0 0.75rem 0; color: #2C2820; font-size: 0.95rem;">Conversation:</h5>
                                        <?php foreach ($replies as $reply): ?>
                                            <div style="margin-bottom: 0.75rem; padding: 0.75rem; background: white; border-radius: 4px; border-left: 3px solid #8A7650;">
                                                <div style="font-weight: 600; color: #2C2820; margin-bottom: 0.25rem;">
                                                    <?php echo htmlspecialchars($reply['sender_name']); ?>
                                                    <span style="font-weight: normal; color: #8B7355; font-size: 0.8rem; margin-left: 0.5rem;">
                                                        <?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?>
                                                    </span>
                                                </div>
                                                <p style="margin: 0; color: #555; font-size: 0.95rem;"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Add Reply -->
                                <?php if ($feedback['status'] !== 'closed'): ?>
                                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #E2D9C8;">
                                        <textarea id="reply-<?php echo $feedback['feedback_id']; ?>" placeholder="Add your response..." style="width: 100%; padding: 0.75rem; border: 2px solid #E2D9C8; border-radius: 4px; font-family: inherit; min-height: 80px; display: none;"></textarea>
                                        <button type="button" class="btn btn--sm btn--gold" onclick="showReplyField(<?php echo $feedback['feedback_id']; ?>)" style="margin-top: 0.5rem;">
                                            <i class="fas fa-reply"></i> Reply
                                        </button>
                                        <button type="button" class="btn btn--sm btn--ghost" id="send-reply-btn-<?php echo $feedback['feedback_id']; ?>" onclick="sendReplyFromProfile(<?php echo $feedback['feedback_id']; ?>)" style="margin-top: 0.5rem; display: none;">
                                            <i class="fas fa-check"></i> Send
                                        </button>
                                        <button type="button" class="btn btn--sm btn--ghost" id="cancel-reply-btn-<?php echo $feedback['feedback_id']; ?>" onclick="hideReplyField(<?php echo $feedback['feedback_id']; ?>)" style="margin-top: 0.5rem; display: none;">
                                            Cancel
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 1.5rem; text-align: center;">
                        <button type="button" class="btn btn--gold" onclick="openFeedbackModal()">
                            <i class="fas fa-plus"></i> Submit New Feedback
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Feedback Submission Modal -->
<div id="feedbackModal" class="avatar-upload-modal">
    <div class="avatar-upload-content" style="max-width: 500px;">
        <h3 style="margin: 0 0 1rem 0; color: #2C2820;">Submit Feedback</h3>
        <form id="profileFeedbackForm">
            <input type="hidden" name="action" value="submit_feedback">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2C2820;">Subject *</label>
                <input type="text" name="subject" placeholder="Brief summary" required style="width: 100%; padding: 0.75rem; border: 2px solid #E2D9C8; border-radius: 4px; font-family: inherit;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2C2820;">Rating</label>
                <div style="display: flex; gap: 0.5rem;" id="profileFeedbackRating">
                    <button type="button" class="star-btn" data-rating="1" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd;">★</button>
                    <button type="button" class="star-btn" data-rating="2" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd;">★</button>
                    <button type="button" class="star-btn" data-rating="3" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd;">★</button>
                    <button type="button" class="star-btn" data-rating="4" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd;">★</button>
                    <button type="button" class="star-btn" data-rating="5" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #ddd;">★</button>
                </div>
                <input type="hidden" name="rating" id="profileFeedbackRatingValue" value="0">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2C2820;">Message *</label>
                <textarea name="message" placeholder="Describe your feedback in detail..." required style="width: 100%; padding: 0.75rem; border: 2px solid #E2D9C8; border-radius: 4px; font-family: inherit; min-height: 120px;"></textarea>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn btn--gold">
                    <i class="fas fa-paper-plane"></i> Submit
                </button>
                <button type="button" class="btn btn--ghost" onclick="closeFeedbackModal()">Cancel</button>
            </div>
        </form>
    </div>
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
        
        <div class="default-avatars">
            <img src="/assets/images/aelarie.jpg" class="default-avatar" onclick="selectDefaultAvatar('/assets/images/avatars/default-avatar.jpg', event)">
            <img src="/assets/images/elarie.jpg" class="default-avatar" onclick="selectDefaultAvatar('/assets/images/avatars/avatar-2.jpg', event)">
            <img src="/assets/images/elarie.jpg" class="default-avatar" onclick="selectDefaultAvatar('/assets/images/avatars/avatar-3.jpg', event)">
            <img src="/assets/images/elarie.jpg" class="default-avatar" onclick="selectDefaultAvatar('/assets/images/avatars/avatar-4.jpg', event)">
        </div>
        
        <div class="modal-buttons">
            <button type="button" onclick="handleAvatarUpload()" class="btn btn--gold" id="confirmUploadBtn">Upload</button>
            <button type="button" class="btn btn--ghost" onclick="closeAvatarModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
// Avatar Modal Functions
const profileAvatar = document.getElementById('profileAvatar');
const avatarPreview = document.getElementById('avatarPreview');

function openAvatarModal() {
    document.getElementById('avatarModal').classList.add('active');
    // Ensure preview matches current profile avatar
    avatarPreview.src = profileAvatar.src;
}

function closeAvatarModal() {
    document.getElementById('avatarModal').classList.remove('active');
}

async function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            avatarPreview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
}

async function handleAvatarUpload() {
    const fileInput = document.getElementById('avatarInput');
    if (!fileInput.files || !fileInput.files[0]) {
        showToast('Please select a file first', 'error');
        return;
    }

    const btn = document.getElementById('confirmUploadBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

    const formData = new FormData();
    formData.append('action', 'upload_avatar');
    formData.append('avatar', fileInput.files[0]);

    try {
        const response = await fetch('api-user-profile.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            showToast('Profile picture updated successfully!', 'success');
            profileAvatar.src = data.image_url;
            closeAvatarModal();
        } else {
            showToast(data.message || 'Failed to upload image.', 'error');
        }
    } catch (error) {
        console.error('Error uploading avatar:', error);
        showToast('An error occurred during upload.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Upload';
    }
}

// Intercept form submission for avatar upload
document.getElementById('avatarUploadForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission as we handle with JS
});

function selectDefaultAvatar(src, event) {
    // Highlight selected
    document.querySelectorAll('.default-avatar').forEach(avatar => {
        avatar.classList.remove('selected');
    });
    event.target.classList.add('selected');
    
    // Submit default avatar selection to API
    const formData = new FormData();
    formData.append('action', 'upload_avatar_default'); // New action for default avatars
    formData.append('avatar_path', src);

    fetch('api-user-profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Default avatar selected successfully!', 'success');
            profileAvatar.src = data.image_url; // Update main avatar
            avatarPreview.src = data.image_url; // Update modal preview
            closeAvatarModal();
            // Optionally, reload the page to update session and other elements
            // setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to set default avatar.', 'error');
        }
    })
    .catch(error => {
        console.error('Error setting default avatar:', error);
        showToast('An error occurred while setting default avatar.', 'error');
    });
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

// Password strength checker
function checkPasswordStrength() {
    const password = document.getElementById('newPassword').value;
    const bars = document.querySelectorAll('.pw-bar');
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
    const strengthText = strength > 0 ? texts[strength - 1] : 'Password strength';
    const strengthSpan = document.querySelector('.pw-strength__text');
    strengthSpan.textContent = strengthText;
    if (strength >= 3) {
        strengthSpan.classList.add('good');
    } else {
        strengthSpan.classList.remove('good');
    }
}

// Toggle password visibility
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}

// Reset form to original values
function resetForm(btn) {
    const form = btn.closest('form');
    if (form) {
        form.reset();
        const originalText = btn.textContent;
        btn.innerHTML = '<i class="fas fa-undo"></i> Reset';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 1500);
    }
}

// Revoke session with confirmation
function revokeSession(btn) {
    if (confirm('Are you sure you want to revoke this session? You will be logged out on that device.')) {
        const row = btn.closest('.session-row');
        row.style.opacity = '0.5';
        setTimeout(() => {
            row.style.display = 'none';
            showToast('Session revoked successfully', 'success');
        }, 300);
    }
}

// Toast notification
function showToast(message, type = 'success') {
    let toast = document.querySelector('.toast-notification');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('show', type);
    setTimeout(() => {
        toast.classList.remove('show', type);
    }, 3000);
}

// Auto-dismiss alerts after 5 seconds
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => alert.remove(), 400);
    }, 5000);
});

// Close modal when clicking outside
document.getElementById('avatarModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAvatarModal();
    }
});

// Feedback Modal Functions
function openFeedbackModal() {
    document.getElementById('feedbackModal').classList.add('active');
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').classList.remove('active');
}

// Close feedback modal when clicking outside
document.getElementById('feedbackModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFeedbackModal();
    }
});

// Star rating for profile feedback
const profileStarButtons = document.querySelectorAll('#profileFeedbackRating .star-btn');
profileStarButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const rating = this.dataset.rating;
        document.getElementById('profileFeedbackRatingValue').value = rating;
        profileStarButtons.forEach(b => b.style.color = '#ddd');
        for (let i = 0; i < rating; i++) {
            profileStarButtons[i].style.color = '#8A7650';
        }
    });
});

// Profile feedback form submission
document.getElementById('profileFeedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'submit_feedback');
    formData.append('subject', this.querySelector('[name="subject"]').value);
    formData.append('message', this.querySelector('[name="message"]').value);
    formData.append('rating', document.getElementById('profileFeedbackRatingValue').value);
    
    fetch('index.php?route=feedback', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            document.getElementById('profileFeedbackForm').reset();
            closeFeedbackModal();
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
});

// Show reply field
function showReplyField(feedbackId) {
    const textarea = document.getElementById(`reply-${feedbackId}`);
    const replyBtn = document.querySelector(`[onclick="showReplyField(${feedbackId})"]`);
    const sendBtn = document.getElementById(`send-reply-btn-${feedbackId}`);
    const cancelBtn = document.getElementById(`cancel-reply-btn-${feedbackId}`);
    
    textarea.style.display = 'block';
    replyBtn.style.display = 'none';
    sendBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
    textarea.focus();
}

// Hide reply field
function hideReplyField(feedbackId) {
    const textarea = document.getElementById(`reply-${feedbackId}`);
    const replyBtn = document.querySelector(`[onclick="showReplyField(${feedbackId})"]`);
    const sendBtn = document.getElementById(`send-reply-btn-${feedbackId}`);
    const cancelBtn = document.getElementById(`cancel-reply-btn-${feedbackId}`);
    
    textarea.style.display = 'none';
    textarea.value = '';
    replyBtn.style.display = 'inline-block';
    sendBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
}

// Send reply from profile
function sendReplyFromProfile(feedbackId) {
    const textarea = document.getElementById(`reply-${feedbackId}`);
    const message = textarea.value.trim();
    
    if (!message) {
        showToast('Please enter your reply', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'add_reply');
    formData.append('feedback_id', feedbackId);
    formData.append('message', message);
    
    fetch('index.php?route=feedback', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            textarea.value = '';
            hideReplyField(feedbackId);
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}
</script>
</body>
</html>
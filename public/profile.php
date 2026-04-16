<?php 
session_start(); 
$page = 'profile'; 

// Simulate logged-in user data (in a real app, fetch from database)
$user = [
    'id' => 1,
    'first_name' => 'Maria',
    'last_name' => 'Santos',
    'email' => 'maria@email.com',
    'phone' => '+63 917 123 4567',
    'city' => 'Bacolod City',
    'birthday' => '1992-03-18',
    'avatar' => 'assets/img/elarie.jpg', // Local image path
    'member_since' => '2022-06-15',
    'is_premium' => true,
    'events_count' => 7,
    'rating' => 4.9,
    'total_spent' => '₱750k'
];

// Handle avatar upload
$avatar_upload_error = '';
$avatar_upload_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $upload_dir = __DIR__ . '/assets/img/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['avatar'];
    $file_name = 'user_' . $user['id'] . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $target_file = $upload_dir . $file_name;
    $relative_path = 'assets/img/' . $file_name;
    
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
                // Delete old avatar if it's not the default
                if ($user['avatar'] !== 'assets/images/avatars/default-avatar.jpg' && file_exists(__DIR__ . '/' . $user['avatar'])) {
                    unlink(__DIR__ . '/' . $user['avatar']);
                }
                $user['avatar'] = $relative_path;
                $avatar_upload_success = 'Profile picture updated successfully!';
            } else {
                $avatar_upload_error = 'Failed to upload image. Please try again.';
            }
        }
    } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
        $avatar_upload_error = 'An error occurred during upload.';
    }
}

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                // Validate and update profile logic here
                $success_message = 'Profile updated successfully!';
                // In a real app, update database with $_POST values
                break;
            case 'update_password':
                // Password update logic here
                $success_message = 'Password updated successfully!';
                break;
            case 'update_notifications':
                // Notification preferences update logic here
                $success_message = 'Notification preferences saved!';
                break;
        }
    }
}

// Check if avatar exists, otherwise use default
$avatar_path = !empty($user['avatar']) && file_exists(__DIR__ . '/' . $user['avatar']) 
    ? $user['avatar'] 
    : 'assets/images/avatars/default-avatar.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Profile — Sinta</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/profile.css">
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
    </style>
</head>
<body>

<?php include __DIR__ . '/../public/nav.php'; ?>

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
        
        <div class="default-avatars">
            <img src="assets/images/aelarie.jpg" class="default-avatar" onclick="selectDefaultAvatar('assets/images/avatars/default-avatar.jpg')">
            <img src="assets/images/elarie.jpg" class="default-avatar" onclick="selectDefaultAvatar('assets/images/avatars/avatar-2.jpg')">
            <img src="assets/images/elarie.jpg" class="default-avatar" onclick="selectDefaultAvatar('assets/images/avatars/avatar-3.jpg')">
            <img src="assets/images/elarie.jpg" class="default-avatar" onclick="selectDefaultAvatar('assets/images/avatars/avatar-4.jpg')">
        </div>
        
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

function selectDefaultAvatar(src) {
    // Highlight selected
    document.querySelectorAll('.default-avatar').forEach(avatar => {
        avatar.classList.remove('selected');
    });
    event.target.classList.add('selected');
    
    // Preview
    document.getElementById('avatarPreview').src = src;
    
    // You would need to handle default avatar selection via AJAX or form submission
    // For now, show a message
    showToast('Default avatar selected. Click Upload to save.', 'info');
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

// Refresh avatar after upload (iframe or AJAX handling)
<?php if ($avatar_upload_success): ?>
setTimeout(() => {
    location.reload();
}, 2000);
<?php endif; ?>
</script>
</body>
</html>
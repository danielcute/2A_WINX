<?php
session_start();
$page = 'checkout';

// Retrieve cart data from session storage (passed via GET or POST)
$cartItems = [];
$cartTotal = 0;
$cartSubtotal = 0;
$userData = [];
$packageDetails = [];

// Check if data was passed via POST (from customize page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $cartItems = json_decode($_POST['cart_data'], true);
    $_SESSION['checkout_cart'] = $cartItems;
    if (is_array($cartItems) && isset($cartItems['programFlow'])) {
        $_SESSION['checkout_program_flow'] = $cartItems['programFlow'];
    }
} 
// Check if data exists in session
else if (isset($_SESSION['checkout_cart'])) {
    $cartItems = $_SESSION['checkout_cart'];
}
// Check if passed via URL parameter (fallback)
else if (isset($_GET['cart'])) {
    $cartItems = json_decode(urldecode($_GET['cart']), true);
}

// Normalize cart items if payload contains items
if (is_array($cartItems) && isset($cartItems['items']) && is_array($cartItems['items'])) {
    $cartItems = $cartItems['items'];
}

// Ensure cartItems is an array
if (!is_array($cartItems)) {
    $cartItems = [];
}

// Retrieve logged-in user information
if (isset($_SESSION['user_id'])) {
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
    }
    require_once ROOT_PATH . '/config/database.php';
    require_once ROOT_PATH . '/app/models/User.php';
    
    $userModel = new User();
    $userData = $userModel->findById($_SESSION['user_id']);
}

// Calculate totals and extract package details
foreach ($cartItems as $item) {
    if (is_array($item) && isset($item['price'])) {
        $cartSubtotal += $item['price'];
        // Store package details for display
        if (isset($item['name']) && empty($packageDetails)) {
            $packageDetails = $item;
        }
    }
}
$serviceFee = round($cartSubtotal * 0.05);
$cartTotal = $cartSubtotal + $serviceFee;
$depositRequired = round($cartTotal * 0.5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Sinta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/SINTA/public/assets/css/global.css">
    <style>
        /* ========================================
           SINTA - CHECKOUT STYLES
           ======================================== */
        :root {
            --primary: #8A7650;
            --primary-dark: #6B5A3E;
            --primary-light: #A6956F;
            --primary-pale: rgba(138, 118, 80, 0.12);
            --white: #FFFFFF;
            --cream: #F5F0E8;
            --dark: #2C2820;
            --gray: #6B6463;
            --gray-light: #A8A09B;
            --border: #E2D9C8;
            --border-2: #D4CAB5;
            --success: #7A8F6B;
            --success-pale: rgba(122, 143, 107, 0.1);
            --serif: 'Cormorant Garamond', Georgia, serif;
            --sans: 'Inter', -apple-system, sans-serif;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 8px 24px rgba(0,0,0,0.08);
            --shadow-lg: 0 20px 40px rgba(0,0,0,0.12);
            --t-fast: 0.2s ease;
            --t-base: 0.3s ease;
            --nav-height: 76px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--sans);
            background: linear-gradient(135deg, var(--cream) 0%, #fff 100%);
            color: var(--dark);
            line-height: 1.5;
            font-size: 1rem;
            min-height: 100vh;
        }

        h1, h2, h3, h4 {
            font-family: var(--serif);
            font-weight: 500;
            letter-spacing: -0.02em;
        }
        h1 { font-size: 2.5rem; }
        h2 { font-size: 1.8rem; }
        h3 { font-size: 1.3rem; }

        /* Checkout Main */
        .checkout-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: calc(var(--nav-height) + 2rem);
        }
        .checkout-header { margin-bottom: 2rem; }
        .checkout-header h1 { margin: 0.5rem 0 0.25rem; }
        .checkout-header h1 em { color: var(--primary); font-style: italic; }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
        }
        .breadcrumb a { color: var(--primary); text-decoration: none; }
        .breadcrumb span { color: var(--gray-light); }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--primary);
        }
        .rule { width: 2rem; height: 1px; background: var(--primary); display: inline-block; }

        /* Checkout Grid */
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 2rem;
        }

        /* Form Section */
        .form-section {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-section__title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
        }
        .form-section__title i { color: var(--primary); font-size: 1.2rem; }
        .form-section__title h3 { font-size: 1.1rem; margin: 0; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-group label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--gray);
        }
        .form-group input, .form-group select, .form-group textarea {
            padding: 0.85rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-family: var(--sans);
            font-size: 0.9rem;
            transition: all var(--t-fast);
            outline: none;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-pale);
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .full-width { grid-column: span 2; }

        /* Payment Methods */
        .payment-methods {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }
        .payment-method {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--t-fast);
            background: var(--white);
        }
        .payment-method:hover { border-color: var(--primary); background: var(--primary-pale); }
        .payment-method.selected { border-color: var(--primary); background: var(--primary-pale); }
        .payment-method input { display: none; }
        .payment-method i { font-size: 1.3rem; color: var(--primary); }
        .payment-method span { font-weight: 500; font-size: 0.85rem; }

        /* Order Summary */
        .order-summary {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            position: sticky;
            top: calc(var(--nav-height) + 1rem);
        }
        .summary-header {
            padding: 1.25rem 1.5rem;
            background: var(--cream);
            border-bottom: 1px solid var(--border);
        }
        .summary-header h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
        }
        .summary-header h3 i { color: var(--primary); }

        .cart-items {
            padding: 1rem 1.5rem;
            max-height: 300px;
            overflow-y: auto;
        }
        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
        }
        .cart-item:last-child { border-bottom: none; }
        .cart-item__icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-md);
            background: var(--primary-pale);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
        }
        .cart-item__details { flex: 1; }
        .cart-item__name { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.2rem; }
        .cart-item__type {
            font-size: 0.7rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .cart-item__details-list {
            font-size: 0.7rem;
            color: var(--gray);
            margin-top: 0.25rem;
            line-height: 1.4;
        }
        .cart-item__price { font-weight: 600; font-size: 0.9rem; color: var(--dark); white-space: nowrap; }

        .summary-breakdown {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }
        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.85rem;
            color: var(--gray);
        }
        .breakdown-total {
            display: flex;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            background: var(--cream);
            font-weight: 700;
            font-size: 1.1rem;
        }
        .breakdown-total span:last-child {
            font-family: var(--serif);
            font-size: 1.4rem;
            color: var(--primary);
        }
        .deposit-note {
            padding: 1rem 1.5rem;
            background: var(--success-pale);
            border-top: 1px solid var(--border);
        }
        .deposit-note p {
            font-size: 0.75rem;
            color: var(--success);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkout-btn {
            width: calc(100% - 3rem);
            margin: 1.5rem;
            padding: 1rem;
            font-size: 0.9rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.8rem 1.6rem;
            font-family: var(--sans);
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
            border-radius: 999px;
            transition: all var(--t-base);
            cursor: pointer;
            border: none;
        }
        .btn--primary { background: var(--primary); color: white; }
        .btn--primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn--primary:disabled { 
            background: var(--gray-light); 
            color: white; 
            cursor: not-allowed; 
            transform: none;
            opacity: 0.7;
        }
        .btn--full { width: 100%; }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .fa-spin {
            animation: spin 1s linear infinite;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all var(--t-base);
        }
        .modal.active {
            opacity: 1;
            visibility: visible;
        }
        .modal__content {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 2rem;
            max-width: 450px;
            width: 90%;
            text-align: center;
            transform: scale(0.9);
            transition: transform var(--t-base);
        }
        .modal.active .modal__content {
            transform: scale(1);
        }
        .modal__icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--success-pale);
            color: var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }
        .modal__content h2 {
            font-family: var(--serif);
            margin-bottom: 0.5rem;
        }
        .modal__content h2 em { color: var(--primary); font-style: italic; }
        .modal__content p { color: var(--gray); margin-bottom: 1.5rem; }
        .modal__actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .checkout-grid { grid-template-columns: 1fr; }
            .order-summary { position: static; margin-top: 1.5rem; }
        }
        @media (max-width: 640px) {
            .checkout-main { padding: 1rem; padding-top: calc(var(--nav-height) + 1rem); }
            .form-row { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .payment-methods { flex-direction: column; }
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 3000;
            animation: slideIn 0.3s ease;
            max-width: 300px;
            box-shadow: var(--shadow-lg);
        }
        .toast.success {
            background: #2e7d32;
        }
        .toast.error {
            background: #c62828;
        }
        .toast.info {
            background: var(--primary);
        }
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @media (max-width: 640px) {
            .toast {
                bottom: 1rem;
                right: 1rem;
                left: 1rem;
                max-width: none;
            }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>

<main class="checkout-main">
    <div class="breadcrumb">
        <a href="occasions.php">Occasions</a> <span>/</span>
        <a href="packages.php">Packages</a> <span>/</span>
        <span>Checkout</span>
    </div>

    <div class="checkout-header">
        <div class="eyebrow"><span class="rule"></span> Step 3 of 3</div>
        <h1>Review & <em>Book</em> Your Event</h1>
        <p>Please review your selections and fill in your details to confirm the booking.</p>
    </div>

    <?php if ($userData): ?>
    <div style="background: var(--success-pale); border: 1px solid var(--success); border-radius: var(--radius-lg); padding: 1rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
        <i class="fas fa-check-circle" style="color: var(--success); font-size: 1.3rem;"></i>
        <div>
            <p style="font-size: 0.9rem; color: var(--success); margin: 0;">
                <strong>Logged in as:</strong> <?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?> (<?php echo htmlspecialchars($userData['email']); ?>)
            </p>
            <p style="font-size: 0.75rem; color: var(--gray); margin: 0.25rem 0 0;">Your contact information has been pre-filled below. You can edit it if needed.</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="checkout-grid">
        <!-- Left: Form Section -->
        <div>
            <!-- Event Details Form -->
            <div class="form-section">
                <div class="form-section__title">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Event Details</h3>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Event Name</label>
                        <input type="text" id="eventName" placeholder="e.g., Santos Wedding" value="">
                    </div>
                    <div class="form-group">
                        <label>Event Date</label>
                        <input type="date" id="eventDate" value="">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Event Time</label>
                        <input type="time" id="eventTime" value="">
                    </div>
                    <div class="form-group">
                        <label>Guest Count</label>
                        <input type="number" id="guestCount" placeholder="Number of guests" value="">
                    </div>
                </div>
                <div class="form-group full-width">
                    <label>Venue / Location</label>
                    <input type="text" id="venueLocation" placeholder="Venue name or address" value="">
                </div>
                <div class="form-group full-width">
                    <label>Special Requests</label>
                    <textarea id="specialRequests" placeholder="Any notes or special instructions for our team…"></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Program Flow</label>
                    <textarea id="programFlow" placeholder="Add your event program schedule here. Example:\n4:00 PM - Guest Arrival\n5:00 PM - Ceremony\n6:00 PM - Reception" rows="5"><?= htmlspecialchars($_SESSION['checkout_program_flow'] ?? '') ?></textarea>
                    <small style="font-size:0.8rem; color: var(--gray);">Write each item on a new line for the event timeline.</small>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-section">
                <div class="form-section__title">
                    <i class="fas fa-user"></i>
                    <h3>Contact Information</h3>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="fullName" placeholder="Your full name" value="<?php echo htmlspecialchars($userData ? ($userData['first_name'] . ' ' . $userData['last_name']) : 'Maria Santos'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($userData ? $userData['email'] : 'maria@email.com'); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" id="phone" placeholder="+63 XXX XXX XXXX" value="<?php echo htmlspecialchars($userData ? $userData['phone'] : '+63 917 123 4567'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Preferred Contact Method</label>
                        <select id="contactMethod">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="form-section">
                <div class="form-section__title">
                    <i class="fas fa-credit-card"></i>
                    <h3>Payment Method</h3>
                </div>
                <p style="font-size: 0.8rem; color: var(--gray); margin-bottom: 1rem;">A 50% deposit is required to confirm your booking. Balance is due 2 weeks before the event.</p>
                <div class="payment-methods">
                    <label class="payment-method selected" onclick="selectPayment(this, 'bank')">
                        <input type="radio" name="payment" value="bank" checked>
                        <i class="fas fa-building-columns"></i>
                        <span>Bank Transfer</span>
                    </label>
                    <label class="payment-method" onclick="selectPayment(this, 'gcash')">
                        <input type="radio" name="payment" value="gcash">
                        <i class="fas fa-mobile-alt"></i>
                        <span>GCash / Maya</span>
                    </label>
                    <label class="payment-method" onclick="selectPayment(this, 'credit')">
                        <input type="radio" name="payment" value="credit">
                        <i class="fas fa-credit-card"></i>
                        <span>Credit Card</span>
                    </label>
                </div>
                <p style="font-size: 0.75rem; color: var(--primary); margin-top: 1rem;"><i class="fas fa-info-circle"></i> Click on a payment method to enter your payment details</p>
            </div>
        </div>

        <!-- Right: Order Summary -->
        <div class="order-summary">
            <div class="summary-header">
                <h3><i class="fas fa-receipt"></i> Order Summary</h3>
            </div>
            
            <div class="cart-items" id="cartItems">
                <?php if (empty($cartItems)): ?>
                    <p style="text-align: center; color: var(--gray); padding: 1rem;">No items in cart. Please go back and select a package.</p>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item__icon">
                                <i class="<?= $item['type'] === 'custom' ? 'fas fa-magic' : 'fas fa-gift' ?>"></i>
                            </div>
                            <div class="cart-item__details">
                                <div class="cart-item__name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="cart-item__type"><?= $item['type'] === 'custom' ? 'Custom Package' : 'Pre-made Package' ?></div>
                                <?php if (!empty($item['features'])): ?>
                                    <div class="cart-item__details-list">
                                        <?php 
                                            $featureList = is_string($item['features']) ? explode("\n", $item['features']) : $item['features'];
                                            $firstThree = array_slice($featureList, 0, 3);
                                            echo implode(', ', array_map('trim', array_filter($firstThree)));
                                        ?>
                                    </div>
                                <?php elseif (!empty($item['details'])): ?>
                                    <div class="cart-item__details-list"><?= htmlspecialchars($item['details']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="cart-item__price">₱<?= number_format($item['price'], 0) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="summary-breakdown">
                <div class="breakdown-row">
                    <span>Subtotal</span>
                    <span>₱<?= number_format($cartSubtotal, 0) ?></span>
                </div>
                <div class="breakdown-row">
                    <span>Service Fee (5%)</span>
                    <span>₱<?= number_format($serviceFee, 0) ?></span>
                </div>
            </div>
            
            <div class="breakdown-total">
                <span>Total</span>
                <span>₱<?= number_format($cartTotal, 0) ?></span>
            </div>
            
            <div class="deposit-note">
                <p><i class="fas fa-info-circle"></i> Deposit Required (50%): <strong>₱<?= number_format($depositRequired, 0) ?></strong></p>
            </div>
            
            <button class="btn btn--primary btn--full checkout-btn" onclick="confirmBooking()">
                <i class="fas fa-lock"></i> Confirm Booking
            </button>
            <p style="font-size: 0.7rem; color: var(--gray-light); text-align: center; margin: 0 1.5rem 1.5rem;">
                By confirming, you agree to our <a href="#" style="color: var(--primary);">Terms of Service</a>
            </p>
        </div>
    </div>
</main>

<!-- Payment Method Modals -->

<!-- Bank Transfer Modal -->
<div id="bankTransferModal" class="modal">
    <div class="modal__content" style="max-width: 500px;">
        <div class="modal__header" style="text-align: left; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;"><i class="fas fa-building-columns"></i> Bank Transfer</h2>
            <button type="button" onclick="closePaymentModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-light);">×</button>
        </div>
        <form id="bankForm" class="payment-form">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Bank Name <span style="color: var(--success);">*</span></label>
                <input type="text" id="bankName" placeholder="e.g., BDO, BPI, Metrobank" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Account Number <span style="color: var(--success);">*</span></label>
                <input type="text" id="bankAccount" placeholder="Your bank account number" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Account Holder Name <span style="color: var(--success);">*</span></label>
                <input type="text" id="bankHolder" placeholder="Name on the account" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <p style="font-size: 0.8rem; color: var(--gray); background: var(--primary-pale); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;"><i class="fas fa-info-circle"></i> Your account information is secure and will only be used for payment verification.</p>
            <div style="display: flex; gap: 1rem;">
                <button type="button" onclick="closePaymentModal()" class="btn btn--ghost" style="flex: 1; background: white; border: 1px solid var(--border);">Cancel</button>
                <button type="submit" class="btn btn--primary" style="flex: 1;">Save Details</button>
            </div>
        </form>
    </div>
</div>

<!-- GCash/Maya Modal -->
<div id="gcashModal" class="modal">
    <div class="modal__content" style="max-width: 500px;">
        <div class="modal__header" style="text-align: left; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;"><i class="fas fa-mobile-alt"></i> GCash / Maya</h2>
            <button type="button" onclick="closePaymentModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-light);">×</button>
        </div>
        <form id="gcashForm" class="payment-form">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Mobile Number <span style="color: var(--success);">*</span></label>
                <input type="tel" id="gcashNumber" placeholder="+63 XXX XXX XXXX" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Account Holder Name <span style="color: var(--success);">*</span></label>
                <input type="text" id="gcashHolder" placeholder="Your full name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Account Type <span style="color: var(--success);">*</span></label>
                <select id="gcashType" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
                    <option value="">Select account type</option>
                    <option value="GCash">GCash</option>
                    <option value="Maya">Maya</option>
                </select>
            </div>
            <p style="font-size: 0.8rem; color: var(--gray); background: var(--primary-pale); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;"><i class="fas fa-info-circle"></i> Our team will send payment instructions to your registered number.</p>
            <div style="display: flex; gap: 1rem;">
                <button type="button" onclick="closePaymentModal()" class="btn btn--ghost" style="flex: 1; background: white; border: 1px solid var(--border);">Cancel</button>
                <button type="submit" class="btn btn--primary" style="flex: 1;">Save Details</button>
            </div>
        </form>
    </div>
</div>

<!-- Credit Card Modal -->
<div id="creditCardModal" class="modal">
    <div class="modal__content" style="max-width: 500px;">
        <div class="modal__header" style="text-align: left; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;"><i class="fas fa-credit-card"></i> Credit Card</h2>
            <button type="button" onclick="closePaymentModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-light);">×</button>
        </div>
        <form id="creditForm" class="payment-form">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Card Holder Name <span style="color: var(--success);">*</span></label>
                <input type="text" id="cardHolder" placeholder="Name on your card" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Card Number <span style="color: var(--success);">*</span></label>
                <input type="text" id="cardNumber" placeholder="XXXX XXXX XXXX XXXX" required maxlength="19" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans); letter-spacing: 2px;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label style="font-size: 0.85rem; font-weight: 600;">Expiry Date <span style="color: var(--success);">*</span></label>
                    <input type="text" id="cardExpiry" placeholder="MM/YY" required maxlength="5" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
                </div>
                <div class="form-group">
                    <label style="font-size: 0.85rem; font-weight: 600;">CVV <span style="color: var(--success);">*</span></label>
                    <input type="text" id="cardCVV" placeholder="XXX" required maxlength="4" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
                </div>
            </div>
            <p style="font-size: 0.8rem; color: var(--gray); background: var(--primary-pale); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;"><i class="fas fa-lock"></i> Your card information is encrypted and secure.</p>
            <div style="display: flex; gap: 1rem;">
                <button type="button" onclick="closePaymentModal()" class="btn btn--ghost" style="flex: 1; background: white; border: 1px solid var(--border);">Cancel</button>
                <button type="submit" class="btn btn--primary" style="flex: 1;">Save Details</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal" id="confirmationModal">
    <div class="modal__content">
        <div class="modal__icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Booking <em>Confirmed!</em></h2>
        <p>Your booking has been submitted. Our team will reach out within 24 hours to finalize every detail.</p>
        <div class="modal__actions">
           <a href="/SINTA/public/index.php?route=event-detail&id=1" class="btn btn--primary">View Event</a>
<a href="/SINTA/public/index.php?route=homepage" class="btn btn--ghost">Go Home</a>
        </div>
    </div>
</div>

<script>
// Global variable to store payment details
let paymentDetails = {
    method: 'bank',
    data: {}
};

// Payment method selection with modal opening
function selectPayment(element, method) {
    document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
    element.classList.add('selected');
    const radio = element.querySelector('input');
    if (radio) radio.checked = true;
    
    paymentDetails.method = method;
    
    // Open the appropriate modal
    closeAllPaymentModals();
    
    if (method === 'bank') {
        document.getElementById('bankTransferModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    } else if (method === 'gcash') {
        document.getElementById('gcashModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    } else if (method === 'credit') {
        document.getElementById('creditCardModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// Close all payment modals
function closeAllPaymentModals() {
    document.getElementById('bankTransferModal').classList.remove('active');
    document.getElementById('gcashModal').classList.remove('active');
    document.getElementById('creditCardModal').classList.remove('active');
}

// Close payment modal
function closePaymentModal() {
    closeAllPaymentModals();
    document.body.style.overflow = '';
}

// Format card number with spaces
document.addEventListener('DOMContentLoaded', function() {
    const cardNumberInput = document.getElementById('cardNumber');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }
    
    const expiryInput = document.getElementById('cardExpiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }
    
    const cvvInput = document.getElementById('cardCVV');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        });
    }
    
    // Bank form submission
    const bankForm = document.getElementById('bankForm');
    if (bankForm) {
        bankForm.addEventListener('submit', function(e) {
            e.preventDefault();
            paymentDetails.data = {
                bankName: document.getElementById('bankName').value,
                bankAccount: document.getElementById('bankAccount').value,
                bankHolder: document.getElementById('bankHolder').value
            };
            showToast('Bank transfer details saved!', 'success');
            closePaymentModal();
        });
    }
    
    // GCash form submission
    const gcashForm = document.getElementById('gcashForm');
    if (gcashForm) {
        gcashForm.addEventListener('submit', function(e) {
            e.preventDefault();
            paymentDetails.data = {
                gcashNumber: document.getElementById('gcashNumber').value,
                gcashHolder: document.getElementById('gcashHolder').value,
                gcashType: document.getElementById('gcashType').value
            };
            showToast('GCash/Maya details saved!', 'success');
            closePaymentModal();
        });
    }
    
    // Credit card form submission
    const creditForm = document.getElementById('creditForm');
    if (creditForm) {
        creditForm.addEventListener('submit', function(e) {
            e.preventDefault();
            paymentDetails.data = {
                cardHolder: document.getElementById('cardHolder').value,
                cardNumber: document.getElementById('cardNumber').value.replace(/\s/g, ''),
                cardExpiry: document.getElementById('cardExpiry').value,
                cardCVV: document.getElementById('cardCVV').value
            };
            showToast('Credit card details saved!', 'success');
            closePaymentModal();
        });
    }
});

// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Global flag to prevent double submissions
let isBookingInProgress = false;

// Confirm booking function
function confirmBooking() {
    // Prevent double submission
    if (isBookingInProgress) {
        showToast('Your booking is already being processed...', 'info');
        return;
    }
    
    // Validate form
    const eventName = document.getElementById('eventName')?.value;
    const eventDate = document.getElementById('eventDate')?.value;
    const fullName = document.getElementById('fullName')?.value;
    const email = document.getElementById('email')?.value;
    
    if (!eventName || !eventDate || !fullName || !email) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    // Validate payment details are filled
    if (!paymentDetails.data || Object.keys(paymentDetails.data).length === 0) {
        showToast('Please click on a payment method and fill in the required details', 'error');
        return;
    }
    
    // Set flag to prevent double submission
    isBookingInProgress = true;
    
    // Get the button and disable it
    const confirmBtn = document.querySelector('.checkout-btn');
    const originalBtnText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Get all form data
    const urlParams = new URLSearchParams(window.location.search);
    const occasionParam = urlParams.get('occasion') || 'Event';
    
    const bookingData = {
        eventName: eventName,
        eventDate: eventDate,
        eventTime: document.getElementById('eventTime')?.value,
        guestCount: document.getElementById('guestCount')?.value,
        venueLocation: document.getElementById('venueLocation')?.value,
        specialRequests: document.getElementById('specialRequests')?.value,
        programFlow: document.getElementById('programFlow')?.value,
        packageName: occasionParam,
        fullName: fullName,
        email: email,
        phone: document.getElementById('phone')?.value,
        contactMethod: document.getElementById('contactMethod')?.value,
        paymentMethod: paymentDetails.method,
        paymentDetails: JSON.stringify(paymentDetails.data),
        cartItems: <?= json_encode($cartItems) ?>,
        subtotal: <?= $cartSubtotal ?>,
        serviceFee: <?= $serviceFee ?>,
        total: <?= $cartTotal ?>,
        deposit: <?= $depositRequired ?>
    };

    fetch('/SINTA/public/index.php?route=checkout-submit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(bookingData),
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            showToast('Booking confirmed! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = '/SINTA/public/index.php?route=plans';
            }, 1500);
        } else {
            showToast(result.message || 'Failed to save your booking.', 'error');
            // Re-enable button on error so user can retry
            isBookingInProgress = false;
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Booking save error:', error);
        showToast('Unable to save booking. Please try again.', 'error');
        // Re-enable button on error so user can retry
        isBookingInProgress = false;
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalBtnText;
    });
}

// Close modal when clicking outside
document.getElementById('confirmationModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Close payment modals when clicking outside
document.getElementById('bankTransferModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});

document.getElementById('gcashModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});

document.getElementById('creditCardModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});

// If cart is empty, show warning and prevent checkout
<?php if (empty($cartItems)): ?>
document.querySelector('.checkout-btn')?.addEventListener('click', function(e) {
    e.preventDefault();
    showToast('Your cart is empty. Please go back and select a package or customize your event first.', 'error');
    setTimeout(() => {
        window.location.href = 'packages.php';
    }, 1500);
});
<?php endif; ?>
</script>
</body>
</html>
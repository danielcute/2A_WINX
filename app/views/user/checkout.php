<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page = 'checkout';

// Include payment modal component
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}
require_once ROOT_PATH . '/services/PaymentService.php';
require_once ROOT_PATH . '/services/PaymentGateways.php';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <title>Checkout — Sinta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    <style>
        /* FullCalendar Customization */
        .fc {
            font-family: var(--sans);
        }
        .fc .fc-button-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .fc .fc-button-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .fc .fc-button-primary.fc-button-active {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .fc-daygrid-day.fc-day-other {
            opacity: 0.2;
        }
        .fc-daygrid-day:hover {
            background-color: var(--primary-pale);
            cursor: pointer;
        }
        .fc-col-header-cell {
            background-color: var(--cream);
            color: var(--dark);
            font-weight: 600;
            padding: 12px 0;
        }
        .fc-daygrid-day {
            padding: 8px;
            min-height: 60px;
        }
        .fc-daygrid-day-number {
            padding: 8px;
        }
        .fc-daygrid-day.selected-date {
            background-color: #8A7650 !important;
            color: white;
        }
        .fc-daygrid-day.selected-date .fc-daygrid-day-number {
            color: white;
            font-weight: 700;
        }
        .fc-event {
            border: none !important;
            padding: 2px 4px !important;
        }
        .fc-daygrid-day.fc-day-disabled {
            background-color: #f0f0f0;
            opacity: 0.6;
        }
    </style>
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

        /* Enhanced Mobile Navigation UX */
        @media (max-width: 768px) {
            .app-nav {
                background: rgba(255, 255, 255, 0.8) !important;
                backdrop-filter: blur(15px) saturate(180%);
                -webkit-backdrop-filter: blur(15px) saturate(180%);
                padding-top: env(safe-area-inset-top);
            }

            #mobileMenu {
                position: fixed;
                top: 0;
                right: 0;
                width: 280px;
                max-width: 85%;
                height: 100vh;
                background: white;
                box-shadow: -10px 0 30px rgba(0,0,0,0.08);
                transform: translateX(100%);
                transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                display: flex !important;
                flex-direction: column;
                padding: calc(80px + env(safe-area-inset-top)) 1.5rem 2rem;
                z-index: 2001;
                visibility: hidden;
                will-change: transform;
            }

            #mobileMenu.active {
                transform: translateX(0);
                visibility: visible;
            }

            body.mobile-menu-open::after {
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.3);
                backdrop-filter: blur(4px);
                z-index: 2000;
            }
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
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
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
                </div>
                <div class="form-group full-width">
                    <label>Event Date & Time <span style="color: var(--success);">*</span></label>
                    <p style="font-size: 0.75rem; color: var(--gray); margin: 0 0 1rem 0;"><i class="fas fa-info-circle"></i> Click on a date to see weather forecast and available times</p>
                    
                    <!-- Weather Info Display -->
                    <div id="weatherInfo" style="padding: 1rem; background: #f9f7f3; border-radius: 8px; margin-bottom: 1rem; display: none; border-left: 4px solid var(--primary);">
                        <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem;">
                            <span id="weatherIcon" style="font-size: 2rem;">🌡️</span>
                            <div style="flex: 1;">
                                <strong id="weatherCondition">Loading weather...</strong>
                                <div style="font-size: 0.85rem; color: #666; margin-top: 0.25rem;">
                                    <span id="weatherTemp">--°C</span> | <span id="weatherAvailability" style="font-weight: 600;">Checking availability...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Container -->
                    <div style="background: white; border: 1.5px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1rem; overflow: auto;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 1rem;">
                            <button type="button" id="calendarPrevBtn" class="btn btn--primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; flex: 0 0 auto;">← Prev</button>
                            <h3 id="calendarMonthYear" style="margin: 0; font-family: var(--serif); font-size: 1.2rem; flex: 1; text-align: center;">Loading...</h3>
                            <button type="button" id="calendarNextBtn" class="btn btn--primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; flex: 0 0 auto;">Next →</button>
                        </div>
                        <div id="customCalendar" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; min-height: 300px;">
                            <!-- Calendar grid generated by JavaScript -->
                        </div>
                    </div>

                    <!-- Legend -->
                    <div style="display: flex; gap: 2rem; margin-bottom: 1.5rem; padding: 1rem; background: var(--cream); border-radius: var(--radius-md);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                            <div style="width: 12px; height: 12px; background: #2e7d32; border-radius: 2px;"></div>
                            <span>Available</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                            <div style="width: 12px; height: 12px; background: #999999; border-radius: 2px;"></div>
                            <span>Booked</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                            <div style="width: 12px; height: 12px; background: #ccc; border-radius: 2px;"></div>
                            <span>Past Date</span>
                        </div>
                    </div>

                    <!-- Time Selection -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #666; display: block; margin-bottom: 0.5rem;">Start Time *</label>
                            <select id="eventStartTime" style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.9rem; background: white; cursor: pointer;">
                                <option value="">-- Select start time --</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #666; display: block; margin-bottom: 0.5rem;">End Time *</label>
                            <select id="eventEndTime" style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.9rem; background: white; cursor: pointer;">
                                <option value="">-- Select end time --</option>
                            </select>
                        </div>
                    </div>

                    <button type="button" id="confirmTimeBtn" class="btn btn--primary" style="width: 100%; display: none; margin-bottom: 1rem;">
                        <i class="fas fa-check"></i> Confirm Time
                    </button>

                    <input type="hidden" id="tempEventDate" value="">
                    <input type="hidden" id="tempEventTime" value="">
                    <input type="hidden" id="eventDate" value="">
                    <input type="hidden" id="eventTime" value="">

                    <!-- Selected Time Display -->
                    <div style="padding: 1rem; background: var(--primary-pale); border-radius: var(--radius-md); border-left: 4px solid var(--primary);">
                        <p style="margin: 0; font-size: 0.9rem; color: var(--dark);"><strong>Selected:</strong> <span id="selectedDateTime">No date/time selected</span></p>
                        <div id="availabilityStatus" style="font-size: 0.85rem; margin-top: 0.5rem; padding: 0.5rem; background: white; border-radius: 4px; display: none;">
                            <span id="availabilityText"></span>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Guest Count</label>
                        <input type="number" id="guestCount" placeholder="Number of guests" value="">
                    </div>
                </div>
                <div class="form-group full-width">
                    <label>Venue / Location</label>
                    <input type="text" id="venueLocation" placeholder="Venue name or address" value="" required>
                    <div id="map" style="height: 350px; width: 100%; border-radius: 12px; margin-top: 10px; border: 2px solid var(--primary); z-index: 10;"></div>
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                </div>
                <div class="form-group full-width">
                    <label>Special Requests</label>
                    <textarea id="specialRequests" placeholder="Any notes or special instructions for our team…"></textarea>
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
            <!-- Payment info - actual payment will be processed on next page -->
            <div class="form-section">
                <p style="font-size: 0.8rem; color: var(--gray); margin-bottom: 1rem;"><i class="fas fa-info-circle"></i> A 50% deposit is required to confirm your booking. Balance is due 2 weeks before the event.</p>
                <p style="font-size: 0.8rem; color: var(--primary); font-weight: 600;">Payment options available on the next page.</p>
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

<!-- Old payment modals removed - using new payment-modal.php component -->

<!-- Confirmation Modal -->
<div class="modal" id="confirmationModal">
    <div class="modal__content">
        <div class="modal__icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Booking <em>Confirmed!</em></h2>
        <p>Your booking has been submitted. Our team will reach out within 24 hours to finalize every detail.</p>
        <div class="modal__actions">
           <a href="/index.php?route=event-detail&id=1" class="btn btn--primary">View Event</a>
<a href="/index.php?route=homepage" class="btn btn--ghost">Go Home</a>
        </div>
    </div>
</div>

<script>
// ============================================
// GLOBAL VARIABLES - DECLARE FIRST
// ============================================
let paymentDetails = {
    method: 'bank',
    data: {}
};

let eventCalendar = null;
let selectedDateForTime = null;

// Calendar variables - MUST be declared before DOMContentLoaded
let currentCalendarMonth = new Date();
let bookedDates = [];

// ============================================
// BOOTSTRAP INITIALIZATION
// ============================================
(function() {
  function bootstrap() {
    // Initialize core calendar with full functionality
    console.log('Bootstrap: Initializing calendar system...');
    try {
      initializeHiddenInputs();
      initializeEventCalendar();
      setupConfirmTimeButton();
      setupPaymentFormHandlers();
      setupModalOutsideClickHandlers();
      setupEmptyCartWarning();
      console.log('✓ Bootstrap complete: All systems initialized');
    } catch(e) {
      console.error('Bootstrap error:', e);
    }
  }
  
  // Initialize on load or immediately if already loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap);
  } else {
    bootstrap();
  }
})();

function initializeMap() {
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;

    var map = L.map('map').setView([14.5995, 120.9842], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var marker;
    map.on('click', function(e) {
        if (marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
        document.getElementById('latitude').value = e.latlng.lat;
        document.getElementById('longitude').value = e.latlng.lng;
    });
    
    setTimeout(() => map.invalidateSize(), 500);
}

// ============================================
// 1. INITIALIZE HIDDEN INPUTS
// ============================================
function initializeHiddenInputs() {
    if (!document.getElementById('tempEventDate')) {
        const tempDate = document.createElement('input');
        tempDate.type = 'hidden';
        tempDate.id = 'tempEventDate';
        document.body.appendChild(tempDate);
        
        const tempTime = document.createElement('input');
        tempTime.type = 'hidden';
        tempTime.id = 'tempEventTime';
        document.body.appendChild(tempTime);
    }
}

// ============================================
// 2. SETUP CARD FORMATTING
// ============================================
function setupCardFormatting() {
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
}

// ============================================
// 3. SETUP PAYMENT METHODS
// ============================================
function setupPaymentMethods() {
    // Payment method selection is handled by onclick="selectPayment(...)" in HTML
    // This function is just for documentation
}

// ============================================
// 4. SETUP TIME SELECTORS
// ============================================
function setupTimeSelectors() {
    const startTimeSelect = document.getElementById('eventStartTime');
    const endTimeSelect = document.getElementById('eventEndTime');
    
    if (startTimeSelect) {
        startTimeSelect.addEventListener('change', updateEndTimeOptions);
    }
}

// ============================================
// 5. INITIALIZE EVENT CALENDAR (CUSTOM IMPLEMENTATION)
// ============================================

function initializeEventCalendar() {
    console.log('Initializing custom calendar...', { currentCalendarMonth, bookedDates });
    try {
        buildCalendar(currentCalendarMonth);
        setupCalendarNavigation();
        // Load booked dates asynchronously after initial render
        setTimeout(() => loadBookedDates(), 500);
    } catch(err) {
        console.error('Error initializing calendar:', err);
    }
}

function buildCalendar(date) {
    const container = document.getElementById('customCalendar');
    if (!container) return;
    
    const year = date.getFullYear();
    const month = date.getMonth();
    
    // Update month/year
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
    const monthEl = document.getElementById('calendarMonthYear');
    if (monthEl) monthEl.textContent = monthNames[month] + ' ' + year;
    
    // Build calendar HTML
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    let html = '';
    
    // Day headers
    ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
        html += `<div style="text-align: center; font-weight: 600; padding: 0.5rem; color: var(--primary);">${day}</div>`;
    });
    
    // Empty cells before month
    for (let i = 0; i < firstDay; i++) {
        html += '<div></div>';
    }
    
    // Days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateObj = new Date(year, month, day);
        // Format as local date (YYYY-MM-DD) accounting for timezone offset
        const yyyy = dateObj.getFullYear();
        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd = String(dateObj.getDate()).padStart(2, '0');
        const dateStr = yyyy + '-' + mm + '-' + dd;
        const isPast = dateObj < today;
        const isToday = dateObj.getTime() === today.getTime();
        const isBooked = bookedDates.includes(dateStr);
        
        const bgColor = isPast ? '#f5f5f5' : (isBooked ? '#E8DCC8' : 'white');
        const borderColor = isBooked ? '#8A7650' : 'var(--border)';
        const opacity = isPast ? '0.6' : '1';
        const fontWeight = isToday ? '700' : '500';
        const cursor = isPast ? 'not-allowed' : 'pointer';
        const clickHandler = !isPast ? `class="calendar-clickable"` : '';
        
        html += `<div 
            data-date="${dateStr}" 
            data-year="${year}"
            data-month="${month}"
            data-day="${day}"
            ${clickHandler}
            style="
                padding: 0.75rem;
                border: 1px solid ${borderColor};
                border-radius: 6px;
                text-align: center;
                cursor: ${cursor};
                background: ${bgColor};
                opacity: ${opacity};
                font-weight: ${fontWeight};
                min-height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                position: relative;
            "
        >${day}${isBooked ? '<span style="position: absolute; top: 2px; right: 4px; font-size: 0.7rem; background: #8A7650; color: white; padding: 2px 4px; border-radius: 2px;">✓</span>' : ''}</div>`;
    }
    
    container.innerHTML = html;
    
    // Attach click and hover handlers
    document.querySelectorAll('.calendar-clickable').forEach(cell => {
        const dateStr = cell.dataset.date;
        const year = parseInt(cell.dataset.year);
        const month = parseInt(cell.dataset.month);
        const day = parseInt(cell.dataset.day);
        const dateObj = new Date(year, month, day);
        const isBooked = bookedDates.includes(dateStr);
        const bgColor = isBooked ? '#E8DCC8' : 'white';
        const hoverColor = isBooked ? '#D9CDB5' : 'var(--primary-pale)';
        
        cell.addEventListener('click', () => {
            selectCalendarDate(dateStr, dateObj);
        });
        
        cell.addEventListener('mouseover', () => {
            cell.style.backgroundColor = hoverColor;
        });
        
        cell.addEventListener('mouseout', () => {
            cell.style.backgroundColor = bgColor;
        });
    });
    
    console.log('Calendar rendered for', monthNames[month], year);
}

function setupCalendarNavigation() {
    const prevBtn = document.getElementById('calendarPrevBtn');
    const nextBtn = document.getElementById('calendarNextBtn');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            currentCalendarMonth.setMonth(currentCalendarMonth.getMonth() - 1);
            buildCalendar(currentCalendarMonth);
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            currentCalendarMonth.setMonth(currentCalendarMonth.getMonth() + 1);
            buildCalendar(currentCalendarMonth);
        });
    }
}

function loadBookedDates() {
    console.log('Loading booked dates...');
    // Fetch booked dates from API
    fetch('api-calendar.php?action=getMonthBookings&month=' + (currentCalendarMonth.getMonth() + 1) + '&year=' + currentCalendarMonth.getFullYear())
        .then(response => {
            if (!response.ok) throw new Error('API error: ' + response.status);
            return response.json();
        })
        .then(data => {
            console.log('Booked dates API response:', data);
            if (data && data.bookings && Array.isArray(data.bookings)) {
                bookedDates = data.bookings.map(b => b.date || b.event_date).filter(d => d);
                console.log('Loaded booked dates:', bookedDates);
            }
            buildCalendar(currentCalendarMonth);
        })
        .catch(error => {
            console.warn('Error loading booked dates (proceeding with empty):', error);
            bookedDates = [];
            buildCalendar(currentCalendarMonth);
        });
}

function selectCalendarDate(dateStr, dateObj) {
    console.log('Date selected:', dateStr);
    
    // Update selected date display
    selectedDateForTime = dateStr;
    document.getElementById('tempEventDate').value = dateStr;
    
    const dateDisplay = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('selectedDateTime').textContent = dateDisplay;
    
    // Fetch weather
    fetchWeatherForDate(dateStr);
    
    // Check availability and populate time slots
    checkAvailabilityAndShowTimes(dateStr);
    
    // Show confirm button
    document.getElementById('confirmTimeBtn').style.display = 'inline-flex';
    
    // Rebuild calendar to show selection
    buildCalendar(currentCalendarMonth);
}

// ============================================
// FETCH WEATHER FOR SELECTED DATE
// ============================================
function fetchWeatherForDate(dateStr) {
    const weatherInfo = document.getElementById('weatherInfo');
    if (!weatherInfo) return;
    
    fetch(`/api-weather.php?action=getForecast&date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('weatherIcon').textContent = data.icon;
                document.getElementById('weatherCondition').textContent = data.condition;
                document.getElementById('weatherTemp').textContent = `${data.tempMin}°C - ${data.tempMax}°C`;
                
                const availElement = document.getElementById('weatherAvailability');
                availElement.textContent = data.availability.message;
                availElement.style.color = data.availability.color || '#666';
                
                weatherInfo.style.display = 'block';
            } else {
                weatherInfo.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching weather:', error);
            weatherInfo.style.display = 'none';
        });
}

// ============================================
// 6. SETUP CONFIRM TIME BUTTON
// ============================================
function setupConfirmTimeButton() {
    const confirmBtn = document.getElementById('confirmTimeBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            confirmTimeSelection();
        });
    }
}

// ============================================
// 7. SETUP PAYMENT FORM HANDLERS
// ============================================
function setupPaymentFormHandlers() {
    // Payment details will be filled in during payment modal interaction
    // This is handled by the payment-modal.php component
}

// ============================================
// 8. SETUP MODAL OUTSIDE CLICK HANDLERS
// ============================================
function setupModalOutsideClickHandlers() {
    document.getElementById('confirmationModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

// ============================================
// 9. SETUP EMPTY CART WARNING
// ============================================
function setupEmptyCartWarning() {
    <?php if (empty($cartItems)): ?>
    document.querySelector('.checkout-btn')?.addEventListener('click', function(e) {
        e.preventDefault();
        showToast('Your cart is empty. Please go back and select a package or customize your event first.', 'error');
        setTimeout(() => {
            window.location.href = 'packages.php';
        }, 1500);
    });
    <?php endif; ?>
}

// ============================================
// PAYMENT METHOD SELECTION
// ============================================
function selectPayment(element, method) {
    document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
    element.classList.add('selected');
    const radio = element.querySelector('input');
    if (radio) radio.checked = true;
    
    paymentDetails.method = method;
    paymentDetails.data = {}; // Reset data
    
    console.log('Payment method selected:', method);
    showToast('Payment method selected: ' + method.toUpperCase(), 'info');
}

// ============================================
// CALENDAR AND TIME SELECTION FUNCTIONS
// ============================================

// Check availability and populate time slots
async function checkAvailabilityAndShowTimes(selectedDate) {
    const timeSlots = generateTimeSlots();
    
    try {
        const apiUrl = `public/api-calendar.php?action=getDateBookings&date=${selectedDate}`;
        console.log('Fetching bookings from:', apiUrl);
        
        const response = await fetch(apiUrl);
        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }
        
        const data = await response.json();
        const bookings = data.bookings || data || [];
        
        console.log('Bookings for', selectedDate, ':', bookings);
        populateTimeSlots(selectedDate, timeSlots, bookings);
    } catch (error) {
        console.error('Error checking availability:', error);
        showToast('Error loading available times. Using all time slots.', 'error');
        populateTimeSlots(selectedDate, timeSlots, []);
    }
}

// Generate time slots from 8 AM to 9 PM (30-minute intervals)
function generateTimeSlots() {
    const slots = [];
    for (let hour = 8; hour <= 21; hour++) {
        slots.push(`${String(hour).padStart(2, '0')}:00`);
        if (hour < 21) slots.push(`${String(hour).padStart(2, '0')}:30`);
    }
    return slots;
}

// Format time to display format
function formatTimeDisplay(time) {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
    return `${displayHour}:${minutes} ${ampm}`;
}

// Check if time slot is available
function isTimeAvailable(slot, bookings) {
    if (!Array.isArray(bookings) || bookings.length === 0) {
        return true;  // All slots available if no bookings
    }
    
    const [slotHour, slotMin] = slot.split(':').map(Number);
    
    for (const booking of bookings) {
        let bookStart, bookEnd;
        const timeStr = booking.timeRange || booking.event_time || '';
        
        if (!timeStr) {
            continue;  // Skip if no time info
        }
        
        // Handle both "HH:MM - HH:MM" and "HH:MM" formats
        if (timeStr.includes(' - ')) {
            [bookStart, bookEnd] = timeStr.split(' - ');
        } else if (timeStr.includes(':')) {
            // Single time - assume 1-hour duration
            bookStart = timeStr;
            const [hour, min] = timeStr.split(':').map(Number);
            const endHour = (hour + 1) % 24;  // Add 1 hour
            bookEnd = String(endHour).padStart(2, '0') + ':' + String(min).padStart(2, '0');
        } else {
            continue;  // Skip if we can't parse
        }
        
        try {
            // Parse times - handle both 24-hour format and 12-hour with AM/PM
            let startH = parseInt(bookStart.split(':')[0]);
            let startM = parseInt(bookStart.split(':')[1]);
            let endH = parseInt(bookEnd.split(':')[0]);
            let endM = parseInt(bookEnd.split(':')[1]);
            
            // If time has AM/PM, convert to 24-hour (simple extraction of hour/min first)
            if (bookStart.includes('AM') || bookStart.includes('PM')) {
                const parts = bookStart.match(/(\d+):(\d+)\s*(AM|PM)/i);
                if (parts) {
                    startH = parseInt(parts[1]);
                    startM = parseInt(parts[2]);
                    if (parts[3].toUpperCase() === 'PM' && startH !== 12) startH += 12;
                    if (parts[3].toUpperCase() === 'AM' && startH === 12) startH = 0;
                }
            }
            
            if (bookEnd.includes('AM') || bookEnd.includes('PM')) {
                const parts = bookEnd.match(/(\d+):(\d+)\s*(AM|PM)/i);
                if (parts) {
                    endH = parseInt(parts[1]);
                    endM = parseInt(parts[2]);
                    if (parts[3].toUpperCase() === 'PM' && endH !== 12) endH += 12;
                    if (parts[3].toUpperCase() === 'AM' && endH === 12) endH = 0;
                }
            }
            
            const slotTime = slotHour * 60 + slotMin;
            const bookStartTime = startH * 60 + startM;
            const bookEndTime = endH * 60 + endM;
            
            // Check if slot overlaps with booking
            if (slotTime >= bookStartTime && slotTime < bookEndTime) {
                return false;
            }
        } catch (e) {
            console.error('Error parsing booking time:', timeStr, e);
            continue;
        }
    }
    return true;
}

// Populate time select dropdowns
function populateTimeSlots(selectedDate, slots, bookings) {
    const startSelect = document.getElementById('eventStartTime');
    const endSelect = document.getElementById('eventEndTime');
    
    if (!startSelect || !endSelect) {
        console.error('Time select elements not found');
        return;
    }
    
    // Clear existing options except the first placeholder
    startSelect.innerHTML = '<option value="">-- Select start time --</option>';
    endSelect.innerHTML = '<option value="">-- Select end time --</option>';
    
    slots.forEach(slot => {
        const displayTime = formatTimeDisplay(slot);
        const isAvailable = isTimeAvailable(slot, bookings);
        
        const option = document.createElement('option');
        option.value = slot;
        option.textContent = displayTime + (isAvailable ? '' : ' (Booked)');
        option.disabled = !isAvailable;
        option.style.color = isAvailable ? 'inherit' : '#999';
        
        startSelect.appendChild(option.cloneNode(true));
    });
    
    // Reattach change listener to start time select (it was cleared when we rebuilt options)
    startSelect.removeEventListener('change', updateEndTimeOptions);
    startSelect.addEventListener('change', updateEndTimeOptions);
    
    // Show selected date with available times count
    const availableTimes = slots.filter(slot => isTimeAvailable(slot, bookings)).length;
    const dateObj = new Date(selectedDate);
    const dateStr = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('selectedDateTime').textContent = dateStr + ' — ' + availableTimes + ' time slots available';
}

function updateEndTimeOptions() {
    const startTime = document.getElementById('eventStartTime').value;
    const endSelect = document.getElementById('eventEndTime');
    
    if (!startTime) {
        endSelect.innerHTML = '<option value="">-- Select end time --</option>';
        return;
    }
    
    const slots = generateTimeSlots();
    const startIndex = slots.indexOf(startTime);
    
    endSelect.innerHTML = '<option value="">-- Select end time --</option>';
    
    // Add end times after start time (minimum 1 hour duration)
    const minEndIndex = Math.max(startIndex + 2, startIndex + 1);  // At least 1 hour
    for (let i = minEndIndex; i < slots.length; i++) {
        const slot = slots[i];
        const displayTime = formatTimeDisplay(slot);
        
        const option = document.createElement('option');
        option.value = slot;
        option.textContent = displayTime;
        endSelect.appendChild(option);
    }
}

function confirmTimeSelection() {
    const startTime = document.getElementById('eventStartTime')?.value;
    const endTime = document.getElementById('eventEndTime')?.value;
    const selectedDate = document.getElementById('tempEventDate').value;
    
    if (!selectedDate) {
        showToast('Please select a date first', 'error');
        return;
    }
    
    if (!startTime || !endTime) {
        showToast('Please select both start and end time', 'error');
        return;
    }
    
    // Store values in hidden fields
    document.getElementById('eventDate').value = selectedDate;
    document.getElementById('eventTime').value = startTime + ' - ' + endTime;
    
    // Update display
    const dateObj = new Date(selectedDate);
    const dateStr = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const startDisplay = formatTimeDisplay(startTime);
    const endDisplay = formatTimeDisplay(endTime);
    
    document.getElementById('selectedDateTime').textContent = dateStr + ' from ' + startDisplay + ' to ' + endDisplay;
    
    // Show availability status
    showAvailabilityStatus(true);
    showToast('Event time confirmed!', 'success');
    
    // Hide the confirm button
    document.getElementById('confirmTimeBtn').style.display = 'none';
}

// Fetch weather for selected date
function fetchWeatherForDate(dateStr) {
    const weatherInfo = document.getElementById('weatherInfo');
    if (!weatherInfo) return;
    
    fetch(`api-weather.php?action=getForecast&date=${dateStr}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('weatherIcon').textContent = data.icon;
          document.getElementById('weatherCondition').textContent = data.condition;
          document.getElementById('weatherTemp').textContent = `${data.tempMin}°C - ${data.tempMax}°C`;
          
          const availElement = document.getElementById('weatherAvailability');
          availElement.textContent = data.availability.message;
          availElement.style.color = data.availability.color;
          
          weatherInfo.style.display = 'block';
        } else {
          weatherInfo.style.display = 'none';
        }
      })
      .catch(error => {
        console.error('Error fetching weather:', error);
      });
}

function showAvailabilityStatus(available) {
    const statusDiv = document.getElementById('availabilityStatus');
    const statusText = document.getElementById('availabilityText');
    
    if (available) {
        statusDiv.style.display = 'block';
        statusDiv.style.background = '#e8f5e9';
        statusDiv.style.color = '#2e7d32';
        statusDiv.style.borderLeft = '4px solid #2e7d32';
        statusText.innerHTML = '<i class="fas fa-check-circle"></i> Time slot is available for booking';
    } else {
        statusDiv.style.display = 'block';
        statusDiv.style.background = '#ffebee';
        statusDiv.style.color = '#c62828';
        statusDiv.style.borderLeft = '4px solid #c62828';
        statusText.innerHTML = '<i class="fas fa-times-circle"></i> Time slot is not available';
    }
}

// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ============================================
// BOOKING CONFIRMATION
// ============================================

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
    const eventStartTime = document.getElementById('eventStartTime')?.value;
    const eventEndTime = document.getElementById('eventEndTime')?.value;
    const fullName = document.getElementById('fullName')?.value;
    const email = document.getElementById('email')?.value;
    
    if (!eventName || !eventDate || !eventStartTime || !eventEndTime || !fullName || !email) {
        showToast('Please fill in all required fields including event date and times', 'error');
        return;
    }
    
    // Validate payment method is selected
    if (!paymentDetails.method) {
        showToast('Please select a payment method', 'error');
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
    
    // Combine date and time
    const startDateTime = eventDate + ' ' + eventStartTime;
    const endDateTime = eventDate + ' ' + eventEndTime;
    
    const bookingData = {
        eventName: eventName,
        eventDate: eventDate,
        eventStartTime: eventStartTime,
        eventEndTime: eventEndTime,
        startDateTime: startDateTime,
        endDateTime: endDateTime,
        guestCount: document.getElementById('guestCount')?.value,
        venueLocation: document.getElementById('venueLocation')?.value,
        specialRequests: document.getElementById('specialRequests')?.value,
        packageName: occasionParam,
        fullName: fullName,
        email: email,
        phone: document.getElementById('phone')?.value,
        contactMethod: document.getElementById('contactMethod')?.value,
        latitude: document.getElementById('latitude')?.value,
        longitude: document.getElementById('longitude')?.value,
        paymentMethod: paymentDetails.method,
        paymentDetails: paymentDetails.data,
        cartItems: <?= json_encode($cartItems) ?>,
        subtotal: <?= $cartSubtotal ?>,
        serviceFee: <?= $serviceFee ?>,
        total: <?= $cartTotal ?>,
        deposit: <?= $depositRequired ?>
    };

    // Open Agreement Modal first
    if (typeof openAgreementModal === 'function') {
        openAgreementModal();
        return;
    }
    
    fetch('index.php?route=checkout-submit', {
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
        if (result.success && result.plan_id) {
            showToast('Booking created! Redirecting to event details...', 'success');
            
            // Redirect to event detail page with plan_id after brief delay
            setTimeout(() => {
                window.location.href = `/index.php?route=event-detail&id=${result.plan_id}`;
            }, 1200);
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

<!-- Booking Agreement Modal Component -->
<?php include ROOT_PATH . '/public/booking-agreement-modal.php'; ?>

<!-- Payment Modal Component -->
<?php include __DIR__ . '/../components/payment-modal.php'; ?>

</body>
</html>
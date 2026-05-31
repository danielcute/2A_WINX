<?php
class CheckoutController {
    public function index() {
        $page = 'checkout';
        
        $cartItems = [];
        $cartTotal = 0;
        $cartSubtotal = 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
            $cartItems = json_decode($_POST['cart_data'], true);
            $_SESSION['checkout_cart'] = $cartItems;
        } elseif (isset($_SESSION['checkout_cart'])) {
            $cartItems = $_SESSION['checkout_cart'];
        } elseif (isset($_GET['cart'])) {
            $cartItems = json_decode(urldecode($_GET['cart']), true);
        }
        
        if (!is_array($cartItems)) {
            $cartItems = [];
        }
        
        // Normalize cart items - handle case where POST sends items as array
        if (!empty($cartItems) && isset($cartItems[0]) && is_array($cartItems[0])) {
            $normalizedItems = $cartItems;
        } else if (!empty($cartItems) && isset($cartItems['items']) && is_array($cartItems['items'])) {
            $normalizedItems = $cartItems['items'];
        } else if (!empty($cartItems) && !isset($cartItems['price'])) {
            // Cart sent as single object, wrap in array
            $normalizedItems = [$cartItems];
        } else {
            $normalizedItems = $cartItems;
        }
        
        $cartItems = $normalizedItems;
        
        foreach ($cartItems as $item) {
            if (is_array($item) && isset($item['price'])) {
                $cartSubtotal += $item['price'];
            }
        }
        $serviceFee = round($cartSubtotal * 0.05);
        $cartTotal = $cartSubtotal + $serviceFee;
        $depositRequired = round($cartTotal * 0.5);
        
        include VIEW_PATH . '/user/checkout.php';
    }

    public function submit() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Invalid request data']);
                exit;
            }

            if (empty($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'User must be logged in to book.']);
                exit;
            }

            $cartItems = isset($data['cartItems']) && is_array($data['cartItems']) ? $data['cartItems'] : [];
            $eventName = trim($data['eventName'] ?? 'Untitled Event');
            $eventDate = trim($data['eventDate'] ?? '');
            $eventTime = trim($data['eventTime'] ?? '');
            $guestCount = isset($data['guestCount']) ? (int)$data['guestCount'] : 0;
            $venue = trim($data['venueLocation'] ?? '');
            $theme = '';
            $packageName = trim($data['packageName'] ?? '');
            
            // Extract package/occasion from URL if available
            if (empty($packageName) && isset($_GET['occasion'])) {
                $packageName = ucfirst($_GET['occasion']);
            }
            
            foreach ($cartItems as $item) {
                if (isset($item['category']) && $item['category'] === 'Theme') {
                    $theme = $item['name'] ?? $theme;
                    break;
                }
            }

            $totalPrice = isset($data['total']) ? (float)$data['total'] : 0;
            $status = 'pending';
            $paymentMethod = trim($data['paymentMethod'] ?? 'bank');
            $paymentDetails = trim($data['paymentDetails'] ?? '{}');

            $events = json_encode([
                'items' => $cartItems,
                'packageName' => $packageName,
                'paymentMethod' => $paymentMethod,
                'contactMethod' => $data['contactMethod'] ?? '',
                'specialRequests' => $data['specialRequests'] ?? '',
                'programFlow' => $data['programFlow'] ?? ''
            ], JSON_UNESCAPED_UNICODE);

            require_once ROOT_PATH . '/app/models/Plan.php';
            require_once ROOT_PATH . '/app/models/Customization.php';
            require_once ROOT_PATH . '/app/models/Notification.php';
            
            $planModel = new Plan();
            $planId = $planModel->create([
                'user_id' => $_SESSION['user_id'],
                'occasion_id' => null,
                'package_id' => null,
                'customize_id' => null,
                'event_name' => $eventName,
                'event_date' => $eventDate,
                'event_time' => $eventTime,
                'guest_count' => $guestCount,
                'venue' => $venue,
                'theme' => $theme,
                'total_price' => $totalPrice,
                'status' => $status,
                'events' => $events,
                'payment_method' => $paymentMethod,
                'payment_details' => $paymentDetails,
                'payment_status' => 'pending'
            ]);

            if ($planId) {
                // Store custom colors if they exist
                foreach ($cartItems as $item) {
                    if (isset($item['customColors']) && $item['category'] === 'Color Combinations') {
                        $customization = new Customization();
                        $customColors = json_decode($item['customColors'], true);
                        $customDescription = $item['customDescription'] ?? 'Custom color combination';
                        $customization->storeCustomColors($planId, $customColors, $customDescription);
                        break;
                    }
                }

                // Plan is the main booking record - no separate booking entry needed
                
                // Notify Admin of new booking
                try {
                    $notif = new Notification();
                    $adminResult = Database::getInstance()->getConnection()->query("SELECT user_id FROM users_tbl WHERE role = 'admin' LIMIT 1");
                    if ($adminResult && $adminRow = $adminResult->fetch_assoc()) {
                        $notif->create([
                            'user_id' => $adminRow['user_id'],
                            'type' => 'book_confirmation',
                            'title' => 'New Booking Received',
                            'message' => 'New booking "' . $eventName . '" from ' . ($_SESSION['user_name'] ?? 'User'),
                            'related_type' => 'plan',
                            'related_id' => $planId
                        ]);
                    }
                } catch (Exception $e) { error_log("Admin notification failed: " . $e->getMessage()); }

                unset($_SESSION['checkout_cart']);
                echo json_encode(['success' => true, 'plan_id' => $planId, 'message' => 'Booking saved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save your plan.']);
            }
        } catch (Exception $e) {
            error_log('Checkout submit error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while processing your booking.']);
        }
        exit;
    }
}

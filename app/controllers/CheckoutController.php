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
        
        foreach ($cartItems as $item) {
            $cartSubtotal += $item['price'];
        }
        $serviceFee = round($cartSubtotal * 0.05);
        $cartTotal = $cartSubtotal + $serviceFee;
        $depositRequired = round($cartTotal * 0.5);
        
        include VIEW_PATH . '/user/checkout.php';
    }
}
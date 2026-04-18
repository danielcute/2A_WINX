<?php
/**
 * MODERN ROUTING CONFIG
 * Location: public/routes-modern.php
 * Include this in your main index.php to use all modern pages
 */

// Modern User Routes
$modernRoutes = [
    'homepage' => '/SINTA/app/views/user/homepage-modern.php',
    'packages' => '/SINTA/app/views/user/packages-modern.php',
    'bookings' => '/SINTA/app/views/user/bookings-modern.php',
    'messages' => '/SINTA/app/views/user/messages-modern.php',
    
    // Keep old routes as fallback (for other pages not yet modernized)
    'occasions' => '/SINTA/app/views/user/occasions.php',
    'plans' => '/SINTA/app/views/user/plans.php',
    'profile' => '/SINTA/app/views/user/profile.php',
    'checkout' => '/SINTA/app/views/user/checkout.php',
    'about' => '/SINTA/app/views/user/about.php',
    'signin' => '/SINTA/app/views/user/signin.php',
    'signup' => '/SINTA/app/views/user/signup.php',
    'landing' => '/SINTA/app/views/landing/landing.php',
    
    // Admin Modern Routes
    'admin-dashboard' => '/SINTA/app/views/admin/admin-dashboard-modern.php',
    'admin-bookings' => '/SINTA/app/views/admin/admin-bookings-modern.php',
    'admin-packages' => '/SINTA/app/views/admin/admin-packages-modern.php',
    'admin-messages' => '/SINTA/app/views/admin/admin-messages-modern.php',
];

// Function to get route file
function getModernRoute($route) {
    global $modernRoutes;
    return isset($modernRoutes[$route]) ? ROOT_PATH . $modernRoutes[$route] : null;
}

// Function to check if route exists
function modernRouteExists($route) {
    $file = getModernRoute($route);
    return $file && file_exists($file);
}

?>

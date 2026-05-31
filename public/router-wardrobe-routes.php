<?php
/**
 * WARDROBE ROUTES
 * ───────────────────────────────────────────────────────────────────────────
 * Add these cases to your existing router switch or if-elseif chain.
 *
 * Typical router pattern (adjust to match your existing file):
 *
 *   $route = $_GET['route'] ?? 'home';
 *   switch ($route) {
 *       // ... existing routes ...
 *
 *       // ── paste the cases below ──
 *   }
 *
 * Make sure WardrobeController is required/autoloaded before the switch.
 */

// ── Require the controller (add this near your other require_once calls) ────
// require_once ROOT_PATH . '/app/controllers/WardrobeController.php';


// ── Cases to insert into your router switch ──────────────────────────────────

case 'admin-wardrobe':
    $controller = new WardrobeController();
    $controller->index();
    break;

case 'admin-wardrobe-add':
    $controller = new WardrobeController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->addSubmit();   // returns JSON
    } else {
        $controller->addForm();     // shows form page
    }
    break;

case 'admin-wardrobe-edit':
    $controller = new WardrobeController();
    $controller->editForm();
    break;

case 'admin-wardrobe-update':
    $controller = new WardrobeController();
    $controller->updateSubmit();    // POST only, returns JSON
    break;

case 'admin-wardrobe-delete':
    $controller = new WardrobeController();
    $controller->deleteSubmit();    // POST only, returns JSON
    break;

case 'admin-wardrobe-selections':
    $controller = new WardrobeController();
    $controller->selections();
    break;
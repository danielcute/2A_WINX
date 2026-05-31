<?php
/**
 * Wardrobe Admin Controller
 * File: /app/controllers/WardrobeController.php
 *
 * Handles routes:
 *   GET/POST  admin-wardrobe               → list all wardrobes
 *   GET       admin-wardrobe-add           → show add form
 *   POST      admin-wardrobe-add           → create wardrobe (JSON response)
 *   GET       admin-wardrobe-edit?id=      → show edit form
 *   POST      admin-wardrobe-update        → update wardrobe (JSON response)
 *   POST      admin-wardrobe-delete        → delete wardrobe (JSON response)
 *   GET       admin-wardrobe-selections    → list rentals
 */

require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Wardrobe.php';

class WardrobeController {

    private User     $userModel;
    private Wardrobe $wardrobeModel;
    private array    $admin;

    public function __construct() {
        $this->userModel     = new User();
        $this->wardrobeModel = new Wardrobe();
    }

    // ── Auth ─────────────────────────────────────────────────────────────────

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php?route=admin-login');
            exit;
        }

        $admin = $this->userModel->findById($_SESSION['user_id']);

        if (!$admin || $admin['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=home');
            exit;
        }

        $this->admin = $admin;
    }

    private function jsonResponse(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // ── Routes ────────────────────────────────────────────────────────────────

    /** GET admin-wardrobe  →  list view */
    public function index(): void {
        $this->requireAdmin();

        $wardrobesByCategory = $this->wardrobeModel->getAllByCategory();
        $allCategories       = $this->wardrobeModel->getCategories();

        require_once VIEW_PATH . '/admin/admin-wardrobe.php';
    }

    /** GET admin-wardrobe-add  →  add form view */
    public function addForm(): void {
        $this->requireAdmin();

        $allCategories = $this->wardrobeModel->getCategories();

        require_once VIEW_PATH . '/admin/admin-wardrobe-add.php';
    }

    /** POST admin-wardrobe-add  →  create and return JSON */
    public function addSubmit(): void {
        $this->requireAdmin();

        $data = [
            'category'             => trim($_POST['category']             ?? ''),
            'name'                 => trim($_POST['name']                 ?? ''),
            'description'          => trim($_POST['description']          ?? ''),
            'rental_price'         => $_POST['rental_price']              ?? '',
            'availability_count'   => $_POST['availability_count']        ?? '',
            'rental_duration_days' => $_POST['rental_duration_days']      ?? '',
            'sizes_available'      => trim($_POST['sizes_available']      ?? ''),
        ];

        [$imageData, $imageType] = Wardrobe::readUploadedImage($_FILES['wardrobe_image'] ?? []);

        $result = $this->wardrobeModel->create($data, $imageData, $imageType);
        $this->jsonResponse($result);
    }

    /** GET admin-wardrobe-edit?id=  →  edit form view */
    public function editForm(): void {
        $this->requireAdmin();

        $id       = (int)($_GET['id'] ?? 0);
        $wardrobe = $id > 0 ? $this->wardrobeModel->findById($id) : null;

        if (!$wardrobe) {
            header('Location: ' . APP_URL . '/admin-wardrobe');
            exit;
        }

        $allCategories = $this->wardrobeModel->getCategories();

        require_once VIEW_PATH . '/admin/admin-wardrobe-edit.php';
    }

    /** POST admin-wardrobe-update  →  update and return JSON */
    public function updateSubmit(): void {
        $this->requireAdmin();

        $wardrobeId = (int)($_POST['wardrobe_id'] ?? 0);

        if ($wardrobeId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid wardrobe ID']);
        }

        $data = [
            'category'             => trim($_POST['category']             ?? ''),
            'name'                 => trim($_POST['name']                 ?? ''),
            'description'          => trim($_POST['description']          ?? ''),
            'rental_price'         => $_POST['rental_price']              ?? '',
            'availability_count'   => $_POST['availability_count']        ?? '',
            'rental_duration_days' => $_POST['rental_duration_days']      ?? '',
            'sizes_available'      => trim($_POST['sizes_available']      ?? ''),
        ];

        [$imageData, $imageType] = Wardrobe::readUploadedImage($_FILES['wardrobe_image'] ?? []);

        $result = $this->wardrobeModel->update($wardrobeId, $data, $imageData, $imageType);
        $this->jsonResponse($result);
    }

    /** POST admin-wardrobe-delete  →  delete and return JSON */
    public function deleteSubmit(): void {
        $this->requireAdmin();

        $wardrobeId = (int)($_POST['wardrobe_id'] ?? 0);

        if ($wardrobeId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid wardrobe ID']);
        }

        $result = $this->wardrobeModel->delete($wardrobeId);
        $this->jsonResponse($result);
    }

    /** GET admin-wardrobe-selections  →  rentals list view */
    public function selections(): void {
        $this->requireAdmin();

        require_once VIEW_PATH . '/admin/admin-wardrobe-selections.php';
    }
}
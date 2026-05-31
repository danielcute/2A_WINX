<?php
/**
 * Wardrobe Admin Controller
 * File: /app/controllers/WardrobeController.php
 *
 * Handles routes:
 *   GET       admin-wardrobe               → list all wardrobes
 *   GET       admin-wardrobe-add           → show add form
 *   POST      admin-wardrobe-add           → create wardrobe (JSON response)
 *   GET       admin-wardrobe-edit?id=      → show edit form
 *   POST      admin-wardrobe-update        → update wardrobe (JSON response)
 *   POST      admin-wardrobe-delete        → delete wardrobe (JSON response)
 *   GET       admin-wardrobe-get?id=       → fetch single wardrobe as JSON (edit modal)
 *   GET       admin-wardrobe-image?id=     → stream wardrobe image
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
            header('Location: ' . BASE_URL . '/index.php?route=signin');
            exit;
        }

        $admin = $this->userModel->findById($_SESSION['user_id']);

        if (!$admin || $admin['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/index.php?route=homepage');
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
            header('Location: ' . BASE_URL . '/index.php?route=admin-wardrobe');
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

    /** GET admin-wardrobe-get?id=  →  return single wardrobe as JSON (for edit modal) */
    public function getJson(): void {
        $this->requireAdmin();

        $id       = (int)($_GET['id'] ?? 0);
        $wardrobe = $id > 0 ? $this->wardrobeModel->findById($id) : null;

        if (!$wardrobe) {
            $this->jsonResponse(['success' => false, 'message' => 'Wardrobe not found']);
        }

        // Base64-encode the image blob for JSON transport
        if (!empty($wardrobe['image'])) {
            $wardrobe['image'] = base64_encode($wardrobe['image']);
        }

        $this->jsonResponse(['success' => true, 'data' => $wardrobe]);
    }

    /** GET admin-wardrobe-image?id=  →  stream raw image */
    public function serveImage(): void {
        $this->requireAdmin();

        $id  = (int)($_GET['id'] ?? 0);
        $row = $id > 0 ? $this->wardrobeModel->getImage($id) : null;

        if (!$row) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: ' . $row['image_type']);
        header('Cache-Control: public, max-age=86400');
        echo $row['image'];
        exit;
    }

    /** GET admin-wardrobe-selections  →  rentals list view */
    public function selections(): void {
        $this->requireAdmin();

        $db = Database::getInstance()->getConnection();

        $filter_status = $_GET['status'] ?? '';
        $filter_plan   = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;

        $sql = "SELECT ws.*, w.name, w.rental_price, w.category,
                       p.event_name, p.event_date,
                       u.first_name, u.last_name
                FROM wardrobe_selections_tbl ws
                LEFT JOIN wardrobes_tbl w ON ws.wardrobe_id = w.wardrobe_id
                LEFT JOIN plans_tbl p ON ws.plan_id = p.plan_id
                LEFT JOIN users_tbl u ON ws.user_id = u.user_id
                WHERE 1=1";

        $params = [];
        $types  = '';

        if ($filter_status !== '') {
            $sql    .= ' AND ws.status = ?';
            $params[] = $filter_status;
            $types   .= 's';
        }
        if ($filter_plan > 0) {
            $sql    .= ' AND ws.plan_id = ?';
            $params[] = $filter_plan;
            $types   .= 'i';
        }

        $sql .= ' ORDER BY ws.created_at DESC';

        $stmt = $db->prepare($sql);
        if ($stmt) {
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result     = $stmt->get_result();
            $selections = [];
            while ($row = $result->fetch_assoc()) {
                $selections[] = $row;
            }
            $stmt->close();
        } else {
            $selections = [];
        }

        require_once VIEW_PATH . '/admin/admin-wardrobe-selections.php';
    }
}
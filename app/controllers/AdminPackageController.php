<?php
/**
 * Admin Package Management Controller
 * Handles CRUD operations for packages
 *
 * Matches DB schema:
 *   packages_tbl          (package_id, occasion_id, name, description, price, created_at)
 *   package_inclusions_tbl (inclusion_id, package_id, item)
 *   package_images_tbl     (image_id, package_id, image_path)
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';

class AdminPackageController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // =========================================================================
    // READ
    // =========================================================================

    /**
     * Return all packages joined with occasion name.
     */
    public function getAll(): array
    {
        $sql = "SELECT p.*, o.events AS occasion_name
                FROM   packages_tbl p
                LEFT JOIN occasions_tbl o ON p.occasion_id = o.occasion_id
                ORDER  BY p.created_at DESC";

        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Return a single package.
     */
    public function getById(int $package_id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, o.events AS occasion_name
             FROM   packages_tbl p
             LEFT JOIN occasions_tbl o ON p.occasion_id = o.occasion_id
             WHERE  p.package_id = ?"
        );
        $stmt->bind_param('i', $package_id);
        $stmt->execute();
        $package = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $package;
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    /**
     * Insert a new package.
     */
    public function create(array $data): array
    {
        // Validate required fields
        if (empty($data['name']) || empty($data['occasion_id']) || !isset($data['price'])) {
            return ['success' => false, 'error' => 'Missing required fields'];
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO packages_tbl (occasion_id, name, package_name, description, price, event_type, category, features, max_guests, duration_hours, venue_type, image, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $occasion_id = (int)    $data['occasion_id'];
            $name        = (string) $data['name'];
            $package_name = (string) ($data['package_name'] ?? $data['name']);
            $description = (string) ($data['description'] ?? '');
            $price       = (float)   $data['price'];
            $event_type  = (string) ($data['event_type'] ?? '');
            $category    = (string) ($data['category'] ?? '');
            $features    = (string) ($data['features'] ?? '');
            $max_guests  = (int)    ($data['max_guests'] ?? 100);
            $duration_hours = (int) ($data['duration_hours'] ?? 4);
            $venue_type  = (string) ($data['venue_type'] ?? '');
            $image       = (string) ($data['image'] ?? '');
            $status      = (string) ($data['status'] ?? 'active');

            $stmt->bind_param('isssdsssiiiss', $occasion_id, $name, $package_name, $description, $price, $event_type, $category, $features, $max_guests, $duration_hours, $venue_type, $image, $status);
            
            if ($stmt->execute()) {
                $package_id = (int) $this->db->insert_id;
                $stmt->close();
                return ['success' => true, 'package_id' => $package_id];
            } else {
                $error = $stmt->error;
                $stmt->close();
                return ['success' => false, 'error' => $error];
            }

        } catch (Exception $e) {
            error_log('AdminPackageController::create() — ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    /**
     * Update an existing package.
     */
    public function update(int $package_id, array $data): array
    {
        if (empty($data['name']) || empty($data['occasion_id']) || !isset($data['price'])) {
            return ['success' => false, 'error' => 'Missing required fields'];
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE packages_tbl
                 SET    occasion_id = ?, name = ?, package_name = ?, description = ?, price = ?, event_type = ?, category = ?, features = ?, max_guests = ?, duration_hours = ?, venue_type = ?, image = ?, status = ?, updated_at = NOW()
                 WHERE  package_id  = ?"
            );

            $occasion_id = (int)    $data['occasion_id'];
            $name        = (string) $data['name'];
            $package_name = (string) ($data['package_name'] ?? $data['name']);
            $description = (string) ($data['description'] ?? '');
            $price       = (float)   $data['price'];
            $event_type  = (string) ($data['event_type'] ?? '');
            $category    = (string) ($data['category'] ?? '');
            $features    = (string) ($data['features'] ?? '');
            $max_guests  = (int)    ($data['max_guests'] ?? 100);
            $duration_hours = (int) ($data['duration_hours'] ?? 4);
            $venue_type  = (string) ($data['venue_type'] ?? '');
            $image       = (string) ($data['image'] ?? '');
            $status      = (string) ($data['status'] ?? 'active');

            $stmt->bind_param('isssdsssiisssi', $occasion_id, $name, $package_name, $description, $price, $event_type, $category, $features, $max_guests, $duration_hours, $venue_type, $image, $status, $package_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true];
            } else {
                $error = $stmt->error;
                $stmt->close();
                return ['success' => false, 'error' => $error];
            }

        } catch (Exception $e) {
            error_log('AdminPackageController::update() — ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    /**
     * Delete a package. FK ON DELETE CASCADE removes inclusions & images too.
     */
    public function delete(int $package_id): array
    {
        $stmt = $this->db->prepare("DELETE FROM packages_tbl WHERE package_id = ?");
        $stmt->bind_param('i', $package_id);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }

        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $error];
    }

    // =========================================================================
    // IMAGE UPLOAD (physical file)
    // =========================================================================

    /**
     * Save an uploaded image file to disk AND update the database image column.
     */
    public function uploadImage(array $file, int $package_id): array
    {
        $upload_dir = ROOT_PATH . '/public/assets/img/packages/';

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $max_size      = 5 * 1024 * 1024; // 5 MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error (code ' . $file['error'] . ')'];
        }

        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP'];
        }

        if ($file['size'] > $max_size) {
            return ['success' => false, 'error' => 'File too large. Maximum size is 5 MB'];
        }

        $ext           = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_name     = 'package_' . $package_id . '_' . time() . '.' . $ext;
        $target_file   = $upload_dir . $file_name;
        $relative_path = '/SINTA/public/assets/img/packages/' . $file_name;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // ✅ Update the image column in packages_tbl
            $stmt = $this->db->prepare(
                "UPDATE packages_tbl SET image = ?, updated_at = NOW() WHERE package_id = ?"
            );
            $stmt->bind_param('si', $relative_path, $package_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'path' => $relative_path];
            } else {
                $stmt->close();
                return ['success' => false, 'error' => 'File uploaded but database save failed'];
            }
        }

        return ['success' => false, 'error' => 'Failed to save uploaded file'];
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function getInclusions(int $package_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT item FROM package_inclusions_tbl
             WHERE  package_id = ? ORDER BY inclusion_id"
        );
        $stmt->bind_param('i', $package_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_column($rows, 'item');
    }

    private function getImages(int $package_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT image_path FROM package_images_tbl
             WHERE  package_id = ? ORDER BY image_id"
        );
        $stmt->bind_param('i', $package_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_column($rows, 'image_path');
    }

    private function insertInclusions(int $package_id, array $inclusions): void
    {
        if (empty($inclusions)) return;

        $stmt = $this->db->prepare(
            "INSERT INTO package_inclusions_tbl (package_id, item) VALUES (?, ?)"
        );
        foreach ($inclusions as $raw) {
            $item = trim((string) $raw);
            if ($item === '') continue;
            // ✅ FIX: $item is a named variable — safe for bind_param reference
            $stmt->bind_param('is', $package_id, $item);
            $stmt->execute();
        }
        $stmt->close();
    }

    private function insertImages(int $package_id, array $images): void
    {
        if (empty($images)) return;

        $stmt = $this->db->prepare(
            "INSERT INTO package_images_tbl (package_id, image_path) VALUES (?, ?)"
        );
        foreach ($images as $raw) {
            $image_path = trim((string) $raw);
            if ($image_path === '') continue;
            // ✅ FIX: $image_path is a named variable — safe for bind_param reference
            $stmt->bind_param('is', $package_id, $image_path);
            $stmt->execute();
        }
        $stmt->close();
    }

    private function deleteInclusions(int $package_id): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM package_inclusions_tbl WHERE package_id = ?"
        );
        $stmt->bind_param('i', $package_id);
        $stmt->execute();
        $stmt->close();
    }

    private function deleteImages(int $package_id): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM package_images_tbl WHERE package_id = ?"
        );
        $stmt->bind_param('i', $package_id);
        $stmt->execute();
        $stmt->close();
    }
}
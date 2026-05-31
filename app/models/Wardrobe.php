<?php
/**
 * Wardrobe Model
 * File: /app/models/Wardrobe.php
 */

require_once ROOT_PATH . '/config/database.php';

class Wardrobe {

    private $db;
    private $conn;

    public function __construct() {
        $this->db   = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    // -------------------------------------------------------
    // READ
    // -------------------------------------------------------

    /** Return every wardrobe, grouped by category */
    public function getAllByCategory(): array {
        $sql = "SELECT wardrobe_id, category, name, description,
                       rental_price, availability_count,
                       rental_duration_days, sizes_available,
                       CASE WHEN image IS NOT NULL THEN 1 ELSE 0 END AS has_image,
                       image_type,
                       created_at, updated_at
                FROM wardrobes_tbl
                ORDER BY category ASC, name ASC";

        $result = $this->conn->query($sql);
        $grouped = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $grouped[$row['category']][] = $row;
            }
        }

        return $grouped;
    }

    /** Return a flat list of all wardrobes (no image blob) */
    public function getAll(): array {
        $sql = "SELECT wardrobe_id, category, name, description,
                       rental_price, availability_count,
                       rental_duration_days, sizes_available,
                       CASE WHEN image IS NOT NULL THEN 1 ELSE 0 END AS has_image,
                       image_type,
                       created_at, updated_at
                FROM wardrobes_tbl
                ORDER BY category ASC, name ASC";

        $result = $this->conn->query($sql);
        $rows   = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /** Return a single wardrobe by ID (includes image blob for API use) */
    public function findById(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT wardrobe_id, category, name, description,
                    rental_price, availability_count,
                    rental_duration_days, sizes_available,
                    image, image_type,
                    created_at, updated_at
             FROM wardrobes_tbl
             WHERE wardrobe_id = ?"
        );

        if (!$stmt) return null;

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    /** Return the raw image blob + mime type for a wardrobe */
    public function getImage(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT image, image_type FROM wardrobes_tbl WHERE wardrobe_id = ?"
        );

        if (!$stmt) return null;

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();

        return ($row && $row['image']) ? $row : null;
    }

    /** Return all distinct categories */
    public function getCategories(): array {
        $result = $this->conn->query(
            "SELECT DISTINCT category FROM wardrobes_tbl ORDER BY category ASC"
        );

        $cats = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $cats[] = $row['category'];
            }
        }

        // Ensure the standard set is always present
        $defaults = ['Wedding','Birthday','Corporate Gala','Debut','Anniversary','Other Events'];
        foreach ($defaults as $d) {
            if (!in_array($d, $cats)) {
                $cats[] = $d;
            }
        }

        sort($cats);
        return $cats;
    }

    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------

    /**
     * Insert a new wardrobe.
     *
     * @param array       $data  Associative array of fields
     * @param string|null $imageData  Raw binary image data (or null)
     * @param string|null $imageType  MIME type e.g. "image/jpeg"
     * @return array ['success' => bool, 'id' => int|null, 'message' => string]
     */
    public function create(array $data, ?string $imageData = null, ?string $imageType = null): array {
        // Validate required fields
        $required = ['category','name','rental_price','availability_count',
                     'rental_duration_days','sizes_available'];

        foreach ($required as $field) {
            if (empty($data[$field]) && $data[$field] !== '0' && $data[$field] !== 0) {
                return ['success' => false, 'id' => null,
                        'message' => "Missing required field: $field"];
            }
        }

        if ($imageData && $imageType) {
            $stmt = $this->conn->prepare(
                "INSERT INTO wardrobes_tbl
                    (category, name, description, rental_price, availability_count,
                     rental_duration_days, sizes_available, image, image_type)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt) {
                return ['success' => false, 'id' => null,
                        'message' => 'DB prepare error: ' . $this->conn->error];
            }

            $category           = $data['category'];
            $name               = $data['name'];
            $description        = $data['description'] ?? '';
            $rental_price       = (float)$data['rental_price'];
            $availability_count = (int)$data['availability_count'];
            $rental_days        = (int)$data['rental_duration_days'];
            $sizes              = $data['sizes_available'];

            $stmt->bind_param('sssdiisss',
                $category, $name, $description,
                $rental_price, $availability_count,
                $rental_days, $sizes,
                $imageData, $imageType
            );
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO wardrobes_tbl
                    (category, name, description, rental_price, availability_count,
                     rental_duration_days, sizes_available)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt) {
                return ['success' => false, 'id' => null,
                        'message' => 'DB prepare error: ' . $this->conn->error];
            }

            $category           = $data['category'];
            $name               = $data['name'];
            $description        = $data['description'] ?? '';
            $rental_price       = (float)$data['rental_price'];
            $availability_count = (int)$data['availability_count'];
            $rental_days        = (int)$data['rental_duration_days'];
            $sizes              = $data['sizes_available'];

            $stmt->bind_param('sssdiis',
                $category, $name, $description,
                $rental_price, $availability_count,
                $rental_days, $sizes
            );
        }

        if ($stmt->execute()) {
            $newId = (int)$this->conn->insert_id;
            $stmt->close();
            return ['success' => true, 'id' => $newId, 'message' => 'Wardrobe added successfully'];
        }

        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'id' => null, 'message' => 'DB execute error: ' . $error];
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------

    /**
     * Update an existing wardrobe.
     *
     * @param int         $id
     * @param array       $data
     * @param string|null $imageData
     * @param string|null $imageType
     * @return array ['success' => bool, 'message' => string]
     */
    public function update(int $id, array $data, ?string $imageData = null, ?string $imageType = null): array {
        // Validate required fields
        $required = ['category','name','rental_price','availability_count',
                     'rental_duration_days','sizes_available'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return ['success' => false, 'message' => "Missing required field: $field"];
            }
        }

        if ($imageData && $imageType) {
            $stmt = $this->conn->prepare(
                "UPDATE wardrobes_tbl
                 SET category = ?, name = ?, description = ?,
                     rental_price = ?, availability_count = ?,
                     rental_duration_days = ?, sizes_available = ?,
                     image = ?, image_type = ?,
                     updated_at = NOW()
                 WHERE wardrobe_id = ?"
            );

            if (!$stmt) {
                return ['success' => false, 'message' => 'DB prepare error: ' . $this->conn->error];
            }

            $category           = $data['category'];
            $name               = $data['name'];
            $description        = $data['description'] ?? '';
            $rental_price       = (float)$data['rental_price'];
            $availability_count = (int)$data['availability_count'];
            $rental_days        = (int)$data['rental_duration_days'];
            $sizes              = $data['sizes_available'];

            $stmt->bind_param('sssdiisssi',
                $category, $name, $description,
                $rental_price, $availability_count,
                $rental_days, $sizes,
                $imageData, $imageType,
                $id
            );
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE wardrobes_tbl
                 SET category = ?, name = ?, description = ?,
                     rental_price = ?, availability_count = ?,
                     rental_duration_days = ?, sizes_available = ?,
                     updated_at = NOW()
                 WHERE wardrobe_id = ?"
            );

            if (!$stmt) {
                return ['success' => false, 'message' => 'DB prepare error: ' . $this->conn->error];
            }

            $category           = $data['category'];
            $name               = $data['name'];
            $description        = $data['description'] ?? '';
            $rental_price       = (float)$data['rental_price'];
            $availability_count = (int)$data['availability_count'];
            $rental_days        = (int)$data['rental_duration_days'];
            $sizes              = $data['sizes_available'];

            $stmt->bind_param('sssdiisi',
                $category, $name, $description,
                $rental_price, $availability_count,
                $rental_days, $sizes,
                $id
            );
        }

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Wardrobe updated successfully'];
        }

        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'DB execute error: ' . $error];
    }

    // -------------------------------------------------------
    // DELETE
    // -------------------------------------------------------

    /**
     * Delete a wardrobe by ID.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete(int $id): array {
        $stmt = $this->conn->prepare(
            "DELETE FROM wardrobes_tbl WHERE wardrobe_id = ?"
        );

        if (!$stmt) {
            return ['success' => false, 'message' => 'DB prepare error: ' . $this->conn->error];
        }

        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Wardrobe deleted successfully'];
            }
            return ['success' => false, 'message' => 'Wardrobe not found'];
        }

        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'DB execute error: ' . $error];
    }

    // -------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------

    /** Read uploaded image file and return [imageData, imageType] or [null, null] */
    public static function readUploadedImage(array $fileArray): array {
        if (empty($fileArray['tmp_name']) || $fileArray['error'] !== UPLOAD_ERR_OK) {
            return [null, null];
        }

        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $mime    = mime_content_type($fileArray['tmp_name']);

        if (!in_array($mime, $allowed)) {
            return [null, null];
        }

        if ($fileArray['size'] > 5 * 1024 * 1024) {
            return [null, null];
        }

        $data = file_get_contents($fileArray['tmp_name']);
        return [$data ?: null, $mime];
    }
}
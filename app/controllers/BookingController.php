<?php
/**
 * Booking Management Controller
 * Handles CRUD operations for bookings/orders
 */

define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';

class BookingController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all bookings with user and package info
     */
    public function getAll() {
        $sql = "SELECT b.*, u.first_name, u.last_name, u.email, u.phone, p.package_name, p.price
                FROM bookings_tbl b
                LEFT JOIN users_tbl u ON b.user_id = u.user_id
                LEFT JOIN packages_tbl p ON b.package_id = p.package_id
                ORDER BY b.created_at DESC";
        
        $result = $this->db->query($sql);
        if (!$result) {
            return ['error' => $this->db->error];
        }
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }
    
    /**
     * Get booking by ID
     */
    public function getById($booking_id) {
        $stmt = $this->db->prepare("
            SELECT b.*, u.first_name, u.last_name, u.email, u.phone, u.address, u.birthday,
                   p.package_name, p.description, p.price, p.event_type
            FROM bookings_tbl b
            LEFT JOIN users_tbl u ON b.user_id = u.user_id
            LEFT JOIN packages_tbl p ON b.package_id = p.package_id
            WHERE b.booking_id = ?
        ");
        
        if (!$stmt) {
            return ['error' => $this->db->error];
        }
        
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Create new booking
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO bookings_tbl 
            (user_id, package_id, event_type, event_date, guest_count, total_price, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        $user_id = $data['user_id'] ?? 0;
        $package_id = $data['package_id'] ?? null;
        $event_type = $data['event_type'] ?? '';
        $event_date = $data['event_date'] ?? null;
        $guest_count = $data['guest_count'] ?? 0;
        $total_price = $data['total_price'] ?? 0;
        $status = $data['status'] ?? 'pending';
        
        $stmt->bind_param(
            "iissdis",
            $user_id,
            $package_id,
            $event_type,
            $event_date,
            $guest_count,
            $total_price,
            $status
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'booking_id' => $this->db->insert_id];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Update booking
     */
    public function update($booking_id, $data) {
        $sets = [];
        $types = "";
        $values = [];
        
        $allowed_fields = ['user_id', 'package_id', 'event_type', 'event_date', 'guest_count', 'total_price', 'status'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $sets[] = "$field = ?";
                if ($field === 'guest_count' || $field === 'user_id' || $field === 'package_id') {
                    $types .= 'i';
                } elseif ($field === 'total_price') {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = $data[$field];
            }
        }
        
        if (empty($sets)) {
            return ['success' => false, 'error' => 'No fields to update'];
        }
        
        $types .= "i";
        $values[] = $booking_id;
        
        $sql = "UPDATE bookings_tbl SET " . implode(", ", $sets) . " WHERE booking_id = ?";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        if ($stmt->execute(array_merge($values, [$booking_id])) === false) {
            // Try with call_user_func_array for better compatibility
            $params = array_merge([$types], $values);
            if (!call_user_func_array([$stmt, 'bind_param'], $params)) {
                return ['success' => false, 'error' => $stmt->error];
            }
            
            if (!$stmt->execute()) {
                return ['success' => false, 'error' => $stmt->error];
            }
        }
        
        return ['success' => true];
    }
    
    /**
     * Delete booking
     */
    public function delete($booking_id) {
        $stmt = $this->db->prepare("DELETE FROM bookings_tbl WHERE booking_id = ?");
        
        if (!$stmt) {
            return ['success' => false, 'error' => $this->db->error];
        }
        
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }
    
    /**
     * Get booking statistics
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(total_price) as total_revenue
                FROM bookings_tbl";
        
        $result = $this->db->query($sql);
        if (!$result) {
            return ['error' => $this->db->error];
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get bookings by user
     */
    public function getByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT b.*, p.package_name, p.price
            FROM bookings_tbl b
            LEFT JOIN packages_tbl p ON b.package_id = p.package_id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
        ");
        
        if (!$stmt) {
            return ['error' => $this->db->error];
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }
    
    /**
     * Get bookings by status
     */
    public function getByStatus($status) {
        $stmt = $this->db->prepare("
            SELECT b.*, u.first_name, u.last_name, u.email, p.package_name
            FROM bookings_tbl b
            LEFT JOIN users_tbl u ON b.user_id = u.user_id
            LEFT JOIN packages_tbl p ON b.package_id = p.package_id
            WHERE b.status = ?
            ORDER BY b.created_at DESC
        ");
        
        if (!$stmt) {
            return ['error' => $this->db->error];
        }
        
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }
}
?>

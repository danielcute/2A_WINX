<?php
/**
 * BOOKINGS API ENDPOINT
 * Location: public/api/bookings/index.php
 * Handles booking operations for users and admin
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

require_once dirname(dirname(dirname(__DIR__))) . '/config/database.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/models/Booking.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$bookingModel = new Booking();
$db = Database::getInstance()->getConnection();

// HANDLE GET REQUESTS
if ($method === 'GET') {
    if ($action === 'getUserBookings') {
        $userId = (int)($_GET['user_id'] ?? ($_SESSION['user_id'] ?? 0));

        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID required']);
            exit;
        }

        $query = "SELECT b.*, p.name as package_name, p.price as package_price
                  FROM checkout_tbl b
                  LEFT JOIN packages_tbl p ON b.package_id = p.package_id
                  WHERE b.user_id = $userId
                  ORDER BY b.date DESC";

        $result = $db->query($query);
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }

        echo json_encode([
            'success' => true,
            'bookings' => $bookings
        ]);
        exit;
    }

    if ($action === 'getById') {
        $bookingId = (int)($_GET['booking_id'] ?? 0);

        if ($bookingId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Booking ID required']);
            exit;
        }

        $booking = $bookingModel->findById($bookingId);

        echo json_encode([
            'success' => !!$booking,
            'booking' => $booking
        ]);
        exit;
    }
}

// HANDLE POST REQUESTS
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'create') {
        $userId = (int)($input['user_id'] ?? $_SESSION['user_id'] ?? 0);

        if ($userId <= 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $bookingId = $bookingModel->create($input);

        if ($bookingId) {
            echo json_encode([
                'success' => true,
                'booking_id' => $bookingId,
                'message' => 'Booking created successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating booking']);
        }
        exit;
    }

    if ($action === 'cancel') {
        $bookingId = (int)($input['booking_id'] ?? 0);

        if ($bookingId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Booking ID required']);
            exit;
        }

        $result = $bookingModel->updateStatus($bookingId, 'cancelled');

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Booking cancelled successfully' : 'Error cancelling booking'
        ]);
        exit;
    }

    if ($action === 'updateStatus') {
        // Admin only
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $bookingId = (int)($input['booking_id'] ?? 0);
        $status = $input['status'] ?? '';

        if ($bookingId <= 0 || empty($status)) {
            http_response_code(400);
            echo json_encode(['error' => 'Booking ID and status required']);
            exit;
        }

        $result = $bookingModel->updateStatus($bookingId, $status);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Status updated successfully' : 'Error updating status'
        ]);
        exit;
    }
}

// HANDLE PUT REQUESTS
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'update') {
        $bookingId = (int)($input['booking_id'] ?? 0);

        if ($bookingId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Booking ID required']);
            exit;
        }

        // Update booking logic
        $updates = [];
        if (isset($input['total_amount'])) $updates['total_amount'] = (float)$input['total_amount'];
        if (isset($input['deposit_amount'])) $updates['deposit_amount'] = (float)$input['deposit_amount'];
        if (isset($input['status'])) $updates['status'] = $input['status'];

        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'No updates provided']);
            exit;
        }

        // Build update query
        $setClause = implode(', ', array_map(fn($k) => "$k = '" . $db->real_escape_string($updates[$k]) . "'", array_keys($updates)));
        $query = "UPDATE checkout_tbl SET $setClause WHERE checkout_id = $bookingId";
        $result = $db->query($query);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Booking updated successfully' : 'Error updating booking'
        ]);
        exit;
    }
}

// DEFAULT RESPONSE
http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
exit;
?>

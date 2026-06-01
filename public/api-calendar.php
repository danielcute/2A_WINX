<?php
/**
 * Calendar API
 * Provides booking data for calendar display with filtering support
 */

// Prevent PHP from outputting HTML errors/warnings that break JSON parsing
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure JSON header is always sent
header('Content-Type: application/json; charset=utf-8');

// Catch fatal errors and return JSON
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) { http_response_code(500); }
        if (ob_get_length() === 0 || strpos(ob_get_contents(), '{') === false) { ob_clean(); echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e['message']]); }
    }
});

session_start();

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php'; // Moved after shutdown function
$db = Database::getInstance()->getConnection(); // Moved after shutdown function

// Determine the request type
$action = $_GET['action'] ?? 'getAll';

if ($action === 'getAll') {
    // Get all bookings (admin view)
    getAdminBookings();
} elseif ($action === 'getFiltered') {
    // Get filtered bookings with status, date range, etc.
    getFilteredBookings();
} elseif ($action === 'getUserBookings') {
    // Get user's bookings (user view)
    getUserBookings();
} elseif ($action === 'getAvailableDates') {
    // Get available dates for booking
    getAvailableDates();
} elseif ($action === 'getMonthBookings') {
    // Get booked dates for a specific month
    getMonthBookings();
} elseif ($action === 'getDateBookings') {
    // Get bookings for a specific date
    getDateBookings();
}

function getAdminBookings() {
    global $db;
    
    $sql = "SELECT 
            p.plan_id as id,
            p.event_name as title,
            p.event_date as start,
            p.event_time,
            p.venue,
            p.status,
            p.total_price,
            u.first_name,
            u.last_name,
            u.email
            FROM plans_tbl p
            LEFT JOIN users_tbl u ON p.user_id = u.user_id
            WHERE p.event_date IS NOT NULL
            ORDER BY p.event_date ASC";
    
    $result = $db->query($sql);
    
    if (!$result) {
        echo json_encode(['error' => 'Database error: ' . $db->error]);
        exit;
    }
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = buildEventObject($row);
    }
    
    header('Content-Type: application/json');
    echo json_encode($events);
}

/**
 * Get filtered bookings based on status, date range, and other criteria
 */
function getFilteredBookings() {
    global $db;
    
    // Get filter parameters
    $status = $_GET['status'] ?? null;
    $startDate = $_GET['startDate'] ?? null;
    $endDate = $_GET['endDate'] ?? null;
    $searchTerm = $_GET['search'] ?? null;
    
    $sql = "SELECT 
            p.plan_id as id,
            p.event_name as title,
            p.event_date as start,
            p.event_time,
            p.venue,
            p.status,
            p.total_price,
            u.first_name,
            u.last_name,
            u.email
            FROM plans_tbl p
            LEFT JOIN users_tbl u ON p.user_id = u.user_id
            WHERE p.event_date IS NOT NULL";
    
    // Add status filter
    if ($status && $status !== 'all') {
        $status = $db->real_escape_string($status);
        $sql .= " AND p.status = '$status'";
    }
    
    // Add date range filter
    if ($startDate) {
        $startDate = $db->real_escape_string($startDate);
        $sql .= " AND DATE(p.event_date) >= '$startDate'";
    }
    
    if ($endDate) {
        $endDate = $db->real_escape_string($endDate);
        $sql .= " AND DATE(p.event_date) <= '$endDate'";
    }
    
    // Add search filter (searches event name, venue, customer name, email)
    if ($searchTerm) {
        $searchTerm = $db->real_escape_string($searchTerm);
        $sql .= " AND (p.event_name LIKE '%$searchTerm%' 
                     OR p.venue LIKE '%$searchTerm%'
                     OR u.first_name LIKE '%$searchTerm%'
                     OR u.last_name LIKE '%$searchTerm%'
                     OR u.email LIKE '%$searchTerm%')";
    }
    
    $sql .= " ORDER BY p.event_date ASC";
    
    $result = $db->query($sql);
    
    if (!$result) {
        echo json_encode(['error' => 'Database error: ' . $db->error]);
        exit;
    }
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = buildEventObject($row);
    }
    
    header('Content-Type: application/json');
    echo json_encode($events);
}

/**
 * Helper function to build event object with proper styling
 */
function buildEventObject($row) {
    $backgroundColor = 'rgba(138, 118, 80, 0.7)'; // Primary color
    if ($row['status'] === 'pending') {
        $backgroundColor = 'rgba(255, 193, 7, 0.7)'; // Yellow for pending
    } elseif ($row['status'] === 'confirmed') {
        $backgroundColor = 'rgba(76, 175, 80, 0.7)'; // Green for confirmed
    } elseif ($row['status'] === 'canceled') {
        $backgroundColor = 'rgba(244, 67, 54, 0.7)'; // Red for canceled
    }
    
    return [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start'],
        'extendedProps' => [
            'time' => $row['event_time'],
            'venue' => $row['venue'],
            'status' => $row['status'],
            'customer' => ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''),
            'email' => $row['email'] ?? '',
            'price' => $row['total_price']
        ],
        'backgroundColor' => $backgroundColor,
        'borderColor' => '#8A7650'
    ];
}

function getAvailableDates() {
    global $db;
    
    // Get all booked dates
    $sql = "SELECT DISTINCT event_date FROM plans_tbl WHERE event_date IS NOT NULL";
    $result = $db->query($sql);
    
    $bookedDates = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['event_date']) {
            $bookedDates[] = date('Y-m-d', strtotime($row['event_date']));
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'bookedDates' => $bookedDates
    ]);
}

/**
 * Get booked dates for a specific month
 * Returns dates that have at least one booking
 */
function getMonthBookings() {
    global $db;
    
    $month = (int)($_GET['month'] ?? date('m'));
    $year = (int)($_GET['year'] ?? date('Y'));
    
    if ($month < 1 || $month > 12) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Invalid month',
            'bookings' => []
        ]);
        exit;
    }
    
    // Get all booked dates for this month
    $sql = "SELECT DISTINCT DATE(event_date) as date FROM plans_tbl 
            WHERE YEAR(event_date) = $year 
            AND MONTH(event_date) = $month
            AND status IN ('pending', 'confirmed')
            ORDER BY event_date ASC";
    
    $result = $db->query($sql);
    
    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Database error',
            'bookings' => []
        ]);
        exit;
    }
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = [
            'date' => $row['date']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);
}

function getDateBookings() {
    global $db;
    
    $date = $_GET['date'] ?? null;
    
    if (!$date) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Date parameter is required',
            'bookings' => []
        ]);
        exit;
    }
    
    // Get all bookings for the specified date
    $sql = "SELECT 
            p.plan_id,
            p.event_name,
            p.event_time,
            p.status,
            u.first_name,
            u.last_name,
            u.user_id
            FROM plans_tbl p
            LEFT JOIN users_tbl u ON p.user_id = u.user_id
            WHERE DATE(p.event_date) = '$date'
            AND p.status IN ('pending', 'confirmed')
            ORDER BY p.event_time ASC";
    
    $result = $db->query($sql);
    
    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Database error',
            'bookings' => []
        ]);
        exit;
    }
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        // Use event_time which is stored as "10:00 AM - 12:00 PM" format
        $timeRange = $row['event_time'];
        
        $currentUserId = $_SESSION['user_id'] ?? null;
        $isOwnBooking = ($currentUserId && $row['user_id'] == $currentUserId);
        $bookedByName = $isOwnBooking ? 'You' : ($row['first_name'] . ' ' . $row['last_name']);
        
        $bookings[] = [
            'bookingId' => $row['plan_id'],
            'eventName' => $row['event_name'],
            'timeRange' => $timeRange,
            'status' => $row['status'],
            'bookedBy' => $bookedByName,
            'isOwnBooking' => $isOwnBooking
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);
}

/**
 * Get user's bookings (user view for calendar)
 */
function getUserBookings() {
    global $db;
    
    $userId = (int)($_GET['userId'] ?? ($_SESSION['user_id'] ?? 0));
    
    if (!$userId) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'error' => 'User not authenticated',
            'events' => []
        ]);
        exit;
    }
    
    // Get user's bookings for calendar display
    $sql = "SELECT 
            p.plan_id as id,
            p.event_name as title,
            DATE(p.event_date) as start,
            p.event_time,
            p.venue,
            p.status,
            p.total_price,
            p.occasion_id,
            o.occasion_name
            FROM plans_tbl p
            LEFT JOIN occasions_tbl o ON p.occasion_id = o.occasion_id
            WHERE p.user_id = ? 
            AND p.event_date IS NOT NULL
            ORDER BY p.event_date ASC";
    
    $stmt = $db->prepare($sql);
    
    if (!$stmt) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $db->error,
            'events' => []
        ]);
        exit;
    }
    
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Query execution failed: ' . $stmt->error,
            'events' => []
        ]);
        $stmt->close();
        exit;
    }
    
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Determine status color
        $backgroundColor = 'rgba(138, 118, 80, 0.7)'; // Primary/default
        if ($row['status'] === 'pending') {
            $backgroundColor = 'rgba(255, 193, 7, 0.7)'; // Yellow for pending
        } elseif ($row['status'] === 'confirmed') {
            $backgroundColor = 'rgba(76, 175, 80, 0.7)'; // Green for confirmed
        } elseif ($row['status'] === 'completed') {
            $backgroundColor = 'rgba(33, 150, 243, 0.7)'; // Blue for completed
        } elseif ($row['status'] === 'canceled') {
            $backgroundColor = 'rgba(244, 67, 54, 0.7)'; // Red for canceled
        }
        
        $events[] = [
            'id' => (int)$row['id'],
            'title' => htmlspecialchars($row['title'] ?? 'Event'),
            'start' => $row['start'],
            'backgroundColor' => $backgroundColor,
            'borderColor' => '#8A7650',
            'extendedProps' => [
                'time' => $row['event_time'] ?? '',
                'venue' => $row['venue'] ?? '',
                'status' => $row['status'] ?? 'pending',
                'occasion' => $row['occasion_name'] ?? 'Event',
                'price' => number_format((float)($row['total_price'] ?? 0), 2)
            ]
        ];
    }
    
    $stmt->close();
    
    header('Content-Type: application/json');
    echo json_encode($events);
}

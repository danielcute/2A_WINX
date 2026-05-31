<?php
/**
 * Admin Notification Controller
 * File: /app/controllers/AdminNotificationController.php
 *
 * Handles route: admin-notifications
 * Actions: get_unread, mark_as_read, mark_all_as_read, delete
 */

// Prevent PHP from outputting HTML errors/warnings that break JSON parsing
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Catch fatal errors and return JSON
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) { http_response_code(500); }
        if (ob_get_length() === 0 || strpos(ob_get_contents(), '{') === false) { ob_clean(); echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e['message']]); }
    }
});

 * File: /app/controllers/AdminNotificationController.php
 *
 * Handles route: admin-notifications
 * Actions: get_unread, mark_as_read, mark_all_as_read, delete
 */

require_once ROOT_PATH . '/config/database.php';

// Ensure session is available for AJAX routes that may bypass index.php session init.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AdminNotificationController {
    private $db;

    public function __construct() {
        // Ensure JSON header is always sent for API responses
        header('Content-Type: application/json; charset=utf-8');
        $this->db = Database::getInstance()->getConnection();
    }

    private function requireAdmin(): void {
        $isAdmin = false;

        // Accept both session styles used across the app.
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            $isAdmin = true;
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $isAdmin = true;
        }

        if (!$isAdmin || !isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
    }

    private function json(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function handle(): void {
        $this->requireAdmin();

        $action = $_GET['action'] ?? $_POST['action'] ?? 'get_unread';

        switch ($action) {
            case 'get_unread':
                $this->getUnread();
                break;
            case 'mark_as_read':
                $this->markAsRead();
                break;
            case 'mark_all_as_read':
                $this->markAllAsRead();
                break;
            case 'delete':
                $this->deleteNotification();
                break;
            default:
                $this->getUnread();
        }
    }

    private function getUnread(): void {
        $limit = min((int)($_GET['limit'] ?? 15), 50);
        $userId = (int)$_SESSION['user_id'];

        // Check if notifications table exists
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'notifications_tbl'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            $this->json([
                'success'       => true,
                'unread_count'  => 0,
                'notifications' => []
            ]);
        }

        $stmt = $this->db->prepare(
            "SELECT id, type, title, message, is_read, created_at
             FROM notifications_tbl
             WHERE user_id = ? AND is_read = 0
             ORDER BY created_at DESC
             LIMIT ?"
        );

        if (!$stmt) {
            $this->json(['success' => true, 'unread_count' => 0, 'notifications' => []]);
        }

        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt->close();

        $this->json([
            'success'       => true,
            'unread_count'  => count($notifications),
            'notifications' => $notifications
        ]);
    }

    private function markAsRead(): void {
        $notifId = (int)($_POST['notification_id'] ?? 0);
        $userId  = (int)$_SESSION['user_id'];

        if ($notifId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid notification ID']);
        }

        $stmt = $this->db->prepare(
            "UPDATE notifications_tbl SET is_read = 1
             WHERE id = ? AND user_id = ?"
        );

        if (!$stmt) {
            $this->json(['success' => false, 'message' => 'DB error']);
        }

        $stmt->bind_param('ii', $notifId, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        $this->json(['success' => $ok]);
    }

    private function markAllAsRead(): void {
        $userId = (int)$_SESSION['user_id'];

        $stmt = $this->db->prepare(
            "UPDATE notifications_tbl SET is_read = 1 WHERE user_id = ?"
        );

        if (!$stmt) {
            $this->json(['success' => false, 'message' => 'DB error']);
        }

        $stmt->bind_param('i', $userId);
        $ok = $stmt->execute();
        $stmt->close();

        $this->json(['success' => $ok]);
    }

    private function deleteNotification(): void {
        $notifId = (int)($_POST['notification_id'] ?? 0);
        $userId  = (int)$_SESSION['user_id'];

        if ($notifId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid notification ID']);
        }

        $stmt = $this->db->prepare(
            "DELETE FROM notifications_tbl WHERE id = ? AND user_id = ?"
        );

        if (!$stmt) {
            $this->json(['success' => false, 'message' => 'DB error']);
        }

        $stmt->bind_param('ii', $notifId, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        $this->json(['success' => $ok]);
    }
}
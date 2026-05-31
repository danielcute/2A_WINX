<?php
/**
 * Public Feedback API
 * Fetches approved feedbacks for display on landing page and public areas
 */
error_reporting(E_ALL);
ini_set('display_errors', '0');
ob_start();

session_start();

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Feedback.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $feedbackModel = new Feedback();
        
        // Get all feedbacks to show publicly on landing page
        $db = Database::getInstance()->getConnection();
        
        if (!$db) {
            error_log("Feedback API: Database connection failed");
            http_response_code(503);
            ob_clean();
            echo json_encode([
                'success' => false,
                'data' => [],
                'message' => 'Database connection error'
            ]);
            exit;
        }
        
        $sql = "SELECT f.feedback_id, f.subject, f.message, f.rating, f.status, f.created_at,
                u.first_name, u.last_name, u.image as user_image
                FROM feedback_tbl f
                LEFT JOIN users_tbl u ON f.user_id = u.user_id
                ORDER BY f.created_at DESC
                LIMIT 10";
        
        $result = $db->query($sql);
        $feedbacks = [];
        
        if (!$result) {
            error_log("Feedback API Query Error: " . $db->error);
            http_response_code(500);
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $db->error
            ]);
            exit;
        }
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Get latest admin reply for this feedback
                $replyId = $row['feedback_id'];
                $replySql = "SELECT message, created_at FROM feedback_replies_tbl 
                            WHERE feedback_id = $replyId AND reply_type = 'admin'
                            ORDER BY created_at DESC LIMIT 1";
                $replyResult = $db->query($replySql);
                $latestReply = null;
                if ($replyResult && $replyResult->num_rows > 0) {
                    $latestReply = $replyResult->fetch_assoc();
                }
                
                $feedbacks[] = [
                    'feedback_id' => $row['feedback_id'],
                    'subject' => htmlspecialchars($row['subject']),
                    'message' => htmlspecialchars(substr($row['message'], 0, 150)) . '...',
                    'rating' => $row['rating'] ?? 0,
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'user_name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
                    'user_image' => $row['user_image'],
                    'admin_reply' => $latestReply ? htmlspecialchars(substr($latestReply['message'], 0, 100)) . '...' : null,
                    'admin_reply_date' => $latestReply ? $latestReply['created_at'] : null
                ];
            }
        }
        
        http_response_code(200);
        ob_clean();
        echo json_encode([
            'success' => true,
            'data' => $feedbacks,
            'total' => count($feedbacks)
        ]);
    } else {
        http_response_code(405);
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    ob_clean();
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

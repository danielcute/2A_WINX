<?php
/**
 * User Model
 * Handles user authentication and profile management
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
require_once ROOT_PATH . '/config/database.php';
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

use PragmaRX\Google2FA\Google2FA;

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findByEmail($email) {
        $email = strtolower(trim($email)); // Ensure lowercase
        error_log("Finding user by email: " . $email);
        
        $stmt = $this->db->prepare("SELECT * FROM users_tbl WHERE LOWER(email) = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            error_log("User found - ID: " . $user['user_id'] . ", Email: " . $user['email']);
        } else {
            error_log("No user found with email: " . $email);
        }
        
        return $user;
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users_tbl WHERE user_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        $email = strtolower(trim($data['email'] ?? '')); // Store as lowercase
        $phone = $data['phone'] ?? '';
        $birthday = $data['birthday'] ?? null;
        $address = $data['address'] ?? '';
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = $data['role'] ?? 'user';
        
        $stmt = $this->db->prepare("INSERT INTO users_tbl (first_name, last_name, email, phone, birthday, address, password, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("ssssssss", $firstName, $lastName, $email, $phone, $birthday, $address, $password, $role);
        
        if ($stmt->execute()) {
            $id = $this->db->insert_id;
            $stmt->close();
            return $id;
        } else {
            error_log("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    public function update($id, $data) {
        $id = (int)$id;
        $sets = [];
        $types = "";
        $values = [];
        
        if (isset($data['first_name'])) {
            $sets[] = "first_name = ?";
            $types .= "s";
            $values[] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $sets[] = "last_name = ?";
            $types .= "s";
            $values[] = $data['last_name'];
        }
        if (isset($data['phone'])) {
            $sets[] = "phone = ?";
            $types .= "s";
            $values[] = $data['phone'];
        }
        if (isset($data['city'])) {
            $sets[] = "city = ?";
            $types .= "s";
            $values[] = $data['city'];
        }
        if (isset($data['birthday'])) {
            $sets[] = "birthday = ?";
            $types .= "s";
            $values[] = $data['birthday'];
        }
        if (isset($data['image'])) {
            $sets[] = "image = ?";
            $types .= "s";
            $values[] = $data['image'];
        }
        
        if (empty($sets)) return false;
        
        // Add the id parameter
        $types .= "i";
        $values[] = $id;
        
        $sql = "UPDATE users_tbl SET " . implode(', ', $sets) . " WHERE user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        // Create references for bind_param (required for pass-by-reference)
        $refs = [];
        $refs[] = &$types;
        foreach ($values as $key => $value) {
            $refs[] = &$values[$key];
        }
        
        call_user_func_array([$stmt, 'bind_param'], $refs);
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function updatePassword($id, $newPassword) {
        $id = (int)$id;
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("UPDATE users_tbl SET password = ? WHERE user_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("si", $hashedPassword, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        // Debug logging
        error_log("=== AUTHENTICATE DEBUG ===");
        error_log("Email provided: " . $email);
        error_log("User found: " . ($user ? "YES (ID: " . $user['user_id'] . ")" : "NO"));
        
        if ($user) {
            error_log("Stored password hash: " . substr($user['password'], 0, 20) . "...");
            error_log("Password verify result: " . (password_verify($password, $user['password']) ? "TRUE" : "FALSE"));
            
            // Extra check: verify the hash is valid
            $info = password_get_info($user['password']);
            error_log("Hash algo: " . $info['algo'] . ", Valid hash: " . ($info['algo'] !== 0 ? "YES" : "NO"));
        }
        
        if ($user && password_verify($password, $user['password'])) {
            error_log("Authentication SUCCESSFUL");
            return $user;
        }
        
        error_log("Authentication FAILED");
        return false;
    }
    
    public function getAll($limit = null, $offset = 0) {
        if ($limit) {
            $stmt = $this->db->prepare("SELECT * FROM users_tbl WHERE role = 'user' ORDER BY created_at DESC LIMIT ?, ?");
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            $offset = (int)$offset;
            $limit = (int)$limit;
            $stmt->bind_param("ii", $offset, $limit);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users_tbl WHERE role = 'user' ORDER BY created_at DESC");
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }
    
    public function getCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users_tbl WHERE role = 'user'");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return 0;
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }
    
    public function emailExists($email) {
        $email = strtolower(trim($email)); // Ensure lowercase
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users_tbl WHERE LOWER(email) = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'] > 0;
    }
    
    /**
     * Generate a new 2FA secret for user
     */
    public function generateTwoFactorSecret() {
        if (!class_exists('PragmaRX\Google2FA\Google2FA')) {
            error_log("SINTA Warning: 2FA library missing. Secret generation skipped.");
            return null;
        }
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey(32);
    }
    
    /**
     * Enable 2FA for a user
     */
    public function enableTwoFactor($userId, $secret) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("UPDATE users_tbl SET two_factor_secret = ?, two_factor_enabled = 1 WHERE user_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        $stmt->bind_param("si", $secret, $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    /**
     * Disable 2FA for a user
     */
    public function disableTwoFactor($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("UPDATE users_tbl SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE user_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        $stmt->bind_param("i", $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    /**
     * Check if 2FA is enabled for a user
     */
    public function isTwoFactorEnabled($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("SELECT two_factor_enabled, two_factor_secret FROM users_tbl WHERE user_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row && $row['two_factor_enabled'] == 1 ? $row : false;
    }
    
    /**
     * Get 2FA secret for a user
     */
    public function getTwoFactorSecret($userId) {
        $userId = (int)$userId;
        $stmt = $this->db->prepare("SELECT two_factor_secret FROM users_tbl WHERE user_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['two_factor_secret'] : null;
    }
    
    /**
     * Verify 2FA code (TOTP compatible with Google Authenticator)
     * @param $userId - The user ID
     * @param $code - The 6-digit code to verify
     * @param $tempSecret - Optional temporary secret (for setup phase). If not provided, fetches from database.
     */
    public function verifyTwoFactorCode($userId, $code, $tempSecret = null) {
        // Use provided secret or fetch from database
        $secret = $tempSecret ?? $this->getTwoFactorSecret($userId);
        
        if (!$secret) {
            error_log("2FA verification failed: No secret found for user $userId");
            return false;
        }
        
        // Sanitize the code - remove any spaces and ensure it's 6 digits
        $code = trim($code);
        $code = preg_replace('/\s+/', '', $code);
        
        if (!preg_match('/^\d{6}$/', $code)) {
            error_log("2FA verification failed for user $userId: Invalid code format: $code");
            return false;
        }
        
        try {
            $google2fa = new Google2FA();
            
            // verifyKey() uses default settings: SHA1 algorithm, 6 digits, 30-second window
            $result = $google2fa->verifyKey($secret, $code);
            
            error_log("2FA Code Verification for user $userId: code=$code, result=" . ($result ? "VALID" : "INVALID"));
            return $result;
        } catch (\Exception $e) {
            error_log("2FA verification exception for user $userId: " . $e->getMessage());
            return false;
        }
    }
}
?>

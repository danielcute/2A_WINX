<?php
require_once ROOT_PATH . '/config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findByEmail($email) {
        $email = strtolower(trim($email)); // Ensure lowercase
        $stmt = $this->db->prepare("SELECT * FROM users_tbl WHERE LOWER(email) = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
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
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $values));
        
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
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
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
}
?>
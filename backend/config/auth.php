<?php
require_once 'db.php';

class AuthManager {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    public function login($username, $password, $userType) {
        try {
            if ($userType === 'admin') {
                return $this->loginAdmin($username, $password);
            } else {
                return $this->loginStudent($username, $password);
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login error: ' . $e->getMessage()];
        }
    }
    
    private function loginAdmin($username, $password) {
        $stmt = $this->conn->prepare("SELECT id, username, password, full_name, email FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $sessionId = $this->createSession($user['id'], 'admin');
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => 'admin'
                ],
                'session_id' => $sessionId
            ];
        }
        
        return ['success' => false, 'message' => 'Invalid admin credentials'];
    }
    
    private function loginStudent($email, $password) {
        $stmt = $this->conn->prepare("
            SELECT s.id, s.name, s.email, s.password, d.name as department_name 
            FROM students s 
            JOIN departments d ON s.department_id = d.id 
            WHERE s.email = ? AND s.is_active = 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $sessionId = $this->createSession($user['id'], 'student');
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'department' => $user['department_name'],
                    'role' => 'student'
                ],
                'session_id' => $sessionId
            ];
        }
        
        return ['success' => false, 'message' => 'Invalid student credentials'];
    }
    
    private function createSession($userId, $userType) {
        $sessionId = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Clean up expired sessions
        $this->cleanupExpiredSessions();
        
        $stmt = $this->conn->prepare("INSERT INTO user_sessions (session_id, user_id, user_type, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sessionId, $userId, $userType, $expiresAt]);
        
        return $sessionId;
    }
    
    public function validateSession($sessionId) {
        $stmt = $this->conn->prepare("
            SELECT us.user_id, us.user_type, us.expires_at 
            FROM user_sessions us 
            WHERE us.session_id = ? AND us.expires_at > NOW()
        ");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();
        
        if (!$session) {
            return ['success' => false, 'message' => 'Invalid or expired session'];
        }
        
        // Get user details based on type
        if ($session['user_type'] === 'admin') {
            $stmt = $this->conn->prepare("SELECT id, username, full_name, email FROM admin_users WHERE id = ?");
            $stmt->execute([$session['user_id']]);
            $user = $stmt->fetch();
            $user['role'] = 'admin';
        } else {
            $stmt = $this->conn->prepare("
                SELECT s.id, s.name, s.email, d.name as department_name 
                FROM students s 
                JOIN departments d ON s.department_id = d.id 
                WHERE s.id = ? AND s.is_active = 1
            ");
            $stmt->execute([$session['user_id']]);
            $user = $stmt->fetch();
            $user['role'] = 'student';
            $user['department'] = $user['department_name'];
        }
        
        return ['success' => true, 'user' => $user];
    }
    
    public function logout($sessionId) {
        $stmt = $this->conn->prepare("DELETE FROM user_sessions WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    private function cleanupExpiredSessions() {
        $stmt = $this->conn->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
        $stmt->execute();
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

// Helper function to check authentication
function requireAuth() {
    $sessionId = $_COOKIE['session_id'] ?? null;
    
    if (!$sessionId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    $auth = new AuthManager();
    $result = $auth->validateSession($sessionId);
    
    if (!$result['success']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid session']);
        exit;
    }
    
    return $result['user'];
}

// Helper function to check admin role
function requireAdmin() {
    $user = requireAuth();
    
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }
    
    return $user;
}

// Helper function to check student role
function requireStudent() {
    $user = requireAuth();
    
    if ($user['role'] !== 'student') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Student access required']);
        exit;
    }
    
    return $user;
}
?>

<?php
require_once '../config/auth.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'validate':
        handleValidate();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    $userType = $input['userType'] ?? '';
    
    if (empty($username) || empty($password) || empty($userType)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    $auth = new AuthManager();
    $result = $auth->login($username, $password, $userType);
    
    if ($result['success']) {
        // Set session cookie
        setcookie('session_id', $result['session_id'], time() + 86400, '/', '', false, true);
        unset($result['session_id']); // Don't send session ID in response
    }
    
    echo json_encode($result);
}

function handleLogout() {
    $sessionId = $_COOKIE['session_id'] ?? null;
    
    if ($sessionId) {
        $auth = new AuthManager();
        $auth->logout($sessionId);
        setcookie('session_id', '', time() - 3600, '/', '', false, true);
    }
    
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

function handleValidate() {
    $sessionId = $_COOKIE['session_id'] ?? null;
    
    if (!$sessionId) {
        echo json_encode(['success' => false, 'message' => 'No session found']);
        return;
    }
    
    $auth = new AuthManager();
    $result = $auth->validateSession($sessionId);
    
    echo json_encode($result);
}
?>

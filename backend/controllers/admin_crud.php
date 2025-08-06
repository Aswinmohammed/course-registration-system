<?php
require_once '../config/auth.php';

// Support method override for PUT via POST + _method (for JSON payloads)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (isset($input['_method']) && $input['_method'] === 'PUT') {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
    }
}

// Require admin authentication
$admin = requireAdmin();

$action = $_GET['action'] ?? '';
$entity = $_GET['entity'] ?? '';

switch ($entity) {
    case 'students':
        handleStudents($action);
        break;
    case 'courses':
        handleCourses($action);
        break;
    case 'departments':
        handleDepartments($action);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid entity']);
}

function handleStudents($action) {
    $conn = getDBConnection();
    
    switch ($action) {
        case 'create':
            createStudent($conn);
            break;
        case 'update':
            updateStudent($conn);
            break;
        case 'delete':
            deleteStudent($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function createStudent($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $department_id = $input['department_id'] ?? '';
    $password = $input['password'] ?? 'student123';
    
    if (empty($name) || empty($email) || empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'Name, email, and department are required']);
        return;
    }
    
    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO students (name, email, department_id, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $department_id, $hashedPassword]);
        
        echo json_encode(['success' => true, 'message' => 'Student created successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating student: ' . $e->getMessage()]);
    }
}

function updateStudent($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        echo json_encode(['success' => false, 'message' => 'Only PUT method allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? '';
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $department_id = $input['department_id'] ?? '';
    
    if (empty($id) || empty($name) || empty($email) || empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    try {
        // Check if email already exists for another student
        $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }

        $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, department_id = ? WHERE id = ?");
        $stmt->execute([$name, $email, $department_id, $id]);
        
        echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating student: ' . $e->getMessage()]);
    }
}

function deleteStudent($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        echo json_encode(['success' => false, 'message' => 'Only DELETE method allowed']);
        return;
    }
    
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Student ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting student: ' . $e->getMessage()]);
    }
}

function handleCourses($action) {
    $conn = getDBConnection();
    
    switch ($action) {
        case 'create':
            createCourse($conn);
            break;
        case 'update':
            updateCourse($conn);
            break;
        case 'delete':
            deleteCourse($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function createCourse($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = $input['name'] ?? '';
    $course_code = $input['course_code'] ?? '';
    $department_id = $input['department_id'] ?? '';
    $seat_limit = $input['seat_limit'] ?? 30;
    
    if (empty($name) || empty($course_code) || empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'Name, course code, and department are required']);
        return;
    }
    
    try {
        // Check if course_code already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ?");
        $stmt->execute([$course_code]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Course code already exists']);
            return;
        }

        $stmt = $conn->prepare("INSERT INTO courses (name, course_code, department_id, seat_limit) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $course_code, $department_id, $seat_limit]);
        
        echo json_encode(['success' => true, 'message' => 'Course created successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating course: ' . $e->getMessage()]);
    }
}

function updateCourse($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        echo json_encode(['success' => false, 'message' => 'Only PUT method allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? '';
    $name = $input['name'] ?? '';
    $course_code = $input['course_code'] ?? '';
    $department_id = $input['department_id'] ?? '';
    $seat_limit = $input['seat_limit'] ?? 30;
    
    if (empty($id) || empty($name) || empty($course_code) || empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    try {
        // Check if course_code already exists for another course
        $stmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ? AND id != ?");
        $stmt->execute([$course_code, $id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Course code already exists']);
            return;
        }

        $stmt = $conn->prepare("UPDATE courses SET name = ?, course_code = ?, department_id = ?, seat_limit = ? WHERE id = ?");
        $stmt->execute([$name, $course_code, $department_id, $seat_limit, $id]);
        
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating course: ' . $e->getMessage()]);
    }
}

function deleteCourse($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        echo json_encode(['success' => false, 'message' => 'Only DELETE method allowed']);
        return;
    }
    
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Course ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting course: ' . $e->getMessage()]);
    }
}

function handleDepartments($action) {
    $conn = getDBConnection();
    
    switch ($action) {
        case 'create':
            createDepartment($conn);
            break;
        case 'update':
            updateDepartment($conn);
            break;
        case 'delete':
            deleteDepartment($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function createDepartment($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = $input['name'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Department name is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
        $stmt->execute([$name]);
        
        echo json_encode(['success' => true, 'message' => 'Department created successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating department: ' . $e->getMessage()]);
    }
}

function updateDepartment($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        echo json_encode(['success' => false, 'message' => 'Only PUT method allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? '';
    $name = $input['name'] ?? '';
    
    if (empty($id) || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'ID and name are required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE departments SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        
        echo json_encode(['success' => true, 'message' => 'Department updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating department: ' . $e->getMessage()]);
    }
}

function deleteDepartment($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        echo json_encode(['success' => false, 'message' => 'Only DELETE method allowed']);
        return;
    }
    
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Department ID is required']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Department deleted successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting department: ' . $e->getMessage()]);
    }
}
?>

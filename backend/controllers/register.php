<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['student_id']) || !isset($input['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Student ID and Course ID are required']);
    exit;
}

try {
    $conn = getDBConnection();
    
    $student_id = (int)$input['student_id'];
    $course_id = (int)$input['course_id'];
    
    // Call the stored procedure
    $stmt = $conn->prepare("CALL register_student_course(?, ?, @result_message, @success)");
    $stmt->execute([$student_id, $course_id]);
    
    // Get the output parameters
    $result = $conn->query("SELECT @result_message as message, @success as success")->fetch();
    
    echo json_encode([
        'success' => (bool)$result['success'],
        'message' => $result['message']
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error registering for course: ' . $e->getMessage()
    ]);
}
?>

<?php
require_once '../config/db.php';

try {
    $conn = getDBConnection();
    
    $student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
    $course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
    
    if ($student_id) {
        // Get enrollments for a specific student
        $query = "
            SELECT 
                r.id,
                r.registered_at,
                c.id as course_id,
                c.name as course_name,
                c.course_code,
                d.name as department_name
            FROM registrations r
            JOIN courses c ON r.course_id = c.id
            JOIN departments d ON c.department_id = d.id
            WHERE r.student_id = ?
            ORDER BY r.registered_at DESC
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$student_id]);
        
    } elseif ($course_id) {
        // Get students enrolled in a specific course
        $query = "
            SELECT 
                r.id,
                r.registered_at,
                s.id as student_id,
                s.name as student_name,
                s.email as student_email,
                d.name as department_name
            FROM registrations r
            JOIN students s ON r.student_id = s.id
            JOIN departments d ON s.department_id = d.id
            WHERE r.course_id = ?
            ORDER BY s.name
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$course_id]);
        
    } else {
        // Get all enrollments with full details
        $query = "
            SELECT 
                r.id,
                r.registered_at,
                s.id as student_id,
                s.name as student_name,
                s.email as student_email,
                c.id as course_id,
                c.name as course_name,
                c.course_code,
                d1.name as student_department,
                d2.name as course_department
            FROM registrations r
            JOIN students s ON r.student_id = s.id
            JOIN courses c ON r.course_id = c.id
            JOIN departments d1 ON s.department_id = d1.id
            JOIN departments d2 ON c.department_id = d2.id
            ORDER BY r.registered_at DESC
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute();
    }
    
    $enrollments = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $enrollments
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching enrollments: ' . $e->getMessage()
    ]);
}
?>

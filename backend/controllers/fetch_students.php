<?php
require_once '../config/db.php';

try {
    $conn = getDBConnection();
    
    // Get all students with department info
    $query = "
        SELECT 
            s.id,
            s.name,
            s.email,
            d.name as department_name
        FROM students s
        JOIN departments d ON s.department_id = d.id
        ORDER BY s.name
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $students
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching students: ' . $e->getMessage()
    ]);
}
?>

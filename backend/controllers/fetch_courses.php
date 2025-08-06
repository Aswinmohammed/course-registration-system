<?php
require_once '../config/db.php';

try {
    $conn = getDBConnection();
    
    // Get all courses with department info and available seats
    $query = "
        SELECT 
            c.id,
            c.name,
            c.course_code,
            c.seat_limit,
            d.name as department_name,
            get_available_seats(c.id) as available_seats,
            (c.seat_limit - get_available_seats(c.id)) as enrolled_count
        FROM courses c
        JOIN departments d ON c.department_id = d.id
        ORDER BY d.name, c.name
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $courses = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $courses
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching courses: ' . $e->getMessage()
    ]);
}
?>

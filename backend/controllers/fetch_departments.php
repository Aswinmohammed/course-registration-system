<?php
require_once '../config/db.php';

try {
    $conn = getDBConnection();
    
    $query = "SELECT id, name FROM departments ORDER BY name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $departments = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $departments
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching departments: ' . $e->getMessage()
    ]);
}
?>

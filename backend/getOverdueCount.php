<?php
require '../adminAuth.php';
require '../db.php';

header('Content-Type: application/json');

try {
    // Count overdue dispatches
    $query = "
        SELECT COUNT(*) as overdueCount
        FROM do_sales
        WHERE paymentStatus IN ('pending', 'partial')
        AND dueDate IS NOT NULL
        AND dueDate != '0000-00-00'
        AND DATEDIFF(dueDate, CURDATE()) < 0
    ";
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'overdueCount' => intval($row['overdueCount'])
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

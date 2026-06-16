<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $officerID = intval($_POST['officerID'] ?? 0);
    
    // Validation
    if ($officerID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid officer ID']);
        exit;
    }
    
    try {
        // Change status from 0 (active) to 1 (inactive)
        $stmt = $conn->prepare("UPDATE distributing_officer SET status = 1, updatedAt = NOW() WHERE DO_ID = ?");
        $stmt->bind_param("i", $officerID);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Warehouse deactivated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Warehouse not found or already inactive']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to deactivate officer: ' . $stmt->error]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

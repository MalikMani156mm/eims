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
    
    // Check if officer was created today
    $checkStmt = $conn->prepare("SELECT createdAt FROM distributing_officer WHERE DO_ID = ?");
    $checkStmt->bind_param("i", $officerID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $createdDate = date('Y-m-d', strtotime($row['createdAt']));
        $today = date('Y-m-d');
        
        if ($createdDate !== $today) {
            echo json_encode(['success' => false, 'message' => 'Officers can only be deleted on the day they were created']);
            $checkStmt->close();
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Warehouse not found']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();
    
    try {
        $stmt = $conn->prepare("DELETE FROM distributing_officer WHERE DO_ID = ?");
        $stmt->bind_param("i", $officerID);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Warehouse deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Warehouse not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete officer: ' . $stmt->error]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

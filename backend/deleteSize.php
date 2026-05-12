<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sizeID = intval($_POST['id'] ?? 0);
    
    if ($sizeID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid tonnage ID']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM sizes WHERE sizeID = ?");
        $stmt->bind_param("i", $sizeID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Tonnage deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete tonnage']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

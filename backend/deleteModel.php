<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelID = intval($_POST['id'] ?? 0);
    
    if ($modelID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid model ID']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM models WHERE modelID = ?");
        $stmt->bind_param("i", $modelID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Model deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete model']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

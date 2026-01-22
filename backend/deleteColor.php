<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colorID = intval($_POST['id'] ?? 0);
    
    if ($colorID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid color ID']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM colors WHERE colorID = ?");
        $stmt->bind_param("i", $colorID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Color deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete color']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

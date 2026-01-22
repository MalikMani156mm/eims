<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regionID = intval($_POST['id'] ?? 0);
    
    if ($regionID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid region ID']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM regions WHERE regionID = ?");
        $stmt->bind_param("i", $regionID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Region deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete region']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

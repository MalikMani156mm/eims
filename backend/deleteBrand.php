<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brandID = intval($_POST['id'] ?? 0);
    
    if ($brandID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid brand ID']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM brands WHERE brandID = ?");
        $stmt->bind_param("i", $brandID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Brand deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete brand']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

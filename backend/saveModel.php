<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelName = trim($_POST['modelName'] ?? '');
    $categoryID = intval($_POST['categoryID'] ?? 0);
    
    if (empty($modelName)) {
        echo json_encode(['success' => false, 'message' => 'Model name is required']);
        exit;
    }
    
    if ($categoryID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a category']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO models (modelName, categoryID) VALUES (?, ?)");
        $stmt->bind_param("si", $modelName, $categoryID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Model added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add model']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

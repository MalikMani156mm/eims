<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sizeName = trim($_POST['sizeName'] ?? '');
    $categoryID = intval($_POST['categoryID'] ?? 0);
    
    if (empty($sizeName)) {
        echo json_encode(['success' => false, 'message' => 'Tonnage is required']);
        exit;
    }
    
    if ($categoryID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a category']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO sizes (sizeName, categoryID) VALUES (?, ?)");
        $stmt->bind_param("si", $sizeName, $categoryID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Tonnage added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add size']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

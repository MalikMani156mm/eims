<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colorName = trim($_POST['colorName'] ?? '');
    
    if (empty($colorName)) {
        echo json_encode(['success' => false, 'message' => 'Color name is required']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO colors (colorName) VALUES (?)");
        $stmt->bind_param("s", $colorName);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Color added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add color']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

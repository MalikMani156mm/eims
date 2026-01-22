<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['categoryName'] ?? '');
    
    if (empty($categoryName)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO categories (categoryName) VALUES (?)");
        $stmt->bind_param("s", $categoryName);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add category']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

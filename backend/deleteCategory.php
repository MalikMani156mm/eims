<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryID = intval($_POST['id'] ?? 0);
    
    if ($categoryID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM categories WHERE categoriesID = ?");
        $stmt->bind_param("i", $categoryID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

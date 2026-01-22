<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['productName'] ?? '');
    $categoryID = intval($_POST['categoryID'] ?? 0);
    $colorID = intval($_POST['colorID'] ?? 0);
    $modelID = intval($_POST['modelID'] ?? 0);
    $sizeID = intval($_POST['sizeID'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $regionID = 1; // Always store as 1
    
    // Validation
    if (empty($productName)) {
        echo json_encode(['success' => false, 'message' => 'Product name is required']);
        exit;
    }
    
    if ($categoryID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a category']);
        exit;
    }
    
    if ($colorID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a color']);
        exit;
    }
    
    if ($modelID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a model']);
        exit;
    }
    
    if ($sizeID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a size']);
        exit;
    }
    
    if ($quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity cannot be negative']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO products (productName, categoryID, colorID, modelID, sizeID, quantity, regionID, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiiiiis", $productName, $categoryID, $colorID, $modelID, $sizeID, $quantity, $regionID, $description);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

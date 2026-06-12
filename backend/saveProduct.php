<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['productName'] ?? '');
    $brandID = intval($_POST['brandID'] ?? 0);
    $categoryID = intval($_POST['categoryID'] ?? 0);
    $colorID = intval($_POST['colorID'] ?? 0);
    $modelID = intval($_POST['modelID'] ?? 0);
    $sizeID = intval($_POST['sizeID'] ?? 0);
    $batchNumber = trim($_POST['batchNumber'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $regionID = intval($_POST['regionID'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($productName)) {
        echo json_encode(['success' => false, 'message' => 'Product name is required']);
        exit;
    }
    
    if ($categoryID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a category']);
        exit;
    }

    if ($brandID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a brand']);
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
    
    if (empty($batchNumber)) {
        echo json_encode(['success' => false, 'message' => 'Batch number is required']);
        exit;
    }
    
    if ($quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity cannot be negative']);
        exit;
    }
    
    if ($regionID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a region']);
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert product (set available = quantity initially, cost = NULL for assembling)
        $stmt = $conn->prepare("INSERT INTO products (productName, categoryID, brandID, colorID, modelID, sizeID, batchNumber, quantity, available, regionID, cost, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?)");
        $stmt->bind_param("siiiiisiiis", $productName, $categoryID, $brandID, $colorID, $modelID, $sizeID, $batchNumber, $quantity, $quantity, $regionID, $description);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add product');
        }
        
        $productID = $conn->insert_id;
        $stmt->close();
        
        // Insert into product_batch table
        $batchStmt = $conn->prepare("INSERT INTO product_batch (productID, batchNumber, quantity, cost) VALUES (?, ?, ?, NULL)");
        $batchStmt->bind_param("isi", $productID, $batchNumber, $quantity);
        
        if (!$batchStmt->execute()) {
            throw new Exception('Failed to log batch information');
        }
        $batchStmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Product added successfully', 'productID' => $productID, 'batchNumber' => $batchNumber]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

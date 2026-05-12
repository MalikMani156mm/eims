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
    $batchNumber = trim($_POST['batchNumber'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $regionID = intval($_POST['regionID'] ?? 0);
    $cost = floatval($_POST['cost'] ?? 0);
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
    
    if ($cost < 0) {
        echo json_encode(['success' => false, 'message' => 'Cost cannot be negative']);
        exit;
    }
    
    // Check for duplicate product (same name, category, color, size, and region)
    $checkStmt = $conn->prepare("SELECT productID FROM products WHERE productName = ? AND categoryID = ? AND colorID = ? AND sizeID = ? AND regionID = ?");
    $checkStmt->bind_param("siiii", $productName, $categoryID, $colorID, $sizeID, $regionID);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Product with same name, category, color, size, and region already exists']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert product (set available = quantity initially)
        $stmt = $conn->prepare("INSERT INTO products (productName, categoryID, colorID, modelID, sizeID, batchNumber, quantity, available, regionID, cost, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiiiisiids", $productName, $categoryID, $colorID, $modelID, $sizeID, $batchNumber, $quantity, $quantity, $regionID, $cost, $description);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add product');
        }
        
        $productID = $conn->insert_id;
        $stmt->close();
        
        // Insert into product_batch table
        $batchStmt = $conn->prepare("INSERT INTO product_batch (productID, batchNumber, quantity, cost) VALUES (?, ?, ?, ?)");
        $batchStmt->bind_param("isid", $productID, $batchNumber, $quantity, $cost);
        
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

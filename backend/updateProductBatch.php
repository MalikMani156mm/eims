<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productID = intval($_POST['productID'] ?? 0);
    $batchNumber = trim($_POST['batchNumber'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $cost = floatval($_POST['cost'] ?? 0);
    
    // Validation
    if ($productID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    if (empty($batchNumber)) {
        echo json_encode(['success' => false, 'message' => 'Batch number is required']);
        exit;
    }
    
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
        exit;
    }
    
    if ($cost < 0) {
        echo json_encode(['success' => false, 'message' => 'Cost cannot be negative']);
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First, insert into product_batch table (log the batch)
        $batchStmt = $conn->prepare("INSERT INTO product_batch (productID, batchNumber, quantity, cost) VALUES (?, ?, ?, ?)");
        $batchStmt->bind_param("isid", $productID, $batchNumber, $quantity, $cost);
        
        if (!$batchStmt->execute()) {
            throw new Exception('Failed to log batch information');
        }
        $batchStmt->close();
        
        // Second, update the main products table
        // Add quantity, increment available, and update cost
        $updateStmt = $conn->prepare("UPDATE products SET quantity = quantity + ?, available = available + ?, cost = ?, updatedAt = NOW() WHERE productID = ?");
        $updateStmt->bind_param("iidi", $quantity, $quantity, $cost, $productID);
        
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update product');
        }
        
        if ($updateStmt->affected_rows === 0) {
            throw new Exception('Product not found');
        }
        
        $updateStmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => "Product updated successfully! Added $quantity units at RS " . number_format($cost, 2) . " per unit",
            'productID' => $productID,
            'batchNumber' => $batchNumber,
            'quantity' => $quantity
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

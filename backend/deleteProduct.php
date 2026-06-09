<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productID = intval($_POST['id'] ?? 0);
    
    if ($productID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    try {
        $conn->begin_transaction();

        $batchStmt = $conn->prepare("DELETE FROM product_batch WHERE productID = ?");
        $batchStmt->bind_param("i", $productID);
        $batchStmt->execute();
        $batchStmt->close();

        $stmt = $conn->prepare("DELETE FROM products WHERE productID = ?");
        $stmt->bind_param("i", $productID);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete product');
        }
        
        $stmt->close();
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

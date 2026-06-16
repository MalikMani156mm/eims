<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/restoreProductInventory.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productID = intval($_POST['id'] ?? 0);

    if ($productID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    try {
        $conn->begin_transaction();

        $checkStmt = $conn->prepare("SELECT productID FROM products WHERE productID = ? LIMIT 1 FOR UPDATE");
        $checkStmt->bind_param('i', $productID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows === 0) {
            $checkStmt->close();
            throw new Exception('Product not found');
        }
        $checkStmt->close();

        restoreProductInventory($conn, $productID);

        $batchStmt = $conn->prepare("DELETE FROM product_batch WHERE productID = ?");
        $batchStmt->bind_param('i', $productID);
        if (!$batchStmt->execute()) {
            $batchStmt->close();
            throw new Exception('Failed to delete product batch records');
        }
        $batchStmt->close();

        $stmt = $conn->prepare("DELETE FROM products WHERE productID = ?");
        $stmt->bind_param('i', $productID);
        if (!$stmt->execute() || $stmt->affected_rows === 0) {
            $stmt->close();
            throw new Exception('Failed to delete product');
        }
        $stmt->close();

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Product deleted. Parts and gas restored to inventory.'
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();

?>

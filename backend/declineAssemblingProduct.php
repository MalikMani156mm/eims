<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/restoreProductInventory.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$productID = intval($_POST['productID'] ?? 0);

if ($productID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    $conn->begin_transaction();

    $productStmt = $conn->prepare("SELECT productID, productName, status FROM products WHERE productID = ? LIMIT 1 FOR UPDATE");
    $productStmt->bind_param('i', $productID);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
    $product = $productResult->fetch_assoc();
    $productStmt->close();

    if (!$product) {
        throw new Exception('Product not found');
    }

    if (intval($product['status']) !== 0) {
        throw new Exception('Only pending assembling products can be declined');
    }

    restoreProductInventory($conn, $productID);

    $deleteBatchStmt = $conn->prepare("DELETE FROM product_batch WHERE productID = ?");
    $deleteBatchStmt->bind_param('i', $productID);
    if (!$deleteBatchStmt->execute()) {
        $deleteBatchStmt->close();
        throw new Exception('Failed to remove product batch records');
    }
    $deleteBatchStmt->close();

    $deleteProductStmt = $conn->prepare("DELETE FROM products WHERE productID = ? AND status = 0");
    $deleteProductStmt->bind_param('i', $productID);
    if (!$deleteProductStmt->execute() || $deleteProductStmt->affected_rows === 0) {
        $deleteProductStmt->close();
        throw new Exception('Failed to remove product');
    }
    $deleteProductStmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Assembling log declined. Product removed and all parts/gases restored to stock.'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();

?>

<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($adminRole) || ($adminRole !== 'admin' && $adminRole !== 'superadmin')) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$productID = intval($_POST['productID'] ?? 0);
$cost = floatval($_POST['cost'] ?? 0);

if ($productID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

if ($cost <= 0) {
    echo json_encode(['success' => false, 'message' => 'Cost must be greater than 0']);
    exit;
}

try {
    $checkStmt = $conn->prepare("SELECT productID, productName, cost, regionID FROM products WHERE productID = ? LIMIT 1");
    $checkStmt->bind_param('i', $productID);

    $checkStmt->execute();
    $product = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    if ($product['cost'] !== null && $product['cost'] !== '') {
        echo json_encode(['success' => false, 'message' => 'Cost has already been added for this product']);
        exit;
    }

    $conn->begin_transaction();

    $updateProduct = $conn->prepare("UPDATE products SET cost = ?, updatedAt = NOW() WHERE productID = ? AND cost IS NULL");
    $updateProduct->bind_param('di', $cost, $productID);
    if (!$updateProduct->execute() || $updateProduct->affected_rows === 0) {
        $updateProduct->close();
        throw new Exception('Failed to update product cost');
    }
    $updateProduct->close();

    $updateBatch = $conn->prepare("UPDATE product_batch SET cost = ? WHERE productID = ? AND cost IS NULL");
    $updateBatch->bind_param('di', $cost, $productID);
    if (!$updateBatch->execute()) {
        $updateBatch->close();
        throw new Exception('Failed to update batch cost');
    }
    $updateBatch->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Cost added successfully for ' . $product['productName'],
        'productID' => $productID,
        'cost' => $cost
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();

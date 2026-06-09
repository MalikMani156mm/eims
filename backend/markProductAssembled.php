<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$productID = intval($_POST['productID'] ?? 0);
$changeRegion = intval($_POST['changeRegion'] ?? 0);
$newRegionID = intval($_POST['regionID'] ?? 0);

if ($productID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    $checkStmt = $conn->prepare("SELECT productID, status, regionID FROM products WHERE productID = ? LIMIT 1");
    $checkStmt->bind_param('i', $productID);
    $checkStmt->execute();
    $product = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    if (intval($product['status']) !== 0) {
        echo json_encode(['success' => false, 'message' => 'Product is already assembled']);
        exit;
    }

    if (isset($adminRole) && $adminRole !== 'superadmin') {
        $userRegionID = isset($regionID) ? intval($regionID) : 0;
        if (intval($product['regionID']) !== $userRegionID) {
            echo json_encode(['success' => false, 'message' => 'You can only assemble products from your region']);
            exit;
        }
    }

    if ($changeRegion === 1) {
        if ($newRegionID <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid region']);
            exit;
        }

        $regionStmt = $conn->prepare("SELECT regionID FROM regions WHERE regionID = ? LIMIT 1");
        $regionStmt->bind_param('i', $newRegionID);
        $regionStmt->execute();
        $regionExists = $regionStmt->get_result()->fetch_assoc();
        $regionStmt->close();

        if (!$regionExists) {
            echo json_encode(['success' => false, 'message' => 'Selected region does not exist']);
            exit;
        }

        $updateStmt = $conn->prepare("UPDATE products SET status = 1, regionID = ?, updatedAt = NOW() WHERE productID = ? AND status = 0");
        $updateStmt->bind_param('ii', $newRegionID, $productID);
    } else {
        $updateStmt = $conn->prepare("UPDATE products SET status = 1, updatedAt = NOW() WHERE productID = ? AND status = 0");
        $updateStmt->bind_param('i', $productID);
    }

    if (!$updateStmt->execute() || $updateStmt->affected_rows === 0) {
        $updateStmt->close();
        echo json_encode(['success' => false, 'message' => 'Failed to mark product as assembled']);
        exit;
    }

    $updateStmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Product marked as assembled successfully'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();

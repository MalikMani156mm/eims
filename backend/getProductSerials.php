<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$productID = intval($_GET['productID'] ?? 0);

if ($productID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT serialID, serialNumber, batchNumber, status, createdAt
        FROM product_serials
        WHERE productID = ?
        ORDER BY createdAt ASC, serialNumber ASC
    ");
    $stmt->bind_param('i', $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    $serials = [];
    while ($row = $result->fetch_assoc()) {
        $serials[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'data' => $serials
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();

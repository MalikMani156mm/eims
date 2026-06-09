<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT
            ps.serialID,
            ps.productID,
            ps.serialNumber,
            ps.batchNumber,
            ps.status,
            ps.createdAt,
            p.productName,
            c.categoryName,
            m.modelName,
            col.colorName,
            s.sizeName,
            r.regionName
        FROM product_serials ps
        INNER JOIN products p ON ps.productID = p.productID
        LEFT JOIN categories c ON p.categoryID = c.categoriesID
        LEFT JOIN colors col ON p.colorID = col.colorID
        LEFT JOIN models m ON p.modelID = m.modelID
        LEFT JOIN sizes s ON p.sizeID = s.sizeID
        LEFT JOIN regions r ON p.regionID = r.regionID
        WHERE p.status = 1
        ORDER BY ps.createdAt DESC, ps.serialNumber ASC
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    $serials = [];
    while ($row = $result->fetch_assoc()) {
        $serials[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'data' => $serials,
        'total' => count($serials)
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();

<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$brandID = intval($_GET['brandID'] ?? 0);

if ($brandID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid brand ID']);
    exit;
}

try {
    $brandStmt = $conn->prepare("SELECT brandID, brandName FROM brands WHERE brandID = ? LIMIT 1");
    $brandStmt->bind_param('i', $brandID);
    $brandStmt->execute();
    $brand = $brandStmt->get_result()->fetch_assoc();
    $brandStmt->close();

    if (!$brand) {
        echo json_encode(['success' => false, 'message' => 'Brand not found']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT
            gl.gas_log_id,
            gl.product_id,
            p.productName,
            gl.serial_number,
            gm.gas_name,
            gbd.batchName,
            r.regionName,
            gl.quantity_used,
            gl.unit_price,
            gl.total_price,
            gl.created_at
        FROM gas_logs gl
        INNER JOIN products p ON gl.product_id = p.productID
        LEFT JOIN gas_master gm ON gl.gas_id = gm.gas_id
        LEFT JOIN gas_batch_details gbd ON gl.batch_id = gbd.batch_id
        LEFT JOIN regions r ON gbd.regionID = r.regionID
        WHERE p.brandID = ?
        ORDER BY gl.created_at DESC
    ");

    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('i', $brandID);
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'brand' => $brand,
        'data' => $logs
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();

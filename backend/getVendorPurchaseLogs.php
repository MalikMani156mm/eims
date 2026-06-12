<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$vendorID = intval($_GET['vendorID'] ?? 0);

if ($vendorID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid vendor ID']);
    exit;
}

try {
    $vendorStmt = $conn->prepare("SELECT vendorID, vendorName FROM vendors WHERE vendorID = ? LIMIT 1");
    $vendorStmt->bind_param('i', $vendorID);
    $vendorStmt->execute();
    $vendorResult = $vendorStmt->get_result();
    $vendor = $vendorResult->fetch_assoc();
    $vendorStmt->close();

    if (!$vendor) {
        echo json_encode(['success' => false, 'message' => 'Vendor not found']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT
            gbd.batch_id,
            gbd.batchName,
            gm.gas_id,
            gm.gas_name,
            gbd.quantity,
            gbd.available,
            gbd.unit_price,
            gbd.total_price,
            gbd.paid_price,
            gbd.pending_price,
            gbd.createdAt,
            r.regionName
        FROM gas_batch_details gbd
        INNER JOIN gas_master gm ON gbd.gas_id = gm.gas_id
        LEFT JOIN regions r ON gbd.regionID = r.regionID
        WHERE gbd.vendorID = ?
        ORDER BY gbd.batch_id DESC
    ");

    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('i', $vendorID);
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'vendor' => $vendor,
        'data' => $logs
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();

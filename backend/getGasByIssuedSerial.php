<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

$serialNumber = trim($_GET['serialNumber'] ?? '');

if (empty($serialNumber)) {
    echo json_encode(['success' => false, 'gases' => []]);
    exit;
}

try {
    // Fetch all gases allocated to this product serial
    $stmt = $conn->prepare("
        SELECT 
            gl.gas_log_id,
            gl.quantity_used,
            gl.unit_price,
            gl.total_price,
            gl.created_at,
            gm.gas_id,
            gm.gas_name,
            gbd.batch_id,
            gbd.batchName,
            r.regionName
        FROM gas_logs gl
        JOIN gas_master gm ON gl.gas_id = gm.gas_id
        JOIN gas_batch_details gbd ON gl.batch_id = gbd.batch_id
        LEFT JOIN regions r ON gbd.regionID = r.regionID
        WHERE gl.serial_number = ?
        ORDER BY gl.created_at DESC
    ");

    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('s', $serialNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    $gases = [];
    while ($row = $result->fetch_assoc()) {
        $gases[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'gases' => $gases
    ]);

} catch (Exception $e) {
    error_log('Get Gas By Serial Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'gases' => []
    ]);
}
?>

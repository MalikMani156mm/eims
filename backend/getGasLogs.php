<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $gasId = intval($_GET['gas_id'] ?? 0);

    if ($gasId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid gas ID']);
        exit;
    }

    try {
        // Fetch all batches for this gas
        $stmt = $conn->prepare("
            SELECT 
                gb.batch_id,
                gb.batchName,
                gb.quantity,
                gb.available,
                gb.unit_price,
                gb.total_price,
                gb.createdAt,
                r.regionName
            FROM gas_batch_details gb
            LEFT JOIN regions r ON gb.regionID = r.regionID
            WHERE gb.gas_id = ?
            ORDER BY gb.batch_id DESC
        ");

        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param('i', $gasId);
        $stmt->execute();
        $result = $stmt->get_result();

        $batches = [];
        while ($row = $result->fetch_assoc()) {
            $batches[] = $row;
        }

        $stmt->close();

        echo json_encode([
            'success' => true,
            'data' => $batches
        ]);

    } catch (Exception $e) {
        error_log('Get Gas Logs Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

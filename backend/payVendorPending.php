<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/gasHelpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$vendorID = intval($_POST['vendorID'] ?? 0);
$paymentAmount = floatval($_POST['paymentAmount'] ?? 0);

if ($vendorID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid vendor ID']);
    exit;
}

if ($paymentAmount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Payment amount must be greater than 0']);
    exit;
}

try {
    $vendorStmt = $conn->prepare("SELECT vendorID, vendorName FROM vendors WHERE vendorID = ? LIMIT 1");
    $vendorStmt->bind_param('i', $vendorID);
    $vendorStmt->execute();
    $vendor = $vendorStmt->get_result()->fetch_assoc();
    $vendorStmt->close();

    if (!$vendor) {
        echo json_encode(['success' => false, 'message' => 'Vendor not found']);
        exit;
    }

    $conn->begin_transaction();

    $batchStmt = $conn->prepare("
        SELECT batch_id, gas_id, paid_price, pending_price
        FROM gas_batch_details
        WHERE vendorID = ? AND pending_price > 0
        ORDER BY createdAt ASC, batch_id ASC
        FOR UPDATE
    ");
    $batchStmt->bind_param('i', $vendorID);
    $batchStmt->execute();
    $batchResult = $batchStmt->get_result();

    $batchRows = [];
    $totalPending = 0;
    $affectedGasIds = [];
    while ($row = $batchResult->fetch_assoc()) {
        $batchRows[] = $row;
        $totalPending += floatval($row['pending_price']);
        $affectedGasIds[intval($row['gas_id'])] = true;
    }
    $batchStmt->close();

    if (empty($batchRows)) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'No pending amount found for this vendor']);
        exit;
    }

    if ($paymentAmount > $totalPending + 0.0001) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Payment amount cannot exceed total pending (RS ' . number_format($totalPending, 2) . ')'
        ]);
        exit;
    }

    $remainingPayment = $paymentAmount;
    $updateStmt = $conn->prepare("
        UPDATE gas_batch_details
        SET paid_price = ?, pending_price = ?, updatedAt = NOW()
        WHERE batch_id = ?
    ");

    foreach ($batchRows as $row) {
        if ($remainingPayment <= 0) {
            break;
        }

        $batchId = intval($row['batch_id']);
        $currentPaid = floatval($row['paid_price']);
        $currentPending = floatval($row['pending_price']);
        $payNow = min($remainingPayment, $currentPending);

        $newPaid = $currentPaid + $payNow;
        $newPending = $currentPending - $payNow;

        $updateStmt->bind_param('ddi', $newPaid, $newPending, $batchId);
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update batch payment');
        }

        $remainingPayment -= $payNow;
    }

    $updateStmt->close();

    foreach (array_keys($affectedGasIds) as $gasId) {
        if ($gasId > 0) {
            syncGasMasterFromBatches($conn, $gasId);
        }
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment of RS ' . number_format($paymentAmount, 2) . ' recorded for ' . $vendor['vendorName'],
        'paid_amount' => round($paymentAmount, 2),
        'remaining_pending' => round(max(0, $totalPending - $paymentAmount), 2)
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();

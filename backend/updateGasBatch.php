<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
date_default_timezone_set('Asia/Karachi');

require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/gasHelpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gasId = intval($_POST['gas_id'] ?? 0);
    $batchName = trim($_POST['batchName'] ?? '');
    $vendorID = intval($_POST['vendorID'] ?? 0);
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unitPrice = floatval($_POST['unitPrice'] ?? 0);
    $paidPrice = floatval($_POST['paidPrice'] ?? 0);

    if ($gasId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid gas ID']);
        exit;
    }

    if (empty($batchName)) {
        echo json_encode(['success' => false, 'message' => 'Batch name is required']);
        exit;
    }

    if ($vendorID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid vendor']);
        exit;
    }

    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
        exit;
    }

    if ($unitPrice <= 0) {
        echo json_encode(['success' => false, 'message' => 'Unit price must be greater than 0']);
        exit;
    }

    if ($paidPrice < 0) {
        echo json_encode(['success' => false, 'message' => 'Paid price cannot be negative']);
        exit;
    }

    $totalPrice = $quantity * $unitPrice;
    if ($paidPrice > $totalPrice) {
        echo json_encode(['success' => false, 'message' => 'Paid price cannot be greater than total price']);
        exit;
    }

    $pendingPrice = $totalPrice - $paidPrice;

    try {
        $conn->begin_transaction();

        $vendorCheckStmt = $conn->prepare("SELECT vendorID FROM vendors WHERE vendorID = ? LIMIT 1");
        $vendorCheckStmt->bind_param('i', $vendorID);
        $vendorCheckStmt->execute();
        if ($vendorCheckStmt->get_result()->num_rows === 0) {
            throw new Exception('Selected vendor does not exist');
        }
        $vendorCheckStmt->close();

        $getMasterStmt = $conn->prepare("SELECT gas_id FROM gas_master WHERE gas_id = ?");
        $getMasterStmt->bind_param('i', $gasId);
        $getMasterStmt->execute();
        if ($getMasterStmt->get_result()->num_rows === 0) {
            throw new Exception('Gas not found');
        }
        $getMasterStmt->close();

        $getRegionStmt = $conn->prepare("SELECT regionID FROM gas_batch_details WHERE gas_id = ? LIMIT 1");
        $getRegionStmt->bind_param('i', $gasId);
        $getRegionStmt->execute();
        $regionResult = $getRegionStmt->get_result();
        if ($regionResult->num_rows === 0) {
            throw new Exception('No batch found for this gas');
        }
        $regionID = intval($regionResult->fetch_assoc()['regionID']);
        $getRegionStmt->close();

        $batchStmt = $conn->prepare("
            INSERT INTO gas_batch_details (gas_id, batchName, regionID, vendorID, quantity, available, unit_price, total_price, paid_price, pending_price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$batchStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $available = $quantity;
        $batchStmt->bind_param('isiiiddddd', $gasId, $batchName, $regionID, $vendorID, $quantity, $available, $unitPrice, $totalPrice, $paidPrice, $pendingPrice);

        if (!$batchStmt->execute()) {
            throw new Exception('Failed to insert batch: ' . $batchStmt->error);
        }

        $newBatchId = $conn->insert_id;
        $batchStmt->close();

        syncGasMasterFromBatches($conn, $gasId);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Gas batch updated successfully',
            'new_batch_id' => $newBatchId
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Gas Batch Update Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

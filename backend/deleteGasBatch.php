<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/gasHelpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batchId = intval($_POST['batch_id'] ?? 0);

    // Validation
    if ($batchId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid batch ID']);
        exit;
    }

    try {
        $conn->begin_transaction();

        // Step 1: Get batch details
        $getBatchStmt = $conn->prepare("SELECT gas_id, quantity, unit_price, total_price FROM gas_batch_details WHERE batch_id = ?");
        if (!$getBatchStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $getBatchStmt->bind_param('i', $batchId);
        $getBatchStmt->execute();
        $batchResult = $getBatchStmt->get_result();

        if ($batchResult->num_rows === 0) {
            throw new Exception('Batch not found');
        }

        $batchData = $batchResult->fetch_assoc();
        $gasId = $batchData['gas_id'];
        $batchQty = $batchData['quantity'];
        $batchUnitPrice = $batchData['unit_price'];
        $batchTotalPrice = $batchData['total_price'];
        $getBatchStmt->close();

        // Step 2: Check if there are other batches for this gas
        $countBatchStmt = $conn->prepare("SELECT COUNT(*) as count FROM gas_batch_details WHERE gas_id = ? AND batch_id != ?");
        if (!$countBatchStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $countBatchStmt->bind_param('ii', $gasId, $batchId);
        $countBatchStmt->execute();
        $countResult = $countBatchStmt->get_result()->fetch_assoc();
        $otherBatchCount = $countResult['count'];
        $countBatchStmt->close();

        // Step 3: Delete the batch
        $deleteBatchStmt = $conn->prepare("DELETE FROM gas_batch_details WHERE batch_id = ?");
        if (!$deleteBatchStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $deleteBatchStmt->bind_param('i', $batchId);

        if (!$deleteBatchStmt->execute()) {
            throw new Exception('Failed to delete batch: ' . $deleteBatchStmt->error);
        }

        $deleteBatchStmt->close();

        // Step 4: Update gas_master based on remaining batches
        if ($otherBatchCount === 0) {
            // No other batches - delete the gas_master entry too
            $deleteGasStmt = $conn->prepare("DELETE FROM gas_master WHERE gas_id = ?");
            if (!$deleteGasStmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }

            $deleteGasStmt->bind_param('i', $gasId);

            if (!$deleteGasStmt->execute()) {
                throw new Exception('Failed to delete gas: ' . $deleteGasStmt->error);
            }

            $deleteGasStmt->close();
            $message = 'Gas batch deleted successfully (all batches removed)';
        } else {
            syncGasMasterFromBatches($conn, intval($gasId));
            $message = 'Gas batch deleted successfully';
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => $message
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log('Gas Batch Delete Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

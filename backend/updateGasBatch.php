<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
date_default_timezone_set('Asia/Karachi');

require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gasId = intval($_POST['gas_id'] ?? 0);
    $batchName = trim($_POST['batchName'] ?? '');
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unitPrice = floatval($_POST['unitPrice'] ?? 0);

    // Log incoming data
    error_log('Update Gas - Input Data: gasId=' . $gasId . ', batchName=' . $batchName . ', quantity=' . $quantity . ', unitPrice=' . $unitPrice);

    // Validation
    if ($gasId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid gas ID']);
        exit;
    }

    if (empty($batchName)) {
        echo json_encode(['success' => false, 'message' => 'Batch name is required']);
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

    try {
        $conn->begin_transaction();

        // Step 1: Get current gas_master data (total_quantity, sum of batch totals)
        $getMasterStmt = $conn->prepare("SELECT quantity, unit_price FROM gas_master WHERE gas_id = ?");
        if (!$getMasterStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $getMasterStmt->bind_param('i', $gasId);
        $getMasterStmt->execute();
        $masterResult = $getMasterStmt->get_result();

        if ($masterResult->num_rows === 0) {
            throw new Exception('Gas not found');
        }

        $masterData = $masterResult->fetch_assoc();
        $currentTotalQty = $masterData['quantity'];
        $currentAvgPrice = $masterData['unit_price'];
        $getMasterStmt->close();

        // Step 1b: Get regionID from existing batch for this gas
        $getRegionStmt = $conn->prepare("SELECT regionID FROM gas_batch_details WHERE gas_id = ? LIMIT 1");
        if (!$getRegionStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $getRegionStmt->bind_param('i', $gasId);
        $getRegionStmt->execute();
        $regionResult = $getRegionStmt->get_result();

        if ($regionResult->num_rows === 0) {
            throw new Exception('No batch found for this gas');
        }

        $regionData = $regionResult->fetch_assoc();
        $regionID = $regionData['regionID'];
        $getRegionStmt->close();

        // Step 2: Calculate new totals
        $newTotalQty = $currentTotalQty + $quantity;
        $totalPrice = $quantity * $unitPrice;

        // Calculate new average unit price (weighted average)
        $currentTotalAmount = $currentTotalQty * $currentAvgPrice;
        $newTotalAmount = $currentTotalAmount + $totalPrice;
        $newAvgPrice = $newTotalAmount / $newTotalQty;

        // Step 3: Insert new batch in gas_batch_details
        $batchStmt = $conn->prepare("
            INSERT INTO gas_batch_details (gas_id, batchName, regionID, quantity, available, unit_price, total_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$batchStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $available = $quantity;
        $batchStmt->bind_param('isidddd', $gasId, $batchName, $regionID, $quantity, $available, $unitPrice, $totalPrice);

        if (!$batchStmt->execute()) {
            throw new Exception('Failed to insert batch: ' . $batchStmt->error);
        }

        $newBatchId = $conn->insert_id;
        $batchStmt->close();

        // Step 4: Update gas_master with new totals
        $updateGasStmt = $conn->prepare("UPDATE gas_master SET quantity = ?, unit_price = ? WHERE gas_id = ?");
        if (!$updateGasStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $updateGasStmt->bind_param('ddi', $newTotalQty, $newAvgPrice, $gasId);

        if (!$updateGasStmt->execute()) {
            throw new Exception('Failed to update gas: ' . $updateGasStmt->error);
        }

        $updateGasStmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Gas batch updated successfully',
            'new_batch_id' => $newBatchId,
            'new_total_qty' => $newTotalQty,
            'new_avg_price' => round($newAvgPrice, 2),
            'new_total_amount' => round($newTotalAmount, 2)
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log('Gas Batch Update Error: ' . $e->getMessage());
        
        // Send detailed error response
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, 'Prepare failed') !== false || strpos($errorMsg, 'Failed to') !== false) {
            $errorMsg .= ' [' . $conn->error . ']';
        }
        
        echo json_encode([
            'success' => false, 
            'message' => 'Database Error: ' . $errorMsg,
            'debug' => $conn->error
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

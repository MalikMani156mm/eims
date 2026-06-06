<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Karachi');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gasName = trim($_POST['gasName'] ?? '');
    $batchName = trim($_POST['batchName'] ?? '');
    $regionID = intval($_POST['regionID'] ?? 0);
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unitPrice = floatval($_POST['unitPrice'] ?? 0);
    $totalPrice = floatval($_POST['totalPrice'] ?? 0);

    // Validation
    if (empty($gasName)) {
        echo json_encode(['success' => false, 'message' => 'Gas name is required']);
        exit;
    }

    if (empty($batchName)) {
        echo json_encode(['success' => false, 'message' => 'Batch name is required']);
        exit;
    }

    if ($regionID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid region selected']);
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

    // Check if gas name already exists
    $checkGasStmt = $conn->prepare("SELECT gas_id FROM gas_master WHERE gas_name = ?");
    if (!$checkGasStmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $checkGasStmt->bind_param('s', $gasName);
    $checkGasStmt->execute();
    $checkGasStmt->store_result();

    if ($checkGasStmt->num_rows > 0) {
        $checkGasStmt->close();
        echo json_encode(['success' => false, 'message' => 'Gas name already exists']);
        exit;
    }
    $checkGasStmt->close();

    try {
        $conn->begin_transaction();

        // Step 1: Insert batch entry in gas_batch_details first (with gas_id = NULL initially, to get batch_id)
        $batchStmt = $conn->prepare("
            INSERT INTO gas_batch_details (gas_id, batchName, regionID, quantity, available, unit_price, total_price) 
            VALUES (NULL, ?, ?, ?, ?, ?, ?)
        ");
        if (!$batchStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $available = $quantity; // Initially all quantity is available
        $batchTotalPrice = $quantity * $unitPrice;

        $batchStmt->bind_param('siiidd', $batchName, $regionID, $quantity, $available, $unitPrice, $batchTotalPrice);

        if (!$batchStmt->execute()) {
            throw new Exception('Failed to insert batch: ' . $batchStmt->error);
        }

        $batchId = $conn->insert_id;
        $batchStmt->close();

        // Step 2: Insert gas entry in gas_master with the batch_id we just got
        $gasStmt = $conn->prepare("
            INSERT INTO gas_master (gas_name, batch_id, quantity, unit_price) 
            VALUES (?, ?, ?, ?)
        ");
        if (!$gasStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $gasStmt->bind_param('sidd', $gasName, $batchId, $quantity, $unitPrice);

        if (!$gasStmt->execute()) {
            throw new Exception('Failed to insert gas: ' . $gasStmt->error);
        }

        $gasId = $conn->insert_id;
        $gasStmt->close();

        // Step 3: Update gas_batch_details with the gas_id we just got
        $updateBatchStmt = $conn->prepare("UPDATE gas_batch_details SET gas_id = ? WHERE batch_id = ?");
        if (!$updateBatchStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $updateBatchStmt->bind_param('ii', $gasId, $batchId);

        if (!$updateBatchStmt->execute()) {
            throw new Exception('Failed to update batch with gas_id: ' . $updateBatchStmt->error);
        }

        $updateBatchStmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Gas added successfully',
            'gas_id' => $gasId,
            'batch_id' => $batchId
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log('Gas Save Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

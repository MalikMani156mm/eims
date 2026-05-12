<?php
require '../adminAuth.php';
require '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$saleID = isset($_POST['saleID']) ? intval($_POST['saleID']) : 0;
$paymentAmount = isset($_POST['paymentAmount']) ? floatval($_POST['paymentAmount']) : 0;
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

if ($saleID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid sale ID']);
    exit;
}

if ($paymentAmount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Payment amount must be greater than 0']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Get current sale details
    $query = "SELECT pendingAmount, grandTotal FROM ledger_sales WHERE saleID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $saleID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Sale not found');
    }
    
    $sale = $result->fetch_assoc();
    $stmt->close();
    
    if ($paymentAmount > $sale['pendingAmount']) {
        throw new Exception('Payment amount cannot exceed pending amount (RS ' . number_format($sale['pendingAmount'], 2) . ')');
    }
    
    // Insert payment record
    $paymentDate = date('Y-m-d H:i:s');
    $insertQuery = "
        INSERT INTO ledger_sale_payments 
        (saleID, paymentDate, paymentAmount, paymentMethod, notes, createdAt) 
        VALUES (?, ?, ?, 'Cash', ?, ?)
    ";
    
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("isdss", $saleID, $paymentDate, $paymentAmount, $remarks, $paymentDate);
    $stmt->execute();
    $stmt->close();
    
    // Calculate new pending amount and paid amount
    $newPendingAmount = $sale['pendingAmount'] - $paymentAmount;
    $newAmountPaid = $sale['grandTotal'] - $newPendingAmount;
    
    // Determine new payment status
    if ($newPendingAmount <= 0) {
        $newStatus = 'paid';
        $newPendingAmount = 0;
    } elseif ($newAmountPaid > 0) {
        $newStatus = 'partial';
    } else {
        $newStatus = 'pending';
    }
    
    // Update sale record
    $updateQuery = "
        UPDATE ledger_sales 
        SET amountPaid = ?, 
            pendingAmount = ?, 
            paymentStatus = ?,
            updatedAt = ?
        WHERE saleID = ?
    ";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ddssi", $newAmountPaid, $newPendingAmount, $newStatus, $paymentDate, $saleID);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment recorded successfully',
        'data' => [
            'newAmountPaid' => $newAmountPaid,
            'newPendingAmount' => $newPendingAmount,
            'paymentStatus' => $newStatus
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

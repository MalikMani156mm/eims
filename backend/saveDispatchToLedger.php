<?php
require '../adminAuth.php';
require '../db.php';

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data format']);
    exit;
}

// Validate required fields
if (empty($data['ledgerName'])) {
    echo json_encode(['success' => false, 'message' => 'Ledger name is required']);
    exit;
}

if (empty($data['items']) || !is_array($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'No items to dispatch']);
    exit;
}

if ($data['paymentDays'] > 45) {
    echo json_encode(['success' => false, 'message' => 'Payment days cannot exceed 45 days']);
    exit;
}

$conn->begin_transaction();

try {
    // Get user ID from adminAuth.php
    $createdByUser = isset($ID) ? $ID : null;
    
    // Calculate due date
    $dueDate = null;
    if (isset($data['paymentDays']) && $data['paymentDays'] > 0) {
        // Validate and format sale date
        $saleDate = $data['saleDate'];
        
        // Create DateTime object for better date handling
        try {
            $saleDateObj = new DateTime($saleDate);
            $saleDateObj->modify('+' . intval($data['paymentDays']) . ' days');
            $dueDate = $saleDateObj->format('Y-m-d');
        } catch (Exception $e) {
            // If date parsing fails, set to null
            $dueDate = null;
        }
    }
    
    // Determine payment status
    $paymentStatus = 'pending';
    if ($data['pendingAmount'] <= 0) {
        $paymentStatus = 'paid';
    } elseif ($data['amountPaid'] > 0) {
        $paymentStatus = 'partial';
    }

    // Server-side validation: amount paid cannot be greater than grand total
    $grandTotal = isset($data['grandTotal']) ? floatval($data['grandTotal']) : 0.0;
    $amountPaidLocal = isset($data['amountPaid']) ? floatval($data['amountPaid']) : 0.0;
    if ($amountPaidLocal > $grandTotal) {
        echo json_encode(['success' => false, 'message' => 'Amount paid cannot be greater than total amount']);
        exit;
    }
    
    // Insert into ledger_sales table
    $stmt = $conn->prepare("
        INSERT INTO ledger_sales (
            ledgerName, ledgerCNIC, ledgerContact, ledgerAddress,
            saleDate, totalAmount, discountAmount, grandTotal, 
            amountPaid, pendingAmount, paymentDays, dueDate, paymentStatus, createdBy
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "sssssdddddissi",
        $data['ledgerName'],
        $data['ledgerCNIC'],
        $data['ledgerContact'],
        $data['ledgerAddress'],
        $data['saleDate'],
        $data['totalAmount'],
        $data['discountAmount'],
        $data['grandTotal'],
        $data['amountPaid'],
        $data['pendingAmount'],
        $data['paymentDays'],
        $dueDate,
        $paymentStatus,
        $createdByUser
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create sale record: ' . $stmt->error);
    }
    
    $saleID = $conn->insert_id;
    $stmt->close();
    
    // Insert sale items and update serial status
    $stmtItem = $conn->prepare("
        INSERT INTO ledger_sale_items (
            saleID, serialID, productID, batchNumber, sellingPrice, 
            discountPercent, discountAmount, finalPrice
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmtUpdateSerial = $conn->prepare("
        UPDATE product_serials 
        SET status = 'issued' 
        WHERE serialID = ?
    ");
    
    $stmtUpdateAvailable = $conn->prepare("
        UPDATE products 
        SET available = available - 1 
        WHERE productID = ?
    ");
    
    foreach ($data['items'] as $item) {
        // Insert item
        $stmtItem->bind_param(
            "iiisdddd",
            $saleID,
            $item['serialID'],
            $item['productID'],
            $item['batchNumber'],
            $item['sellingPrice'],
            $item['discountPercent'],
            $item['discountAmount'],
            $item['finalPrice']
        );
        
        if (!$stmtItem->execute()) {
            throw new Exception('Failed to insert sale item: ' . $stmtItem->error);
        }
        
        // Update serial status to issued
        $stmtUpdateSerial->bind_param("i", $item['serialID']);
        if (!$stmtUpdateSerial->execute()) {
            throw new Exception('Failed to update serial status: ' . $stmtUpdateSerial->error);
        }
        
        // Decrement available quantity
        $stmtUpdateAvailable->bind_param("i", $item['productID']);
        if (!$stmtUpdateAvailable->execute()) {
            throw new Exception('Failed to update product availability: ' . $stmtUpdateAvailable->error);
        }
    }
    
    $stmtItem->close();
    $stmtUpdateSerial->close();
    $stmtUpdateAvailable->close();
    
    // If amount was paid, record it in payments table
    if ($data['amountPaid'] > 0) {
        $stmtPayment = $conn->prepare("
            INSERT INTO ledger_sale_payments (saleID, paymentAmount, paymentMethod, notes)
            VALUES (?, ?, 'cash', 'Initial payment')
        ");
        
        $stmtPayment->bind_param("id", $saleID, $data['amountPaid']);
        $stmtPayment->execute();
        $stmtPayment->close();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Ledger dispatch created successfully',
        'saleID' => $saleID
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error creating dispatch: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

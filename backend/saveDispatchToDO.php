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
if (empty($data['DO_ID'])) {
    echo json_encode(['success' => false, 'message' => 'Distributing Officer is required']);
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
    
    // Insert into do_sales table (include regionID from authenticated user)
    $stmt = $conn->prepare(
        "INSERT INTO do_sales (
            DO_ID, regionID, saleDate, totalAmount, discountAmount, grandTotal, 
            amountPaid, pendingAmount, paymentDays, dueDate, paymentStatus, createdBy
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    // Prepare variables for bind_param (bind_param requires variables, not expressions)
    $doID = (int)$data['DO_ID'];
    $userRegion = isset($regionID) ? (int)$regionID : null;
    $saleDateStr = $data['saleDate'];
    $totalAmount = isset($data['totalAmount']) ? floatval($data['totalAmount']) : 0.0;
    $discountAmount = isset($data['discountAmount']) ? floatval($data['discountAmount']) : 0.0;
    $grandTotal = isset($data['grandTotal']) ? floatval($data['grandTotal']) : 0.0;
    $amountPaidLocal = isset($data['amountPaid']) ? floatval($data['amountPaid']) : 0.0;
    $pendingAmountLocal = isset($data['pendingAmount']) ? floatval($data['pendingAmount']) : 0.0;
    $paymentDaysLocal = isset($data['paymentDays']) ? intval($data['paymentDays']) : 0;
    $dueDateLocal = $dueDate; // may be null
    $paymentStatusLocal = $paymentStatus;
    $createdByUserLocal = isset($createdByUser) ? intval($createdByUser) : null;

    // Server-side validation: amount paid cannot be greater than grand total
    if ($amountPaidLocal > $grandTotal) {
        echo json_encode(['success' => false, 'message' => 'Amount paid cannot be greater than total amount']);
        exit;
    }

    // bind_param format: DO_ID(i), regionID(i), saleDate(s), totalAmount(d), discountAmount(d), grandTotal(d), amountPaid(d), pendingAmount(d), paymentDays(i), dueDate(s), paymentStatus(s), createdBy(i)
    $stmt->bind_param(
        "iisdddddissi",
        $doID,
        $userRegion,
        $saleDateStr,
        $totalAmount,
        $discountAmount,
        $grandTotal,
        $amountPaidLocal,
        $pendingAmountLocal,
        $paymentDaysLocal,
        $dueDateLocal,
        $paymentStatusLocal,
        $createdByUserLocal
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create sale record: ' . $stmt->error);
    }
    
    $saleID = $conn->insert_id;
    $stmt->close();
    
    // Insert sale items and update serial status
    $stmtItem = $conn->prepare("
        INSERT INTO do_sale_items (
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
        // Prepare local variables for binding
        $serialID = isset($item['serialID']) ? intval($item['serialID']) : 0;
        $productID = isset($item['productID']) ? intval($item['productID']) : 0;
        $batchNumberLocal = isset($item['batchNumber']) ? $item['batchNumber'] : '';
        $sellingPrice = isset($item['sellingPrice']) ? floatval($item['sellingPrice']) : 0.0;
        $discountPercent = isset($item['discountPercent']) ? floatval($item['discountPercent']) : 0.0;
        $discountAmountItem = isset($item['discountAmount']) ? floatval($item['discountAmount']) : 0.0;
        $finalPrice = isset($item['finalPrice']) ? floatval($item['finalPrice']) : 0.0;

        // Insert item
        $stmtItem->bind_param(
            "iiisdddd",
            $saleID,
            $serialID,
            $productID,
            $batchNumberLocal,
            $sellingPrice,
            $discountPercent,
            $discountAmountItem,
            $finalPrice
        );

        if (!$stmtItem->execute()) {
            throw new Exception('Failed to insert sale item: ' . $stmtItem->error);
        }

        // Update serial status to sold
        $stmtUpdateSerial->bind_param("i", $serialID);
        if (!$stmtUpdateSerial->execute()) {
            throw new Exception('Failed to update serial status: ' . $stmtUpdateSerial->error);
        }

        // Decrement available quantity
        $stmtUpdateAvailable->bind_param("i", $productID);
        if (!$stmtUpdateAvailable->execute()) {
            throw new Exception('Failed to update product availability: ' . $stmtUpdateAvailable->error);
        }
    }
    
    $stmtItem->close();
    $stmtUpdateSerial->close();
    $stmtUpdateAvailable->close();
    
    // If amount was paid, record it in payments table
    if ($amountPaidLocal > 0) {
        $stmtPayment = $conn->prepare(
            "INSERT INTO do_sale_payments (saleID, paymentAmount, paymentMethod, notes)
            VALUES (?, ?, 'cash', 'Initial payment')"
        );

        $paidAmountParam = $amountPaidLocal;
        $stmtPayment->bind_param("id", $saleID, $paidAmountParam);
        $stmtPayment->execute();
        $stmtPayment->close();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Dispatch created successfully',
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

<?php
require '../adminAuth.php';
require '../db.php';

header('Content-Type: application/json');

if (!isset($_GET['saleID'])) {
    echo json_encode(['success' => false, 'message' => 'Sale ID is required']);
    exit;
}

$saleID = intval($_GET['saleID']);

try {
    // Fetch sale details with ledger info
    $saleQuery = "
        SELECT ls.*
        FROM ledger_sales ls
        WHERE ls.saleID = ?
    ";
    
    $stmt = $conn->prepare($saleQuery);
    $stmt->bind_param("i", $saleID);
    $stmt->execute();
    $saleResult = $stmt->get_result();
    
    if ($saleResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Sale not found']);
        exit;
    }
    
    $sale = $saleResult->fetch_assoc();
    $stmt->close();
    
    // Fetch sale items
    $itemsQuery = "
        SELECT 
            lsi.*,
            ps.serialNumber,
            p.productName,
            m.modelName,
            col.colorName,
            s.sizeName
        FROM ledger_sale_items lsi
        INNER JOIN product_serials ps ON lsi.serialID = ps.serialID
        INNER JOIN products p ON lsi.productID = p.productID
        LEFT JOIN models m ON p.modelID = m.modelID
        LEFT JOIN colors col ON p.colorID = col.colorID
        LEFT JOIN sizes s ON p.sizeID = s.sizeID
        WHERE lsi.saleID = ?
        ORDER BY lsi.itemID ASC
    ";
    
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bind_param("i", $saleID);
    $stmt->execute();
    $itemsResult = $stmt->get_result();
    
    $items = [];
    while ($row = $itemsResult->fetch_assoc()) {
        // Build product name with details
        $productFullName = $row['productName'];
        if (!empty($row['modelName'])) $productFullName .= ' - ' . $row['modelName'];
        if (!empty($row['colorName'])) $productFullName .= ' (' . $row['colorName'] . ')';
        if (!empty($row['sizeName'])) $productFullName .= ' - ' . $row['sizeName'];
        
        $row['productName'] = $productFullName;
        $items[] = $row;
    }
    
    $stmt->close();
    
    // Fetch payment history
    $paymentsQuery = "
        SELECT 
            p.*
        FROM ledger_sale_payments p
        WHERE p.saleID = ?
        ORDER BY p.paymentDate DESC
    ";
    
    $stmt = $conn->prepare($paymentsQuery);
    $stmt->bind_param("i", $saleID);
    $stmt->execute();
    $paymentsResult = $stmt->get_result();
    
    $payments = [];
    while ($row = $paymentsResult->fetch_assoc()) {
        $payments[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'sale' => $sale,
            'items' => $items,
            'payments' => $payments
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

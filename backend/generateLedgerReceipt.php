<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if (!isset($_GET['saleID'])) {
    die('Sale ID is required');
}

$saleID = intval($_GET['saleID']);

// Fetch sale details
$saleQuery = "
    SELECT 
        ls.*,
        u.full_name as createdByName
    FROM ledger_sales ls
    LEFT JOIN users u ON ls.createdBy = u.user_id
    WHERE ls.saleID = ?
";

$stmt = $conn->prepare($saleQuery);
$stmt->bind_param("i", $saleID);
$stmt->execute();
$saleResult = $stmt->get_result();

if ($saleResult->num_rows === 0) {
    die('Sale not found');
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
        s.sizeName,
        c.categoryName
    FROM ledger_sale_items lsi
    INNER JOIN product_serials ps ON lsi.serialID = ps.serialID
    INNER JOIN products p ON lsi.productID = p.productID
    LEFT JOIN models m ON p.modelID = m.modelID
    LEFT JOIN colors col ON p.colorID = col.colorID
    LEFT JOIN sizes s ON p.sizeID = s.sizeID
    LEFT JOIN categories c ON p.categoryID = c.categoriesID
    WHERE lsi.saleID = ?
    ORDER BY lsi.itemID ASC
";

$stmt = $conn->prepare($itemsQuery);
$stmt->bind_param("i", $saleID);
$stmt->execute();
$itemsResult = $stmt->get_result();

$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ledger Receipt #<?php echo str_pad($saleID, 6, '0', STR_PAD_LEFT); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 3px solid #11998e;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .receipt-header h1 {
            color: #11998e;
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .receipt-header .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .receipt-number {
            font-size: 18px;
            color: #666;
            margin-top: 10px;
        }
        
        .receipt-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-section {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-section h3 {
            color: #11998e;
            margin-bottom: 10px;
            font-size: 16px;
            border-bottom: 2px solid #11998e;
            padding-bottom: 5px;
        }
        
        .info-row {
            padding: 5px 0;
            display: flex;
        }
        
        .info-label {
            font-weight: bold;
            width: 140px;
            color: #555;
        }
        
        .info-value {
            color: #333;
            flex: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table thead {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .items-table tbody tr:hover {
            background: #f0fff4;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .serial-code {
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        
        .summary-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 16px;
        }
        
        .summary-row.total {
            font-size: 20px;
            font-weight: bold;
            color: #11998e;
            border-top: 2px solid #11998e;
            margin-top: 10px;
            padding-top: 15px;
        }
        
        .payment-section {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #ffc107;
        }
        
        .payment-section h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-paid {
            background: #4caf50;
            color: white;
        }
        
        .status-partial {
            background: #ff9800;
            color: white;
        }
        
        .status-pending {
            background: #f44336;
            color: white;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .print-button {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                padding: 20px;
            }
            
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <button class="print-button" onclick="window.print()">🖨️ Print Receipt</button>
    </div>
    
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="company-name">Electronic Inventory Management System</div>
            <h1>LEDGER DISTRIBUTION RECEIPT</h1>
            <div class="receipt-number">Receipt #<?php echo str_pad($saleID, 6, '0', STR_PAD_LEFT); ?></div>
        </div>
        
        <div class="receipt-info-grid">
            <div class="info-section">
                <h3>📋 Ledger Details</h3>
                <div class="info-row">
                    <span class="info-label">Ledger Name:</span>
                    <span class="info-value"><strong><?php echo htmlspecialchars($sale['ledgerName']); ?></strong></span>
                </div>
                <?php if (!empty($sale['ledgerCNIC'])): ?>
                <div class="info-row">
                    <span class="info-label">CNIC:</span>
                    <span class="info-value"><?php echo htmlspecialchars($sale['ledgerCNIC']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($sale['ledgerContact'])): ?>
                <div class="info-row">
                    <span class="info-label">Contact:</span>
                    <span class="info-value"><?php echo htmlspecialchars($sale['ledgerContact']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($sale['ledgerAddress'])): ?>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?php echo htmlspecialchars($sale['ledgerAddress']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="info-section">
                <h3>📅 Sale Information</h3>
                <div class="info-row">
                    <span class="info-label">Sale Date:</span>
                    <span class="info-value"><?php echo date('d M Y', strtotime($sale['saleDate'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Created By:</span>
                    <span class="info-value"><?php echo !empty($sale['createdByName']) ? htmlspecialchars($sale['createdByName']) : 'System'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value">
                        <span class="status-badge status-<?php echo $sale['paymentStatus']; ?>">
                            <?php echo strtoupper($sale['paymentStatus']); ?>
                        </span>
                    </span>
                </div>
                <?php if (!empty($sale['dueDate']) && $sale['dueDate'] != '0000-00-00'): ?>
                <div class="info-row">
                    <span class="info-label">Due Date:</span>
                    <span class="info-value"><?php echo date('d M Y', strtotime($sale['dueDate'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Serial Number</th>
                    <th>Product Details</th>
                    <th>Batch</th>
                    <th class="text-right">Price</th>
                    <th class="text-center">Disc %</th>
                    <th class="text-right">Disc Amt</th>
                    <th class="text-right">Final Price</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                foreach ($items as $item): 
                    $productFullName = $item['productName'];
                    if (!empty($item['modelName'])) $productFullName .= ' - ' . $item['modelName'];
                    if (!empty($item['colorName'])) $productFullName .= ' (' . $item['colorName'] . ')';
                    if (!empty($item['sizeName'])) $productFullName .= ' - ' . $item['sizeName'];
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><span class="serial-code"><?php echo htmlspecialchars($item['serialNumber']); ?></span></td>
                    <td><strong><?php echo htmlspecialchars($productFullName); ?></strong></td>
                    <td><?php echo htmlspecialchars($item['batchNumber']); ?></td>
                    <td class="text-right">RS <?php echo number_format($item['sellingPrice'], 2); ?></td>
                    <td class="text-center"><?php echo number_format($item['discountPercent'], 2); ?>%</td>
                    <td class="text-right">RS <?php echo number_format($item['discountAmount'], 2); ?></td>
                    <td class="text-right"><strong>RS <?php echo number_format($item['finalPrice'], 2); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="summary-section">
            <div class="summary-row">
                <span>Total Items:</span>
                <span><strong><?php echo count($items); ?> items</strong></span>
            </div>
            <div class="summary-row">
                <span>Total Amount:</span>
                <span>RS <?php echo number_format($sale['totalAmount'], 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Total Discount:</span>
                <span>RS <?php echo number_format($sale['discountAmount'], 2); ?></span>
            </div>
            <div class="summary-row total">
                <span>GRAND TOTAL:</span>
                <span>RS <?php echo number_format($sale['grandTotal'], 2); ?></span>
            </div>
        </div>
        
        <div class="payment-section">
            <h3>💰 Payment Details</h3>
            <div class="summary-row">
                <span>Amount Paid:</span>
                <span><strong>RS <?php echo number_format($sale['amountPaid'], 2); ?></strong></span>
            </div>
            <div class="summary-row">
                <span>Pending Amount:</span>
                <span style="color: <?php echo $sale['pendingAmount'] > 0 ? '#f44336' : '#4caf50'; ?>;">
                    <strong>RS <?php echo number_format($sale['pendingAmount'], 2); ?></strong>
                </span>
            </div>
            <?php if ($sale['paymentDays'] > 0): ?>
            <div class="summary-row">
                <span>Payment Days:</span>
                <span><strong><?php echo $sale['paymentDays']; ?> days</strong></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>This is a computer-generated receipt and does not require a signature.</p>
            <p>Generated on: <?php echo date('d M Y h:i A'); ?></p>
        </div>
    </div>
</body>
</html>

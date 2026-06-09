<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

$productID = intval($_GET['productID'] ?? 0);

if ($productID <= 0) {
    die('Product ID is required');
}

$productStmt = $conn->prepare("
    SELECT
        p.*,
        b.brandName,
        c.categoryName,
        col.colorName,
        m.modelName,
        s.sizeName,
        r.regionName
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoriesID
    LEFT JOIN brands b ON p.brandID = b.brandID
    LEFT JOIN colors col ON p.colorID = col.colorID
    LEFT JOIN models m ON p.modelID = m.modelID
    LEFT JOIN sizes s ON p.sizeID = s.sizeID
    LEFT JOIN regions r ON p.regionID = r.regionID
    WHERE p.productID = ?
    LIMIT 1
");
$productStmt->bind_param('i', $productID);
$productStmt->execute();
$productResult = $productStmt->get_result();
$product = $productResult->fetch_assoc();
$productStmt->close();

if (!$product) {
    die('Product not found');
}

$serialStmt = $conn->prepare("
    SELECT serialNumber, batchNumber, status, createdAt
    FROM product_serials
    WHERE productID = ?
    ORDER BY serialNumber ASC
");
$serialStmt->bind_param('i', $productID);
$serialStmt->execute();
$serialResult = $serialStmt->get_result();
$serials = [];
while ($row = $serialResult->fetch_assoc()) {
    $serials[] = $row;
}
$serialStmt->close();

$serialNumbers = array_column($serials, 'serialNumber');
$parts = [];
$gases = [];

if (!empty($serialNumbers)) {
    $placeholders = implode(',', array_fill(0, count($serialNumbers), '?'));
    $types = str_repeat('s', count($serialNumbers));

    $partsStmt = $conn->prepare("
        SELECT
            p.partID,
            p.partName,
            p.serialNumber,
            p.batchName,
            p.quantity,
            p.status,
            p.issuedDate,
            p.issuedToSerialNumber,
            COALESCE(b.brandName, '') AS brandName
        FROM parts p
        LEFT JOIN brands b ON p.brandID = b.brandID
        WHERE p.issuedToSerialNumber IN ($placeholders)
        ORDER BY p.partName, p.serialNumber
    ");
    $partsStmt->bind_param($types, ...$serialNumbers);
    $partsStmt->execute();
    $partsResult = $partsStmt->get_result();
    while ($row = $partsResult->fetch_assoc()) {
        $parts[] = $row;
    }
    $partsStmt->close();
}

$gasStmt = $conn->prepare("
    SELECT
        gl.serial_number,
        gl.quantity_used,
        gl.unit_price,
        gl.total_price,
        gl.created_at,
        gm.gas_name,
        gbd.batchName,
        r.regionName
    FROM gas_logs gl
    JOIN gas_master gm ON gl.gas_id = gm.gas_id
    JOIN gas_batch_details gbd ON gl.batch_id = gbd.batch_id
    LEFT JOIN regions r ON gbd.regionID = r.regionID
    WHERE gl.product_id = ?
    ORDER BY gm.gas_name, gl.created_at ASC
");
$gasStmt->bind_param('i', $productID);
$gasStmt->execute();
$gasResult = $gasStmt->get_result();
while ($row = $gasResult->fetch_assoc()) {
    $gases[] = $row;
}
$gasStmt->close();

if (!empty($serialNumbers)) {
    $placeholders = implode(',', array_fill(0, count($serialNumbers), '?'));
    $types = str_repeat('s', count($serialNumbers));
    $extraGasSql = "
        SELECT
            gl.serial_number,
            gl.quantity_used,
            gl.unit_price,
            gl.total_price,
            gl.created_at,
            gm.gas_name,
            gbd.batchName,
            r.regionName
        FROM gas_logs gl
        JOIN gas_master gm ON gl.gas_id = gm.gas_id
        JOIN gas_batch_details gbd ON gl.batch_id = gbd.batch_id
        LEFT JOIN regions r ON gbd.regionID = r.regionID
        WHERE gl.serial_number IN ($placeholders)
          AND gl.product_id != ?
        ORDER BY gm.gas_name, gl.created_at ASC
    ";
    $extraGasStmt = $conn->prepare($extraGasSql);
    $bindTypes = $types . 'i';
    $extraGasStmt->bind_param($bindTypes, ...array_merge($serialNumbers, [$productID]));
    $extraGasStmt->execute();
    $extraGasResult = $extraGasStmt->get_result();
    while ($row = $extraGasResult->fetch_assoc()) {
        $gases[] = $row;
    }
    $extraGasStmt->close();
}

$conn->close();

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$partsGrouped = [];
foreach ($parts as $part) {
    $name = $part['partName'] ?: '(unknown)';
    if (!isset($partsGrouped[$name])) {
        $partsGrouped[$name] = [];
    }
    $partsGrouped[$name][] = $part;
}

$gasGrouped = [];
foreach ($gases as $gas) {
    $name = $gas['gas_name'] ?: '(unknown)';
    if (!isset($gasGrouped[$name])) {
        $gasGrouped[$name] = [];
    }
    $gasGrouped[$name][] = $gas;
}

$totalCost = floatval($product['quantity']) * floatval($product['cost']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assembling Sheet - <?php echo h($product['productName']); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; color: #222; padding: 24px; background: #fff; }
        .print-actions { text-align: center; margin-bottom: 24px; }
        .print-button {
            background: #667eea;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }
        .header {
            border: 2px solid #667eea;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .header h1 { font-size: 24px; margin-bottom: 6px; color: #667eea; }
        .header p { color: #666; font-size: 14px; }
        .section {
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 18px;
            overflow: hidden;
        }
        .section-title {
            background: #667eea;
            color: #fff;
            padding: 10px 14px;
            font-size: 15px;
            font-weight: 700;
        }
        .section-body { padding: 14px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 20px;
            font-size: 14px;
        }
        .grid strong { color: #555; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; vertical-align: top; }
        th { background: #f5f7ff; }
        .part-block, .gas-block { margin-bottom: 14px; }
        .part-block h4, .gas-block h4 { margin-bottom: 8px; color: #333; }
        .mono { font-family: Consolas, monospace; }
        .badge {
            display: inline-block;
            background: #4caf50;
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
        }
        @media print {
            .print-actions { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="print-button" onclick="window.print()">Print Assembling Sheet</button>
    </div>

    <div class="header">
        <h1>Factory Assembling Sheet</h1>
        <p>Product ID: <?php echo h($product['productID']); ?> | Issued: <?php echo h(date('M d, Y h:i A', strtotime($product['createdAt']))); ?></p>
    </div>

    <div class="section">
        <div class="section-title">Product Details</div>
        <div class="section-body grid">
            <div><strong>Product Name:</strong> <?php echo h($product['productName']); ?></div>
            <div><strong>Category:</strong> <?php echo h($product['categoryName'] ?? 'N/A'); ?></div>
            <div><strong>Brand:</strong> <?php echo h($product['brandName'] ?? 'N/A'); ?></div>
            <div><strong>Model:</strong> <?php echo h($product['modelName'] ?? 'N/A'); ?></div>
            <div><strong>Color:</strong> <?php echo h($product['colorName'] ?? 'N/A'); ?></div>
            <div><strong>Tonnage:</strong> <?php echo h($product['sizeName'] ?? 'N/A'); ?></div>
            <div><strong>Region:</strong> <?php echo h($product['regionName'] ?? 'N/A'); ?></div>
            <div><strong>Batch Number:</strong> <?php echo h($product['batchNumber']); ?></div>
            <div><strong>Quantity:</strong> <?php echo h($product['quantity']); ?></div>
            <div><strong>Cost / Unit:</strong> RS <?php echo number_format(floatval($product['cost']), 2); ?></div>
            <div><strong>Total Cost:</strong> RS <?php echo number_format($totalCost, 2); ?></div>
            <div><strong>Description:</strong> <?php echo h($product['description'] ?: 'N/A'); ?></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Product Serial Numbers</div>
        <div class="section-body">
            <?php if (empty($serials)): ?>
                <p>No serial numbers found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Serial Number</th>
                            <th>Batch</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serials as $index => $serial): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td class="mono"><?php echo h($serial['serialNumber']); ?></td>
                                <td><?php echo h($serial['batchNumber']); ?></td>
                                <td><?php echo h(ucfirst($serial['status'] ?? 'available')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Issued Parts</div>
        <div class="section-body">
            <?php if (empty($partsGrouped)): ?>
                <p>No issued parts found for this product.</p>
            <?php else: ?>
                <?php foreach ($partsGrouped as $partName => $items): ?>
                    <div class="part-block">
                        <h4><?php echo h($partName); ?></h4>
                        <?php
                        $withSerials = array_filter($items, fn($i) => !empty($i['serialNumber']));
                        $withoutSerials = array_filter($items, fn($i) => empty($i['serialNumber']));
                        ?>
                        <?php foreach ($withSerials as $item): ?>
                            <div style="margin-bottom:8px;">
                                <div><strong>Part Serial:</strong> <span class="mono"><?php echo h($item['serialNumber']); ?></span> <span class="badge">Issued</span></div>
                                <div><strong>Brand:</strong> <?php echo h($item['brandName'] ?: 'N/A'); ?></div>
                                <div><strong>Batch:</strong> <?php echo h($item['batchName'] ?: 'N/A'); ?></div>
                                <div><strong>Issued To Product Serial:</strong> <span class="mono"><?php echo h($item['issuedToSerialNumber']); ?></span></div>
                                <div><strong>Issued On:</strong> <?php echo h($item['issuedDate'] ? date('M d, Y h:i A', strtotime($item['issuedDate'])) : 'N/A'); ?></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!empty($withoutSerials)): ?>
                            <?php
                            $qty = 0;
                            foreach ($withoutSerials as $row) {
                                $qty += max(1, intval($row['quantity']));
                            }
                            ?>
                            <div style="margin-bottom:8px;">
                                <div><strong>No Serial Parts:</strong> × <?php echo $qty; ?> <span class="badge">Issued</span></div>
                                <div><strong>Brand:</strong> <?php echo h($withoutSerials[array_key_first($withoutSerials)]['brandName'] ?: 'N/A'); ?></div>
                                <div><strong>Issued To Product Serial:</strong> <span class="mono"><?php echo h($withoutSerials[array_key_first($withoutSerials)]['issuedToSerialNumber']); ?></span></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Issued Gases</div>
        <div class="section-body">
            <?php if (empty($gasGrouped)): ?>
                <p>No issued gases found for this product.</p>
            <?php else: ?>
                <?php foreach ($gasGrouped as $gasName => $items): ?>
                    <div class="gas-block">
                        <h4><?php echo h($gasName); ?></h4>
                        <?php foreach ($items as $gas): ?>
                            <div style="margin-bottom:10px; padding:10px; background:#fafafa; border-left:4px solid #ff9800;">
                                <div><strong>Batch:</strong> <?php echo h($gas['batchName'] ?? 'N/A'); ?> <span class="badge">Issued</span></div>
                                <div><strong>Product Serial:</strong> <span class="mono"><?php echo h($gas['serial_number']); ?></span></div>
                                <div><strong>Quantity:</strong> <?php echo number_format(floatval($gas['quantity_used']), 2); ?> Units</div>
                                <div><strong>Region:</strong> <?php echo h($gas['regionName'] ?? 'N/A'); ?></div>
                                <div><strong>Unit Price:</strong> RS <?php echo number_format(floatval($gas['unit_price']), 2); ?></div>
                                <div><strong>Total Price:</strong> RS <?php echo number_format(floatval($gas['total_price']), 2); ?></div>
                                <div><strong>Issued On:</strong> <?php echo h($gas['created_at'] ? date('M d, Y h:i A', strtotime($gas['created_at'])) : 'N/A'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
header('Content-Type: application/json');

$partSerial = trim($_GET['partSerial'] ?? '');
if ($partSerial === '') {
    echo json_encode(['success' => false, 'message' => 'partSerial required']);
    exit;
}

try {
    // Find the part row by serialNumber, include region name
    $stmt = $conn->prepare("SELECT p.*, r.regionName FROM parts p LEFT JOIN regions r ON p.regionID = r.regionID WHERE p.serialNumber = ? LIMIT 1");
    $stmt->bind_param('s', $partSerial);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Part serial not found']);
        $stmt->close();
        $conn->close();
        exit;
    }

    $part = $res->fetch_assoc();
    $stmt->close();

    if ($part['status'] === 'available') {
        echo json_encode(['success' => true, 'available' => true, 'part' => $part]);
        $conn->close();
        exit;
    }

    // If used, find the product where this part was issued
    $issuedTo = $part['issuedToSerialNumber'] ?? '';
    if (empty($issuedTo)) {
        echo json_encode(['success' => true, 'available' => false, 'used' => true, 'part' => $part, 'product' => null]);
        $conn->close();
        exit;
    }

    // Query product details by product serial
    $pstmt = $conn->prepare(
        "SELECT 
            ps.serialID, ps.serialNumber, ps.productID, ps.batchNumber, ps.status,
            p.productName, p.modelID, p.categoryID, p.colorID, p.sizeID, p.regionID, pb.cost,
            m.modelName, c.categoryName, col.colorName, s.sizeName AS size, r.regionName, ps.createdAt
        FROM product_serials ps
        INNER JOIN products p ON ps.productID = p.productID
        LEFT JOIN product_batch pb ON p.productID = pb.productID AND ps.batchNumber = pb.batchNumber
        LEFT JOIN models m ON p.modelID = m.modelID
        LEFT JOIN categories c ON p.categoryID = c.categoriesID
        LEFT JOIN colors col ON p.colorID = col.colorID
        LEFT JOIN sizes s ON p.sizeID = s.sizeID
        LEFT JOIN regions r ON p.regionID = r.regionID
        WHERE ps.serialNumber = ?
        LIMIT 1"
    );
    $pstmt->bind_param('s', $issuedTo);
    $pstmt->execute();
    $pres = $pstmt->get_result();

    if ($pres->num_rows === 0) {
        echo json_encode(['success' => true, 'available' => false, 'used' => true, 'part' => $part, 'product' => null]);
        $pstmt->close();
        $conn->close();
        exit;
    }

    $product = $pres->fetch_assoc();
    $pstmt->close();

    // Build response data similar to getProductBySerial.php
    $productFullName = $product['productName'];
    if (!empty($product['modelName'])) $productFullName .= ' - ' . $product['modelName'];
    if (!empty($product['colorName'])) $productFullName .= ' (' . $product['colorName'] . ')';
    if (!empty($product['size'])) $productFullName .= ' - ' . $product['size'];

    $responseData = [
        'serialID' => $product['serialID'],
        'serialNumber' => $product['serialNumber'],
        'productID' => $product['productID'],
        'productName' => $productFullName,
        'batchNumber' => $product['batchNumber'],
        'cost' => $product['cost'] ?: 0,
        'status' => $product['status'],
        'modelName' => $product['modelName'],
        'categoryName' => $product['categoryName'],
        'colorName' => $product['colorName'],
        'sizeName' => $product['size'],
        'regionName' => $product['regionName'],
        'createdAt' => $product['createdAt']
    ];

    echo json_encode(['success' => true, 'available' => false, 'used' => true, 'part' => $part, 'product' => $responseData]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();

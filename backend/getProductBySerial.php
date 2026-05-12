<?php
require '../adminAuth.php';
require '../db.php';

header('Content-Type: application/json');

if (!isset($_GET['serialNumber']) || empty($_GET['serialNumber'])) {
    echo json_encode(['success' => false, 'message' => 'Serial number is required']);
    exit;
}

$serialNumber = trim($_GET['serialNumber']);

try {
    // Query to get product details with serial number
    $stmt = $conn->prepare("
        SELECT 
            ps.serialID,
            ps.serialNumber,
            ps.productID,
            ps.batchNumber,
            ps.status,
            p.productName,
            p.modelID,
            p.categoryID,
            p.colorID,
            p.sizeID,
            p.regionID,
            pb.cost,
            m.modelName,
            c.categoryName,
            col.colorName,
            s.sizeName AS size,
            r.regionName
        FROM product_serials ps
        INNER JOIN products p ON ps.productID = p.productID
        LEFT JOIN product_batch pb ON p.productID = pb.productID AND ps.batchNumber = pb.batchNumber
        LEFT JOIN models m ON p.modelID = m.modelID
        LEFT JOIN categories c ON p.categoryID = c.categoriesID
        LEFT JOIN colors col ON p.colorID = col.colorID
        LEFT JOIN sizes s ON p.sizeID = s.sizeID
        LEFT JOIN regions r ON p.regionID = r.regionID
        WHERE ps.serialNumber = ?
        LIMIT 1
    ");
    
    $stmt->bind_param("s", $serialNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Serial number not found'
        ]);
        exit;
    }
    
    $product = $result->fetch_assoc();
    
    // Check if product is available
    if ($product['status'] !== 'available') {
        echo json_encode([
            'success' => false,
            'message' => 'Product is not available (Status: ' . ucfirst($product['status']) . ')'
        ]);
        exit;
    }
    
    // Build product name with details
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
        'size' => $product['size'],
        'regionName' => $product['regionName']
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $responseData
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

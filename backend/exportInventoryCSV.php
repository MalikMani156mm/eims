<?php
require '../adminAuth.php';
require '../db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_export_' . date('Y-m-d_His') . '.csv');
header('Pragma: no-cache');
header('Expires: 0');

$filters = [];
$types = '';
$params = [];

$categoryID = intval($_GET['category'] ?? 0);
$regionID = intval($_GET['region'] ?? 0);
$modelID = intval($_GET['model'] ?? 0);
$colorID = intval($_GET['color'] ?? 0);
$sizeID = intval($_GET['size'] ?? 0);

if ($categoryID > 0) {
    $filters[] = 'p.categoryID = ?';
    $types .= 'i';
    $params[] = $categoryID;
}

if ($regionID > 0) {
    $filters[] = 'p.regionID = ?';
    $types .= 'i';
    $params[] = $regionID;
}

if ($modelID > 0) {
    $filters[] = 'p.modelID = ?';
    $types .= 'i';
    $params[] = $modelID;
}

if ($colorID > 0) {
    $filters[] = 'p.colorID = ?';
    $types .= 'i';
    $params[] = $colorID;
}

if ($sizeID > 0) {
    $filters[] = 'p.sizeID = ?';
    $types .= 'i';
    $params[] = $sizeID;
}

$whereClause = '';
if (!empty($filters)) {
    $whereClause = ' WHERE ' . implode(' AND ', $filters);
}

$query = "
    SELECT 
        p.productName,
        c.categoryName,
        r.regionName,
        m.modelName,
        col.colorName,
        s.sizeName,
        p.batchNumber,
        p.quantity,
        p.available,
        p.cost,
        COALESCE(AVG(pb.cost), p.cost) AS averageCost,
        p.description,
        p.createdAt
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoriesID
    LEFT JOIN colors col ON p.colorID = col.colorID
    LEFT JOIN models m ON p.modelID = m.modelID
    LEFT JOIN sizes s ON p.sizeID = s.sizeID
    LEFT JOIN regions r ON p.regionID = r.regionID
    LEFT JOIN product_batch pb ON p.productID = pb.productID
    {$whereClause}
    GROUP BY p.productID
    ORDER BY p.createdAt DESC
";

$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    exit('Unable to prepare export query.');
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$output = fopen('php://output', 'w');
fputcsv($output, [
    'Product Name',
    'Category',
    'Region',
    'Model',
    'Color',
    'Tonnage',
    'Batch Number',
    'Stock In',
    'Stock Out',
    'Available',
    'Average Cost/Unit',
    'Description',
    'Created At'
]);

while ($row = $result->fetch_assoc()) {
    $quantity = intval($row['quantity'] ?? 0);
    $available = intval($row['available'] ?? $quantity);
    $stockOut = max(0, $quantity - $available);

    fputcsv($output, [
        $row['productName'] ?? '',
        $row['categoryName'] ?? '',
        $row['regionName'] ?? '',
        $row['modelName'] ?? '',
        $row['colorName'] ?? '',
        $row['sizeName'] ?? '',
        $row['batchNumber'] ?? '',
        $quantity,
        $stockOut,
        $available,
        number_format((float)($row['averageCost'] ?? $row['cost'] ?? 0), 2, '.', ''),
        $row['description'] ?? '',
        $row['createdAt'] ?? ''
    ]);
}

$stmt->close();
$conn->close();
fclose($output);
exit;
?>
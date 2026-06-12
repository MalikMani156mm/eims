<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

$vendorID = intval($_GET['vendorID'] ?? 0);

if ($vendorID <= 0) {
    http_response_code(400);
    exit('Invalid vendor ID');
}

$vendorStmt = $conn->prepare("SELECT vendorName FROM vendors WHERE vendorID = ? LIMIT 1");
$vendorStmt->bind_param('i', $vendorID);
$vendorStmt->execute();
$vendorResult = $vendorStmt->get_result();
$vendor = $vendorResult->fetch_assoc();
$vendorStmt->close();

if (!$vendor) {
    http_response_code(404);
    exit('Vendor not found');
}

$safeVendorName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $vendor['vendorName']);
$filename = 'vendor_purchase_logs_' . $safeVendorName . '_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

$stmt = $conn->prepare("
    SELECT
        gm.gas_name,
        gbd.batchName,
        r.regionName,
        gbd.quantity,
        gbd.available,
        gbd.unit_price,
        gbd.total_price,
        gbd.paid_price,
        gbd.pending_price,
        gbd.createdAt
    FROM gas_batch_details gbd
    INNER JOIN gas_master gm ON gbd.gas_id = gm.gas_id
    LEFT JOIN regions r ON gbd.regionID = r.regionID
    WHERE gbd.vendorID = ?
    ORDER BY gbd.batch_id DESC
");

if (!$stmt) {
    http_response_code(500);
    exit('Unable to prepare export query.');
}

$stmt->bind_param('i', $vendorID);
$stmt->execute();
$result = $stmt->get_result();

$output = fopen('php://output', 'w');
fputcsv($output, [
    'Vendor Name',
    'Gas Name',
    'Batch Name',
    'Region',
    'Quantity (kg)',
    'Available (kg)',
    'Unit Price',
    'Total Price',
    'Paid',
    'Pending',
    'Purchased At'
]);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $vendor['vendorName'],
        $row['gas_name'] ?? '',
        $row['batchName'] ?? '',
        $row['regionName'] ?? '',
        number_format((float)($row['quantity'] ?? 0), 2, '.', ''),
        number_format((float)($row['available'] ?? 0), 2, '.', ''),
        number_format((float)($row['unit_price'] ?? 0), 2, '.', ''),
        number_format((float)($row['total_price'] ?? 0), 2, '.', ''),
        number_format((float)($row['paid_price'] ?? 0), 2, '.', ''),
        number_format((float)($row['pending_price'] ?? 0), 2, '.', ''),
        $row['createdAt'] ?? ''
    ]);
}

$stmt->close();
$conn->close();
fclose($output);
exit;

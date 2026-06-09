<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$serialNumbersJSON = $_POST['serialNumbers'] ?? '';
$serialNumbers = json_decode($serialNumbersJSON, true);

if (!is_array($serialNumbers) || empty($serialNumbers)) {
    echo json_encode(['success' => false, 'message' => 'No serial numbers provided']);
    exit;
}

$trimmed = [];
foreach ($serialNumbers as $serialNumber) {
    $serialNumber = trim((string) $serialNumber);
    if ($serialNumber === '') {
        continue;
    }
    $trimmed[] = $serialNumber;
}

if (empty($trimmed)) {
    echo json_encode(['success' => false, 'message' => 'No valid serial numbers provided']);
    exit;
}

$unique = array_unique($trimmed);
if (count($unique) !== count($trimmed)) {
    echo json_encode(['success' => false, 'message' => 'Duplicate serial numbers in your list. Each must be unique.']);
    exit;
}

$checkStmt = $conn->prepare("SELECT serialNumber FROM product_serials WHERE serialNumber = ? LIMIT 1");
if (!$checkStmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$duplicates = [];
foreach ($trimmed as $serialNumber) {
    $checkStmt->bind_param('s', $serialNumber);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        $duplicates[] = $serialNumber;
    }
}
$checkStmt->close();
$conn->close();

if (!empty($duplicates)) {
    $list = implode(', ', $duplicates);
    echo json_encode([
        'success' => false,
        'message' => 'Serial number(s) already exist in the database: ' . $list,
        'duplicates' => $duplicates
    ]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'All serial numbers are available']);

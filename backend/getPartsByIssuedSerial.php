<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

$serialNumber = trim($_GET['serialNumber'] ?? '');
if ($serialNumber === '') {
    echo json_encode(['success' => false, 'message' => 'serialNumber required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT p.partID, p.partName, p.serialNumber, p.batchName, p.quantity, p.status, p.issuedDate, p.brandID, COALESCE(b.brandName, '') AS brand FROM parts p LEFT JOIN brands b ON p.brandID = b.brandID WHERE p.issuedToSerialNumber = ? ORDER BY p.partName, p.serialNumber");
    $stmt->bind_param('s', $serialNumber);
    $stmt->execute();
    $res = $stmt->get_result();

    $parts = [];
    while ($row = $res->fetch_assoc()) {
        $parts[] = $row;
    }

    echo json_encode(['success' => true, 'parts' => $parts]);
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
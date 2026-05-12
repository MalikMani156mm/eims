<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

$serialNumber = trim($_GET['serialNumber'] ?? '');
if ($serialNumber === '') {
    echo json_encode(['success' => false, 'message' => 'serialNumber required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT partID, partName, serialNumber, batchName, quantity, status, issuedDate FROM parts WHERE issuedToSerialNumber = ? ORDER BY partName, serialNumber");
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
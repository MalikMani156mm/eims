<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

$brandID = intval($_GET['brandID'] ?? $_POST['brandID'] ?? 0);
if ($brandID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid brandID']);
    exit;
}

$partsWithSerial = [];
$partsWithoutSerial = [];

// Get distinct part names for this brand
$stmt = $conn->prepare("SELECT DISTINCT partName FROM parts WHERE brandID = ? ORDER BY partName ASC");
$stmt->bind_param('i', $brandID);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $partName = $row['partName'];

    // Check if this part has serial numbers
    $serialCheck = $conn->prepare("SELECT COUNT(*) as count FROM parts WHERE partName = ? AND brandID = ? AND serialNumber IS NOT NULL LIMIT 1");
    $serialCheck->bind_param('si', $partName, $brandID);
    $serialCheck->execute();
    $scRes = $serialCheck->get_result()->fetch_assoc();
    $serialCount = intval($scRes['count'] ?? 0);
    $serialCheck->close();

    if ($serialCount > 0) {
        $serialParts = [];
        $pstmt = $conn->prepare("SELECT partID, partName, serialNumber, batchName FROM parts WHERE partName = ? AND brandID = ? AND serialNumber IS NOT NULL AND status = 'available' ORDER BY serialNumber ASC");
        $pstmt->bind_param('si', $partName, $brandID);
        $pstmt->execute();
        $pRes = $pstmt->get_result();
        while ($p = $pRes->fetch_assoc()) {
            $serialParts[] = $p;
        }
        $pstmt->close();

        if (!empty($serialParts)) {
            $partsWithSerial[$partName] = $serialParts;
        }
    } else {
        $noSerialCheck = $conn->prepare("SELECT COUNT(*) as count FROM parts WHERE partName = ? AND brandID = ? AND serialNumber IS NULL AND status = 'available'");
        $noSerialCheck->bind_param('si', $partName, $brandID);
        $noSerialCheck->execute();
        $noCount = $noSerialCheck->get_result()->fetch_assoc();
        $availableCount = intval($noCount['count'] ?? 0);
        $noSerialCheck->close();

        if ($availableCount > 0) {
            $partsWithoutSerial[$partName] = $availableCount;
        }
    }
}
$stmt->close();

echo json_encode([
    'success' => true,
    'partsWithSerial' => $partsWithSerial,
    'partsWithoutSerial' => $partsWithoutSerial
]);

$conn->close();
exit;

?>

<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$partName = trim($_POST['partName'] ?? '');
$regionID = intval($_POST['regionID'] ?? 0);
$batchName = trim($_POST['batchName'] ?? '');
$quantity = intval($_POST['quantity'] ?? 0);
$issuedTo = trim($_POST['issuedTo'] ?? '');
$issuedProductSerial = trim($_POST['issuedProductSerialNumber'] ?? '');

if ($partName === '' || $regionID <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters']);
    exit;
}
if ($issuedTo === '') { echo json_encode(['success' => false, 'message' => '"Issued To" is required']); exit; }
if ($issuedProductSerial === '') { echo json_encode(['success' => false, 'message' => '"Issued Product Serial Number" is required']); exit; }

try {
    $conn->begin_transaction();

    // Lock candidate aggregate rows (serialNumber IS NULL & status = 'available')
    $sel = $conn->prepare("SELECT partID, quantity, used FROM parts WHERE partName = ? AND regionID = ? AND serialNumber IS NULL AND status = 'available' ORDER BY createdAt ASC FOR UPDATE");
    $sel->bind_param('si', $partName, $regionID);
    $sel->execute();
    $res = $sel->get_result();

    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;

    $totalAvail = array_reduce($rows, function($acc, $r){ return $acc + (int)$r['quantity']; }, 0);
    if ($totalAvail < $quantity) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Insufficient available quantity']);
        exit;
    }

    $remaining = $quantity;
    $upd = $conn->prepare("UPDATE parts SET quantity = ?, used = ?, status = ?, issuedToSerialNumber = ?, issuedDate = NOW() WHERE partID = ?");
    // Prepare logging statement
    $logStmt = $conn->prepare("INSERT INTO issue_parts_log (partName, regionID, batchName, serialNumber, quantityIssued, issuedTo, issuedBy, issuedByName, issuedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    foreach ($rows as $r) {
        if ($remaining <= 0) break;
        $pid = (int)$r['partID'];
        $rowQty = (int)$r['quantity'];
        $rowUsed = (int)$r['used'];

        if ($rowQty <= 0) continue;

        if ($rowQty <= $remaining) {
            $useNow = $rowQty;
            $newQty = 0;
        } else {
            $useNow = $remaining;
            $newQty = $rowQty - $useNow;
        }

        $newUsed = $rowUsed + $useNow;
        $newStatus = ($newQty <= 0) ? 'used' : 'available';

        $upd->bind_param('iissi', $newQty, $newUsed, $newStatus, $issuedProductSerial, $pid);
        if (!$upd->execute()) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
            exit;
        }

        // Record audit log for this consumption
        $serialVal = null; // these are aggregated rows (serialNumber IS NULL)
        $issuedBy = isset($ID) ? (int)$ID : null;
        $issuedByName = isset($adminName) ? $adminName : null;
        // types: partName(s), regionID(i), batchName(s), serialNumber(s), quantityIssued(i), issuedTo(s), issuedBy(i), issuedByName(s)
        $logStmt->bind_param('sissisis', $partName, $regionID, $batchName, $serialVal, $useNow, $issuedTo, $issuedBy, $issuedByName);
        $logStmt->execute();

        $remaining -= $useNow;
    }

    $logStmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => "Issued {$quantity} items for {$partName}"]);
    $sel->close();
    $upd->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>

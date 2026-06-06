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
$serialsJson = $_POST['serials'] ?? '[]';
$issuedTo = trim($_POST['issuedTo'] ?? '');
$issuedProductSerial = trim($_POST['issuedProductSerialNumber'] ?? '');

$serials = json_decode($serialsJson, true);
if (!is_array($serials)) $serials = [];

if ($partName === '' || $regionID <= 0 || empty($serials)) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters']);
    exit;
}
if ($issuedTo === '') { echo json_encode(['success' => false, 'message' => '"Issued To" is required']); exit; }
if ($issuedProductSerial === '') { echo json_encode(['success' => false, 'message' => '"Issued Product Serial" is required']); exit; }

try {
    $conn->begin_transaction();

    // Verify all serials are available
    $in = implode(',', array_fill(0, count($serials), '?'));
    $types = str_repeat('s', count($serials));
    $params = $serials; // array of serial strings

    $verifySql = "SELECT serialNumber, partID FROM parts WHERE partName = ? AND regionID = ? AND serialNumber IN ($in) AND status = 'available'";
    $stmt = $conn->prepare($verifySql);
    // bind dynamic params: first partName, regionID, then serials...
    $bindNames = array_merge([$partName, $regionID], $params);
    $bindTypes = 'si' . $types;
    $refs = [];
    $refs[] = & $bindTypes;
    foreach ($bindNames as $i => $v) $refs[] = & $bindNames[$i];
    call_user_func_array([$stmt, 'bind_param'], $refs);
    $stmt->execute();
    $res = $stmt->get_result();
    $found = [];
    while ($r = $res->fetch_assoc()) $found[] = $r['serialNumber'];

    if (count($found) !== count($serials)) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'One or more serials are not available']);
        exit;
    }

    // Update each serial row to used and log
    $upd = $conn->prepare("UPDATE parts SET status = 'used', used = 1, issuedToSerialNumber = ?, issuedDate = NOW() WHERE partName = ? AND regionID = ? AND serialNumber = ? AND status = 'available'");
    $log = $conn->prepare("INSERT INTO issue_parts_log (partName, regionID, batchName, serialNumber, quantityIssued, issuedTo, issuedBy, issuedByName, issuedAt) VALUES (?, ?, ?, ?, 1, ?, ?, ?, NOW())");

    foreach ($serials as $s) {
        $sTrim = trim($s);
        // execute update - store issued product serial into parts.issuedToSerialNumber
        $upd->bind_param('ssis', $issuedProductSerial, $partName, $regionID, $sTrim);
        if (!$upd->execute() || $upd->affected_rows !== 1) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to update serial: '.$sTrim]);
            exit;
        }

        $issuedBy = isset($ID) ? (int)$ID : null;
        $issuedByName = isset($adminName) ? $adminName : null;
        $log->bind_param('sisssis', $partName, $regionID, $batchName = null, $sTrim, $issuedTo, $issuedBy, $issuedByName);
        $log->execute();
    }

    $upd->close();
    $log->close();
    $stmt->close();
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Issued selected serials']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>

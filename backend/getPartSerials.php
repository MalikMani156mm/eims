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

if ($partName === '' || $regionID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    // Get region name
    $rstmt = $conn->prepare('SELECT regionName FROM regions WHERE regionID = ? LIMIT 1');
    $rstmt->bind_param('i', $regionID);
    $rstmt->execute();
    $rres = $rstmt->get_result();
    $regionName = $rres->num_rows ? $rres->fetch_assoc()['regionName'] : 'Unknown';
    $rstmt->close();

    // Aggregate by serialNumber but keep per-status sums so a serial can appear
    // in both available and used lists if there are rows with different statuses.
    $stmt = $conn->prepare(
        "SELECT
            serialNumber,
            SUM(CASE WHEN status = 'available' THEN IF(quantity > 0, quantity, 1) ELSE 0 END) AS avail_qty,
            SUM(CASE WHEN status = 'used' THEN IF(quantity > 0, quantity, 1) ELSE 0 END) AS used_qty,
            MIN(createdAt) AS createdAt,
            MAX(issuedDate) AS issuedDate
        FROM parts
        WHERE partName = ? AND regionID = ?
        GROUP BY serialNumber
        ORDER BY COALESCE(serialNumber, '') ASC, createdAt ASC"
    );
    $stmt->bind_param('si', $partName, $regionID);
    $stmt->execute();
    $res = $stmt->get_result();

    $available = [];
    $used = [];

    while ($row = $res->fetch_assoc()) {
        $serial = $row['serialNumber'];
        $createdAt = $row['createdAt'];
        $issuedDate = $row['issuedDate'];

        $availQty = (int)$row['avail_qty'];
        $usedQty = (int)$row['used_qty'];

        if ($availQty > 0) {
            $available[] = [
                'serialNumber' => $serial,
                'issuedDate' => null,
                'createdAt' => $createdAt,
                'quantity' => $availQty
            ];
        }

        if ($usedQty > 0) {
            $used[] = [
                'serialNumber' => $serial,
                'issuedDate' => $issuedDate,
                'createdAt' => $createdAt,
                'quantity' => $usedQty
            ];
        }
    }

    echo json_encode(['success' => true, 'regionName' => $regionName, 'available' => $available, 'used' => $used]);
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();

?>

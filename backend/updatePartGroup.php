<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$partName = trim($_POST['partName'] ?? '');
$oldBatchName = trim($_POST['oldBatchName'] ?? '');
$oldRegionID = intval($_POST['oldRegionID'] ?? 0);
$oldBrandID = intval($_POST['oldBrandID'] ?? 0);
$oldSizeID = intval($_POST['oldSizeID'] ?? 0);
$oldTypeID = intval($_POST['oldTypeID'] ?? 0);

$batchName = trim($_POST['batchName'] ?? '');
$regionID = intval($_POST['regionID'] ?? 0);
$brandID = intval($_POST['brandID'] ?? 0);
$sizeID = intval($_POST['sizeID'] ?? 0);
$typeID = intval($_POST['typeID'] ?? 0);

if ($partName === '') {
    echo json_encode(['success' => false, 'message' => 'Part name is required']);
    exit;
}

if ($oldBatchName === '' || $oldRegionID <= 0 || $oldBrandID <= 0 || $oldSizeID <= 0 || $oldTypeID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid original part group']);
    exit;
}

if ($batchName === '') {
    echo json_encode(['success' => false, 'message' => 'Batch name is required']);
    exit;
}

if ($regionID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Region is required']);
    exit;
}

if ($brandID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Brand is required']);
    exit;
}

if ($sizeID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Size is required']);
    exit;
}

if ($typeID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Type is required']);
    exit;
}

try {
    $conn->begin_transaction();

    $checkStmt = $conn->prepare("
        SELECT COUNT(*) AS cnt
        FROM parts
        WHERE partName = ? AND batchName = ? AND regionID = ? AND brandID = ? AND sizeID = ? AND typeID = ?
    ");
    $checkStmt->bind_param('ssiiii', $partName, $oldBatchName, $oldRegionID, $oldBrandID, $oldSizeID, $oldTypeID);
    $checkStmt->execute();
    $matchCount = (int)($checkStmt->get_result()->fetch_assoc()['cnt'] ?? 0);
    $checkStmt->close();

    if ($matchCount === 0) {
        throw new Exception('Part group not found');
    }

    $updateStmt = $conn->prepare("
        UPDATE parts
        SET batchName = ?, regionID = ?, brandID = ?, sizeID = ?, typeID = ?, updatedAt = NOW()
        WHERE partName = ? AND batchName = ? AND regionID = ? AND brandID = ? AND sizeID = ? AND typeID = ?
    ");
    $updateStmt->bind_param(
        'siiiissiiii',
        $batchName,
        $regionID,
        $brandID,
        $sizeID,
        $typeID,
        $partName,
        $oldBatchName,
        $oldRegionID,
        $oldBrandID,
        $oldSizeID,
        $oldTypeID
    );

    if (!$updateStmt->execute()) {
        $updateStmt->close();
        throw new Exception('Failed to update part group');
    }

    $updatedRows = $updateStmt->affected_rows;
    $updateStmt->close();

    $conn->commit();

    $message = $updatedRows > 0
        ? "Updated $updatedRows part record(s) successfully"
        : 'Part group saved (no field changes detected)';

    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();

?>

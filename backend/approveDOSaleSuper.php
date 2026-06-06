<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
header('Content-Type: application/json');

if (!isset($_POST['saleID']) || !is_numeric($_POST['saleID'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid saleID']);
    exit;
}

$saleID = intval($_POST['saleID']);
$approver = isset($ID) ? intval($ID) : 0;

$stmt = $conn->prepare("UPDATE do_sales SET approvedBySuperAdmin = ? WHERE saleID = ? AND approvedBySuperAdmin IS NULL");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ii', $approver, $saleID);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Sale super-approved']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes (maybe already super-approved)']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

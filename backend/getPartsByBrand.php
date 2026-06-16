<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/partGroupHelpers.php';

$brandID = intval($_GET['brandID'] ?? $_POST['brandID'] ?? 0);
if ($brandID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid brandID']);
    exit;
}

$groups = loadPartsGroupsForBrand($conn, $brandID);

echo json_encode([
    'success' => true,
    'partsWithSerialGroups' => $groups['partsWithSerialGroups'],
    'partsWithoutSerialGroups' => $groups['partsWithoutSerialGroups']
]);

$conn->close();
exit;

?>

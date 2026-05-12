<?php
require '../adminAuth.php';
require '../db.php';
header('Content-Type: application/json');

if (!isset($_POST['saleID']) || !is_numeric($_POST['saleID'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid saleID']);
    exit;
}

$saleID = intval($_POST['saleID']);

// Start transaction
$conn->begin_transaction();
try {
    // Fetch sale items
    $stmt = $conn->prepare("SELECT serialID, productID FROM do_sale_items WHERE saleID = ?");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('i', $saleID);
    $stmt->execute();
    $res = $stmt->get_result();

    $productCounts = [];
    $serials = [];
    while ($row = $res->fetch_assoc()) {
        $pid = intval($row['productID']);
        $sid = isset($row['serialID']) ? intval($row['serialID']) : 0;
        if ($pid > 0) {
            if (!isset($productCounts[$pid])) $productCounts[$pid] = 0;
            $productCounts[$pid]++;
        }
        if ($sid > 0) $serials[] = $sid;
    }
    $stmt->close();

    // Revert serials to available
    if (!empty($serials)) {
        $placeholders = implode(',', array_fill(0, count($serials), '?'));
        $types = str_repeat('i', count($serials));
        $sql = "UPDATE product_serials SET status = 'available' WHERE serialID IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        // bind params dynamically
        $stmt->bind_param($types, ...$serials);
        if (!$stmt->execute()) throw new Exception('Failed to update serials: ' . $stmt->error);
        $stmt->close();
    }

    // Increment products.available
    if (!empty($productCounts)) {
        $stmt = $conn->prepare("UPDATE products SET available = available + ? WHERE productID = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        foreach ($productCounts as $pid => $count) {
            $stmt->bind_param('ii', $count, $pid);
            if (!$stmt->execute()) throw new Exception('Failed to update product available: ' . $stmt->error);
        }
        $stmt->close();
    }

    // Delete the sale (will cascade delete items/payments)
    $stmt = $conn->prepare("DELETE FROM do_sales WHERE saleID = ?");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('i', $saleID);
    if (!$stmt->execute()) throw new Exception('Failed to delete sale: ' . $stmt->error);
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Sale rejected and inventory reverted']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();

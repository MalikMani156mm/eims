<?php
require '../adminAuth.php';
require '../db.php';

header('Content-Type: application/json');

$count = 0;
try {
    // Count dispatches approved by admin but awaiting superadmin
    if (isset($adminRole) && $adminRole === 'superadmin') {
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM do_sales WHERE approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NULL");
    } else {
        // For non-superadmin, limit to region
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM do_sales WHERE approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NULL AND regionID = ?");
        $stmt->bind_param('i', $regionID);
    }

    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $count = intval($row['cnt']);
        }
        $stmt->close();
    }

    echo json_encode(['success' => true, 'count' => $count]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'count' => 0, 'message' => $e->getMessage()]);
}

$conn->close();
?>

<?php
require '../adminAuth.php';
require '../db.php';

header('Content-Type: application/json');

// Default: no pending
$count = 0;

try {
    if (isset($adminRole) && $adminRole === 'superadmin') {
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM distributing_officer WHERE status = 1");
    } else {
        // Non-superadmin: count only in same region
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM distributing_officer WHERE status = 1 AND regionID = ?");
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

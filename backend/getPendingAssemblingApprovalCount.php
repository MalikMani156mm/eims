<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($adminRole) || ($adminRole !== 'admin' && $adminRole !== 'superadmin')) {
    echo json_encode(['success' => true, 'count' => 0]);
    exit;
}

$count = 0;

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM products WHERE cost IS NULL");

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

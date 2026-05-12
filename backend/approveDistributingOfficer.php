<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$officerID = intval($_POST['officerID'] ?? 0);

if ($officerID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid officer ID']);
    exit;
}

try {
    // Ensure officer exists and is pending (status = 1)
    $check = $conn->prepare("SELECT status FROM distributing_officer WHERE DO_ID = ? LIMIT 1");
    $check->bind_param('i', $officerID);
    $check->execute();
    $res = $check->get_result();
    if (!$res || $res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Officer not found']);
        exit;
    }
    $row = $res->fetch_assoc();
    if ((int)$row['status'] !== 1) {
        echo json_encode(['success' => false, 'message' => 'Officer is not pending approval']);
        exit;
    }
    $check->close();

    // Update status -> 0 and set approvedBy
    $stmt = $conn->prepare("UPDATE distributing_officer SET status = 0, approvedBy = ?, updatedAt = NOW() WHERE DO_ID = ?");
    $approver = isset($ID) ? intval($ID) : 0;
    $stmt->bind_param('ii', $approver, $officerID);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Officer approved']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to approve: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>

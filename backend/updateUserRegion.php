<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Karachi');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regionID = intval($_POST['regionID'] ?? 0);
    
    // Validate inputs
    if ($regionID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid region selected']);
        exit;
    }

    if (!isset($ID)) {
        echo json_encode(['success' => false, 'message' => 'User session not found']);
        exit;
    }

    try {
        // Verify that this region is available for the user (exists in user_regions table)
        $verifyStmt = $conn->prepare("SELECT id FROM user_regions WHERE user_id = ? AND regionID = ? LIMIT 1");
        if (!$verifyStmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }

        $verifyStmt->bind_param('ii', $ID, $regionID);
        $verifyStmt->execute();
        $verifyRes = $verifyStmt->get_result();
        
        if ($verifyRes->num_rows === 0) {
            $verifyStmt->close();
            echo json_encode(['success' => false, 'message' => 'You do not have access to this region']);
            exit;
        }
        $verifyStmt->close();

        // Update the user's regionID
        $updateStmt = $conn->prepare("UPDATE users SET regionID = ? WHERE user_id = ?");
        if (!$updateStmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }

        $updateStmt->bind_param('ii', $regionID, $ID);
        
        if ($updateStmt->execute()) {
            $updateStmt->close();
            echo json_encode(['success' => true, 'message' => 'Region updated successfully']);
        } else {
            $updateStmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to update region']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

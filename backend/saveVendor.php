<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendorName = trim($_POST['vendorName'] ?? '');

    if (empty($vendorName)) {
        echo json_encode(['success' => false, 'message' => 'Vendor name is required']);
        exit;
    }

    try {
        $checkStmt = $conn->prepare("SELECT vendorID FROM vendors WHERE vendorName = ? LIMIT 1");
        $checkStmt->bind_param('s', $vendorName);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $checkStmt->close();
            echo json_encode(['success' => false, 'message' => 'Vendor name already exists']);
            exit;
        }
        $checkStmt->close();

        $stmt = $conn->prepare("INSERT INTO vendors (vendorName) VALUES (?)");
        $stmt->bind_param('s', $vendorName);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vendor added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add vendor']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

$conn->close();

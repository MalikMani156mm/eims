<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendorID = intval($_POST['id'] ?? 0);

    if ($vendorID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid vendor ID']);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM vendors WHERE vendorID = ?");
        $stmt->bind_param('i', $vendorID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vendor deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete vendor']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

$conn->close();

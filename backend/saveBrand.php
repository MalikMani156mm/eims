<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brandName = trim($_POST['brandName'] ?? '');
    
    if (empty($brandName)) {
        echo json_encode(['success' => false, 'message' => 'Brand name is required']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO brands (brandName) VALUES (?)");
        $stmt->bind_param("s", $brandName);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Brand added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add brand']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regionName = trim($_POST['regionName'] ?? '');
    
    if (empty($regionName)) {
        echo json_encode(['success' => false, 'message' => 'Region name is required']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO regions (regionName) VALUES (?)");
        $stmt->bind_param("s", $regionName);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Region added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add region']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

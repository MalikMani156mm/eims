<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $officerID = intval($_POST['officerID'] ?? 0);
    $cnic = trim($_POST['cnic'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = intval($_POST['status'] ?? 0);
    $regionID_post = intval($_POST['regionID'] ?? 0);
    $regionIDToUse = $regionID_post > 0 ? $regionID_post : (isset($regionID) ? intval($regionID) : 0);
    
    // Validation
    if ($officerID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid officer ID']);
        exit;
    }
    
    if (empty($cnic)) {
        echo json_encode(['success' => false, 'message' => 'CNIC is required']);
        exit;
    }
    
    if (empty($contactNumber)) {
        echo json_encode(['success' => false, 'message' => 'Contact number is required']);
        exit;
    }
    
    if (empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Address is required']);
        exit;
    }
    
    // Check if CNIC already exists for different officer
    $checkStmt = $conn->prepare("SELECT DO_ID FROM distributing_officer WHERE CNIC = ? AND DO_ID != ?");
    $checkStmt->bind_param("si", $cnic, $officerID);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Another officer with this CNIC already exists']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();
    
    try {
        $stmt = $conn->prepare("UPDATE distributing_officer SET CNIC = ?, contactNumber = ?, Address = ?, regionID = ?, status = ?, updatedAt = NOW() WHERE DO_ID = ?");
        $stmt->bind_param("sssiii", $cnic, $contactNumber, $address, $regionIDToUse, $status, $officerID);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Warehouse updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made or officer not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update officer: ' . $stmt->error]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

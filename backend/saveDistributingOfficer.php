<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = intval($_POST['status'] ?? 0);
    $regionID_post = intval($_POST['regionID'] ?? 0);
    $regionIDToUse = $regionID_post > 0 ? $regionID_post : (isset($regionID) ? intval($regionID) : 0);
    
    // Validation
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required']);
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
    
    // Check if CNIC already exists
    $checkStmt = $conn->prepare("SELECT DO_ID FROM distributing_officer WHERE CNIC = ?");
    $checkStmt->bind_param("s", $cnic);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'An officer with this CNIC already exists']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();
    
    try {
        $stmt = $conn->prepare("INSERT INTO distributing_officer (DO_Name, CNIC, contactNumber, Address, regionID, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $name, $cnic, $contactNumber, $address, $regionIDToUse, $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Distributing officer added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add officer: ' . $stmt->error]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

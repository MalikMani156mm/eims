<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $regionID = intval($_POST['regionID'] ?? 0);
    $role = trim($_POST['role'] ?? '');
    $dashboard_access = trim($_POST['dashboard_access'] ?? '');
    $is_active = intval($_POST['is_active'] ?? 1);
    
    // Validation
    if (empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'Full name is required']);
        exit;
    }
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    }
    
    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Password is required']);
        exit;
    }
    
    if ($regionID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a region']);
        exit;
    }
    
    if (empty($role)) {
        echo json_encode(['success' => false, 'message' => 'Please select a role']);
        exit;
    }
    
    if (empty($dashboard_access)) {
        echo json_encode(['success' => false, 'message' => 'Dashboard access is required']);
        exit;
    }
    
    // Check if username already exists
    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, password, email, phone, regionID, role, dashboard_access, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssissi", $full_name, $username, $hashedPassword, $email, $phone, $regionID, $role, $dashboard_access, $is_active);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add user']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($adminRole) || $adminRole !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userID = intval($_POST['user_id'] ?? 0);
$newPassword = trim($_POST['newPassword'] ?? '');
$confirmPassword = trim($_POST['confirmPassword'] ?? '');

if ($userID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

if ($newPassword === '' || $confirmPassword === '') {
    echo json_encode(['success' => false, 'message' => 'All password fields are required']);
    exit;
}

if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit;
}

if (in_array($newPassword, ['12345678', '123456789'], true)) {
    echo json_encode(['success' => false, 'message' => 'Weak password! Please choose a stronger one']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

$checkStmt = $conn->prepare("SELECT user_id, username FROM users WHERE user_id = ? LIMIT 1");
$checkStmt->bind_param('i', $userID);
$checkStmt->execute();
$userResult = $checkStmt->get_result();
$user = $userResult->fetch_assoc();
$checkStmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?");
$updateStmt->bind_param('si', $hashedPassword, $userID);

if ($updateStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully for ' . $user['username']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to reset password']);
}

$updateStmt->close();
$conn->close();

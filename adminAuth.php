<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "lkjasjriongwer294neiufie2498u92jkfdsni9743nu894nfdskdfnkv9843nfk7283";

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!isset($_COOKIE['auth_token'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}

$jwt = $_COOKIE['auth_token'];

try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    $ID = $decoded->uid;
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $ID);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $adminRole = $admin['role'];
    $adminName = $admin['username'];
    $fullName = $admin['full_name'];
    $regionID = $admin['regionID'];
    $dashboard = $admin['dashboard_access'];
} catch (Exception $e) {
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Session expired']);
        exit();
    } else {
        echo "<script>alert('Session expired, please login again.'); window.location.href = 'index.php';</script>";
        exit();
    }
}

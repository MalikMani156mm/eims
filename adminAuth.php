<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
require 'vendor/autoload.php';
require 'db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "lkjasjriongwer294neiufie2498u92jkfdsni9743nu894nfdskdfnkv9843nfk7283";

if (!isset($_COOKIE['auth_token'])) {
    header("Location: index.php");
    exit();
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
    $dashboard = $admin['dashboard_access'];
} catch (Exception $e) {
    // Invalid or expired token
    echo "<script>alert('Session expired, please login again.'); window.location.href = 'index.php';</script>";
}

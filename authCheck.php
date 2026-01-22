<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "lkjasjriongwer294neiufie2498u92jkfdsni9743nu894nfdskdfnkv9843nfk7283";

if (isset($_COOKIE['auth_token'])) {
    try {
        $jwt = $_COOKIE['auth_token'];
        $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
        header("Location: dashboard.php");
    } catch (Exception $e) {
        // Token is expired or invalid; stay on login page
    }
}
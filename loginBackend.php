<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

date_default_timezone_set('Asia/Karachi');

$host = "localhost";
$dbname = "eims_db";
$dbuser = "root";
$dbpass = "";
$secretKey = "lkjasjriongwer294neiufie2498u92jkfdsni9743nu894nfdskdfnkv9843nfk7283";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $issuedAt = time();
            $expire = $issuedAt + (60 * 60 * 12); // 12 hours

            // ✅ Payload with uid and role
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expire,
                'uid' => $user['user_id'],
                'username' => $username,
                'role' => $user['role']
            ];

            $jwt = JWT::encode($payload, $secretKey, 'HS256');

            setcookie("auth_token", $jwt, [
                'expires' => $expire,
                'secure' => false, // Set to true if using HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: index.php?error=invalid_password");
            exit();
        }
    } else {
        header("Location: index.php?error=invalid_username");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

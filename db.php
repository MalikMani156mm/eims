<?php
// Prevent multiple database connections from duplicate includes
if (defined('EIMS_DB_CONNECTED')) {
    return;
}
define('EIMS_DB_CONNECTED', true);

// Set timezone to Asia/Karachi globally
date_default_timezone_set('Asia/Karachi');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eims_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

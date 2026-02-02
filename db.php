<?php
// Production: Use Railway environment variables
// Local: Falls back to localhost defaults
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'salon_booking';
$port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '';

$conn = new mysqli($host, $user, $pass, $db, (int)$port);

if ($conn->connect_error) {
    die('DB connect error: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>

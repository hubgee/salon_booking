<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // your password if set
$db   = 'salon_booking'; // ensure this matches your DB name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('DB connect error: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

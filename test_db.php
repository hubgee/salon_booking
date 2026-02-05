<?php
// Include your existing connection file
require 'db.php';

// Test 1: Show tables
echo "<h3>Connected to Railway DB. Tables:</h3>";
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        echo $row[0] . "<br>";
    }
} else {
    echo "Error showing tables: " . $conn->error;
}

// Test 2: Show sample data from bookings table
echo "<h3>Sample rows from bookings table:</h3>";
$result = $conn->query("SELECT * FROM bookings LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<pre>" . print_r($row, true) . "</pre>";
    }
} else {
    echo "Error fetching data: " . $conn->error;
}

$conn->close();
?>
<?php
// get_unread_notifications.php
// Returns the current count of unread notifications

header('Content-Type: application/json');

require_once 'db.php';

// Get unread count
$query = $conn->query("SELECT COUNT(*) AS unread FROM notifications WHERE is_seen = 0");
if ($query) {
    $row = $query->fetch_assoc();
    $unread_count = intval($row['unread']);
    echo json_encode(['unread' => $unread_count]);
} else {
    echo json_encode(['unread' => 0, 'error' => 'Query failed']);
}

$conn->close();
?>


<?php
// mark_notifications_seen.php
// Marks all notifications as seen and returns the new unread count (should be 0)

header('Content-Type: application/json');

require_once 'db.php';

// Update all unseen notifications to seen
$update_stmt = $conn->prepare("UPDATE notifications SET is_seen = 1, seen_at = NOW() WHERE is_seen = 0");
if ($update_stmt) {
    $update_stmt->execute();
    $update_stmt->close();
}

// Get new unread count (should be 0)
$query = $conn->query("SELECT COUNT(*) AS unread FROM notifications WHERE is_seen = 0");
if ($query) {
    $row = $query->fetch_assoc();
    $unread_count = intval($row['unread']);
    echo json_encode(['unread' => $unread_count, 'success' => true]);
} else {
    echo json_encode(['unread' => 0, 'success' => false, 'error' => 'Query failed']);
}

$conn->close();
?>


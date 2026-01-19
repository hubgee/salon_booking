<?php
// unregister_push_subscription.php
// Remove admin's push subscription

session_start();
if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');
require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['endpoint'])) {
    echo json_encode(['success' => false, 'error' => 'Missing endpoint']);
    exit();
}

$endpoint = $input['endpoint'];

$delete_stmt = $conn->prepare("DELETE FROM push_subscriptions WHERE endpoint = ?");
$delete_stmt->bind_param("s", $endpoint);

if ($delete_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Subscription removed']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to remove subscription']);
}

$delete_stmt->close();
$conn->close();
?>


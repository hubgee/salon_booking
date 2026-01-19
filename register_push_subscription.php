<?php
// register_push_subscription.php
// Register admin's push subscription for push notifications

session_start();
if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');
require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['endpoint']) || !isset($input['keys']['p256dh']) || !isset($input['keys']['auth'])) {
    echo json_encode(['success' => false, 'error' => 'Missing subscription data']);
    exit();
}

$endpoint = $input['endpoint'];
$p256dh = $input['keys']['p256dh'];
$auth = $input['keys']['auth'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Check if subscription already exists
$check_stmt = $conn->prepare("SELECT id FROM push_subscriptions WHERE endpoint = ?");
$check_stmt->bind_param("s", $endpoint);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing subscription
    $update_stmt = $conn->prepare("UPDATE push_subscriptions SET p256dh = ?, auth = ?, user_agent = ?, last_used = NOW() WHERE endpoint = ?");
    $update_stmt->bind_param("ssss", $p256dh, $auth, $user_agent, $endpoint);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Subscription updated']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update subscription']);
    }
    $update_stmt->close();
} else {
    // Insert new subscription
    $insert_stmt = $conn->prepare("INSERT INTO push_subscriptions (endpoint, p256dh, auth, user_agent) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("ssss", $endpoint, $p256dh, $auth, $user_agent);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Subscription registered']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to register subscription: ' . $conn->error]);
    }
    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();
?>


<?php
// process_booking.php

// Set content type to JSON
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'db.php';

$name    = $_POST['name'] ?? '';
$service_id = $_POST['service'] ?? null;
$date    = $_POST['date'] ?? '';
$time    = $_POST['time'] ?? '';

// Validate required fields
if (!$service_id || $name === '' || $date === '' || $time === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields.'
    ]);
    exit;
}

// Validate service ID is numeric
if (!is_numeric($service_id) || $service_id < 1 || $service_id > 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid service selected.'
    ]);
    exit;
}

$service_id = intval($service_id);

// Check if the selected time slot is already taken
$slot_check = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE date = ? AND time = ?");
if (!$slot_check) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$slot_check->bind_param("ss", $date, $time);
$slot_check->execute();
$slot_result = $slot_check->get_result();
$row = $slot_result->fetch_assoc();
$slot_check->close();

if ($row && intval($row['total']) > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'That time slot is already taken. Please choose a different time.'
    ]);
    exit;
}

// Get service name from database
$service_name = '';
$service_query = $conn->prepare("SELECT service_name FROM services WHERE id = ?");
if ($service_query) {
    $service_query->bind_param("i", $service_id);
    $service_query->execute();
    $service_result = $service_query->get_result();
    if ($service_row = $service_result->fetch_assoc()) {
        $service_name = $service_row['service_name'];
    }
    $service_query->close();
}

// Fallback if service not found in database
if (empty($service_name)) {
    $service_map = [
        1 => 'Classic Set',
        2 => 'Classic Cat Eye',
        3 => 'Hybrid',
        4 => 'Hybrid Cat Eye',
        5 => 'Volume',
        6 => 'Volume Cat Eye'
    ];
    $service_name = $service_map[$service_id] ?? 'Unknown Service';
}

// Insert appointment
$sql = "INSERT INTO appointments (name, service, date, time) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("siss", $name, $service_id, $date, $time);

if ($stmt->execute()) {
    $appointment_id = $stmt->insert_id;
    
    // Create notification record
    $notification_sql = "INSERT INTO notifications (appointment_id, type, is_seen) VALUES (?, 'booking_created', 0)";
    $notification_stmt = $conn->prepare($notification_sql);
    if ($notification_stmt) {
        $notification_stmt->bind_param("i", $appointment_id);
        $notification_stmt->execute();
        $notification_stmt->close();
    }
    
    // Get unread count
    $unread_query = $conn->query("SELECT COUNT(*) AS unread FROM notifications WHERE is_seen = 0");
    $unread_count = 0;
    if ($unread_query) {
        $unread_row = $unread_query->fetch_assoc();
        $unread_count = intval($unread_row['unread']);
    }
    
    // Notify Node.js server
    function notify_node($event, $data, $target = 'admin') {
        $url = 'http://localhost:3001/notify';
        $payload = json_encode([
            'token' => 'change-this-token',
            'event' => $event,
            'data'  => $data,
            'target'=> $target
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 3
        ]);
        curl_exec($ch);
        $ch = null;
    }

    // Send push notifications
    function send_push_notification($booking_data, $unread_count) {
        $url = 'http://localhost:3001/send-push';
        $payload = json_encode([
            'token' => 'change-this-token',
            'bookingData' => $booking_data,
            'unreadCount' => $unread_count
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 5
        ]);
        curl_exec($ch);
        $ch = null;
    }

    // Prepare booking data
    $booking_data = [
        'name' => $name,
        'service' => $service_id,
        'service_name' => $service_name,
        'date' => $date,
        'time' => $time
    ];

    // Send notifications
    notify_node('booking_created', [
        'name'    => $name,
        'service' => $service_id,
        'service_name' => $service_name,
        'date'    => $date,
        'time'    => $time
    ]);

    send_push_notification($booking_data, $unread_count);

    echo json_encode([
        'success' => true,
        'message' => 'Your booking has been confirmed!',
        'booking' => [
            'name' => $name,
            'service' => $service_name,
            'date' => $date,
            'time' => $time,
            
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save booking: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>

<?php
// confirm_booking.php

// Include database connection
require_once 'db.php';

// Get booking ID from request
$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    // Fetch booking details
    $stmt = $conn->prepare("SELECT a.name, s.service_name, a.date, a.time FROM appointments a JOIN services s ON a.service = s.id WHERE a.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();

    if ($booking) {

        // ---- Notify Node.js server ----
        function notify_node($event, $data, $target = 'admin') {
            $url = 'http://localhost:3001/notify'; // Node server endpoint
            $payload = json_encode([
                'token' => 'change-this-token', // must match AUTH_TOKEN in server.js
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
            curl_close($ch);
        }

        // Notify admin dashboard
        notify_node('booking_confirmed', [
            'name'    => $booking['name'] ?? 'Unknown',
            'service' => $booking['service_name'] ?? 'Unknown',
            'date'    => $booking['date'] ?? '',
            'time'    => $booking['time'] ?? ''
        ], 'admin');

        // Redirect back to dashboard with success message
        header("Location: dashboard.php?confirmed=1");
        exit;
    } else {
        // ❌ Booking not found
        echo "Booking not found.";
    }
} else {
    echo "No booking ID provided.";
}

$conn->close();
?>

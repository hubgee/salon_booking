<?php
// filepath: c:\xampp\htdocs\salon_booking\get_appointments.php
// Returns appointments as JSON for dynamic table refresh

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once 'db.php';

// Verify connection
if (!$conn) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ]);
    exit();
}

// Fetch all appointments with service names, ordered by date and time (ascending)
// Don't select status - we're not displaying it
$sql = "SELECT a.id, a.name, s.service_name, a.date, a.time, a.created_at
        FROM appointments a 
        JOIN services s ON a.service = s.id 
        ORDER BY a.date ASC, a.time ASC";

error_log('[API] Fetching appointments with query: ' . $sql);

$result = $conn->query($sql);

if (!$result) {
    error_log('[API] Query failed: ' . $conn->error);
    echo json_encode([
        'success' => false,
        'error' => 'Query failed: ' . $conn->error
    ]);
    exit();
}

$appointments = [];
$row_count = 0;

while ($row = $result->fetch_assoc()) {
    $row_count++;
    $appointments[] = [
        'id' => intval($row['id']),
        'name' => htmlspecialchars($row['name']),
        'service_name' => htmlspecialchars($row['service_name']),
        'date' => htmlspecialchars($row['date']),
        'time' => htmlspecialchars($row['time']),
        'created_at' => $row['created_at']
    ];
}

error_log('[API] Fetched ' . $row_count . ' appointments successfully');

$response = [
    'success' => true,
    'appointments' => $appointments,
    'count' => count($appointments),
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response);

$conn->close();
?>
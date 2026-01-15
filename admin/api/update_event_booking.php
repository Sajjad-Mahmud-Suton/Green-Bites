<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    UPDATE EVENT BOOKING API (Admin)                       ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Updates an existing event booking                                      ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate CSRF
$csrf = $data['csrf_token'] ?? '';
if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$booking_id = intval($data['id'] ?? 0);
if ($booking_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit;
}

// Check if booking exists
$checkStmt = mysqli_prepare($conn, "SELECT id FROM event_bookings WHERE id = ?");
mysqli_stmt_bind_param($checkStmt, 'i', $booking_id);
mysqli_stmt_execute($checkStmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt))) {
    mysqli_stmt_close($checkStmt);
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit;
}
mysqli_stmt_close($checkStmt);

// Build dynamic update query
$updates = [];
$params = [];
$types = "";

$allowedFields = [
    'event_name' => 's',
    'event_type' => 's',
    'customer_name' => 's',
    'customer_phone' => 's',
    'customer_email' => 's',
    'event_date' => 's',
    'event_time' => 's',
    'end_time' => 's',
    'guest_count' => 'i',
    'venue' => 's',
    'package_type' => 's',
    'advance_amount' => 'd',
    'total_amount' => 'd',
    'payment_status' => 's',
    'booking_status' => 's',
    'special_requirements' => 's',
    'menu_items' => 's',
    'decorations' => 's',
    'notes' => 's'
];

foreach ($allowedFields as $field => $type) {
    if (isset($data[$field])) {
        $updates[] = "{$field} = ?";
        $params[] = $data[$field];
        $types .= $type;
    }
}

if (empty($updates)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
    exit;
}

// Add booking_id to params
$params[] = $booking_id;
$types .= 'i';

$sql = "UPDATE event_bookings SET " . implode(", ", $updates) . " WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'message' => 'Booking updated successfully!']);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Failed to update booking']);
}

mysqli_close($conn);
?>

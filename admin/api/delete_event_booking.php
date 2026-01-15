<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    DELETE EVENT BOOKING API (Admin)                       ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Deletes an event booking                                               ║
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

$stmt = mysqli_prepare($conn, "DELETE FROM event_bookings WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $booking_id);

if (mysqli_stmt_execute($stmt) && mysqli_affected_rows($conn) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'message' => 'Booking deleted successfully!']);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Booking not found or already deleted']);
}

mysqli_close($conn);
?>

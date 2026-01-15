<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    ADD EVENT BOOKING API (Admin)                          ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Creates a new event booking                                            ║
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

// Validate required fields
$required = ['event_name', 'customer_name', 'customer_phone', 'event_date', 'event_time'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: {$field}"]);
        exit;
    }
}

// Sanitize inputs
$event_name = trim($data['event_name']);
$event_type = $data['event_type'] ?? 'other';
$customer_name = trim($data['customer_name']);
$customer_phone = trim($data['customer_phone']);
$customer_email = !empty($data['customer_email']) ? trim($data['customer_email']) : null;
$event_date = $data['event_date'];
$event_time = $data['event_time'];
$end_time = !empty($data['end_time']) ? $data['end_time'] : null;
$guest_count = intval($data['guest_count'] ?? 1);
$venue = !empty($data['venue']) ? trim($data['venue']) : 'Green Bites Restaurant';
$package_type = $data['package_type'] ?? 'standard';
$advance_amount = floatval($data['advance_amount'] ?? 0);
$total_amount = floatval($data['total_amount'] ?? 0);
$payment_status = $data['payment_status'] ?? 'pending';
$booking_status = $data['booking_status'] ?? 'pending';
$special_requirements = !empty($data['special_requirements']) ? trim($data['special_requirements']) : null;
$menu_items = !empty($data['menu_items']) ? trim($data['menu_items']) : null;
$decorations = !empty($data['decorations']) ? trim($data['decorations']) : null;
$notes = !empty($data['notes']) ? trim($data['notes']) : null;
$created_by = $_SESSION['admin_id'];

// Validate event_type
$valid_event_types = ['birthday', 'wedding', 'corporate', 'anniversary', 'graduation', 'reunion', 'other'];
if (!in_array($event_type, $valid_event_types)) {
    $event_type = 'other';
}

// Validate package_type
$valid_packages = ['basic', 'standard', 'premium', 'custom'];
if (!in_array($package_type, $valid_packages)) {
    $package_type = 'standard';
}

// Validate payment_status
$valid_payment_statuses = ['pending', 'partial', 'paid', 'refunded'];
if (!in_array($payment_status, $valid_payment_statuses)) {
    $payment_status = 'pending';
}

// Validate booking_status
$valid_booking_statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
if (!in_array($booking_status, $valid_booking_statuses)) {
    $booking_status = 'pending';
}

// Insert booking
$stmt = mysqli_prepare($conn, 
    "INSERT INTO event_bookings (event_name, event_type, customer_name, customer_phone, customer_email, 
     event_date, event_time, end_time, guest_count, venue, package_type, advance_amount, total_amount, 
     payment_status, booking_status, special_requirements, menu_items, decorations, notes, created_by) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

mysqli_stmt_bind_param($stmt, 'ssssssssissddssssssi', 
    $event_name, $event_type, $customer_name, $customer_phone, $customer_email,
    $event_date, $event_time, $end_time, $guest_count, $venue, $package_type, 
    $advance_amount, $total_amount, $payment_status, $booking_status, 
    $special_requirements, $menu_items, $decorations, $notes, $created_by
);

if (mysqli_stmt_execute($stmt)) {
    $booking_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Event booking created successfully!',
        'booking_id' => $booking_id
    ]);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Failed to create booking: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>

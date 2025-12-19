<?php
/**
 * Place Order API Endpoint
 * ------------------------
 * Accepts POST with JSON body: items, total_price, student_id (optional), special_instructions (optional)
 * Validates user is logged in, validates order data, inserts into orders table.
 * Returns JSON response.
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Utility: JSON response and exit
function respond($success, $message, $extra = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $extra);
    echo json_encode($response);
    exit;
}

// Enforce POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    respond(false, 'Please login to place an order.');
}

$user_id = $_SESSION['user_id'];

// Read JSON body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    respond(false, 'Invalid request data.');
}

// Extract and validate data
$items = $data['items'] ?? [];
$total_price = floatval($data['total_price'] ?? 0);
$student_id = trim($data['student_id'] ?? '');
$special_instructions = trim($data['special_instructions'] ?? '');

// Validate items
if (empty($items) || !is_array($items)) {
    respond(false, 'Your cart is empty.');
}

// Validate total price
if ($total_price <= 0) {
    respond(false, 'Invalid order total.');
}

// Recalculate total from items for security
$calculated_total = 0;
foreach ($items as $item) {
    if (!isset($item['price']) || !isset($item['quantity'])) {
        respond(false, 'Invalid item in cart.');
    }
    $calculated_total += floatval($item['price']) * intval($item['quantity']);
}

// Allow small difference for rounding
if (abs($calculated_total - $total_price) > 1) {
    // Use calculated total for security
    $total_price = $calculated_total;
}

// Convert items to JSON string for storage
$items_json = json_encode($items, JSON_UNESCAPED_UNICODE);

try {
    // Insert order into database
    $sql = "INSERT INTO orders (user_id, items, student_id, special_instructions, total_price, status, order_date) 
            VALUES (?, ?, ?, ?, ?, 'Pending', NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        respond(false, 'Server error. Please try again later.');
    }

    mysqli_stmt_bind_param($stmt, 'isssd', $user_id, $items_json, $student_id, $special_instructions, $total_price);
    $success = mysqli_stmt_execute($stmt);

    if (!$success) {
        mysqli_stmt_close($stmt);
        respond(false, 'Failed to place order. Please try again.');
    }

    $order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    respond(true, 'Order placed successfully!', [
        'order_id' => $order_id
    ]);

} catch (Throwable $e) {
    respond(false, 'Unexpected server error. Please try again later.');
}

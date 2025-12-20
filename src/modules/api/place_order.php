<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║   ██████╗ ██████╗ ███████╗███████╗███╗   ██╗    ██████╗ ██╗████████╗███████╗║
 * ║  ██╔════╝ ██╔══██╗██╔════╝██╔════╝████╗  ██║    ██╔══██╗██║╚══██╔══╝██╔════╝║
 * ║  ██║  ███╗██████╔╝█████╗  █████╗  ██╔██╗ ██║    ██████╔╝██║   ██║   █████╗  ║
 * ║  ██║   ██║██╔══██╗██╔══╝  ██╔══╝  ██║╚██╗██║    ██╔══██╗██║   ██║   ██╔══╝  ║
 * ║  ╚██████╔╝██║  ██║███████╗███████╗██║ ╚████║    ██████╔╝██║   ██║   ███████╗║
 * ║   ╚═════╝ ╚═╝  ╚═╝╚══════╝╚══════╝╚═╝  ╚═══╝    ╚═════╝ ╚═╝   ╚═╝   ╚══════╝║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: place_order.php                                                    ║
 * ║  PATH: /api/place_order.php                                               ║
 * ║  DESCRIPTION: Order placement API endpoint                                ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  SECTIONS:                                                                ║
 * ║    1. Initialization                                                      ║
 * ║    2. Authentication Check                                                ║
 * ║    3. Request Body Parsing                                                ║
 * ║    4. Order Validation                                                    ║
 * ║    5. Price Verification                                                  ║
 * ║    6. Bill Number Generation                                              ║
 * ║    7. Order Insertion                                                     ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  ACCEPTS: POST (JSON body)                                                ║
 * ║    - items: array of cart items                                           ║
 * ║    - total_price: order total                                             ║
 * ║    - student_id: (optional)                                               ║
 * ║    - special_instructions: (optional)                                     ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  RETURNS: JSON { success: bool, message: string, order_id?, bill_number? }║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 1: INITIALIZATION
   ═══════════════════════════════════════════════════════════════════════════ */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../config/bootstrap.php';

session_start();
header('Content-Type: application/json');


/* ═══════════════════════════════════════════════════════════════════════════
   HELPER FUNCTION: JSON Response
   ═══════════════════════════════════════════════════════════════════════════ */

function respond($success, $message, $extra = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $extra);
    echo json_encode($response);
    exit;
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 2: AUTHENTICATION CHECK
   ═══════════════════════════════════════════════════════════════════════════ */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

if (!isset($_SESSION['user_id'])) {
    respond(false, 'Please login to place an order.');
}

$user_id = $_SESSION['user_id'];


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 3: REQUEST BODY PARSING
   ═══════════════════════════════════════════════════════════════════════════ */

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    respond(false, 'Invalid request data.');
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 4: ORDER VALIDATION
   ═══════════════════════════════════════════════════════════════════════════ */

$items = $data['items'] ?? [];
$total_price = floatval($data['total_price'] ?? 0);
$student_id = trim($data['student_id'] ?? '');
$special_instructions = trim($data['special_instructions'] ?? '');

if (empty($items) || !is_array($items)) {
    respond(false, 'Your cart is empty.');
}

if ($total_price <= 0) {
    respond(false, 'Invalid order total.');
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 5: PRICE VERIFICATION (Security)
   ═══════════════════════════════════════════════════════════════════════════ */

$calculated_total = 0;
foreach ($items as $item) {
    if (!isset($item['price']) || !isset($item['quantity'])) {
        respond(false, 'Invalid item in cart.');
    }
    $calculated_total += floatval($item['price']) * intval($item['quantity']);
}

// Allow small difference for rounding, use calculated for security
if (abs($calculated_total - $total_price) > 1) {
    $total_price = $calculated_total;
}

// Convert items to JSON string for storage
$items_json = json_encode($items, JSON_UNESCAPED_UNICODE);


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 6 & 7: BILL NUMBER GENERATION & ORDER INSERTION
   ═══════════════════════════════════════════════════════════════════════════ */

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
    
    // Generate unique bill number: GB-YYYYMMDD-XXXX
    $bill_number = 'GB-' . date('Ymd') . '-' . str_pad($order_id, 4, '0', STR_PAD_LEFT);
    
    // Update order with bill number
    $updateStmt = mysqli_prepare($conn, "UPDATE orders SET bill_number = ? WHERE id = ?");
    mysqli_stmt_bind_param($updateStmt, 'si', $bill_number, $order_id);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);

    respond(true, 'Order placed successfully!', [
        'order_id' => $order_id,
        'bill_number' => $bill_number
    ]);

} catch (Throwable $e) {
    respond(false, 'Unexpected server error. Please try again later.');
}

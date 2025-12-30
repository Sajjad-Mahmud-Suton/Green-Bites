<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    CANCEL ORDER API                                       ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Cancels user's pending order                                           ║
 * ║  • Restores stock quantity back to menu_items                             ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to cancel orders']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// First check if order exists and belongs to user
$stmt = mysqli_prepare($conn, "SELECT id, status, user_id, items FROM orders WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Check if order belongs to this user
if ($order['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'You can only cancel your own orders']);
    exit;
}

// Check if order is pending
$currentStatus = strtolower($order['status']);
if ($currentStatus !== 'pending') {
    $statusMessages = [
        'processing' => 'This order is already being processed and cannot be cancelled',
        'completed' => 'This order has been completed and cannot be cancelled',
        'delivered' => 'This order has been delivered and cannot be cancelled',
        'cancelled' => 'This order is already cancelled'
    ];
    $message = $statusMessages[$currentStatus] ?? 'This order cannot be cancelled';
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Cancel the order
$stmt = mysqli_prepare($conn, "UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $order_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    
    /* ═══════════════════════════════════════════════════════════════════════════
       RESTORE STOCK QUANTITY
       When order is cancelled, add back the quantity to menu_items
       ═══════════════════════════════════════════════════════════════════════════ */
    
    $items = json_decode($order['items'], true);
    if (is_array($items)) {
        foreach ($items as $item) {
            $item_id = intval($item['id'] ?? 0);
            $quantity = intval($item['quantity'] ?? 0);
            
            if ($item_id > 0 && $quantity > 0) {
                // Restore quantity back to stock
                $restoreStmt = mysqli_prepare($conn, "UPDATE menu_items SET quantity = quantity + ? WHERE id = ?");
                mysqli_stmt_bind_param($restoreStmt, 'ii', $quantity, $item_id);
                mysqli_stmt_execute($restoreStmt);
                mysqli_stmt_close($restoreStmt);
            }
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order cancelled successfully! Stock has been restored.'
    ]);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to cancel order. Please try again.'
    ]);
}

mysqli_close($conn);
?>

<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    UPDATE ORDER STATUS API (Admin)                        ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Updates order status from admin panel                                  ║
 * ║  • Restores stock when order is cancelled                                 ║
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
$csrf = $data['csrf_token'] ?? '';
if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$order_id = intval($data['order_id'] ?? 0);
$status = trim($data['status'] ?? '');

$valid_statuses = ['Pending', 'Processing', 'Completed', 'Delivered', 'Cancelled'];
if ($order_id <= 0 || !in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Get current order status and items before updating
$orderStmt = mysqli_prepare($conn, "SELECT status, items FROM orders WHERE id = ?");
mysqli_stmt_bind_param($orderStmt, 'i', $order_id);
mysqli_stmt_execute($orderStmt);
$orderResult = mysqli_stmt_get_result($orderStmt);
$order = mysqli_fetch_assoc($orderResult);
mysqli_stmt_close($orderStmt);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$previousStatus = $order['status'];

// Update order status
$stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $status, $order_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    
    /* ═══════════════════════════════════════════════════════════════════════════
       STOCK QUANTITY MANAGEMENT
       - Restore stock when order is cancelled (if not already cancelled)
       - Deduct stock when cancelled order is changed back to active status
       ═══════════════════════════════════════════════════════════════════════════ */
    
    $items = json_decode($order['items'], true);
    
    // If changing TO Cancelled (and wasn't already cancelled) - restore stock
    if ($status === 'Cancelled' && $previousStatus !== 'Cancelled') {
        if (is_array($items)) {
            foreach ($items as $item) {
                $item_id = intval($item['id'] ?? 0);
                $quantity = intval($item['quantity'] ?? 0);
                
                if ($item_id > 0 && $quantity > 0) {
                    $restoreStmt = mysqli_prepare($conn, "UPDATE menu_items SET quantity = quantity + ? WHERE id = ?");
                    mysqli_stmt_bind_param($restoreStmt, 'ii', $quantity, $item_id);
                    mysqli_stmt_execute($restoreStmt);
                    mysqli_stmt_close($restoreStmt);
                }
            }
        }
    }
    
    // If changing FROM Cancelled to active status - deduct stock again
    if ($previousStatus === 'Cancelled' && $status !== 'Cancelled') {
        if (is_array($items)) {
            foreach ($items as $item) {
                $item_id = intval($item['id'] ?? 0);
                $quantity = intval($item['quantity'] ?? 0);
                
                if ($item_id > 0 && $quantity > 0) {
                    $deductStmt = mysqli_prepare($conn, "UPDATE menu_items SET quantity = GREATEST(0, quantity - ?) WHERE id = ?");
                    mysqli_stmt_bind_param($deductStmt, 'ii', $quantity, $item_id);
                    mysqli_stmt_execute($deductStmt);
                    mysqli_stmt_close($deductStmt);
                }
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Status updated!']);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Failed to update']);
}

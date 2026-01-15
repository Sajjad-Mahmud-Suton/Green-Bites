<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    UPDATE ORDER STATUS API (Admin)                        ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Updates order status from admin panel                                  ║
 * ║  • Restores stock when order is cancelled                                 ║
 * ║  • Calculates and records profit when order is delivered                  ║
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
$orderStmt = mysqli_prepare($conn, "SELECT status, items, order_date FROM orders WHERE id = ?");
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
    
    $items = json_decode($order['items'], true);
    
    /* ═══════════════════════════════════════════════════════════════════════════
       PROFIT CALCULATION - Calculate profit when order is delivered
       ═══════════════════════════════════════════════════════════════════════════ */
    
    if ($status === 'Delivered' && $previousStatus !== 'Delivered') {
        // Check if profit already calculated for this order
        $checkProfitStmt = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM profits WHERE order_id = ?");
        mysqli_stmt_bind_param($checkProfitStmt, 'i', $order_id);
        mysqli_stmt_execute($checkProfitStmt);
        $profitCheck = mysqli_fetch_assoc(mysqli_stmt_get_result($checkProfitStmt));
        mysqli_stmt_close($checkProfitStmt);
        
        if ($profitCheck['cnt'] == 0 && is_array($items)) {
            foreach ($items as $item) {
                $item_id = intval($item['id'] ?? 0);
                $item_name = $item['title'] ?? $item['name'] ?? 'Unknown Item';
                $quantity = intval($item['quantity'] ?? 1);
                $selling_price = floatval($item['price'] ?? 0);
                
                // Get buying price from menu_items
                $buying_price = 0;
                if ($item_id > 0) {
                    $menuStmt = mysqli_prepare($conn, "SELECT buying_price FROM menu_items WHERE id = ?");
                    mysqli_stmt_bind_param($menuStmt, 'i', $item_id);
                    mysqli_stmt_execute($menuStmt);
                    $menuResult = mysqli_fetch_assoc(mysqli_stmt_get_result($menuStmt));
                    mysqli_stmt_close($menuStmt);
                    
                    if ($menuResult) {
                        $buying_price = floatval($menuResult['buying_price']);
                    }
                }
                
                // If no buying price set, assume 80% of selling price
                if ($buying_price <= 0) {
                    $buying_price = round($selling_price * 0.8, 2);
                }
                
                $revenue = $selling_price * $quantity;
                $investment = $buying_price * $quantity;
                $profit_amount = $revenue - $investment;
                
                // Insert profit record
                $profitStmt = mysqli_prepare($conn, 
                    "INSERT INTO profits (order_id, product_id, product_name, quantity, selling_price, buying_price, profit_amount, revenue, investment, calculated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                mysqli_stmt_bind_param($profitStmt, 'iisiddddd', 
                    $order_id, 
                    $item_id, 
                    $item_name, 
                    $quantity, 
                    $selling_price, 
                    $buying_price, 
                    $profit_amount,
                    $revenue,
                    $investment
                );
                mysqli_stmt_execute($profitStmt);
                mysqli_stmt_close($profitStmt);
            }
        }
    }
    
    // If order is changed FROM Delivered to something else, remove profit records
    if ($previousStatus === 'Delivered' && $status !== 'Delivered') {
        $deleteProfitStmt = mysqli_prepare($conn, "DELETE FROM profits WHERE order_id = ?");
        mysqli_stmt_bind_param($deleteProfitStmt, 'i', $order_id);
        mysqli_stmt_execute($deleteProfitStmt);
        mysqli_stmt_close($deleteProfitStmt);
    }
    
    /* ═══════════════════════════════════════════════════════════════════════════
       STOCK QUANTITY MANAGEMENT
       - Restore stock when order is cancelled (if not already cancelled)
       - Deduct stock when cancelled order is changed back to active status
       ═══════════════════════════════════════════════════════════════════════════ */
    
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

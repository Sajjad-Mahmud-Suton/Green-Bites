<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  GREEN BITES - Admin Manual Order Placement API                           ║
 * ║  Allows admin to place orders on behalf of customers                      ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * 
 * ACCEPTS: POST (JSON body)
 *   - items: array of {id, quantity}
 *   - customer_name: (optional) customer name for the order
 *   - special_instructions: (optional) notes
 * 
 * RETURNS: JSON { success, message, order_id, bill_number }
 */

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../../db.php';

// Verify admin is logged in
if (!isset($_SESSION['admin_id'])) {
    jsonResponse(false, 'Unauthorized access');
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    jsonResponse(false, 'Invalid request data');
}

// Get order items
$items = $data['items'] ?? [];
$customer_name = trim($data['customer_name'] ?? 'Walk-in Customer');
$special_instructions = trim($data['special_instructions'] ?? '');

if (empty($items) || !is_array($items)) {
    jsonResponse(false, 'No items in order');
}

// Validate and calculate order total
$orderItems = [];
$totalPrice = 0;
$stockErrors = [];

foreach ($items as $item) {
    $item_id = intval($item['id'] ?? 0);
    $quantity = intval($item['quantity'] ?? 0);
    
    if ($item_id <= 0 || $quantity <= 0) {
        continue;
    }
    
    // Get menu item details
    $stmt = mysqli_prepare($conn, "SELECT id, title, price, discount_percent, quantity, buying_price FROM menu_items WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $menuItem = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$menuItem) {
        $stockErrors[] = "Item #$item_id not found";
        continue;
    }
    
    // Check stock
    if ($menuItem['quantity'] < $quantity) {
        if ($menuItem['quantity'] <= 0) {
            $stockErrors[] = $menuItem['title'] . ' is out of stock';
        } else {
            $stockErrors[] = $menuItem['title'] . ': only ' . $menuItem['quantity'] . ' available';
        }
        continue;
    }
    
    // Calculate final price with discount
    $originalPrice = floatval($menuItem['price']);
    $discount = intval($menuItem['discount_percent'] ?? 0);
    $finalPrice = $discount > 0 ? $originalPrice - ($originalPrice * $discount / 100) : $originalPrice;
    
    $orderItems[] = [
        'id' => $item_id,
        'title' => $menuItem['title'],
        'price' => $finalPrice,
        'quantity' => $quantity,
        'buying_price' => floatval($menuItem['buying_price'] ?? 0)
    ];
    
    $totalPrice += $finalPrice * $quantity;
}

if (!empty($stockErrors)) {
    jsonResponse(false, 'Stock issues: ' . implode(', ', $stockErrors));
}

if (empty($orderItems)) {
    jsonResponse(false, 'No valid items in order');
}

// Convert items to JSON for storage
$itemsJson = json_encode($orderItems, JSON_UNESCAPED_UNICODE);

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // Insert order with is_manual_order flag
    // For manual orders, we use the admin_id as the user_id (or could use NULL)
    $sql = "INSERT INTO orders (user_id, items, student_id, special_instructions, total_price, payment_method, status, is_manual_order, manual_order_by, order_date) 
            VALUES (NULL, ?, ?, ?, ?, 'Pay at Counter', 'Pending', 1, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare order insert');
    }
    
    mysqli_stmt_bind_param($stmt, 'sssdi', $itemsJson, $customer_name, $special_instructions, $totalPrice, $admin_id);
    $success = mysqli_stmt_execute($stmt);
    
    if (!$success) {
        throw new Exception('Failed to insert order');
    }
    
    $order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    // Generate bill number
    $bill_number = 'GB-' . date('Ymd') . '-' . str_pad($order_id, 4, '0', STR_PAD_LEFT);
    
    // Update with bill number
    $updateStmt = mysqli_prepare($conn, "UPDATE orders SET bill_number = ? WHERE id = ?");
    mysqli_stmt_bind_param($updateStmt, 'si', $bill_number, $order_id);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
    
    // Reduce stock for each item
    foreach ($orderItems as $orderItem) {
        $reduceStmt = mysqli_prepare($conn, "UPDATE menu_items SET quantity = quantity - ? WHERE id = ?");
        mysqli_stmt_bind_param($reduceStmt, 'ii', $orderItem['quantity'], $orderItem['id']);
        mysqli_stmt_execute($reduceStmt);
        mysqli_stmt_close($reduceStmt);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    jsonResponse(true, 'Manual order placed successfully', [
        'order_id' => $order_id,
        'bill_number' => $bill_number,
        'total' => $totalPrice,
        'items_count' => count($orderItems),
        'placed_by' => $admin_name
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    jsonResponse(false, 'Failed to place order: ' . $e->getMessage());
}

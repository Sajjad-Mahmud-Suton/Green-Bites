<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    GET USER ORDERS API                                    ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Returns orders for logged-in user                                      ║
 * ║  • Used for real-time order status updates                                ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Parse items JSON
    $row['items_parsed'] = json_decode($row['items'], true);
    $orders[] = $row;
}
mysqli_stmt_close($stmt);

// Get statistics
$totalOrders = count($orders);
$totalSpent = 0;
$pendingOrders = 0;

foreach ($orders as $order) {
    if (strtolower($order['status']) !== 'cancelled') {
        $totalSpent += floatval($order['total_price']);
    }
    if (strtolower($order['status']) === 'pending') {
        $pendingOrders++;
    }
}

echo json_encode([
    'success' => true,
    'orders' => $orders,
    'stats' => [
        'total_orders' => $totalOrders,
        'total_spent' => $totalSpent,
        'pending_orders' => $pendingOrders
    ]
]);

mysqli_close($conn);
?>

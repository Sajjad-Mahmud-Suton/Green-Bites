<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                          GET ORDERS API                                   ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  Returns all orders for admin panel auto-refresh                          ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Fetch orders with user info
$sql = "SELECT o.*, u.full_name, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.order_date DESC 
        LIMIT 100";

$result = mysqli_query($conn, $sql);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

echo json_encode([
    'success' => true,
    'orders' => $orders
]);

mysqli_close($conn);
?>

<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    GET DASHBOARD STATS API (Admin)                        ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Returns real-time dashboard statistics                                 ║
 * ║  • Used for auto-updating stat cards                                      ║
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

$stats = [];

// Total menu items
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items");
$stats['menu_items'] = mysqli_fetch_assoc($result)['count'];

// Total orders
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = mysqli_fetch_assoc($result)['count'];

// Pending orders
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'");
$stats['pending_orders'] = mysqli_fetch_assoc($result)['count'];

// Processing orders
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Processing'");
$stats['processing_orders'] = mysqli_fetch_assoc($result)['count'];

// Total users
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$stats['total_users'] = mysqli_fetch_assoc($result)['count'];

// Total revenue
$result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE status != 'Cancelled'");
$stats['total_revenue'] = floatval(mysqli_fetch_assoc($result)['total'] ?? 0);

// Today's orders
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()");
$stats['today_orders'] = mysqli_fetch_assoc($result)['count'];

// Today's revenue
$result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE DATE(order_date) = CURDATE() AND status != 'Cancelled'");
$stats['today_revenue'] = floatval(mysqli_fetch_assoc($result)['total'] ?? 0);

// Unseen complaints count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE is_seen = 0");
$stats['new_complaints'] = mysqli_fetch_assoc($result)['count'];

// Total complaints
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints");
$stats['total_complaints'] = mysqli_fetch_assoc($result)['count'];

// Low stock items
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE quantity <= 5 AND quantity > 0");
$stats['low_stock'] = mysqli_fetch_assoc($result)['count'];

// Out of stock items
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE quantity = 0");
$stats['out_of_stock'] = mysqli_fetch_assoc($result)['count'];

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'timestamp' => date('Y-m-d H:i:s')
]);

mysqli_close($conn);
?>

<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    GET MENU ITEMS API (Admin)                             ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Returns all menu items with stock info                                 ║
 * ║  • Used for real-time stock updates in admin panel                        ║
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

// Fetch all menu items with category info
$sql = "SELECT m.*, c.name as category_name 
        FROM menu_items m 
        LEFT JOIN categories c ON m.category_id = c.id 
        ORDER BY m.id DESC";

$result = mysqli_query($conn, $sql);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

// Get stock statistics
$stats = [];

// Low stock count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE quantity <= 5 AND quantity > 0");
$stats['low_stock'] = mysqli_fetch_assoc($result)['count'];

// Out of stock count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE quantity = 0");
$stats['out_of_stock'] = mysqli_fetch_assoc($result)['count'];

// Total items
$stats['total_items'] = count($items);

echo json_encode([
    'success' => true,
    'items' => $items,
    'stats' => $stats
]);

mysqli_close($conn);
?>

<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    CHECK STOCK API                                        ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Returns available quantity for a menu item                             ║
 * ║  • Used for real-time stock validation in cart                            ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

$item_id = intval($_GET['id'] ?? 0);

if ($item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, title, quantity, is_available FROM menu_items WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

echo json_encode([
    'success' => true,
    'item_id' => $item['id'],
    'title' => $item['title'],
    'quantity' => intval($item['quantity']),
    'is_available' => $item['is_available'] == 1,
    'in_stock' => intval($item['quantity']) > 0
]);

mysqli_close($conn);
?>

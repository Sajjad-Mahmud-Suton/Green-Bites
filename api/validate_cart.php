<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    VALIDATE CART API                                      ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Validates all cart items against available stock                       ║
 * ║  • Returns which items have insufficient stock                            ║
 * ║  • Used before checkout to prevent over-ordering                          ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

// Accept both GET and POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$items = $data['items'] ?? [];

if (empty($items) || !is_array($items)) {
    echo json_encode(['success' => false, 'message' => 'No items provided']);
    exit;
}

$validationResults = [];
$hasErrors = false;

foreach ($items as $item) {
    $item_id = intval($item['id'] ?? 0);
    $requested_qty = intval($item['quantity'] ?? 0);
    
    if ($item_id <= 0) continue;
    
    // Get current stock
    $stmt = mysqli_prepare($conn, "SELECT id, title, quantity, is_available FROM menu_items WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $menuItem = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$menuItem) {
        $validationResults[] = [
            'id' => $item_id,
            'title' => $item['title'] ?? 'Unknown Item',
            'requested' => $requested_qty,
            'available' => 0,
            'valid' => false,
            'message' => 'Item no longer exists'
        ];
        $hasErrors = true;
        continue;
    }
    
    $availableQty = intval($menuItem['quantity']);
    $isAvailable = $menuItem['is_available'] == 1;
    
    $isValid = $isAvailable && $availableQty >= $requested_qty;
    
    if (!$isValid) {
        $hasErrors = true;
    }
    
    $validationResults[] = [
        'id' => $item_id,
        'title' => $menuItem['title'],
        'requested' => $requested_qty,
        'available' => $availableQty,
        'valid' => $isValid,
        'message' => !$isAvailable 
            ? 'Item is currently unavailable' 
            : ($availableQty <= 0 
                ? 'Out of stock' 
                : ($availableQty < $requested_qty 
                    ? "Only {$availableQty} available" 
                    : 'OK'))
    ];
}

echo json_encode([
    'success' => true,
    'valid' => !$hasErrors,
    'items' => $validationResults,
    'message' => $hasErrors 
        ? 'Some items have insufficient stock. Please adjust your cart.' 
        : 'All items available'
]);

mysqli_close($conn);
?>

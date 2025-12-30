<?php
/**
 * Update Menu Item Quantity API
 * Allows admin to quickly increase/decrease stock quantity
 */
session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

// Check admin auth
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Verify CSRF
$csrf = $input['csrf_token'] ?? '';
if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$id = intval($input['id'] ?? 0);
$change = intval($input['change'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

// Get current quantity
$stmt = mysqli_prepare($conn, "SELECT quantity, title FROM menu_items WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

$currentQty = intval($item['quantity'] ?? 0);
$newQty = $currentQty + $change;

// Don't allow negative quantity
if ($newQty < 0) {
    $newQty = 0;
}

// Update quantity
$stmt = mysqli_prepare($conn, "UPDATE menu_items SET quantity = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $newQty, $id);

if (mysqli_stmt_execute($stmt)) {
    $statusMsg = '';
    if ($newQty == 0) {
        $statusMsg = ' - Item is now OUT OF STOCK!';
    } elseif ($newQty <= 5) {
        $statusMsg = ' - Low stock warning!';
    }
    
    echo json_encode([
        'success' => true, 
        'message' => "Quantity updated to {$newQty}{$statusMsg}",
        'new_quantity' => $newQty,
        'item_title' => $item['title']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
}
mysqli_stmt_close($stmt);

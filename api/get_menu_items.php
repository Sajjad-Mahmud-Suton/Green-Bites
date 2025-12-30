<?php
/**
 * Get Menu Items API - For real-time stock updates on user facing pages
 */

session_start();
header('Content-Type: application/json');
require_once '../db.php';

$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$itemId = isset($_GET['item_id']) ? intval($_GET['item_id']) : null;

try {
    if ($itemId) {
        // Get single item
        $stmt = mysqli_prepare($conn, "SELECT id, title, price, quantity, is_available FROM menu_items WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $itemId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($item) {
            echo json_encode([
                'success' => true,
                'item' => [
                    'id' => intval($item['id']),
                    'title' => $item['title'],
                    'price' => floatval($item['price']),
                    'quantity' => intval($item['quantity']),
                    'is_available' => intval($item['is_available']),
                    'is_stockout' => intval($item['quantity']) == 0,
                    'is_low_stock' => intval($item['quantity']) > 0 && intval($item['quantity']) <= 5
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Item not found']);
        }
    } else if ($categoryId) {
        // Get items by category
        $stmt = mysqli_prepare($conn, "SELECT id, title, price, quantity, is_available FROM menu_items WHERE category_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $items = [];
        while ($item = mysqli_fetch_assoc($result)) {
            $items[] = [
                'id' => intval($item['id']),
                'title' => $item['title'],
                'price' => floatval($item['price']),
                'quantity' => intval($item['quantity']),
                'is_available' => intval($item['is_available']),
                'is_stockout' => intval($item['quantity']) == 0,
                'is_low_stock' => intval($item['quantity']) > 0 && intval($item['quantity']) <= 5
            ];
        }
        mysqli_stmt_close($stmt);
        
        echo json_encode(['success' => true, 'items' => $items]);
    } else {
        // Get all items
        $result = mysqli_query($conn, "SELECT id, title, price, quantity, is_available FROM menu_items ORDER BY title ASC");
        
        $items = [];
        while ($item = mysqli_fetch_assoc($result)) {
            $items[] = [
                'id' => intval($item['id']),
                'title' => $item['title'],
                'price' => floatval($item['price']),
                'quantity' => intval($item['quantity']),
                'is_available' => intval($item['is_available']),
                'is_stockout' => intval($item['quantity']) == 0,
                'is_low_stock' => intval($item['quantity']) > 0 && intval($item['quantity']) <= 5
            ];
        }
        
        echo json_encode(['success' => true, 'items' => $items]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

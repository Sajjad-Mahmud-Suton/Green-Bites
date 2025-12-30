<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║  ███╗   ███╗███████╗███╗   ██╗██╗   ██╗                                   ║
 * ║  ████╗ ████║██╔════╝████╗  ██║██║   ██║                                   ║
 * ║  ██╔████╔██║█████╗  ██╔██╗ ██║██║   ██║                                   ║
 * ║  ██║╚██╔╝██║██╔══╝  ██║╚██╗██║██║   ██║                                   ║
 * ║  ██║ ╚═╝ ██║███████╗██║ ╚████║╚██████╔╝                                   ║
 * ║  ╚═╝     ╚═╝╚══════╝╚═╝  ╚═══╝ ╚═════╝                                    ║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: menu.php                                                           ║
 * ║  PATH: /admin/includes/menu.php                                           ║
 * ║  DESCRIPTION: Menu management functions for admin panel                   ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FEATURES:                                                                ║
 * ║    • Add, Edit, Delete menu items                                         ║
 * ║    • Manage stock quantities                                              ║
 * ║    • Category-wise menu listing                                           ║
 * ║    • Low stock alerts                                                     ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Prevent direct access
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not allowed');
}

/* ═══════════════════════════════════════════════════════════════════════════
   STOCK THRESHOLD CONSTANTS
   ═══════════════════════════════════════════════════════════════════════════ */

define('LOW_STOCK_THRESHOLD', 5);
define('OUT_OF_STOCK_THRESHOLD', 0);

/* ═══════════════════════════════════════════════════════════════════════════
   MENU ITEM FETCHING FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get all menu items with category names
 * @param mysqli $conn Database connection
 * @return array List of menu items
 */
function getAllMenuItems($conn) {
    $sql = "SELECT m.*, c.name as category_name 
            FROM menu_items m 
            LEFT JOIN categories c ON m.category_id = c.id 
            ORDER BY m.id DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    return $items;
}

/**
 * Get menu items by category
 * @param mysqli $conn Database connection
 * @param int $categoryId Category ID
 * @return array Filtered menu items
 */
function getMenuItemsByCategory($conn, $categoryId) {
    $sql = "SELECT m.*, c.name as category_name 
            FROM menu_items m 
            LEFT JOIN categories c ON m.category_id = c.id 
            WHERE m.category_id = ?
            ORDER BY m.title ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $categoryId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $items;
}

/**
 * Get single menu item by ID
 * @param mysqli $conn Database connection
 * @param int $itemId Menu item ID
 * @return array|null Menu item or null
 */
function getMenuItemById($conn, $itemId) {
    $stmt = mysqli_prepare($conn, "SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN categories c ON m.category_id = c.id WHERE m.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $itemId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $item = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $item;
}

/* ═══════════════════════════════════════════════════════════════════════════
   MENU ITEM CRUD FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Add new menu item
 * @param mysqli $conn Database connection
 * @param array $data Menu item data
 * @return int|false New item ID or false
 */
function addMenuItem($conn, $data) {
    $title = trim($data['title'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $imageUrl = trim($data['image_url'] ?? '');
    $categoryId = intval($data['category_id'] ?? 0);
    $description = trim($data['description'] ?? '');
    $quantity = intval($data['quantity'] ?? 0);
    
    if (empty($title) || $price <= 0 || $categoryId <= 0) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "INSERT INTO menu_items (title, price, image_url, category_id, description, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sdissi', $title, $price, $imageUrl, $categoryId, $description, $quantity);
    
    if (mysqli_stmt_execute($stmt)) {
        $newId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return $newId;
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Update menu item
 * @param mysqli $conn Database connection
 * @param int $itemId Item ID to update
 * @param array $data Updated data
 * @return bool Success status
 */
function updateMenuItem($conn, $itemId, $data) {
    $title = trim($data['title'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $imageUrl = trim($data['image_url'] ?? '');
    $categoryId = intval($data['category_id'] ?? 0);
    $description = trim($data['description'] ?? '');
    $quantity = intval($data['quantity'] ?? 0);
    
    if (empty($title) || $price <= 0 || $categoryId <= 0 || $itemId <= 0) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE menu_items SET title = ?, price = ?, image_url = ?, category_id = ?, description = ?, quantity = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sdissii', $title, $price, $imageUrl, $categoryId, $description, $quantity, $itemId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Delete menu item
 * @param mysqli $conn Database connection
 * @param int $itemId Item ID to delete
 * @return bool Success status
 */
function deleteMenuItem($conn, $itemId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM menu_items WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $itemId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}

/* ═══════════════════════════════════════════════════════════════════════════
   STOCK MANAGEMENT FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Update item quantity
 * @param mysqli $conn Database connection
 * @param int $itemId Menu item ID
 * @param int $quantity New quantity
 * @return bool Success status
 */
function updateItemQuantity($conn, $itemId, $quantity) {
    $quantity = max(0, intval($quantity));
    
    $stmt = mysqli_prepare($conn, "UPDATE menu_items SET quantity = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $quantity, $itemId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Decrease item quantity (for orders)
 * @param mysqli $conn Database connection
 * @param int $itemId Menu item ID
 * @param int $amount Amount to decrease
 * @return bool Success status
 */
function decreaseItemQuantity($conn, $itemId, $amount) {
    $amount = max(0, intval($amount));
    
    $stmt = mysqli_prepare($conn, "UPDATE menu_items SET quantity = GREATEST(0, quantity - ?) WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $amount, $itemId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Increase item quantity (for restocking)
 * @param mysqli $conn Database connection
 * @param int $itemId Menu item ID
 * @param int $amount Amount to add
 * @return bool Success status
 */
function increaseItemQuantity($conn, $itemId, $amount) {
    $amount = max(0, intval($amount));
    
    $stmt = mysqli_prepare($conn, "UPDATE menu_items SET quantity = quantity + ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $amount, $itemId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Get low stock items
 * @param mysqli $conn Database connection
 * @return array Items with low stock
 */
function getLowStockItems($conn) {
    $threshold = LOW_STOCK_THRESHOLD;
    $sql = "SELECT m.*, c.name as category_name 
            FROM menu_items m 
            LEFT JOIN categories c ON m.category_id = c.id 
            WHERE m.quantity <= ? AND m.quantity > 0
            ORDER BY m.quantity ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $threshold);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $items;
}

/**
 * Get out of stock items
 * @param mysqli $conn Database connection
 * @return array Out of stock items
 */
function getOutOfStockItems($conn) {
    $sql = "SELECT m.*, c.name as category_name 
            FROM menu_items m 
            LEFT JOIN categories c ON m.category_id = c.id 
            WHERE m.quantity = 0
            ORDER BY m.title ASC";
    
    $result = mysqli_query($conn, $sql);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    return $items;
}

/* ═══════════════════════════════════════════════════════════════════════════
   MENU STATISTICS FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get menu statistics
 * @param mysqli $conn Database connection
 * @return array Menu statistics
 */
function getMenuStatistics($conn) {
    $stats = [];
    
    // Total menu items
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items");
    $stats['total_items'] = mysqli_fetch_assoc($result)['count'];
    
    // Available items
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE is_available = 1 AND quantity > 0");
    $stats['available_items'] = mysqli_fetch_assoc($result)['count'];
    
    // Low stock count
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE quantity <= " . LOW_STOCK_THRESHOLD . " AND quantity > 0");
    $stats['low_stock'] = mysqli_fetch_assoc($result)['count'];
    
    // Out of stock count
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE quantity = 0");
    $stats['out_of_stock'] = mysqli_fetch_assoc($result)['count'];
    
    // Total categories with items
    $result = mysqli_query($conn, "SELECT COUNT(DISTINCT category_id) as count FROM menu_items");
    $stats['categories_used'] = mysqli_fetch_assoc($result)['count'];
    
    return $stats;
}

/**
 * Get stock status badge class
 * @param int $quantity Item quantity
 * @return string CSS class name
 */
function getStockBadgeClass($quantity) {
    if ($quantity <= 0) {
        return 'bg-danger';
    } elseif ($quantity <= LOW_STOCK_THRESHOLD) {
        return 'bg-warning text-dark';
    }
    return 'bg-success';
}

/**
 * Get stock status text
 * @param int $quantity Item quantity
 * @return string Status text
 */
function getStockStatusText($quantity) {
    if ($quantity <= 0) {
        return 'Out of Stock';
    } elseif ($quantity <= LOW_STOCK_THRESHOLD) {
        return 'Low Stock';
    }
    return 'In Stock';
}

/* ═══════════════════════════════════════════════════════════════════════════
   END OF FILE
   ═══════════════════════════════════════════════════════════════════════════ */
?>

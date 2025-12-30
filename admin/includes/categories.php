<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║   ██████╗ █████╗ ████████╗███████╗ ██████╗  ██████╗ ██████╗ ██╗███████╗   ║
 * ║  ██╔════╝██╔══██╗╚══██╔══╝██╔════╝██╔════╝ ██╔═══██╗██╔══██╗██║██╔════╝   ║
 * ║  ██║     ███████║   ██║   █████╗  ██║  ███╗██║   ██║██████╔╝██║█████╗     ║
 * ║  ██║     ██╔══██║   ██║   ██╔══╝  ██║   ██║██║   ██║██╔══██╗██║██╔══╝     ║
 * ║  ╚██████╗██║  ██║   ██║   ███████╗╚██████╔╝╚██████╔╝██║  ██║██║███████╗   ║
 * ║   ╚═════╝╚═╝  ╚═╝   ╚═╝   ╚══════╝ ╚═════╝  ╚═════╝ ╚═╝  ╚═╝╚═╝╚══════╝   ║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: categories.php                                                     ║
 * ║  PATH: /admin/includes/categories.php                                     ║
 * ║  DESCRIPTION: Category management functions for admin panel               ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FEATURES:                                                                ║
 * ║    • Add, Edit, Delete categories                                         ║
 * ║    • Category statistics                                                  ║
 * ║    • Category-wise item count                                             ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Prevent direct access
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not allowed');
}

/* ═══════════════════════════════════════════════════════════════════════════
   CATEGORY FETCHING FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get all categories
 * @param mysqli $conn Database connection
 * @return array List of categories
 */
function getAllCategories($conn) {
    $sql = "SELECT * FROM categories ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    return $categories;
}

/**
 * Get all categories with item count
 * @param mysqli $conn Database connection
 * @return array Categories with item counts
 */
function getCategoriesWithItemCount($conn) {
    $sql = "SELECT c.*, COUNT(m.id) as item_count 
            FROM categories c 
            LEFT JOIN menu_items m ON c.id = m.category_id 
            GROUP BY c.id 
            ORDER BY c.name ASC";
    
    $result = mysqli_query($conn, $sql);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    return $categories;
}

/**
 * Get single category by ID
 * @param mysqli $conn Database connection
 * @param int $categoryId Category ID
 * @return array|null Category data or null
 */
function getCategoryById($conn, $categoryId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $categoryId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $category = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $category;
}

/**
 * Check if category name exists
 * @param mysqli $conn Database connection
 * @param string $name Category name
 * @param int $excludeId ID to exclude (for updates)
 * @return bool True if exists
 */
function categoryNameExists($conn, $name, $excludeId = 0) {
    $sql = "SELECT id FROM categories WHERE name = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $name, $excludeId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

/* ═══════════════════════════════════════════════════════════════════════════
   CATEGORY CRUD FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Add new category
 * @param mysqli $conn Database connection
 * @param string $name Category name
 * @param string $description Category description
 * @param string $imageUrl Category image URL
 * @return int|false New category ID or false
 */
function addCategory($conn, $name, $description = '', $imageUrl = '') {
    $name = trim($name);
    $description = trim($description);
    $imageUrl = trim($imageUrl);
    
    if (empty($name)) {
        return false;
    }
    
    if (categoryNameExists($conn, $name)) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, description, image_url) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sss', $name, $description, $imageUrl);
    
    if (mysqli_stmt_execute($stmt)) {
        $newId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return $newId;
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Update category
 * @param mysqli $conn Database connection
 * @param int $categoryId Category ID
 * @param string $name New name
 * @param string $description New description
 * @param string $imageUrl New image URL
 * @return bool Success status
 */
function updateCategory($conn, $categoryId, $name, $description = '', $imageUrl = '') {
    $name = trim($name);
    $description = trim($description);
    $imageUrl = trim($imageUrl);
    
    if (empty($name) || $categoryId <= 0) {
        return false;
    }
    
    if (categoryNameExists($conn, $name, $categoryId)) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE categories SET name = ?, description = ?, image_url = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sssi', $name, $description, $imageUrl, $categoryId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Delete category (only if empty)
 * @param mysqli $conn Database connection
 * @param int $categoryId Category ID
 * @return array Result with success status and message
 */
function deleteCategory($conn, $categoryId) {
    // Check if category has items
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $categoryId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = mysqli_fetch_assoc($result)['count'];
    mysqli_stmt_close($stmt);
    
    if ($count > 0) {
        return [
            'success' => false,
            'message' => "Cannot delete category. It contains {$count} menu item(s). Please move or delete them first."
        ];
    }
    
    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $categoryId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return [
        'success' => $success,
        'message' => $success ? 'Category deleted successfully!' : 'Failed to delete category.'
    ];
}

/* ═══════════════════════════════════════════════════════════════════════════
   CATEGORY STATISTICS FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get category statistics
 * @param mysqli $conn Database connection
 * @return array Category statistics
 */
function getCategoryStatistics($conn) {
    $stats = [];
    
    // Total categories
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories");
    $stats['total_categories'] = mysqli_fetch_assoc($result)['count'];
    
    // Categories with items
    $result = mysqli_query($conn, "SELECT COUNT(DISTINCT category_id) as count FROM menu_items");
    $stats['categories_with_items'] = mysqli_fetch_assoc($result)['count'];
    
    // Empty categories
    $stats['empty_categories'] = $stats['total_categories'] - $stats['categories_with_items'];
    
    // Average items per category
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items");
    $totalItems = mysqli_fetch_assoc($result)['count'];
    $stats['avg_items_per_category'] = $stats['categories_with_items'] > 0 
        ? round($totalItems / $stats['categories_with_items'], 1) 
        : 0;
    
    return $stats;
}

/**
 * Get category with most items
 * @param mysqli $conn Database connection
 * @return array|null Top category or null
 */
function getTopCategory($conn) {
    $sql = "SELECT c.*, COUNT(m.id) as item_count 
            FROM categories c 
            LEFT JOIN menu_items m ON c.id = m.category_id 
            GROUP BY c.id 
            ORDER BY item_count DESC 
            LIMIT 1";
    
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

/**
 * Get category sales data
 * @param mysqli $conn Database connection
 * @return array Category sales data
 */
function getCategorySalesData($conn) {
    $categories = getAllCategories($conn);
    $salesData = [];
    
    // Get all orders
    $result = mysqli_query($conn, "SELECT items FROM orders WHERE status != 'Cancelled'");
    
    $categoryRevenue = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items = json_decode($row['items'], true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $categoryId = intval($item['category_id'] ?? 0);
                if ($categoryId > 0) {
                    if (!isset($categoryRevenue[$categoryId])) {
                        $categoryRevenue[$categoryId] = 0;
                    }
                    $categoryRevenue[$categoryId] += floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 1);
                }
            }
        }
    }
    
    foreach ($categories as $category) {
        $salesData[] = [
            'id' => $category['id'],
            'name' => $category['name'],
            'revenue' => $categoryRevenue[$category['id']] ?? 0
        ];
    }
    
    // Sort by revenue
    usort($salesData, function($a, $b) {
        return $b['revenue'] - $a['revenue'];
    });
    
    return $salesData;
}

/* ═══════════════════════════════════════════════════════════════════════════
   END OF FILE
   ═══════════════════════════════════════════════════════════════════════════ */
?>

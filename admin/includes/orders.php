<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║   ██████╗ ██████╗ ██████╗ ███████╗██████╗ ███████╗                        ║
 * ║  ██╔═══██╗██╔══██╗██╔══██╗██╔════╝██╔══██╗██╔════╝                        ║
 * ║  ██║   ██║██████╔╝██║  ██║█████╗  ██████╔╝███████╗                        ║
 * ║  ██║   ██║██╔══██╗██║  ██║██╔══╝  ██╔══██╗╚════██║                        ║
 * ║  ╚██████╔╝██║  ██║██████╔╝███████╗██║  ██║███████║                        ║
 * ║   ╚═════╝ ╚═╝  ╚═╝╚═════╝ ╚══════╝╚═╝  ╚═╝╚══════╝                        ║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: orders.php                                                         ║
 * ║  PATH: /admin/includes/orders.php                                         ║
 * ║  DESCRIPTION: Order management functions for admin panel                  ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FEATURES:                                                                ║
 * ║    • Fetch all orders with user details                                   ║
 * ║    • Update order status (Pending, Processing, Completed, Delivered)      ║
 * ║    • Get order statistics                                                 ║
 * ║    • Filter orders by status/date                                         ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Prevent direct access
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not allowed');
}

/* ═══════════════════════════════════════════════════════════════════════════
   ORDER STATUS CONSTANTS
   ═══════════════════════════════════════════════════════════════════════════ */

define('ORDER_STATUS_PENDING', 'Pending');
define('ORDER_STATUS_PROCESSING', 'Processing');
define('ORDER_STATUS_COMPLETED', 'Completed');
define('ORDER_STATUS_DELIVERED', 'Delivered');
define('ORDER_STATUS_CANCELLED', 'Cancelled');

/**
 * Get all valid order statuses
 * @return array List of valid statuses
 */
function getValidOrderStatuses() {
    return [
        ORDER_STATUS_PENDING,
        ORDER_STATUS_PROCESSING,
        ORDER_STATUS_COMPLETED,
        ORDER_STATUS_DELIVERED,
        ORDER_STATUS_CANCELLED
    ];
}

/* ═══════════════════════════════════════════════════════════════════════════
   ORDER FETCHING FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get all orders with user information
 * @param mysqli $conn Database connection
 * @param int $limit Number of orders to fetch
 * @return array List of orders
 */
function getAllOrders($conn, $limit = 100) {
    $sql = "SELECT o.*, u.full_name, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.order_date DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $orders;
}

/**
 * Get orders by status
 * @param mysqli $conn Database connection
 * @param string $status Order status to filter by
 * @return array Filtered orders
 */
function getOrdersByStatus($conn, $status) {
    if (!in_array($status, getValidOrderStatuses())) {
        return [];
    }
    
    $sql = "SELECT o.*, u.full_name, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.status = ?
            ORDER BY o.order_date DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $orders;
}

/**
 * Get today's orders
 * @param mysqli $conn Database connection
 * @return array Today's orders
 */
function getTodaysOrders($conn) {
    $sql = "SELECT o.*, u.full_name, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE DATE(o.order_date) = CURDATE()
            ORDER BY o.order_date DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    return $orders;
}

/* ═══════════════════════════════════════════════════════════════════════════
   ORDER STATUS UPDATE FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Update order status
 * @param mysqli $conn Database connection
 * @param int $orderId Order ID
 * @param string $status New status
 * @return bool Success status
 */
function updateOrderStatus($conn, $orderId, $status) {
    if (!in_array($status, getValidOrderStatuses())) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $orderId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Get single order by ID
 * @param mysqli $conn Database connection
 * @param int $orderId Order ID
 * @return array|null Order data or null
 */
function getOrderById($conn, $orderId) {
    $sql = "SELECT o.*, u.full_name, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $orderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $order;
}

/* ═══════════════════════════════════════════════════════════════════════════
   ORDER STATISTICS FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get order statistics
 * @param mysqli $conn Database connection
 * @return array Order statistics
 */
function getOrderStatistics($conn) {
    $stats = [];
    
    // Total orders
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = mysqli_fetch_assoc($result)['count'];
    
    // Pending orders
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'");
    $stats['pending_orders'] = mysqli_fetch_assoc($result)['count'];
    
    // Processing orders
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Processing'");
    $stats['processing_orders'] = mysqli_fetch_assoc($result)['count'];
    
    // Completed orders
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Completed'");
    $stats['completed_orders'] = mysqli_fetch_assoc($result)['count'];
    
    // Delivered orders
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Delivered'");
    $stats['delivered_orders'] = mysqli_fetch_assoc($result)['count'];
    
    // Today's orders
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()");
    $stats['today_orders'] = mysqli_fetch_assoc($result)['count'];
    
    // Total revenue (excluding cancelled)
    $result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE status != 'Cancelled'");
    $stats['total_revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;
    
    // Today's revenue
    $result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE DATE(order_date) = CURDATE() AND status != 'Cancelled'");
    $stats['today_revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;
    
    return $stats;
}

/**
 * Get status badge class for styling
 * @param string $status Order status
 * @return string CSS class name
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case ORDER_STATUS_PENDING:
            return 'bg-warning text-dark';
        case ORDER_STATUS_PROCESSING:
            return 'bg-info';
        case ORDER_STATUS_COMPLETED:
            return 'bg-success';
        case ORDER_STATUS_DELIVERED:
            return 'bg-primary';
        case ORDER_STATUS_CANCELLED:
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

/* ═══════════════════════════════════════════════════════════════════════════
   END OF FILE
   ═══════════════════════════════════════════════════════════════════════════ */
?>

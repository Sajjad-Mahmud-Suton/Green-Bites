<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║  ██╗   ██╗███████╗███████╗██████╗ ███████╗                                ║
 * ║  ██║   ██║██╔════╝██╔════╝██╔══██╗██╔════╝                                ║
 * ║  ██║   ██║███████╗█████╗  ██████╔╝███████╗                                ║
 * ║  ██║   ██║╚════██║██╔══╝  ██╔══██╗╚════██║                                ║
 * ║  ╚██████╔╝███████║███████╗██║  ██║███████║                                ║
 * ║   ╚═════╝ ╚══════╝╚══════╝╚═╝  ╚═╝╚══════╝                                ║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: users.php                                                          ║
 * ║  PATH: /admin/includes/users.php                                          ║
 * ║  DESCRIPTION: User management functions for admin panel                   ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FEATURES:                                                                ║
 * ║    • View all registered users                                            ║
 * ║    • User statistics                                                      ║
 * ║    • User order history                                                   ║
 * ║    • Account management                                                   ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Prevent direct access
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not allowed');
}

/* ═══════════════════════════════════════════════════════════════════════════
   USER FETCHING FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get all users
 * @param mysqli $conn Database connection
 * @param int $limit Number of users to fetch
 * @return array List of users
 */
function getAllUsers($conn, $limit = 100) {
    $sql = "SELECT id, full_name, email, username, student_id, phone, created_at 
            FROM users 
            ORDER BY created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $users;
}

/**
 * Get user by ID
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return array|null User data or null
 */
function getUserById($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, username, student_id, phone, created_at FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $user;
}

/**
 * Search users
 * @param mysqli $conn Database connection
 * @param string $query Search query
 * @return array Matching users
 */
function searchUsers($conn, $query) {
    $searchTerm = "%{$query}%";
    $sql = "SELECT id, full_name, email, username, student_id, phone, created_at 
            FROM users 
            WHERE full_name LIKE ? OR email LIKE ? OR username LIKE ? OR student_id LIKE ?
            ORDER BY full_name ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $users;
}

/* ═══════════════════════════════════════════════════════════════════════════
   USER ORDER HISTORY FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get user's order history
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return array User's orders
 */
function getUserOrders($conn, $userId) {
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
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
 * Get user's total spending
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return float Total amount spent
 */
function getUserTotalSpending($conn, $userId) {
    $sql = "SELECT SUM(total_price) as total FROM orders WHERE user_id = ? AND status != 'Cancelled'";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return floatval($row['total'] ?? 0);
}

/**
 * Get user's order count
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return int Number of orders
 */
function getUserOrderCount($conn, $userId) {
    $sql = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return intval($row['count'] ?? 0);
}

/* ═══════════════════════════════════════════════════════════════════════════
   USER STATISTICS FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get user statistics
 * @param mysqli $conn Database connection
 * @return array User statistics
 */
function getUserStatistics($conn) {
    $stats = [];
    
    // Total users
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = mysqli_fetch_assoc($result)['count'];
    
    // New users today
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
    $stats['new_users_today'] = mysqli_fetch_assoc($result)['count'];
    
    // New users this week
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $stats['new_users_week'] = mysqli_fetch_assoc($result)['count'];
    
    // New users this month
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $stats['new_users_month'] = mysqli_fetch_assoc($result)['count'];
    
    // Active users (made order in last 30 days)
    $result = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as count FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $stats['active_users'] = mysqli_fetch_assoc($result)['count'];
    
    return $stats;
}

/**
 * Get top customers by spending
 * @param mysqli $conn Database connection
 * @param int $limit Number of customers
 * @return array Top customers
 */
function getTopCustomers($conn, $limit = 10) {
    $sql = "SELECT u.id, u.full_name, u.email, 
                   COUNT(o.id) as order_count, 
                   SUM(o.total_price) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'Cancelled'
            GROUP BY u.id
            HAVING order_count > 0
            ORDER BY total_spent DESC
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $customers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $customers;
}

/**
 * Get top customers by order count
 * @param mysqli $conn Database connection
 * @param int $limit Number of customers
 * @return array Top customers
 */
function getMostFrequentCustomers($conn, $limit = 10) {
    $sql = "SELECT u.id, u.full_name, u.email, 
                   COUNT(o.id) as order_count, 
                   SUM(o.total_price) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id
            GROUP BY u.id
            HAVING order_count > 0
            ORDER BY order_count DESC
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $customers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $customers;
}

/* ═══════════════════════════════════════════════════════════════════════════
   END OF FILE
   ═══════════════════════════════════════════════════════════════════════════ */
?>

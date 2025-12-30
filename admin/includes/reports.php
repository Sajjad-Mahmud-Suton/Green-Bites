<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║  ██████╗ ███████╗██████╗  ██████╗ ██████╗ ████████╗███████╗               ║
 * ║  ██╔══██╗██╔════╝██╔══██╗██╔═══██╗██╔══██╗╚══██╔══╝██╔════╝               ║
 * ║  ██████╔╝█████╗  ██████╔╝██║   ██║██████╔╝   ██║   ███████╗               ║
 * ║  ██╔══██╗██╔══╝  ██╔═══╝ ██║   ██║██╔══██╗   ██║   ╚════██║               ║
 * ║  ██║  ██║███████╗██║     ╚██████╔╝██║  ██║   ██║   ███████║               ║
 * ║  ╚═╝  ╚═╝╚══════╝╚═╝      ╚═════╝ ╚═╝  ╚═╝   ╚═╝   ╚══════╝               ║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: reports.php                                                        ║
 * ║  PATH: /admin/includes/reports.php                                        ║
 * ║  DESCRIPTION: Reporting & Analytics functions for admin panel             ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FEATURES:                                                                ║
 * ║    • Sales reports (daily, weekly, monthly)                               ║
 * ║    • Revenue analytics                                                    ║
 * ║    • Popular items analysis                                               ║
 * ║    • PDF report generation helpers                                        ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Prevent direct access
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not allowed');
}

/* ═══════════════════════════════════════════════════════════════════════════
   REPORT PERIOD CONSTANTS
   ═══════════════════════════════════════════════════════════════════════════ */

define('REPORT_PERIOD_TODAY', 'today');
define('REPORT_PERIOD_WEEK', 'week');
define('REPORT_PERIOD_MONTH', 'month');
define('REPORT_PERIOD_YEAR', 'year');
define('REPORT_PERIOD_CUSTOM', 'custom');

/* ═══════════════════════════════════════════════════════════════════════════
   REVENUE REPORT FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get revenue for today
 * @param mysqli $conn Database connection
 * @return float Today's revenue
 */
function getTodayRevenue($conn) {
    $result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE DATE(order_date) = CURDATE() AND status != 'Cancelled'");
    return floatval(mysqli_fetch_assoc($result)['total'] ?? 0);
}

/**
 * Get revenue for this week
 * @param mysqli $conn Database connection
 * @return float This week's revenue
 */
function getWeeklyRevenue($conn) {
    $result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status != 'Cancelled'");
    return floatval(mysqli_fetch_assoc($result)['total'] ?? 0);
}

/**
 * Get revenue for this month
 * @param mysqli $conn Database connection
 * @return float This month's revenue
 */
function getMonthlyRevenue($conn) {
    $result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE()) AND status != 'Cancelled'");
    return floatval(mysqli_fetch_assoc($result)['total'] ?? 0);
}

/**
 * Get total revenue (all time)
 * @param mysqli $conn Database connection
 * @return float Total revenue
 */
function getTotalRevenue($conn) {
    $result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE status != 'Cancelled'");
    return floatval(mysqli_fetch_assoc($result)['total'] ?? 0);
}

/**
 * Get revenue for date range
 * @param mysqli $conn Database connection
 * @param string $startDate Start date (Y-m-d)
 * @param string $endDate End date (Y-m-d)
 * @return float Revenue for period
 */
function getRevenueForPeriod($conn, $startDate, $endDate) {
    $sql = "SELECT SUM(total_price) as total FROM orders WHERE DATE(order_date) BETWEEN ? AND ? AND status != 'Cancelled'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total = mysqli_fetch_assoc($result)['total'] ?? 0;
    mysqli_stmt_close($stmt);
    return floatval($total);
}

/* ═══════════════════════════════════════════════════════════════════════════
   SALES REPORT FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get daily sales data for last N days
 * @param mysqli $conn Database connection
 * @param int $days Number of days
 * @return array Daily sales data
 */
function getDailySalesData($conn, $days = 7) {
    $sql = "SELECT DATE(order_date) as date, 
                   COUNT(*) as order_count, 
                   SUM(total_price) as revenue
            FROM orders 
            WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) 
              AND status != 'Cancelled'
            GROUP BY DATE(order_date)
            ORDER BY date ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $days);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $data;
}

/**
 * Get weekly sales data for last N weeks
 * @param mysqli $conn Database connection
 * @param int $weeks Number of weeks
 * @return array Weekly sales data
 */
function getWeeklySalesData($conn, $weeks = 4) {
    $days = $weeks * 7;
    $sql = "SELECT YEAR(order_date) as year, 
                   WEEK(order_date) as week, 
                   COUNT(*) as order_count, 
                   SUM(total_price) as revenue
            FROM orders 
            WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) 
              AND status != 'Cancelled'
            GROUP BY YEAR(order_date), WEEK(order_date)
            ORDER BY year ASC, week ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $days);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $data;
}

/**
 * Get monthly sales data for last N months
 * @param mysqli $conn Database connection
 * @param int $months Number of months
 * @return array Monthly sales data
 */
function getMonthlySalesData($conn, $months = 12) {
    $sql = "SELECT YEAR(order_date) as year, 
                   MONTH(order_date) as month, 
                   MONTHNAME(order_date) as month_name,
                   COUNT(*) as order_count, 
                   SUM(total_price) as revenue
            FROM orders 
            WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) 
              AND status != 'Cancelled'
            GROUP BY YEAR(order_date), MONTH(order_date)
            ORDER BY year ASC, month ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $months);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $data;
}

/* ═══════════════════════════════════════════════════════════════════════════
   POPULAR ITEMS ANALYSIS FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get most popular items by order count
 * @param mysqli $conn Database connection
 * @param int $limit Number of items
 * @return array Popular items
 */
function getMostPopularItems($conn, $limit = 10) {
    // Parse items JSON from orders to count
    $sql = "SELECT items FROM orders WHERE status != 'Cancelled'";
    $result = mysqli_query($conn, $sql);
    
    $itemCounts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items = json_decode($row['items'], true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $name = $item['title'] ?? $item['name'] ?? 'Unknown';
                if (!isset($itemCounts[$name])) {
                    $itemCounts[$name] = ['name' => $name, 'count' => 0, 'revenue' => 0];
                }
                $itemCounts[$name]['count'] += intval($item['quantity'] ?? 1);
                $itemCounts[$name]['revenue'] += floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 1);
            }
        }
    }
    
    // Sort by count
    usort($itemCounts, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    return array_slice($itemCounts, 0, $limit);
}

/**
 * Get best selling items by revenue
 * @param mysqli $conn Database connection
 * @param int $limit Number of items
 * @return array Best selling items
 */
function getBestSellingItems($conn, $limit = 10) {
    $items = getMostPopularItems($conn, 100);
    
    // Sort by revenue
    usort($items, function($a, $b) {
        return $b['revenue'] - $a['revenue'];
    });
    
    return array_slice($items, 0, $limit);
}

/* ═══════════════════════════════════════════════════════════════════════════
   ORDER STATUS ANALYSIS FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get order count by status
 * @param mysqli $conn Database connection
 * @return array Order counts by status
 */
function getOrderCountByStatus($conn) {
    $sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
    $result = mysqli_query($conn, $sql);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[$row['status']] = intval($row['count']);
    }
    
    return $data;
}

/**
 * Get hourly order distribution
 * @param mysqli $conn Database connection
 * @return array Hourly distribution
 */
function getHourlyOrderDistribution($conn) {
    $sql = "SELECT HOUR(order_date) as hour, COUNT(*) as count 
            FROM orders 
            WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY HOUR(order_date)
            ORDER BY hour ASC";
    
    $result = mysqli_query($conn, $sql);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[$row['hour']] = intval($row['count']);
    }
    
    return $data;
}

/* ═══════════════════════════════════════════════════════════════════════════
   DASHBOARD SUMMARY FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get dashboard summary data
 * @param mysqli $conn Database connection
 * @return array Dashboard summary
 */
function getDashboardSummary($conn) {
    $summary = [];
    
    // Today's stats
    $summary['today'] = [
        'revenue' => getTodayRevenue($conn),
        'orders' => 0
    ];
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()");
    $summary['today']['orders'] = mysqli_fetch_assoc($result)['count'];
    
    // This week's stats
    $summary['week'] = [
        'revenue' => getWeeklyRevenue($conn),
        'orders' => 0
    ];
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $summary['week']['orders'] = mysqli_fetch_assoc($result)['count'];
    
    // This month's stats
    $summary['month'] = [
        'revenue' => getMonthlyRevenue($conn),
        'orders' => 0
    ];
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())");
    $summary['month']['orders'] = mysqli_fetch_assoc($result)['count'];
    
    // All time stats
    $summary['total'] = [
        'revenue' => getTotalRevenue($conn),
        'orders' => 0
    ];
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
    $summary['total']['orders'] = mysqli_fetch_assoc($result)['count'];
    
    return $summary;
}

/**
 * Format currency for display
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function formatCurrency($amount) {
    return '৳' . number_format($amount, 0);
}

/**
 * Get percentage change between two values
 * @param float $current Current value
 * @param float $previous Previous value
 * @return float Percentage change
 */
function getPercentageChange($current, $previous) {
    if ($previous == 0) {
        return $current > 0 ? 100 : 0;
    }
    return round((($current - $previous) / $previous) * 100, 1);
}

/* ═══════════════════════════════════════════════════════════════════════════
   END OF FILE
   ═══════════════════════════════════════════════════════════════════════════ */
?>

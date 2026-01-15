<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                      PROFIT DASHBOARD API                                 ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  Provides comprehensive profit analytics for the admin dashboard          ║
 * ║  • Total profit, revenue, investment                                      ║
 * ║  • Period-based breakdowns (daily, weekly, monthly, yearly)               ║
 * ║  • Most profitable items, most sold items                                 ║
 * ║  • Profit trends and category distribution                                ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
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

// Get optional date filters
$dateFrom = isset($_GET['from']) ? $_GET['from'] : null;
$dateTo = isset($_GET['to']) ? $_GET['to'] : null;
$productSearch = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build date condition
$dateCondition = "";
$dateParams = [];
$dateTypes = "";

if ($dateFrom && $dateTo) {
    $dateCondition = " AND DATE(p.calculated_at) BETWEEN ? AND ?";
    $dateParams = [$dateFrom, $dateTo];
    $dateTypes = "ss";
} elseif ($dateFrom) {
    $dateCondition = " AND DATE(p.calculated_at) >= ?";
    $dateParams = [$dateFrom];
    $dateTypes = "s";
} elseif ($dateTo) {
    $dateCondition = " AND DATE(p.calculated_at) <= ?";
    $dateParams = [$dateTo];
    $dateTypes = "s";
}

/* ═══════════════════════════════════════════════════════════════════════════
   OVERVIEW STATISTICS
   ═══════════════════════════════════════════════════════════════════════════ */

function getOverviewStats($conn, $dateCondition = '', $dateParams = [], $dateTypes = '') {
    $stats = [
        'total_profit' => 0,
        'total_revenue' => 0,
        'total_investment' => 0,
        'total_orders' => 0,
        'total_items_sold' => 0,
        'profit_margin_percent' => 0,
        'avg_profit_per_item' => 0
    ];
    
    $sql = "SELECT 
                COALESCE(SUM(profit_amount), 0) as total_profit,
                COALESCE(SUM(revenue), 0) as total_revenue,
                COALESCE(SUM(investment), 0) as total_investment,
                COUNT(DISTINCT order_id) as total_orders,
                COALESCE(SUM(quantity), 0) as total_items_sold
            FROM profits p
            WHERE 1=1 {$dateCondition}";
    
    if (empty($dateParams)) {
        $result = mysqli_query($conn, $sql);
    } else {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $dateTypes, ...$dateParams);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }
    
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['total_profit'] = floatval($row['total_profit']);
        $stats['total_revenue'] = floatval($row['total_revenue']);
        $stats['total_investment'] = floatval($row['total_investment']);
        $stats['total_orders'] = intval($row['total_orders']);
        $stats['total_items_sold'] = intval($row['total_items_sold']);
        
        // Calculate margin percentage
        if ($stats['total_revenue'] > 0) {
            $stats['profit_margin_percent'] = round(($stats['total_profit'] / $stats['total_revenue']) * 100, 1);
        }
        
        // Average profit per item
        if ($stats['total_items_sold'] > 0) {
            $stats['avg_profit_per_item'] = round($stats['total_profit'] / $stats['total_items_sold'], 2);
        }
    }
    
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    
    return $stats;
}

/* ═══════════════════════════════════════════════════════════════════════════
   PERIOD STATISTICS (Today, Week, Month, Year)
   ═══════════════════════════════════════════════════════════════════════════ */

function getPeriodStats($conn) {
    $periods = [];
    
    // Today
    $sql = "SELECT 
                COALESCE(SUM(profit_amount), 0) as profit,
                COUNT(DISTINCT order_id) as orders
            FROM profits 
            WHERE DATE(calculated_at) = CURDATE()";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $periods['today'] = [
        'profit' => floatval($row['profit']),
        'orders' => intval($row['orders'])
    ];
    
    // This Week
    $sql = "SELECT 
                COALESCE(SUM(profit_amount), 0) as profit,
                COUNT(DISTINCT order_id) as orders
            FROM profits 
            WHERE YEARWEEK(calculated_at, 1) = YEARWEEK(CURDATE(), 1)";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $periods['week'] = [
        'profit' => floatval($row['profit']),
        'orders' => intval($row['orders'])
    ];
    
    // This Month
    $sql = "SELECT 
                COALESCE(SUM(profit_amount), 0) as profit,
                COUNT(DISTINCT order_id) as orders
            FROM profits 
            WHERE YEAR(calculated_at) = YEAR(CURDATE()) AND MONTH(calculated_at) = MONTH(CURDATE())";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $periods['month'] = [
        'profit' => floatval($row['profit']),
        'orders' => intval($row['orders'])
    ];
    
    // This Year
    $sql = "SELECT 
                COALESCE(SUM(profit_amount), 0) as profit,
                COUNT(DISTINCT order_id) as orders
            FROM profits 
            WHERE YEAR(calculated_at) = YEAR(CURDATE())";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $periods['year'] = [
        'profit' => floatval($row['profit']),
        'orders' => intval($row['orders'])
    ];
    
    return $periods;
}

/* ═══════════════════════════════════════════════════════════════════════════
   PROFIT TREND (Last 30 Days)
   ═══════════════════════════════════════════════════════════════════════════ */

function getProfitTrend($conn, $days = 30) {
    $trend = [];
    
    $sql = "SELECT 
                DATE(calculated_at) as date,
                COALESCE(SUM(profit_amount), 0) as profit,
                COALESCE(SUM(revenue), 0) as revenue,
                COALESCE(SUM(investment), 0) as investment
            FROM profits 
            WHERE calculated_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(calculated_at)
            ORDER BY date ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $days);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Initialize all days with 0
    $allDays = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $allDays[$date] = [
            'label' => date('M j', strtotime($date)),
            'profit' => 0,
            'revenue' => 0,
            'investment' => 0
        ];
    }
    
    // Fill in actual data
    while ($row = mysqli_fetch_assoc($result)) {
        if (isset($allDays[$row['date']])) {
            $allDays[$row['date']]['profit'] = floatval($row['profit']);
            $allDays[$row['date']]['revenue'] = floatval($row['revenue']);
            $allDays[$row['date']]['investment'] = floatval($row['investment']);
        }
    }
    
    mysqli_stmt_close($stmt);
    
    return array_values($allDays);
}

/* ═══════════════════════════════════════════════════════════════════════════
   MONTHLY REVENUE vs PROFIT vs INVESTMENT (Last 12 Months)
   ═══════════════════════════════════════════════════════════════════════════ */

function getMonthlyComparison($conn) {
    $data = [];
    
    $sql = "SELECT 
                DATE_FORMAT(calculated_at, '%Y-%m') as month,
                DATE_FORMAT(calculated_at, '%b %Y') as label,
                COALESCE(SUM(profit_amount), 0) as profit,
                COALESCE(SUM(revenue), 0) as revenue,
                COALESCE(SUM(investment), 0) as investment
            FROM profits 
            WHERE calculated_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(calculated_at, '%Y-%m')
            ORDER BY month ASC";
    
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'label' => $row['label'],
            'profit' => floatval($row['profit']),
            'revenue' => floatval($row['revenue']),
            'investment' => floatval($row['investment'])
        ];
    }
    
    return $data;
}

/* ═══════════════════════════════════════════════════════════════════════════
   MOST PROFITABLE ITEMS (Top 10)
   ═══════════════════════════════════════════════════════════════════════════ */

function getMostProfitableItems($conn, $limit = 10) {
    $items = [];
    
    $sql = "SELECT 
                product_id,
                product_name,
                SUM(quantity) as units_sold,
                SUM(profit_amount) as total_profit,
                SUM(revenue) as total_revenue,
                ROUND((SUM(profit_amount) / NULLIF(SUM(revenue), 0)) * 100, 1) as margin_percent
            FROM profits 
            GROUP BY product_id, product_name
            ORDER BY total_profit DESC
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'id' => intval($row['product_id']),
            'name' => $row['product_name'],
            'units_sold' => intval($row['units_sold']),
            'profit' => floatval($row['total_profit']),
            'revenue' => floatval($row['total_revenue']),
            'margin' => floatval($row['margin_percent'])
        ];
    }
    
    mysqli_stmt_close($stmt);
    
    return $items;
}

/* ═══════════════════════════════════════════════════════════════════════════
   MOST SOLD ITEMS (Top 10)
   ═══════════════════════════════════════════════════════════════════════════ */

function getMostSoldItems($conn, $limit = 10) {
    $items = [];
    
    $sql = "SELECT 
                product_id,
                product_name,
                SUM(quantity) as units_sold,
                SUM(revenue) as total_revenue,
                SUM(profit_amount) as total_profit
            FROM profits 
            GROUP BY product_id, product_name
            ORDER BY units_sold DESC
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'id' => intval($row['product_id']),
            'name' => $row['product_name'],
            'units_sold' => intval($row['units_sold']),
            'revenue' => floatval($row['total_revenue']),
            'profit' => floatval($row['total_profit'])
        ];
    }
    
    mysqli_stmt_close($stmt);
    
    return $items;
}

/* ═══════════════════════════════════════════════════════════════════════════
   PROFIT BY CATEGORY
   ═══════════════════════════════════════════════════════════════════════════ */

function getProfitByCategory($conn) {
    $categories = [];
    
    $sql = "SELECT 
                c.id,
                c.name as category_name,
                COALESCE(SUM(p.profit_amount), 0) as profit,
                COALESCE(SUM(p.revenue), 0) as revenue,
                COALESCE(SUM(p.quantity), 0) as items_sold
            FROM categories c
            LEFT JOIN menu_items m ON c.id = m.category_id
            LEFT JOIN profits p ON m.id = p.product_id
            GROUP BY c.id, c.name
            HAVING profit > 0
            ORDER BY profit DESC";
    
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = [
            'id' => intval($row['id']),
            'name' => $row['category_name'],
            'profit' => floatval($row['profit']),
            'revenue' => floatval($row['revenue']),
            'items_sold' => intval($row['items_sold'])
        ];
    }
    
    return $categories;
}

/* ═══════════════════════════════════════════════════════════════════════════
   RECENT PROFIT RECORDS
   ═══════════════════════════════════════════════════════════════════════════ */

function getRecentProfitRecords($conn, $limit = 50, $search = '') {
    $records = [];
    
    $sql = "SELECT 
                p.id,
                p.order_id,
                p.product_id,
                p.product_name,
                p.quantity,
                p.selling_price,
                p.buying_price,
                p.revenue,
                p.investment,
                p.profit_amount,
                p.calculated_at
            FROM profits p
            WHERE 1=1";
    
    if (!empty($search)) {
        $sql .= " AND p.product_name LIKE ?";
        $searchParam = "%{$search}%";
    }
    
    $sql .= " ORDER BY p.calculated_at DESC LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!empty($search)) {
        mysqli_stmt_bind_param($stmt, 'si', $searchParam, $limit);
    } else {
        mysqli_stmt_bind_param($stmt, 'i', $limit);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = [
            'id' => intval($row['id']),
            'order_id' => intval($row['order_id']),
            'product_id' => intval($row['product_id']),
            'product_name' => $row['product_name'],
            'quantity' => intval($row['quantity']),
            'selling_price' => floatval($row['selling_price']),
            'buying_price' => floatval($row['buying_price']),
            'revenue' => floatval($row['revenue']),
            'investment' => floatval($row['investment']),
            'profit' => floatval($row['profit_amount']),
            'date' => $row['calculated_at']
        ];
    }
    
    mysqli_stmt_close($stmt);
    
    return $records;
}

/* ═══════════════════════════════════════════════════════════════════════════
   COMPILE AND RETURN ALL DATA
   ═══════════════════════════════════════════════════════════════════════════ */

try {
    $response = [
        'success' => true,
        'overview' => getOverviewStats($conn, $dateCondition, $dateParams, $dateTypes),
        'periods' => getPeriodStats($conn),
        'profit_trend' => getProfitTrend($conn, 30),
        'monthly_comparison' => getMonthlyComparison($conn),
        'most_profitable_items' => getMostProfitableItems($conn, 10),
        'most_sold_items' => getMostSoldItems($conn, 10),
        'profit_by_category' => getProfitByCategory($conn),
        'recent_records' => getRecentProfitRecords($conn, 50, $productSearch),
        'filters' => [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'search' => $productSearch
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching profit data: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

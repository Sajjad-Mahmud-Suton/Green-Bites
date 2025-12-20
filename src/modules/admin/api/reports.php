<?php
/**
 * Reports API - Get Business Analytics Data
 * ------------------------------------------
 * Returns comprehensive business reports data
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../db.php';

// Check admin auth
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$period = $_GET['period'] ?? 'month'; // today, week, month, year, all
$year = intval($_GET['year'] ?? date('Y'));
$month = intval($_GET['month'] ?? date('m'));

$response = [
    'success' => true,
    'period' => $period,
    'generated_at' => date('Y-m-d H:i:s')
];

// ============================================
// 1. OVERVIEW STATISTICS
// ============================================

// Today's Stats
$todayResult = mysqli_query($conn, "
    SELECT 
        COUNT(*) as orders,
        COALESCE(SUM(total_price), 0) as revenue
    FROM orders 
    WHERE DATE(order_date) = CURDATE() AND status != 'Cancelled'
");
$today = mysqli_fetch_assoc($todayResult);

// This Week
$weekResult = mysqli_query($conn, "
    SELECT 
        COUNT(*) as orders,
        COALESCE(SUM(total_price), 0) as revenue
    FROM orders 
    WHERE YEARWEEK(order_date) = YEARWEEK(CURDATE()) AND status != 'Cancelled'
");
$week = mysqli_fetch_assoc($weekResult);

// This Month
$monthResult = mysqli_query($conn, "
    SELECT 
        COUNT(*) as orders,
        COALESCE(SUM(total_price), 0) as revenue
    FROM orders 
    WHERE YEAR(order_date) = YEAR(CURDATE()) 
    AND MONTH(order_date) = MONTH(CURDATE()) 
    AND status != 'Cancelled'
");
$monthData = mysqli_fetch_assoc($monthResult);

// This Year
$yearResult = mysqli_query($conn, "
    SELECT 
        COUNT(*) as orders,
        COALESCE(SUM(total_price), 0) as revenue
    FROM orders 
    WHERE YEAR(order_date) = YEAR(CURDATE()) AND status != 'Cancelled'
");
$yearData = mysqli_fetch_assoc($yearResult);

// All Time
$allResult = mysqli_query($conn, "
    SELECT 
        COUNT(*) as orders,
        COALESCE(SUM(total_price), 0) as revenue
    FROM orders 
    WHERE status != 'Cancelled'
");
$allTime = mysqli_fetch_assoc($allResult);

// Average Order Value
$avgResult = mysqli_query($conn, "
    SELECT COALESCE(AVG(total_price), 0) as avg_order 
    FROM orders WHERE status != 'Cancelled'
");
$avgOrder = mysqli_fetch_assoc($avgResult)['avg_order'];

$response['overview'] = [
    'today' => [
        'orders' => intval($today['orders']),
        'revenue' => floatval($today['revenue'])
    ],
    'week' => [
        'orders' => intval($week['orders']),
        'revenue' => floatval($week['revenue'])
    ],
    'month' => [
        'orders' => intval($monthData['orders']),
        'revenue' => floatval($monthData['revenue'])
    ],
    'year' => [
        'orders' => intval($yearData['orders']),
        'revenue' => floatval($yearData['revenue'])
    ],
    'all_time' => [
        'orders' => intval($allTime['orders']),
        'revenue' => floatval($allTime['revenue'])
    ],
    'avg_order_value' => round($avgOrder, 2)
];

// ============================================
// 2. MONTHLY REVENUE CHART (Last 12 months)
// ============================================
$monthlyRevenue = [];
$monthlyResult = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') as month,
        DATE_FORMAT(order_date, '%b %Y') as month_label,
        COUNT(*) as orders,
        COALESCE(SUM(total_price), 0) as revenue
    FROM orders 
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    AND status != 'Cancelled'
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month ASC
");
while ($row = mysqli_fetch_assoc($monthlyResult)) {
    $monthlyRevenue[] = [
        'month' => $row['month'],
        'label' => $row['month_label'],
        'orders' => intval($row['orders']),
        'revenue' => floatval($row['revenue'])
    ];
}
$response['monthly_chart'] = $monthlyRevenue;

// ============================================
// 3. DAILY REVENUE (Last 30 days)
// ============================================
$dailyRevenue = [];
$dailyResult = mysqli_query($conn, "
    SELECT 
        DATE(order_date) as date,
        DATE_FORMAT(order_date, '%d %b') as date_label,
        COUNT(*) as orders,
        COALESCE(SUM(total_price), 0) as revenue
    FROM orders 
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    AND status != 'Cancelled'
    GROUP BY DATE(order_date)
    ORDER BY date ASC
");
while ($row = mysqli_fetch_assoc($dailyResult)) {
    $dailyRevenue[] = [
        'date' => $row['date'],
        'label' => $row['date_label'],
        'orders' => intval($row['orders']),
        'revenue' => floatval($row['revenue'])
    ];
}
$response['daily_chart'] = $dailyRevenue;

// ============================================
// 4. TOP SELLING ITEMS (Top 10)
// ============================================
$topItems = [];
$ordersResult = mysqli_query($conn, "
    SELECT items FROM orders WHERE status != 'Cancelled'
");

$itemSales = [];
while ($order = mysqli_fetch_assoc($ordersResult)) {
    $items = json_decode($order['items'], true);
    if (is_array($items)) {
        foreach ($items as $item) {
            $name = $item['title'] ?? $item['name'] ?? 'Unknown';
            $qty = intval($item['quantity'] ?? 1);
            $price = floatval($item['price'] ?? 0);
            
            if (!isset($itemSales[$name])) {
                $itemSales[$name] = ['name' => $name, 'quantity' => 0, 'revenue' => 0];
            }
            $itemSales[$name]['quantity'] += $qty;
            $itemSales[$name]['revenue'] += ($qty * $price);
        }
    }
}

// Sort by quantity sold
usort($itemSales, function($a, $b) {
    return $b['quantity'] - $a['quantity'];
});

$response['top_items'] = array_slice($itemSales, 0, 10);

// ============================================
// 5. ORDER STATUS DISTRIBUTION
// ============================================
$statusResult = mysqli_query($conn, "
    SELECT 
        status,
        COUNT(*) as count
    FROM orders 
    GROUP BY status
");
$statusDist = [];
while ($row = mysqli_fetch_assoc($statusResult)) {
    $statusDist[$row['status']] = intval($row['count']);
}
$response['order_status'] = $statusDist;

// ============================================
// 6. CATEGORY PERFORMANCE
// ============================================
$categoryPerf = [];
foreach ($itemSales as $item) {
    // Try to find category
    $itemName = mysqli_real_escape_string($conn, $item['name']);
    $catResult = mysqli_query($conn, "
        SELECT c.name as category 
        FROM menu_items m 
        JOIN categories c ON m.category_id = c.id 
        WHERE m.title = '$itemName' 
        LIMIT 1
    ");
    $cat = mysqli_fetch_assoc($catResult);
    $categoryName = $cat['category'] ?? 'Other';
    
    if (!isset($categoryPerf[$categoryName])) {
        $categoryPerf[$categoryName] = ['name' => $categoryName, 'quantity' => 0, 'revenue' => 0];
    }
    $categoryPerf[$categoryName]['quantity'] += $item['quantity'];
    $categoryPerf[$categoryName]['revenue'] += $item['revenue'];
}

usort($categoryPerf, function($a, $b) {
    return $b['revenue'] - $a['revenue'];
});

$response['category_performance'] = array_values($categoryPerf);

// ============================================
// 7. HOURLY ORDER DISTRIBUTION
// ============================================
$hourlyResult = mysqli_query($conn, "
    SELECT 
        HOUR(order_date) as hour,
        COUNT(*) as orders
    FROM orders 
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY HOUR(order_date)
    ORDER BY hour
");
$hourlyDist = array_fill(0, 24, 0);
while ($row = mysqli_fetch_assoc($hourlyResult)) {
    $hourlyDist[intval($row['hour'])] = intval($row['orders']);
}
$response['hourly_distribution'] = $hourlyDist;

// ============================================
// 8. CUSTOMER INSIGHTS
// ============================================
// Top customers
$topCustomers = [];
$custResult = mysqli_query($conn, "
    SELECT 
        u.full_name,
        u.email,
        COUNT(o.id) as total_orders,
        SUM(o.total_price) as total_spent
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.status != 'Cancelled'
    GROUP BY o.user_id
    ORDER BY total_spent DESC
    LIMIT 10
");
while ($row = mysqli_fetch_assoc($custResult)) {
    $topCustomers[] = [
        'name' => $row['full_name'],
        'email' => $row['email'],
        'orders' => intval($row['total_orders']),
        'spent' => floatval($row['total_spent'])
    ];
}
$response['top_customers'] = $topCustomers;

// New customers this month
$newCustResult = mysqli_query($conn, "
    SELECT COUNT(*) as count FROM users 
    WHERE YEAR(created_at) = YEAR(CURDATE()) 
    AND MONTH(created_at) = MONTH(CURDATE())
");
$response['new_customers_this_month'] = mysqli_fetch_assoc($newCustResult)['count'];

// ============================================
// 9. COMPARISON WITH PREVIOUS PERIOD
// ============================================
// This month vs last month
$lastMonthResult = mysqli_query($conn, "
    SELECT 
        COUNT(*) as orders,
        COALESCE(SUM(total_price), 0) as revenue
    FROM orders 
    WHERE YEAR(order_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    AND MONTH(order_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    AND status != 'Cancelled'
");
$lastMonth = mysqli_fetch_assoc($lastMonthResult);

$orderGrowth = $lastMonth['orders'] > 0 
    ? round((($monthData['orders'] - $lastMonth['orders']) / $lastMonth['orders']) * 100, 1)
    : 100;

$revenueGrowth = $lastMonth['revenue'] > 0 
    ? round((($monthData['revenue'] - $lastMonth['revenue']) / $lastMonth['revenue']) * 100, 1)
    : 100;

$response['growth'] = [
    'orders' => $orderGrowth,
    'revenue' => $revenueGrowth,
    'last_month_orders' => intval($lastMonth['orders']),
    'last_month_revenue' => floatval($lastMonth['revenue'])
];

// ============================================
// 10. PEAK HOURS
// ============================================
$peakHour = array_search(max($hourlyDist), $hourlyDist);
$response['peak_hour'] = $peakHour;
$response['peak_hour_label'] = sprintf('%02d:00 - %02d:00', $peakHour, $peakHour + 1);

echo json_encode($response);

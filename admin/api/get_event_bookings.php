<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    GET EVENT BOOKINGS API (Admin)                         ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Retrieves event bookings with filters                                  ║
 * ║  • Supports: upcoming, past, this_week, this_month, past_week             ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$status = $_GET['status'] ?? '';
$eventType = $_GET['event_type'] ?? '';
$search = $_GET['search'] ?? '';

// Ensure table exists
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'event_bookings'");
if (mysqli_num_rows($tableCheck) == 0) {
    // Create table
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `event_bookings` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `event_name` VARCHAR(200) NOT NULL,
        `event_type` ENUM('birthday', 'wedding', 'corporate', 'anniversary', 'graduation', 'reunion', 'other') NOT NULL DEFAULT 'other',
        `customer_name` VARCHAR(100) NOT NULL,
        `customer_phone` VARCHAR(20) NOT NULL,
        `customer_email` VARCHAR(100) DEFAULT NULL,
        `event_date` DATE NOT NULL,
        `event_time` TIME NOT NULL,
        `end_time` TIME DEFAULT NULL,
        `guest_count` INT(11) NOT NULL DEFAULT 1,
        `venue` VARCHAR(200) DEFAULT 'Green Bites Restaurant',
        `package_type` ENUM('basic', 'standard', 'premium', 'custom') NOT NULL DEFAULT 'standard',
        `advance_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `payment_status` ENUM('pending', 'partial', 'paid', 'refunded') NOT NULL DEFAULT 'pending',
        `booking_status` ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
        `special_requirements` TEXT DEFAULT NULL,
        `menu_items` TEXT DEFAULT NULL,
        `decorations` TEXT DEFAULT NULL,
        `notes` TEXT DEFAULT NULL,
        `created_by` INT(11) DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_event_date` (`event_date`),
        KEY `idx_booking_status` (`booking_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}

// Build query based on filter
$whereConditions = ["1=1"];
$params = [];
$types = "";

// Date-based filters
$today = date('Y-m-d');
switch ($filter) {
    case 'upcoming':
        $whereConditions[] = "event_date >= ?";
        $params[] = $today;
        $types .= "s";
        break;
    case 'past':
        $whereConditions[] = "event_date < ?";
        $params[] = $today;
        $types .= "s";
        break;
    case 'this_week':
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $whereConditions[] = "event_date BETWEEN ? AND ?";
        $params[] = $weekStart;
        $params[] = $weekEnd;
        $types .= "ss";
        break;
    case 'past_week':
        $pastWeekStart = date('Y-m-d', strtotime('-1 week monday'));
        $pastWeekEnd = date('Y-m-d', strtotime('-1 week sunday'));
        $whereConditions[] = "event_date BETWEEN ? AND ?";
        $params[] = $pastWeekStart;
        $params[] = $pastWeekEnd;
        $types .= "ss";
        break;
    case 'this_month':
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $whereConditions[] = "event_date BETWEEN ? AND ?";
        $params[] = $monthStart;
        $params[] = $monthEnd;
        $types .= "ss";
        break;
    case 'next_month':
        $nextMonthStart = date('Y-m-01', strtotime('+1 month'));
        $nextMonthEnd = date('Y-m-t', strtotime('+1 month'));
        $whereConditions[] = "event_date BETWEEN ? AND ?";
        $params[] = $nextMonthStart;
        $params[] = $nextMonthEnd;
        $types .= "ss";
        break;
    case 'today':
        $whereConditions[] = "event_date = ?";
        $params[] = $today;
        $types .= "s";
        break;
}

// Status filter
if (!empty($status)) {
    $whereConditions[] = "booking_status = ?";
    $params[] = $status;
    $types .= "s";
}

// Event type filter
if (!empty($eventType)) {
    $whereConditions[] = "event_type = ?";
    $params[] = $eventType;
    $types .= "s";
}

// Search filter
if (!empty($search)) {
    $whereConditions[] = "(event_name LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR customer_email LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ssss";
}

$whereClause = implode(" AND ", $whereConditions);
$sql = "SELECT * FROM event_bookings WHERE {$whereClause} ORDER BY event_date ASC, event_time ASC";

if (empty($params)) {
    $result = mysqli_query($conn, $sql);
} else {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

$bookings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bookings[] = $row;
}

// Get stats
$stats = [
    'total' => 0,
    'upcoming' => 0,
    'today' => 0,
    'this_week' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0,
    'total_revenue' => 0
];

$statsResult = mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN event_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming,
    SUM(CASE WHEN event_date = CURDATE() THEN 1 ELSE 0 END) as today,
    SUM(CASE WHEN event_date BETWEEN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY) THEN 1 ELSE 0 END) as this_week,
    SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(CASE WHEN booking_status != 'cancelled' THEN total_amount ELSE 0 END) as total_revenue
FROM event_bookings");

if ($statsRow = mysqli_fetch_assoc($statsResult)) {
    $stats = [
        'total' => intval($statsRow['total']),
        'upcoming' => intval($statsRow['upcoming']),
        'today' => intval($statsRow['today']),
        'this_week' => intval($statsRow['this_week']),
        'pending' => intval($statsRow['pending']),
        'confirmed' => intval($statsRow['confirmed']),
        'completed' => intval($statsRow['completed']),
        'cancelled' => intval($statsRow['cancelled']),
        'total_revenue' => floatval($statsRow['total_revenue'])
    ];
}

echo json_encode([
    'success' => true,
    'bookings' => $bookings,
    'stats' => $stats,
    'filter' => $filter
]);

mysqli_close($conn);
?>

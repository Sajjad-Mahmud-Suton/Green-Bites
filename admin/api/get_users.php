<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  GREEN BITES - Get Users API                                              ║
 * ║  Returns list of all registered users with status and statistics          ║
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
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

// Build query with status and order stats
$sql = "SELECT 
            u.id, 
            u.full_name, 
            u.username, 
            u.email,
            u.phone,
            u.status,
            u.added_by,
            u.status_changed_by,
            u.status_changed_at,
            u.created_at,
            COUNT(o.id) as total_orders,
            COALESCE(SUM(CASE WHEN o.status != 'Cancelled' THEN o.total_price ELSE 0 END), 0) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE 1=1";

$params = [];
$types = '';

// Apply status filter
if ($status_filter && in_array($status_filter, ['active', 'paused', 'suspended'])) {
    $sql .= " AND u.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

// Apply search
if ($search) {
    $searchTerm = '%' . $search . '%';
    $sql .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['total_orders'] = (int)$row['total_orders'];
    $row['total_spent'] = (float)$row['total_spent'];
    $users[] = $row;
}
mysqli_stmt_close($stmt);

// Get status counts
$statusCounts = [
    'active' => 0,
    'paused' => 0,
    'suspended' => 0,
    'total' => 0
];

$countResult = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM users GROUP BY status");
while ($row = mysqli_fetch_assoc($countResult)) {
    $status = $row['status'] ?: 'active';
    if (isset($statusCounts[$status])) {
        $statusCounts[$status] = (int)$row['count'];
    }
    $statusCounts['total'] += (int)$row['count'];
}

echo json_encode([
    'success' => true, 
    'users' => $users,
    'counts' => $statusCounts
]);

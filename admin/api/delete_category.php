<?php
session_start();

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// CSRF check
$headers = getallheaders();
$csrfToken = $headers['X-CSRF-Token'] ?? '';
if ($csrfToken !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

header('Content-Type: application/json');

// Database connection
require_once '../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

// Check if category has menu items
$checkQuery = "SELECT COUNT(*) as cnt FROM menu_items WHERE category_id = ?";
$checkStmt = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($checkStmt, "i", $id);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);
$count = mysqli_fetch_assoc($checkResult)['cnt'];

if ($count > 0) {
    echo json_encode([
        'success' => false, 
        'message' => "Cannot delete category. It has $count menu item(s) associated with it. Please remove or reassign them first."
    ]);
    exit;
}

// Delete category
$query = "DELETE FROM categories WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Category not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

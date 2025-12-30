<?php
session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

// Check admin auth
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Verify CSRF
$csrf = $_POST['csrf_token'] ?? '';
if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

// Get data
$title = trim($_POST['name'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 10);
$image_url = trim($_POST['image_url'] ?? '');
$description = trim($_POST['description'] ?? '');

// Validate
if (empty($title) || $category_id <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Name, category and price are required']);
    exit;
}

// Validate quantity
if ($quantity < 0) {
    $quantity = 0;
}

// Insert
$stmt = mysqli_prepare($conn, "INSERT INTO menu_items (title, price, image_url, category_id, description, quantity) VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'sdsisi', $title, $price, $image_url, $category_id, $description, $quantity);

if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($conn);
    echo json_encode(['success' => true, 'message' => 'Menu item added!', 'id' => $id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add item']);
}
mysqli_stmt_close($stmt);

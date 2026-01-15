<?php
session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$title = trim($_POST['name'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$buying_price = floatval($_POST['buying_price'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$discount_percent = intval($_POST['discount_percent'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 0);
$image_url = trim($_POST['image_url'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($id <= 0 || empty($title) || $category_id <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Validate buying price
if ($buying_price < 0) {
    $buying_price = 0;
}
if ($buying_price > $price) {
    echo json_encode(['success' => false, 'message' => 'Buying price must be less than or equal to selling price']);
    exit;
}

// Validate quantity and discount
if ($quantity < 0) {
    $quantity = 0;
}
if ($discount_percent < 0) $discount_percent = 0;
if ($discount_percent > 99) $discount_percent = 99;

$stmt = mysqli_prepare($conn, "UPDATE menu_items SET title = ?, price = ?, buying_price = ?, discount_percent = ?, image_url = ?, category_id = ?, description = ?, quantity = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'sddisisii', $title, $price, $buying_price, $discount_percent, $image_url, $category_id, $description, $quantity, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Menu item updated!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update']);
}
mysqli_stmt_close($stmt);

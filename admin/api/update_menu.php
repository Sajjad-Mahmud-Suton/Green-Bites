<?php
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
$price = floatval($_POST['price'] ?? 0);
$image_url = trim($_POST['image_url'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($id <= 0 || empty($title) || $category_id <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE menu_items SET title = ?, price = ?, image_url = ?, category_id = ?, description = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'sdsisi', $title, $price, $image_url, $category_id, $description, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Menu item updated!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update']);
}
mysqli_stmt_close($stmt);

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

$name = trim($_POST['name'] ?? '');
$icon = trim($_POST['icon'] ?? 'tag');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO categories (name, icon) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, 'ss', $name, $icon);

if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($conn);
    echo json_encode(['success' => true, 'message' => 'Category added!', 'id' => $id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add category. Name may already exist.']);
}
mysqli_stmt_close($stmt);

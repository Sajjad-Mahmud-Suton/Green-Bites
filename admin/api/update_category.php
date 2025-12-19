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
$name = trim($_POST['name'] ?? '');
$icon = trim($_POST['icon'] ?? 'tag');

if ($id <= 0 || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE categories SET name = ?, icon = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'ssi', $name, $icon, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Category updated!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update category']);
}
mysqli_stmt_close($stmt);

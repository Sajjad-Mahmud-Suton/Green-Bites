<?php
session_start();
header('Content-Type: application/json');
require_once '../../db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$result = mysqli_query($conn, "SELECT id, full_name, username, email, created_at FROM users ORDER BY created_at DESC");
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

echo json_encode(['success' => true, 'users' => $users]);

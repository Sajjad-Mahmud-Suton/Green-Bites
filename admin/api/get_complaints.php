<?php
session_start();
header('Content-Type: application/json');
require_once '../../db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM complaints ORDER BY created_at DESC");
$complaints = [];
while ($row = mysqli_fetch_assoc($result)) {
    $complaints[] = $row;
}

echo json_encode(['success' => true, 'complaints' => $complaints]);

<?php
session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$markAll = isset($_POST['mark_all']) && $_POST['mark_all'] === '1';

if ($markAll) {
    // Mark all as seen
    $result = mysqli_query($conn, "UPDATE complaints SET is_seen = 1 WHERE is_seen = 0");
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'All complaints marked as seen']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update']);
    }
} elseif ($id > 0) {
    // Mark single complaint as seen
    $stmt = mysqli_prepare($conn, "UPDATE complaints SET is_seen = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Complaint marked as seen']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid complaint ID']);
}

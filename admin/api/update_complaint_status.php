<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    UPDATE COMPLAINT STATUS API                            ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  Updates complaint status and admin response                              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$admin_response = trim($_POST['admin_response'] ?? '');

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid complaint ID']);
    exit;
}

// Validate status
$validStatuses = ['pending', 'seen', 'in_progress', 'resolved', 'closed'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Update complaint
$stmt = mysqli_prepare($conn, "UPDATE complaints SET status = ?, admin_response = ?, responded_at = NOW(), is_seen = 1 WHERE id = ?");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ssi', $status, $admin_response, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Complaint updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update complaint']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

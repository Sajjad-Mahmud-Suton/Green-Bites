<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                   GREEN BITES - DELETE COMPLAINT API                      ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * 
 * This API allows admin to delete a complaint from the system
 */

session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get complaint ID
$complaint_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($complaint_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid complaint ID']);
    exit;
}

// First, get the complaint to check if it has an image
$stmt = mysqli_prepare($conn, "SELECT image_path FROM complaints WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $complaint_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$complaint = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$complaint) {
    echo json_encode(['success' => false, 'message' => 'Complaint not found']);
    exit;
}

// Delete the image file if exists
if (!empty($complaint['image_path'])) {
    $image_file = __DIR__ . '/../../' . $complaint['image_path'];
    if (file_exists($image_file)) {
        unlink($image_file);
    }
}

// Delete the complaint from database
$stmt = mysqli_prepare($conn, "DELETE FROM complaints WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $complaint_id);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Complaint deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete complaint']);
}

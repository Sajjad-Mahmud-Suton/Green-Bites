<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  GREEN BITES - Update User Status API                                     ║
 * ║  Allows admin to change user status (active/paused/suspended)             ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * 
 * ACCEPTS: POST (JSON body)
 *   - user_id: User ID to update
 *   - status: New status (active/paused/suspended)
 * 
 * RETURNS: JSON { success, message }
 */

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../../db.php';

// Verify admin is logged in
if (!isset($_SESSION['admin_id'])) {
    jsonResponse(false, 'Unauthorized access');
}

$admin_id = $_SESSION['admin_id'];

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    jsonResponse(false, 'Invalid request data');
}

$user_id = intval($data['user_id'] ?? 0);
$new_status = $data['status'] ?? '';

if ($user_id <= 0) {
    jsonResponse(false, 'Invalid user ID');
}

// Validate status
$allowed_statuses = ['active', 'paused', 'suspended'];
if (!in_array($new_status, $allowed_statuses)) {
    jsonResponse(false, 'Invalid status. Must be: active, paused, or suspended');
}

// Check if user exists
$checkStmt = mysqli_prepare($conn, "SELECT id, status FROM users WHERE id = ?");
mysqli_stmt_bind_param($checkStmt, 'i', $user_id);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);
$user = mysqli_fetch_assoc($checkResult);
mysqli_stmt_close($checkStmt);

if (!$user) {
    jsonResponse(false, 'User not found');
}

if ($user['status'] === $new_status) {
    jsonResponse(true, 'User status is already ' . $new_status);
}

// Update user status
$sql = "UPDATE users SET 
        status = ?,
        status_changed_by = ?,
        status_changed_at = NOW()
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sii', $new_status, $admin_id, $user_id);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($success) {
    // Log the action
    $oldStatus = $user['status'];
    error_log("Admin #{$admin_id} changed user #{$user_id} status from '{$oldStatus}' to '{$new_status}'");
    
    jsonResponse(true, 'User status updated to ' . ucfirst($new_status), [
        'user_id' => $user_id,
        'old_status' => $oldStatus,
        'new_status' => $new_status
    ]);
} else {
    jsonResponse(false, 'Failed to update user status');
}

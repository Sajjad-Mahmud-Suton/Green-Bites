<?php
/**
 * Update Profile API Endpoint
 * ---------------------------
 * Handles profile updates including name, email, and password changes.
 * Requires user to be logged in and provide current password for changes.
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Utility: JSON response and exit
function respond($success, $message, $extra = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $extra);
    echo json_encode($response);
    exit;
}

// Enforce POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    respond(false, 'Please login to update your profile.');
}

// Verify CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (empty($csrf_token) || $csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    respond(false, 'Invalid security token. Please refresh and try again.');
}

$user_id = $_SESSION['user_id'];

// Get form data
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate required fields
if (empty($full_name)) {
    respond(false, 'Full name is required.');
}

if (empty($email)) {
    respond(false, 'Email is required.');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Invalid email format.');
}

// Validate name length
if (strlen($full_name) < 2 || strlen($full_name) > 100) {
    respond(false, 'Full name must be between 2 and 100 characters.');
}

// Fetch current user data
$stmt = mysqli_prepare($conn, "SELECT email, password FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    respond(false, 'User not found.');
}

// Check if email is being changed
$emailChanged = (strtolower($email) !== strtolower($user['email']));

// Check if password is being changed
$passwordChanged = !empty($new_password);

// If making any sensitive changes, require current password
if ($emailChanged || $passwordChanged) {
    if (empty($current_password)) {
        respond(false, 'Current password is required to change email or password.');
    }
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        respond(false, 'Current password is incorrect.');
    }
}

// If changing email, check if new email is already taken
if ($emailChanged) {
    $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
    mysqli_stmt_bind_param($checkStmt, 'si', $email, $user_id);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_fetch_assoc($checkResult)) {
        mysqli_stmt_close($checkStmt);
        respond(false, 'This email is already registered to another account.');
    }
    mysqli_stmt_close($checkStmt);
}

// Validate new password if provided
if ($passwordChanged) {
    if (strlen($new_password) < 6) {
        respond(false, 'New password must be at least 6 characters long.');
    }
    
    if ($new_password !== $confirm_password) {
        respond(false, 'New passwords do not match.');
    }
}

// Build update query
try {
    if ($passwordChanged) {
        // Update with new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $full_name, $email, $hashed_password, $user_id);
    } else {
        // Update without password
        $sql = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $full_name, $email, $user_id);
    }

    if (!$stmt) {
        respond(false, 'Database error. Please try again.');
    }

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        // Update session data
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;

        respond(true, 'Profile updated successfully!');
    } else {
        respond(false, 'Failed to update profile. Please try again.');
    }

} catch (Throwable $e) {
    respond(false, 'Unexpected error. Please try again later.');
}

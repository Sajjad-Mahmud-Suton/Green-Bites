<?php
/**
 * Reset Password Endpoint
 * -----------------------
 * Accepts POST: token, new_password, confirm_password, csrf_token
 * Validates token and expiry, updates user password, and deletes the reset token.
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

function respond($success, $message, $extra = [])
{
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $extra);
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

// CSRF validation
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    respond(false, 'Security validation failed. Please refresh the page and try again.');
}

$token            = trim($_POST['token'] ?? '');
$new_password     = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($token === '' || $new_password === '' || $confirm_password === '') {
    respond(false, 'All fields are required.');
}

if ($new_password !== $confirm_password) {
    respond(false, 'Passwords do not match.');
}

// Basic password strength check
if (strlen($new_password) < 8) {
    respond(false, 'Password must be at least 8 characters long.');
}

try {
    // Find reset token and ensure not expired
    $sql  = "SELECT email, expires_at FROM password_resets WHERE token = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        respond(false, 'Server error. Please try again later.');
    }

    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $reset  = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    if (!$reset) {
        respond(false, 'Invalid or expired reset token.');
    }

    if (strtotime($reset['expires_at']) < time()) {
        respond(false, 'Reset token has expired. Please request a new one.');
    }

    $email = $reset['email'];

    // Hash new password
    $passwordHash = password_hash($new_password, PASSWORD_BCRYPT);
    if ($passwordHash === false) {
        respond(false, 'Failed to secure your password. Please try again.');
    }

    // Update user password
    $updateSql  = "UPDATE users SET password_hash = ? WHERE email = ? LIMIT 1";
    $updateStmt = mysqli_prepare($conn, $updateSql);
    if (!$updateStmt) {
        respond(false, 'Server error. Please try again later.');
    }

    mysqli_stmt_bind_param($updateStmt, 'ss', $passwordHash, $email);
    $ok = mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);

    if (!$ok) {
        respond(false, 'Unable to reset password at the moment. Please try again.');
    }

    // Delete used token
    $deleteSql  = "DELETE FROM password_resets WHERE token = ? OR expires_at < NOW()";
    $deleteStmt = mysqli_prepare($conn, $deleteSql);
    if ($deleteStmt) {
        mysqli_stmt_bind_param($deleteStmt, 's', $token);
        mysqli_stmt_execute($deleteStmt);
        mysqli_stmt_close($deleteStmt);
    }

    respond(true, 'Password reset successful.', [
        'redirect' => 'login.php'
    ]);
} catch (Throwable $e) {
    respond(false, 'Unexpected server error. Please try again later.');
}



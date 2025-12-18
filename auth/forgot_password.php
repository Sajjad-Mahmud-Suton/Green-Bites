<?php
/**
 * Forgot Password Endpoint
 * ------------------------
 * Accepts POST: email, csrf_token
 * If email exists, creates a password reset token and stores it with 1-hour expiry.
 * Returns JSON with generic message and includes token for testing.
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

$email = trim($_POST['email'] ?? '');

if ($email === '') {
    respond(false, 'Email is required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}

try {
    // Check if email exists
    $sql  = "SELECT email FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        respond(false, 'Server error. Please try again later.');
    }

    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    // Always respond with generic message to avoid revealing if account exists
    $genericMessage = 'If this email is registered, a password reset link has been sent.';

    if (!$user) {
        respond(true, $genericMessage);
    }

    // Generate secure random token
    $token  = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now

    // Insert or update password_resets row
    $insertSql = "INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())";
    $insertStmt = mysqli_prepare($conn, $insertSql);
    if (!$insertStmt) {
        respond(false, 'Server error. Please try again later.');
    }

    mysqli_stmt_bind_param($insertStmt, 'sss', $email, $token, $expiry);
    $ok = mysqli_stmt_execute($insertStmt);
    mysqli_stmt_close($insertStmt);

    if (!$ok) {
        respond(false, 'Unable to process request at the moment. Please try again.');
    }

    // In production, you would send the email here.
    // For development/testing, we return the token directly.

    respond(true, $genericMessage, [
        'token' => $token
    ]);
} catch (Throwable $e) {
    respond(false, 'Unexpected server error. Please try again later.');
}



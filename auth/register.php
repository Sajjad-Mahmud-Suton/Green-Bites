<?php
/**
 * User Registration Endpoint
 * --------------------------
 * Accepts POST: full_name, username, email, password, confirm_password, csrf_token
 * Validates input, checks for duplicates, hashes password, and inserts new user.
 * Returns JSON response.
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Utility: JSON response and exit
function respond($success, $message, $extra = [])
{
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

// Basic CSRF protection
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    respond(false, 'Security validation failed. Please refresh the page and try again.');
}

// Retrieve and sanitize input
$full_name        = trim($_POST['full_name'] ?? '');
$username         = trim($_POST['username'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate required fields
if ($full_name === '' || $username === '' || $email === '' || $password === '' || $confirm_password === '') {
    respond(false, 'All fields are required.');
}

// Email format validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}

// Password confirmation
if ($password !== $confirm_password) {
    respond(false, 'Passwords do not match.');
}

// Password strength: min 8 chars, at least one upper, one lower, one number
$passwordErrors = [];
if (strlen($password) < 8) {
    $passwordErrors[] = 'at least 8 characters';
}
if (!preg_match('/[A-Z]/', $password)) {
    $passwordErrors[] = 'one uppercase letter';
}
if (!preg_match('/[a-z]/', $password)) {
    $passwordErrors[] = 'one lowercase letter';
}
if (!preg_match('/[0-9]/', $password)) {
    $passwordErrors[] = 'one number';
}

if (!empty($passwordErrors)) {
    respond(false, 'Password must contain ' . implode(', ', $passwordErrors) . '.');
}

// Check if username or email already exists using prepared statement
try {
    $sql = "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        respond(false, 'Server error. Please try again later.');
    }

    mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Do not reveal which one exists
        mysqli_stmt_close($stmt);
        respond(false, 'An account with these details already exists.');
    }
    mysqli_stmt_close($stmt);

    // Hash password securely
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    if ($passwordHash === false) {
        respond(false, 'Failed to secure your password. Please try again.');
    }

    // Insert new user
    $insertSql = "INSERT INTO users (full_name, username, email, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insertStmt = mysqli_prepare($conn, $insertSql);
    if (!$insertStmt) {
        respond(false, 'Server error. Please try again later.');
    }

    mysqli_stmt_bind_param($insertStmt, 'ssss', $full_name, $username, $email, $passwordHash);
    $ok = mysqli_stmt_execute($insertStmt);
    mysqli_stmt_close($insertStmt);

    if (!$ok) {
        respond(false, 'Unable to create account at the moment. Please try again.');
    }

    respond(true, 'Account created successfully.', [
        'redirect' => 'login.php'
    ]);
} catch (Throwable $e) {
    // Log error server-side in real applications
    respond(false, 'Unexpected server error. Please try again later.');
}



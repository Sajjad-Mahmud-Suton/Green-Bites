<?php
/**
 * User Login Endpoint - Secure Version
 * -------------------------------------
 * Accepts POST: email, password, csrf_token
 * Features: CSRF protection, brute force prevention, secure sessions
 */

require_once __DIR__ . '/../config/security.php';

initSecureSession();
setSecurityHeaders();
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

// Rate limiting
$clientIP = getClientIP();
if (!checkRateLimit($clientIP . '_login', 10, 60)) { // 10 login attempts per minute
    respond(false, 'Too many login attempts. Please try again later.');
}

// CSRF check
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    respond(false, 'Security validation failed. Please refresh the page and try again.');
}

// Check if IP is locked out from too many failed attempts
$lockoutMinutes = isLoginLocked($clientIP);
if ($lockoutMinutes) {
    securityLog('login_locked', 'Login attempt while locked out', ['ip' => $clientIP]);
    respond(false, "Too many failed attempts. Try again in $lockoutMinutes minutes.");
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    respond(false, 'Email and password are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}

// Lookup user by email using prepared statement
try {
    $sql  = "SELECT id, full_name, username, email, password FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        respond(false, 'Server error. Please try again later.');
    }

    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    // Use generic error to avoid revealing if email exists
    if (!$user || !password_verify($password, $user['password'])) {
        recordLoginAttempt($clientIP, false);
        securityLog('login_failed', 'Failed login attempt', ['email' => $email]);
        respond(false, 'Invalid email or password.');
    }

    // Successful login: clear failed attempts, regenerate session ID
    recordLoginAttempt($clientIP, true);
    session_regenerate_id(true);
    
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['full_name']   = $user['full_name'];
    $_SESSION['username']    = $user['username'];
    $_SESSION['email']       = $user['email'];
    $_SESSION['last_active'] = time();
    $_SESSION['user_ip']     = $clientIP;
    
    securityLog('login_success', 'Successful login', ['user_id' => $user['id'], 'email' => $email]);

    respond(true, 'Login successful.', [
        'redirect' => 'index.php',
        'user' => [
            'id'        => $user['id'],
            'full_name' => $user['full_name'],
            'username'  => $user['username'],
            'email'     => $user['email']
        ]
    ]);
} catch (Throwable $e) {
    respond(false, 'Unexpected server error. Please try again later.');
}



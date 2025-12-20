<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║   ██████╗ ██████╗ ███████╗███████╗███╗   ██╗    ██████╗ ██╗████████╗███████╗║
 * ║  ██╔════╝ ██╔══██╗██╔════╝██╔════╝████╗  ██║    ██╔══██╗██║╚══██╔══╝██╔════╝║
 * ║  ██║  ███╗██████╔╝█████╗  █████╗  ██╔██╗ ██║    ██████╔╝██║   ██║   █████╗  ║
 * ║  ██║   ██║██╔══██╗██╔══╝  ██╔══╝  ██║╚██╗██║    ██╔══██╗██║   ██║   ██╔══╝  ║
 * ║  ╚██████╔╝██║  ██║███████╗███████╗██║ ╚████║    ██████╔╝██║   ██║   ███████╗║
 * ║   ╚═════╝ ╚═╝  ╚═╝╚══════╝╚══════╝╚═╝  ╚═══╝    ╚═════╝ ╚═╝   ╚═╝   ╚══════╝║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: login.php                                                          ║
 * ║  PATH: /auth/login.php                                                    ║
 * ║  DESCRIPTION: User authentication endpoint (secure version)               ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  SECTIONS:                                                                ║
 * ║    1. Security Initialization                                             ║
 * ║    2. Request Validation (method, rate limit, CSRF)                       ║
 * ║    3. Brute Force Protection                                              ║
 * ║    4. Input Validation                                                    ║
 * ║    5. User Authentication                                                 ║
 * ║    6. Session Creation                                                    ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  ACCEPTS: POST                                                            ║
 * ║    - email: User's email address                                          ║
 * ║    - password: User's password                                            ║
 * ║    - csrf_token: CSRF protection token                                    ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FEATURES: CSRF protection, brute force prevention, secure sessions       ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 1: INITIALIZATION (Security disabled for development)
   ═══════════════════════════════════════════════════════════════════════════ */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';


/* ═══════════════════════════════════════════════════════════════════════════
   HELPER FUNCTION: JSON Response
   ═══════════════════════════════════════════════════════════════════════════ */

function respond($success, $message, $extra = [])
{
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $extra);
    echo json_encode($response);
    exit;
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 2: REQUEST VALIDATION (Security disabled for development)
   ═══════════════════════════════════════════════════════════════════════════ */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

// Rate limiting - DISABLED for development
// $clientIP = getClientIP();
// if (!checkRateLimit($clientIP . '_login', 10, 60)) {
//     respond(false, 'Too many login attempts. Please try again later.');
// }

// CSRF token validation - DISABLED for development
// $csrfToken = $_POST['csrf_token'] ?? '';
// if (!validateCSRFToken($csrfToken)) {
//     respond(false, 'Security validation failed. Please refresh the page and try again.');
// }


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 3: BRUTE FORCE PROTECTION - DISABLED for development
   ═══════════════════════════════════════════════════════════════════════════ */

// $lockoutMinutes = isLoginLocked($clientIP);
$lockoutMinutes = 0; // Disabled
if ($lockoutMinutes) {
    securityLog('login_locked', 'Login attempt while locked out', ['ip' => $clientIP]);
    respond(false, "Too many failed attempts. Try again in $lockoutMinutes minutes.");
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 4: INPUT VALIDATION
   ═══════════════════════════════════════════════════════════════════════════ */

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    respond(false, 'Email and password are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 5: USER AUTHENTICATION
   ═══════════════════════════════════════════════════════════════════════════ */

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


    /* ═══════════════════════════════════════════════════════════════════════════
       SECTION 6: SESSION CREATION (Successful Login)
       ═══════════════════════════════════════════════════════════════════════════ */

    // Clear failed attempts and regenerate session ID
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



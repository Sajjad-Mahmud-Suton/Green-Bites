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
 * ║  FILE: forgot_password.php                                                ║
 * ║  PATH: /auth/forgot_password.php                                          ║
 * ║  DESCRIPTION: Password reset request endpoint                             ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  SECTIONS:                                                                ║
 * ║    1. Initialization                                                      ║
 * ║    2. Request & CSRF Validation                                           ║
 * ║    3. Email Validation                                                    ║
 * ║    4. User Lookup & Token Generation                                      ║
 * ║    5. Email Sending                                                       ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  ACCEPTS: POST { email, csrf_token }                                      ║
 * ║  RETURNS: JSON { success: bool, message: string }                         ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 1: INITIALIZATION
   ═══════════════════════════════════════════════════════════════════════════ */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config/mail_helper.php';


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
   SECTION 2: REQUEST VALIDATION (CSRF disabled for development)
   ═══════════════════════════════════════════════════════════════════════════ */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

// CSRF validation - DISABLED for development
// $csrfToken = $_POST['csrf_token'] ?? '';
// if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
//     respond(false, 'Security validation failed. Please refresh the page and try again.');
// }


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 3: EMAIL VALIDATION
   ═══════════════════════════════════════════════════════════════════════════ */

$email = trim($_POST['email'] ?? '');

if ($email === '') {
    respond(false, 'Email is required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 4: USER LOOKUP & TOKEN GENERATION
   ═══════════════════════════════════════════════════════════════════════════ */

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
    $insertSql = "INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)";
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

    // Get user's name for personalized email
    $userName = 'User';
    $nameQuery = "SELECT full_name FROM users WHERE email = ? LIMIT 1";
    $nameStmt = mysqli_prepare($conn, $nameQuery);
    if ($nameStmt) {
        mysqli_stmt_bind_param($nameStmt, 's', $email);
        mysqli_stmt_execute($nameStmt);
        $nameResult = mysqli_stmt_get_result($nameStmt);
        if ($nameRow = mysqli_fetch_assoc($nameResult)) {
            $userName = $nameRow['full_name'] ?: 'User';
        }
        mysqli_stmt_close($nameStmt);
    }

    // Send password reset email
    // Set to false to send real emails via SMTP
    $skipEmail = false; // Email sending enabled
    
    if ($skipEmail) {
        // Development mode: return token directly without sending email
        respond(true, $genericMessage, [
            'token' => $token,
            'reset_link' => 'reset_password.php?token=' . $token,
            'dev_note' => 'Development mode: Email not sent. Click the link or copy token.'
        ]);
    }
    
    $emailResult = sendPasswordResetEmail($email, $token, $userName);
    
    if ($emailResult['success']) {
        respond(true, $genericMessage);
    } else {
        // Email failed but token was created - still show generic message for security
        respond(true, $genericMessage, [
            'token' => $token,
            'dev_note' => 'Email sending failed. Configure SMTP in config/email.php. Use this token to test: reset_password.php?token=' . $token
        ]);
    }
} catch (Throwable $e) {
    // Show detailed error in development
    respond(false, 'Server error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
}



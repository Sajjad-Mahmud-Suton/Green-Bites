<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    GREEN BITES - SEND OTP API                             ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * 
 * Sends a 6-digit OTP code to the user's email for verification during signup
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config/mail_helper.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

$email = trim($_POST['email'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');

if (empty($email)) {
    respond(false, 'Email is required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}

// Check if email already exists in database
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    respond(false, 'This email is already registered. Please login instead.');
}
mysqli_stmt_close($stmt);

// Check if username already exists
if (!empty($username)) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        respond(false, 'This username is already taken. Please choose another.');
    }
    mysqli_stmt_close($stmt);
}

// Generate 6-digit OTP
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Store OTP in session with expiry (2 minutes)
$_SESSION['signup_otp'] = $otp;
$_SESSION['signup_otp_email'] = $email;
$_SESSION['signup_otp_expires'] = time() + 120; // 2 minutes

// Send OTP email
$emailResult = sendOTPEmail($email, $otp, $full_name ?: 'User');

if ($emailResult['success']) {
    respond(true, 'Verification code sent to your email!');
} else {
    respond(false, 'Failed to send verification code. Please try again.');
}

<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    GREEN BITES - VERIFY OTP API                           ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * 
 * Verifies the OTP and creates the user account if valid
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

$otp = trim($_POST['otp'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate OTP
if (empty($otp)) {
    respond(false, 'Please enter the verification code.');
}

// Check if OTP session exists
if (!isset($_SESSION['signup_otp']) || !isset($_SESSION['signup_otp_email']) || !isset($_SESSION['signup_otp_expires'])) {
    respond(false, 'Verification session expired. Please request a new code.');
}

// Check if OTP expired
if (time() > $_SESSION['signup_otp_expires']) {
    unset($_SESSION['signup_otp'], $_SESSION['signup_otp_email'], $_SESSION['signup_otp_expires']);
    respond(false, 'Verification code has expired. Please request a new code.');
}

// Check if email matches
if ($_SESSION['signup_otp_email'] !== $email) {
    respond(false, 'Email mismatch. Please request a new code.');
}

// Verify OTP
if ($_SESSION['signup_otp'] !== $otp) {
    respond(false, 'Invalid verification code. Please try again.');
}

// OTP is valid - now create the account
// Clear OTP session
unset($_SESSION['signup_otp'], $_SESSION['signup_otp_email'], $_SESSION['signup_otp_expires']);

// Validate required fields
if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
    respond(false, 'All fields are required.');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}

// Check for duplicate username/email
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    respond(false, 'An account with these details already exists.');
}
mysqli_stmt_close($stmt);

// Hash password
$passwordHash = password_hash($password, PASSWORD_BCRYPT);
if ($passwordHash === false) {
    respond(false, 'Failed to secure your password. Please try again.');
}

// Create user account
$insertSql = "INSERT INTO users (full_name, username, email, password, created_at) VALUES (?, ?, ?, ?, NOW())";
$insertStmt = mysqli_prepare($conn, $insertSql);
if (!$insertStmt) {
    respond(false, 'Server error. Please try again later.');
}

mysqli_stmt_bind_param($insertStmt, 'ssss', $full_name, $username, $email, $passwordHash);
$ok = mysqli_stmt_execute($insertStmt);
mysqli_stmt_close($insertStmt);

if (!$ok) {
    respond(false, 'Unable to create account. Please try again.');
}

respond(true, 'Account created successfully! Redirecting to login...', [
    'redirect' => 'login.php'
]);

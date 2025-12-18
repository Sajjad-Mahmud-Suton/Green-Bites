<?php
/**
 * Username Availability Check Endpoint
 * ------------------------------------
 * Accepts POST: username, csrf_token
 * Returns JSON: { available: bool }
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

function respond($available)
{
    echo json_encode([
        'available' => $available
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false);
}

// CSRF validation
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    respond(false);
}

$username = trim($_POST['username'] ?? '');

if ($username === '' || strlen($username) < 3) {
    respond(false);
}

try {
    $sql  = "SELECT id FROM users WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        respond(false);
    }

    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $available = mysqli_stmt_num_rows($stmt) === 0;
    mysqli_stmt_close($stmt);

    respond($available);
} catch (Throwable $e) {
    respond(false);
}



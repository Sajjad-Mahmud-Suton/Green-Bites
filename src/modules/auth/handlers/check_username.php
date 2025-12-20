<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                GREEN BITES - CHECK USERNAME ENDPOINT                      ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Accepts POST: username, csrf_token
 * Returns JSON: { available: bool }
 */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../../config/bootstrap.php';

session_start();
header('Content-Type: application/json');

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



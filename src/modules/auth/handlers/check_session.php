<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                 GREEN BITES - CHECK SESSION ENDPOINT                      ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Returns JSON describing whether the user is logged in and basic profile info.
 * Also enforces a simple inactivity timeout.
 */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../../config/bootstrap.php';

session_start();
header('Content-Type: application/json');

// Session timeout in seconds (e.g. 30 minutes)
$timeout = 30 * 60;

// Handle session timeout logic
if (isset($_SESSION['user_id'])) {
    $now = time();
    if (isset($_SESSION['last_active']) && ($now - (int)$_SESSION['last_active']) > $timeout) {
        // Session expired, destroy it
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        echo json_encode([
            'logged_in' => false
        ]);
        exit;
    }

    // Update last active timestamp
    $_SESSION['last_active'] = $now;

    echo json_encode([
        'logged_in' => true,
        'user_id'   => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'] ?? '',
        'username'  => $_SESSION['username'] ?? '',
        'email'     => $_SESSION['email'] ?? ''
    ]);
    exit;
}

echo json_encode([
    'logged_in' => false
]);



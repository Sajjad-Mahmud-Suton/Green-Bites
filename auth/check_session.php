<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  GREEN BITES - Check Session Endpoint                                     ║
 * ║  Returns JSON describing whether the user is logged in                    ║
 * ║  Also enforces inactivity timeout and user status checks                  ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Session timeout in seconds (e.g. 30 minutes)
$timeout = 30 * 60;

/**
 * Helper function to destroy session and return logged out response
 */
function destroySessionAndReturn($reason = null) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    
    $response = ['logged_in' => false];
    if ($reason) {
        $response['reason'] = $reason;
        $response['message'] = getStatusMessage($reason);
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Get user-friendly message for status
 */
function getStatusMessage($status) {
    switch ($status) {
        case 'paused':
            return 'Your account has been temporarily paused. Please contact support for assistance.';
        case 'suspended':
            return 'Your account has been suspended. Please contact support for more information.';
        case 'timeout':
            return 'Your session has expired. Please log in again.';
        default:
            return 'You have been logged out.';
    }
}

// Handle session timeout logic
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $now = time();
    
    // Check session timeout
    if (isset($_SESSION['last_active']) && ($now - (int)$_SESSION['last_active']) > $timeout) {
        destroySessionAndReturn('timeout');
    }
    
    // Check user status in database
    $stmt = mysqli_prepare($conn, "SELECT status FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$user) {
        // User no longer exists
        destroySessionAndReturn('deleted');
    }
    
    $status = $user['status'] ?? 'active';
    
    // Check if user is paused or suspended
    if ($status === 'paused' || $status === 'suspended') {
        destroySessionAndReturn($status);
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



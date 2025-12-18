<?php
/**
 * User Logout Endpoint
 * --------------------
 * Destroys the current session and returns JSON response.
 */

session_start();
header('Content-Type: application/json');

function respond($success, $extra = [])
{
    $response = array_merge([
        'success' => $success
    ], $extra);
    echo json_encode($response);
    exit;
}

// Only allow POST to prevent CSRF via simple link
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ['message' => 'Invalid request method.']);
}

// CSRF token optional here but recommended
$csrfToken = $_POST['csrf_token'] ?? '';
if (!empty($_SESSION['csrf_token']) && !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    respond(false, ['message' => 'Security validation failed.']);
}

// Clear session data
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

respond(true, ['redirect' => 'index.php']);



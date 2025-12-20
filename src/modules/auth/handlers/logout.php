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
 * ║  FILE: logout.php                                                         ║
 * ║  PATH: /auth/logout.php                                                   ║
 * ║  DESCRIPTION: User logout endpoint - destroys session                     ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  SECTIONS:                                                                ║
 * ║    1. Initialization                                                      ║
 * ║    2. Request Validation                                                  ║
 * ║    3. Session Destruction                                                 ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  ACCEPTS: POST { csrf_token (optional) }                                  ║
 * ║  RETURNS: JSON { success: bool, redirect: string }                        ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 1: INITIALIZATION
   ═══════════════════════════════════════════════════════════════════════════ */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../../config/bootstrap.php';

session_start();
header('Content-Type: application/json');


/* ═══════════════════════════════════════════════════════════════════════════
   HELPER FUNCTION: JSON Response
   ═══════════════════════════════════════════════════════════════════════════ */

function respond($success, $extra = [])
{
    $response = array_merge([
        'success' => $success
    ], $extra);
    echo json_encode($response);
    exit;
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 2: REQUEST VALIDATION
   ═══════════════════════════════════════════════════════════════════════════ */

// Only allow POST to prevent CSRF via simple link
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ['message' => 'Invalid request method.']);
}

// CSRF token optional here but recommended
$csrfToken = $_POST['csrf_token'] ?? '';
if (!empty($_SESSION['csrf_token']) && !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    respond(false, ['message' => 'Security validation failed.']);
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 3: SESSION DESTRUCTION
   ═══════════════════════════════════════════════════════════════════════════ */

// Clear session data
$_SESSION = [];

// Delete session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy session
session_destroy();

respond(true, ['redirect' => 'index.php']);



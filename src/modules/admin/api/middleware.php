<?php
/**
 * Admin API Middleware
 * --------------------
 * Include this at the top of all admin API files for security
 */

require_once __DIR__ . '/../../config/security.php';

// Secure session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Set security headers
setSecurityHeaders();
header('Content-Type: application/json');

// Check admin authentication
function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Session timeout (30 minutes)
    if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time'] > 1800)) {
        session_unset();
        session_destroy();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        exit;
    }
    
    // IP consistency check
    if (isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== getClientIP()) {
        securityLog('admin_api_hijack', 'Possible API session hijack', [
            'session_ip' => $_SESSION['admin_ip'],
            'current_ip' => getClientIP()
        ]);
        session_unset();
        session_destroy();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security error']);
        exit;
    }
    
    // Update activity
    $_SESSION['admin_login_time'] = time();
    
    return true;
}

// Verify CSRF token
function requireCSRF($token = null) {
    if ($token === null) {
        // Try to get from POST or JSON body
        $token = $_POST['csrf_token'] ?? null;
        
        if ($token === null) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $token = $data['csrf_token'] ?? null;
        }
    }
    
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security validation failed']);
        exit;
    }
    
    return true;
}

// Rate limit for APIs
function apiRateLimit($limit = 60) {
    $clientIP = getClientIP();
    if (!checkRateLimit($clientIP . '_api', $limit, 60)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many requests']);
        exit;
    }
    return true;
}

// Enforce POST method
function requirePOST() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    return true;
}

// Standard JSON response
function apiResponse($success, $message, $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

// Initialize - call this at top of each admin API file
function initAdminAPI() {
    requireAdmin();
    apiRateLimit();
    return true;
}

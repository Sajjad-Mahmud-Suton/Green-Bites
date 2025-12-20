<?php
/**
 * Green Bites Security Configuration
 * ===================================
 * Comprehensive security settings and helper functions
 * Include this file at the top of all PHP files for maximum security
 */

// ============================================
// ERROR HANDLING (Hide errors in production)
// ============================================
define('PRODUCTION_MODE', false); // Set to true in production

if (PRODUCTION_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// ============================================
// SECURITY CONSTANTS
// ============================================
define('SESSION_TIMEOUT', 1800);           // 30 minutes session timeout
define('MAX_LOGIN_ATTEMPTS', 5);           // Max failed login attempts
define('LOGIN_LOCKOUT_TIME', 900);         // 15 minutes lockout
define('CSRF_TOKEN_EXPIRY', 3600);         // 1 hour CSRF token expiry
define('PASSWORD_MIN_LENGTH', 8);
define('RATE_LIMIT_REQUESTS', 100);        // Max requests per minute
define('RATE_LIMIT_WINDOW', 60);           // Rate limit window in seconds

// ============================================
// SECURE HEADERS
// ============================================
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS filter
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions policy
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
    
    // Content Security Policy (adjust as needed)
    // header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https:;");
    
    // Remove PHP version
    header_remove('X-Powered-By');
}

// ============================================
// SECURE SESSION CONFIGURATION
// ============================================
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Secure session cookie settings
        $cookieParams = [
            'lifetime' => 0,                    // Session cookie
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']), // HTTPS only if available
            'httponly' => true,                 // No JavaScript access
            'samesite' => 'Strict'              // CSRF protection
        ];
        
        session_set_cookie_params($cookieParams);
        
        // Use secure session ID settings
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.entropy_length', 32);
        ini_set('session.hash_function', 'sha256');
        
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
    
    // Check session timeout
    if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    
    $_SESSION['last_active'] = time();
    return true;
}

// ============================================
// CSRF TOKEN MANAGEMENT
// ============================================
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function getCSRFInput() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

// ============================================
// INPUT SANITIZATION
// ============================================
function sanitizeInput($data, $type = 'string') {
    if (is_array($data)) {
        return array_map(function($item) use ($type) {
            return sanitizeInput($item, $type);
        }, $data);
    }
    
    $data = trim($data);
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var($data, FILTER_SANITIZE_URL);
        case 'html':
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        case 'alphanumeric':
            return preg_replace('/[^a-zA-Z0-9]/', '', $data);
        case 'string':
        default:
            // Remove null bytes and encode special chars
            $data = str_replace(chr(0), '', $data);
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// ============================================
// INPUT VALIDATION
// ============================================
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'at least ' . PASSWORD_MIN_LENGTH . ' characters';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'one number';
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = 'one special character';
    }
    
    return empty($errors) ? true : $errors;
}

function validateUsername($username) {
    // Alphanumeric and underscore only, 3-30 characters
    return preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username);
}

// ============================================
// RATE LIMITING (Simple file-based)
// ============================================
function checkRateLimit($identifier, $maxRequests = RATE_LIMIT_REQUESTS, $windowSeconds = RATE_LIMIT_WINDOW) {
    $rateLimitDir = __DIR__ . '/../logs/rate_limits/';
    
    if (!is_dir($rateLimitDir)) {
        mkdir($rateLimitDir, 0755, true);
    }
    
    $file = $rateLimitDir . md5($identifier) . '.json';
    $now = time();
    
    $data = ['requests' => [], 'blocked_until' => 0];
    
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: $data;
    }
    
    // Check if blocked
    if ($data['blocked_until'] > $now) {
        return false;
    }
    
    // Clean old requests
    $data['requests'] = array_filter($data['requests'], function($timestamp) use ($now, $windowSeconds) {
        return ($now - $timestamp) < $windowSeconds;
    });
    
    // Check rate limit
    if (count($data['requests']) >= $maxRequests) {
        $data['blocked_until'] = $now + $windowSeconds;
        file_put_contents($file, json_encode($data));
        return false;
    }
    
    // Add current request
    $data['requests'][] = $now;
    file_put_contents($file, json_encode($data));
    
    return true;
}

// ============================================
// LOGIN ATTEMPT TRACKING
// ============================================
function recordLoginAttempt($identifier, $success = false) {
    $file = __DIR__ . '/../logs/login_attempts/' . md5($identifier) . '.json';
    $dir = dirname($file);
    
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $now = time();
    $data = ['attempts' => [], 'locked_until' => 0];
    
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: $data;
    }
    
    if ($success) {
        // Clear on successful login
        @unlink($file);
        return true;
    }
    
    // Clean old attempts (older than lockout time)
    $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($now) {
        return ($now - $timestamp) < LOGIN_LOCKOUT_TIME;
    });
    
    // Add failed attempt
    $data['attempts'][] = $now;
    
    // Check if should be locked
    if (count($data['attempts']) >= MAX_LOGIN_ATTEMPTS) {
        $data['locked_until'] = $now + LOGIN_LOCKOUT_TIME;
    }
    
    file_put_contents($file, json_encode($data));
    
    return count($data['attempts']) < MAX_LOGIN_ATTEMPTS;
}

function isLoginLocked($identifier) {
    $file = __DIR__ . '/../logs/login_attempts/' . md5($identifier) . '.json';
    
    if (!file_exists($file)) {
        return false;
    }
    
    $data = json_decode(file_get_contents($file), true);
    
    if (!$data) {
        return false;
    }
    
    if (isset($data['locked_until']) && $data['locked_until'] > time()) {
        return ceil(($data['locked_until'] - time()) / 60); // Minutes remaining
    }
    
    return false;
}

// ============================================
// SECURE FILE UPLOAD
// ============================================
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $maxSize = 5242880) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by extension',
        ];
        $errors[] = $uploadErrors[$file['error']] ?? 'Unknown upload error';
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size exceeds ' . ($maxSize / 1048576) . 'MB limit';
    }
    
    // Check MIME type using finfo (more reliable than $_FILES['type'])
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes);
    }
    
    // Check for PHP code in file content
    $content = file_get_contents($file['tmp_name']);
    if (preg_match('/<\?php|<\?=|<%/i', $content)) {
        $errors[] = 'File contains potentially malicious content';
    }
    
    // Validate image dimensions for image files
    if (empty($errors) && strpos($mimeType, 'image/') === 0) {
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'File is not a valid image';
        }
    }
    
    return $errors;
}

function generateSecureFilename($originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $safeName = bin2hex(random_bytes(16));
    return $safeName . '.' . $extension;
}

// ============================================
// SQL INJECTION PREVENTION HELPERS
// ============================================
function prepareAndExecute($conn, $sql, $types, $params) {
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        if (PRODUCTION_MODE) {
            error_log('SQL Prepare Error: ' . mysqli_error($conn));
            return false;
        }
        throw new Exception('Database error');
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        if (PRODUCTION_MODE) {
            error_log('SQL Execute Error: ' . mysqli_stmt_error($stmt));
            return false;
        }
        throw new Exception('Database error');
    }
    
    return $stmt;
}

// ============================================
// LOGGING
// ============================================
function securityLog($type, $message, $data = []) {
    $logDir = __DIR__ . '/../logs/security/';
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . date('Y-m-d') . '.log';
    
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? null,
        'data' => $data
    ];
    
    file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);
}

// ============================================
// IP BLOCKING
// ============================================
function isIPBlocked($ip) {
    $file = __DIR__ . '/../logs/blocked_ips.json';
    
    if (!file_exists($file)) {
        return false;
    }
    
    $blocked = json_decode(file_get_contents($file), true) ?: [];
    
    foreach ($blocked as $blockedIP => $until) {
        if ($blockedIP === $ip && $until > time()) {
            return true;
        }
    }
    
    return false;
}

function blockIP($ip, $duration = 3600) {
    $file = __DIR__ . '/../logs/blocked_ips.json';
    $blocked = [];
    
    if (file_exists($file)) {
        $blocked = json_decode(file_get_contents($file), true) ?: [];
    }
    
    $blocked[$ip] = time() + $duration;
    file_put_contents($file, json_encode($blocked));
    
    securityLog('ip_blocked', 'IP blocked', ['ip' => $ip, 'duration' => $duration]);
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
function getClientIP() {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            // Handle comma-separated IPs (X-Forwarded-For)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function jsonResponse($success, $message, $extra = [], $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));
    exit;
}

// ============================================
// INITIALIZE SECURITY
// ============================================
function initSecurity() {
    // Set security headers
    setSecurityHeaders();
    
    // Initialize secure session
    initSecureSession();
    
    // Check if IP is blocked
    $clientIP = getClientIP();
    if (isIPBlocked($clientIP)) {
        http_response_code(403);
        die('Access Denied');
    }
    
    // Basic rate limiting
    if (!checkRateLimit($clientIP)) {
        http_response_code(429);
        die('Too Many Requests. Please try again later.');
    }
    
    // Generate CSRF token
    generateCSRFToken();
}

// Auto-initialize if requested
// initSecurity();

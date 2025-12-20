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
 * ║  FILE: paths.php                                                          ║
 * ║  PATH: /src/config/paths.php                                              ║
 * ║  DESCRIPTION: Central path configuration for the entire application       ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  USAGE: Include this file at the top of every PHP file                    ║
 * ║         require_once __DIR__ . '/../../config/paths.php'; (from modules)  ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 1: ROOT PATHS (Server/File System)
   ═══════════════════════════════════════════════════════════════════════════ */

// Base root path (Green-Bites folder)
define('ROOT_PATH', dirname(dirname(__DIR__)));

// Source folder path
define('SRC_PATH', dirname(__DIR__));


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 2: ASSET PATHS (File System)
   ═══════════════════════════════════════════════════════════════════════════ */

define('ASSETS_PATH', SRC_PATH . '/assets');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('IMAGES_PATH', ASSETS_PATH . '/images');


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 3: MODULE PATHS (File System)
   ═══════════════════════════════════════════════════════════════════════════ */

define('MODULES_PATH', SRC_PATH . '/modules');
define('AUTH_PATH', MODULES_PATH . '/auth');
define('AUTH_VIEWS_PATH', AUTH_PATH . '/views');
define('AUTH_HANDLERS_PATH', AUTH_PATH . '/handlers');
define('ADMIN_PATH', MODULES_PATH . '/admin');
define('ADMIN_API_PATH', ADMIN_PATH . '/api');
define('API_PATH', MODULES_PATH . '/api');
define('USER_PATH', MODULES_PATH . '/user');
define('PAGES_PATH', MODULES_PATH . '/pages');
define('MENU_PAGES_PATH', PAGES_PATH . '/menu');
define('POLICY_PAGES_PATH', PAGES_PATH . '/policies');
define('SUPPORT_PAGES_PATH', PAGES_PATH . '/support');


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 4: CONFIG & INCLUDES PATHS (File System)
   ═══════════════════════════════════════════════════════════════════════════ */

define('CONFIG_PATH', SRC_PATH . '/config');
define('INCLUDES_PATH', SRC_PATH . '/includes');
define('COMPONENTS_PATH', INCLUDES_PATH . '/components');
define('HELPERS_PATH', INCLUDES_PATH . '/helpers');
define('DATABASE_PATH', SRC_PATH . '/database');
define('VENDOR_PATH', SRC_PATH . '/vendor');


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 5: URL PATHS (For HTML/CSS/JS references)
   ═══════════════════════════════════════════════════════════════════════════ */

// Detect base URL automatically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);

// Find the Green-Bites folder in the path
$basePath = '';
if (strpos($scriptPath, '/Green-Bites') !== false) {
    $basePath = substr($scriptPath, 0, strpos($scriptPath, '/Green-Bites') + strlen('/Green-Bites'));
} elseif (strpos($scriptPath, '/green-bites') !== false) {
    $basePath = substr($scriptPath, 0, strpos($scriptPath, '/green-bites') + strlen('/green-bites'));
} else {
    // Fallback for root
    $basePath = '/Green-Bites';
}

define('BASE_URL', $protocol . '://' . $host . $basePath);
define('SRC_URL', BASE_URL . '/src');
define('ASSETS_URL', SRC_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 6: MODULE URLs
   ═══════════════════════════════════════════════════════════════════════════ */

define('MODULES_URL', SRC_URL . '/modules');
define('AUTH_URL', MODULES_URL . '/auth');
define('AUTH_VIEWS_URL', AUTH_URL . '/views');
define('AUTH_HANDLERS_URL', AUTH_URL . '/handlers');
define('ADMIN_URL', MODULES_URL . '/admin');
define('ADMIN_API_URL', ADMIN_URL . '/api');
define('API_URL', MODULES_URL . '/api');
define('USER_URL', MODULES_URL . '/user');
define('PAGES_URL', MODULES_URL . '/pages');
define('MENU_PAGES_URL', PAGES_URL . '/menu');
define('POLICY_PAGES_URL', PAGES_URL . '/policies');
define('SUPPORT_PAGES_URL', PAGES_URL . '/support');


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 7: HELPER FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get asset URL (CSS, JS, Images)
 * @param string $type - 'css', 'js', or 'images'
 * @param string $file - filename
 * @return string Full URL to asset
 */
function asset($type, $file) {
    switch ($type) {
        case 'css':
            return CSS_URL . '/' . $file;
        case 'js':
            return JS_URL . '/' . $file;
        case 'images':
        case 'img':
            return IMAGES_URL . '/' . $file;
        default:
            return ASSETS_URL . '/' . $file;
    }
}

/**
 * Get module URL
 * @param string $module - 'auth', 'admin', 'api', 'user', 'pages'
 * @param string $file - filename (optional)
 * @return string Full URL to module
 */
function moduleUrl($module, $file = '') {
    $urls = [
        'auth' => AUTH_URL,
        'admin' => ADMIN_URL,
        'api' => API_URL,
        'user' => USER_URL,
        'pages' => PAGES_URL
    ];
    
    $base = $urls[$module] ?? MODULES_URL;
    return $file ? $base . '/' . $file : $base;
}

/**
 * Include a component file
 * @param string $component - component name (without .php)
 */
function component($component) {
    require_once COMPONENTS_PATH . '/' . $component . '.php';
}

/**
 * Include a helper file
 * @param string $helper - helper name (without .php)
 */
function helper($helper) {
    require_once HELPERS_PATH . '/' . $helper . '.php';
}

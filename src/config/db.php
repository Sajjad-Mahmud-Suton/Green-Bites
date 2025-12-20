<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    GREEN BITES - DATABASE CONNECTION                      ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  Description : Secure database connection handler                         ║
 * ║  Security    : Blocks direct access, uses prepared statements             ║
 * ║  Charset     : UTF-8 MB4 for emoji support                                ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  USAGE:                                                                   ║
 * ║  require_once 'db.php';                                                   ║
 * ║  // Use $conn for database queries                                        ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// ═══════════════════════════════════════════════════════════════════════════
// SECURITY: Block direct access to this file
// ═══════════════════════════════════════════════════════════════════════════
if (basename($_SERVER['PHP_SELF']) === 'db.php') {
    http_response_code(403);
    die('Access Denied');
}

// ═══════════════════════════════════════════════════════════════════════════
// DATABASE CREDENTIALS
// Note: Move to environment variables in production!
// ═══════════════════════════════════════════════════════════════════════════
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'green_bites');

// ═══════════════════════════════════════════════════════════════════════════
// ESTABLISH CONNECTION
// ═══════════════════════════════════════════════════════════════════════════
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    error_log("Database Connection Failed: " . mysqli_connect_error());
    
    if (defined('PRODUCTION_MODE') && PRODUCTION_MODE) {
        die("Service temporarily unavailable. Please try again later.");
    } else {
        die("Database Connection Failed: " . mysqli_connect_error());
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// SECURITY SETTINGS
// ═══════════════════════════════════════════════════════════════════════════
mysqli_set_charset($conn, 'utf8mb4');
mysqli_query($conn, "SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
?>

<?php
/**
 * Database Connection - Secure Version
 * -------------------------------------
 * Only allow internal includes, not direct access
 */

// Block direct access
if (basename($_SERVER['PHP_SELF']) === 'db.php') {
    http_response_code(403);
    die('Access Denied');
}

// Database credentials (move to environment variables in production)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'green_bites');

// Create connection with error handling
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    // Log error but don't expose details
    error_log("Database Connection Failed: " . mysqli_connect_error());
    
    // Show generic message
    if (defined('PRODUCTION_MODE') && PRODUCTION_MODE) {
        die("Service temporarily unavailable. Please try again later.");
    } else {
        die("Database Connection Failed: " . mysqli_connect_error());
    }
}

// Set charset to prevent encoding attacks
mysqli_set_charset($conn, 'utf8mb4');

// Enable strict mode
mysqli_query($conn, "SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
?>

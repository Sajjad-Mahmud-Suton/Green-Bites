<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    ADMIN SETTINGS API                                     ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Get and update admin settings                                          ║
 * ║  • Includes complaint submission toggle                                   ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

// Ensure settings table exists
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'admin_settings'");
if (mysqli_num_rows($tableCheck) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `admin_settings` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `setting_key` VARCHAR(100) NOT NULL UNIQUE,
        `setting_value` TEXT NOT NULL,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    
    // Insert default settings
    mysqli_query($conn, "INSERT IGNORE INTO `admin_settings` (`setting_key`, `setting_value`) VALUES 
        ('complaints_enabled', '1'),
        ('complaints_disabled_message', 'Complaint submission is currently closed. Please try again later.')");
}

// Handle GET request - get settings
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $key = $_GET['key'] ?? '';
    
    if ($key) {
        $stmt = mysqli_prepare($conn, "SELECT setting_value FROM admin_settings WHERE setting_key = ?");
        mysqli_stmt_bind_param($stmt, 's', $key);
        mysqli_stmt_execute($stmt);
        $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true,
            'key' => $key,
            'value' => $result ? $result['setting_value'] : null
        ]);
    } else {
        // Get all settings
        $result = mysqli_query($conn, "SELECT setting_key, setting_value FROM admin_settings");
        $settings = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        echo json_encode(['success' => true, 'settings' => $settings]);
    }
    exit;
}

// Handle POST request - update settings (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $csrf = $data['csrf_token'] ?? '';
    if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        exit;
    }
    
    $key = $data['key'] ?? '';
    $value = $data['value'] ?? '';
    
    if (empty($key)) {
        echo json_encode(['success' => false, 'message' => 'Setting key required']);
        exit;
    }
    
    // Update or insert setting
    $stmt = mysqli_prepare($conn, "INSERT INTO admin_settings (setting_key, setting_value) VALUES (?, ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
    mysqli_stmt_bind_param($stmt, 'sss', $key, $value, $value);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Setting updated!']);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Failed to update setting']);
    }
    exit;
}

mysqli_close($conn);
?>

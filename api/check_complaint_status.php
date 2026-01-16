<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    CHECK COMPLAINT STATUS API                             ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  • Returns whether complaint submission is enabled or disabled            ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// Check if complaints are enabled
$enabled = true;
$message = '';

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'admin_settings'");
if (mysqli_num_rows($tableCheck) > 0) {
    $settingResult = mysqli_query($conn, "SELECT setting_value FROM admin_settings WHERE setting_key = 'complaints_enabled'");
    if ($setting = mysqli_fetch_assoc($settingResult)) {
        $enabled = $setting['setting_value'] === '1';
    }
    
    // Get disabled message
    $msgResult = mysqli_query($conn, "SELECT setting_value FROM admin_settings WHERE setting_key = 'complaints_disabled_message'");
    if ($msg = mysqli_fetch_assoc($msgResult)) {
        $message = $msg['setting_value'];
    }
}

echo json_encode([
    'success' => true,
    'enabled' => $enabled,
    'message' => $message ?: 'Complaint submission is currently closed. Please try again later.'
]);

mysqli_close($conn);
?>

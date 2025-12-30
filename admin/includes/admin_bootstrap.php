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
 * ║  ADMIN INCLUDES - BOOTSTRAP FILE                                          ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: admin_bootstrap.php                                                ║
 * ║  PATH: /admin/includes/admin_bootstrap.php                                ║
 * ║  DESCRIPTION: Main loader for all admin include files                     ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  USAGE:                                                                   ║
 * ║    define('ADMIN_ACCESS', true);                                          ║
 * ║    require_once 'includes/admin_bootstrap.php';                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  INCLUDED MODULES:                                                        ║
 * ║    ┌─────────────────┬─────────────────────────────────────────────────┐  ║
 * ║    │ orders.php      │ Order management functions                      │  ║
 * ║    │ menu.php        │ Menu item & stock management                    │  ║
 * ║    │ users.php       │ User account management                         │  ║
 * ║    │ complaints.php  │ Complaint handling functions                    │  ║
 * ║    │ categories.php  │ Category management                             │  ║
 * ║    │ reports.php     │ Analytics & reporting                           │  ║
 * ║    └─────────────────┴─────────────────────────────────────────────────┘  ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// ═══════════════════════════════════════════════════════════════════════════
// SECURITY: Verify admin access constant is defined
// ═══════════════════════════════════════════════════════════════════════════
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not allowed. Please define ADMIN_ACCESS constant.');
}

// ═══════════════════════════════════════════════════════════════════════════
// DEFINE INCLUDE PATH
// ═══════════════════════════════════════════════════════════════════════════
define('ADMIN_INCLUDES_PATH', __DIR__);

// ═══════════════════════════════════════════════════════════════════════════
// LOAD ALL ADMIN MODULES
// ═══════════════════════════════════════════════════════════════════════════

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │ MODULE 1: ORDER MANAGEMENT                                              │
 * │ Functions: getAllOrders, updateOrderStatus, getOrderStatistics, etc.   │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
require_once ADMIN_INCLUDES_PATH . '/orders.php';

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │ MODULE 2: MENU MANAGEMENT                                               │
 * │ Functions: getAllMenuItems, addMenuItem, updateItemQuantity, etc.      │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
require_once ADMIN_INCLUDES_PATH . '/menu.php';

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │ MODULE 3: USER MANAGEMENT                                               │
 * │ Functions: getAllUsers, getUserOrders, getUserStatistics, etc.         │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
require_once ADMIN_INCLUDES_PATH . '/users.php';

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │ MODULE 4: COMPLAINT MANAGEMENT                                          │
 * │ Functions: getAllComplaints, updateComplaintStatus, etc.               │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
require_once ADMIN_INCLUDES_PATH . '/complaints.php';

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │ MODULE 5: CATEGORY MANAGEMENT                                           │
 * │ Functions: getAllCategories, addCategory, deleteCategory, etc.         │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
require_once ADMIN_INCLUDES_PATH . '/categories.php';

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │ MODULE 6: REPORTS & ANALYTICS                                           │
 * │ Functions: getTodayRevenue, getDailySalesData, getMostPopularItems,etc.│
 * └─────────────────────────────────────────────────────────────────────────┘
 */
require_once ADMIN_INCLUDES_PATH . '/reports.php';


// ═══════════════════════════════════════════════════════════════════════════
// HELPER FUNCTIONS (Global)
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format PHP date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y g:i A') {
    return date($format, strtotime($date));
}

/**
 * Escape HTML for safe output
 * @param string $string Input string
 * @return string Escaped string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Check if request is AJAX
 * @return bool True if AJAX request
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response and exit
 * @param bool $success Success status
 * @param string $message Response message
 * @param array $data Additional data
 */
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


// ═══════════════════════════════════════════════════════════════════════════
// ADMIN BOOTSTRAP COMPLETE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  ✓ All admin modules loaded successfully                                  ║
 * ║                                                                           ║
 * ║  Available Function Groups:                                               ║
 * ║  ├── Orders:      getValidOrderStatuses, getAllOrders, updateOrderStatus ║
 * ║  ├── Menu:        getAllMenuItems, addMenuItem, updateItemQuantity       ║
 * ║  ├── Users:       getAllUsers, getUserOrders, getTopCustomers            ║
 * ║  ├── Complaints:  getAllComplaints, updateComplaintStatus                ║
 * ║  ├── Categories:  getAllCategories, addCategory, deleteCategory          ║
 * ║  └── Reports:     getTodayRevenue, getDailySalesData, getDashboardSummary║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */
?>

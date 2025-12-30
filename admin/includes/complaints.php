<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║   ██████╗ ██████╗ ███╗   ███╗██████╗ ██╗      █████╗ ██╗███╗   ██╗████████╗║
 * ║  ██╔════╝██╔═══██╗████╗ ████║██╔══██╗██║     ██╔══██╗██║████╗  ██║╚══██╔══╝║
 * ║  ██║     ██║   ██║██╔████╔██║██████╔╝██║     ███████║██║██╔██╗ ██║   ██║   ║
 * ║  ██║     ██║   ██║██║╚██╔╝██║██╔═══╝ ██║     ██╔══██║██║██║╚██╗██║   ██║   ║
 * ║  ╚██████╗╚██████╔╝██║ ╚═╝ ██║██║     ███████╗██║  ██║██║██║ ╚████║   ██║   ║
 * ║   ╚═════╝ ╚═════╝ ╚═╝     ╚═╝╚═╝     ╚══════╝╚═╝  ╚═╝╚═╝╚═╝  ╚═══╝   ╚═╝   ║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: complaints.php                                                     ║
 * ║  PATH: /admin/includes/complaints.php                                     ║
 * ║  DESCRIPTION: Complaint management functions for admin panel              ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FEATURES:                                                                ║
 * ║    • View all customer complaints                                         ║
 * ║    • Update complaint status                                              ║
 * ║    • Mark complaints as seen/resolved                                     ║
 * ║    • Complaint statistics                                                 ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Prevent direct access
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not allowed');
}

/* ═══════════════════════════════════════════════════════════════════════════
   COMPLAINT STATUS CONSTANTS
   ═══════════════════════════════════════════════════════════════════════════ */

define('COMPLAINT_STATUS_PENDING', 'pending');
define('COMPLAINT_STATUS_REVIEWING', 'reviewing');
define('COMPLAINT_STATUS_RESOLVED', 'resolved');
define('COMPLAINT_STATUS_REJECTED', 'rejected');

/**
 * Get all valid complaint statuses
 * @return array List of valid statuses
 */
function getValidComplaintStatuses() {
    return [
        COMPLAINT_STATUS_PENDING,
        COMPLAINT_STATUS_REVIEWING,
        COMPLAINT_STATUS_RESOLVED,
        COMPLAINT_STATUS_REJECTED
    ];
}

/* ═══════════════════════════════════════════════════════════════════════════
   COMPLAINT FETCHING FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get all complaints with user information
 * @param mysqli $conn Database connection
 * @param int $limit Number of complaints to fetch
 * @return array List of complaints
 */
function getAllComplaints($conn, $limit = 100) {
    $sql = "SELECT c.*, u.full_name, u.email 
            FROM complaints c 
            LEFT JOIN users u ON c.user_id = u.id 
            ORDER BY c.created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $complaints = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $complaints[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $complaints;
}

/**
 * Get unseen/new complaints
 * @param mysqli $conn Database connection
 * @return array Unseen complaints
 */
function getUnseenComplaints($conn) {
    $sql = "SELECT c.*, u.full_name, u.email 
            FROM complaints c 
            LEFT JOIN users u ON c.user_id = u.id 
            WHERE c.is_seen = 0
            ORDER BY c.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $complaints = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $complaints[] = $row;
    }
    
    return $complaints;
}

/**
 * Get complaints by status
 * @param mysqli $conn Database connection
 * @param string $status Complaint status
 * @return array Filtered complaints
 */
function getComplaintsByStatus($conn, $status) {
    if (!in_array($status, getValidComplaintStatuses())) {
        return [];
    }
    
    $sql = "SELECT c.*, u.full_name, u.email 
            FROM complaints c 
            LEFT JOIN users u ON c.user_id = u.id 
            WHERE c.status = ?
            ORDER BY c.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $complaints = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $complaints[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $complaints;
}

/**
 * Get single complaint by ID
 * @param mysqli $conn Database connection
 * @param int $complaintId Complaint ID
 * @return array|null Complaint data or null
 */
function getComplaintById($conn, $complaintId) {
    $sql = "SELECT c.*, u.full_name, u.email 
            FROM complaints c 
            LEFT JOIN users u ON c.user_id = u.id 
            WHERE c.id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $complaintId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $complaint = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $complaint;
}

/* ═══════════════════════════════════════════════════════════════════════════
   COMPLAINT STATUS UPDATE FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Mark complaint as seen
 * @param mysqli $conn Database connection
 * @param int $complaintId Complaint ID
 * @return bool Success status
 */
function markComplaintAsSeen($conn, $complaintId) {
    $stmt = mysqli_prepare($conn, "UPDATE complaints SET is_seen = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $complaintId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}

/**
 * Mark all complaints as seen
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function markAllComplaintsAsSeen($conn) {
    return mysqli_query($conn, "UPDATE complaints SET is_seen = 1 WHERE is_seen = 0");
}

/**
 * Update complaint status
 * @param mysqli $conn Database connection
 * @param int $complaintId Complaint ID
 * @param string $status New status
 * @return bool Success status
 */
function updateComplaintStatus($conn, $complaintId, $status) {
    if (!in_array($status, getValidComplaintStatuses())) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE complaints SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $complaintId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Add admin response to complaint
 * @param mysqli $conn Database connection
 * @param int $complaintId Complaint ID
 * @param string $response Admin response
 * @return bool Success status
 */
function addComplaintResponse($conn, $complaintId, $response) {
    $stmt = mysqli_prepare($conn, "UPDATE complaints SET admin_response = ?, responded_at = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $response, $complaintId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}

/* ═══════════════════════════════════════════════════════════════════════════
   COMPLAINT STATISTICS FUNCTIONS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Get complaint statistics
 * @param mysqli $conn Database connection
 * @return array Complaint statistics
 */
function getComplaintStatistics($conn) {
    $stats = [];
    
    // Total complaints
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints");
    $stats['total_complaints'] = mysqli_fetch_assoc($result)['count'];
    
    // Unseen complaints
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE is_seen = 0");
    $stats['unseen_complaints'] = mysqli_fetch_assoc($result)['count'];
    
    // Pending complaints
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status = 'pending'");
    $stats['pending_complaints'] = mysqli_fetch_assoc($result)['count'];
    
    // Resolved complaints
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status = 'resolved'");
    $stats['resolved_complaints'] = mysqli_fetch_assoc($result)['count'];
    
    // Today's complaints
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE DATE(created_at) = CURDATE()");
    $stats['today_complaints'] = mysqli_fetch_assoc($result)['count'];
    
    // This week's complaints
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $stats['week_complaints'] = mysqli_fetch_assoc($result)['count'];
    
    return $stats;
}

/**
 * Get complaint status badge class
 * @param string $status Complaint status
 * @return string CSS class name
 */
function getComplaintBadgeClass($status) {
    switch ($status) {
        case COMPLAINT_STATUS_PENDING:
            return 'bg-warning text-dark';
        case COMPLAINT_STATUS_REVIEWING:
            return 'bg-info';
        case COMPLAINT_STATUS_RESOLVED:
            return 'bg-success';
        case COMPLAINT_STATUS_REJECTED:
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

/**
 * Get complaint status display text
 * @param string $status Raw status
 * @return string Formatted status text
 */
function getComplaintStatusText($status) {
    switch ($status) {
        case COMPLAINT_STATUS_PENDING:
            return 'Pending';
        case COMPLAINT_STATUS_REVIEWING:
            return 'Under Review';
        case COMPLAINT_STATUS_RESOLVED:
            return 'Resolved';
        case COMPLAINT_STATUS_REJECTED:
            return 'Rejected';
        default:
            return ucfirst($status);
    }
}

/* ═══════════════════════════════════════════════════════════════════════════
   END OF FILE
   ═══════════════════════════════════════════════════════════════════════════ */
?>

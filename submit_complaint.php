<?php
/**
 * Submit Complaint
 * ----------------
 * NOTE: Security features disabled for development
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Rate limiting - DISABLED for development
// $clientIP = getClientIP();
// if (!checkRateLimit($clientIP . '_complaint', 5, 3600)) {
//     echo json_encode(['success' => false, 'message' => 'Too many complaints. Please try again later.']);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// CSRF validation - DISABLED for development
// $csrfToken = $_POST['csrf_token'] ?? '';
// if (!validateCSRFToken($csrfToken)) {
//     echo json_encode(['success' => false, 'message' => 'Security validation failed. Please refresh and try again.']);
//     exit;
// }

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$orderId = trim($_POST['order_id'] ?? '');

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Name, email, and message are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Handle image upload with enhanced security
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/complaints/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Use secure file validation
    $uploadErrors = validateFileUpload($file, $allowedTypes, $maxSize);
    if (!empty($uploadErrors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $uploadErrors)]);
        exit;
    }
    
    // Generate secure random filename (prevents path traversal)
    $filename = generateSecureFilename($file['name']);
    $targetPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $imagePath = $targetPath;
        securityLog('file_upload', 'Complaint image uploaded', ['filename' => $filename]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        exit;
    }
}

// Prepare and execute INSERT statement using prepared statements
$stmt = mysqli_prepare($conn, "INSERT INTO complaints (name, email, message, image_path) VALUES (?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

// Bind parameters
mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $message, $imagePath);

// Execute statement
if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit complaint: ' . mysqli_error($conn)]);
}

// Close statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>


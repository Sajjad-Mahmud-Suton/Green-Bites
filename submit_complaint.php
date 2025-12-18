<?php
/**
 * Complaint Submission Handler
 * Handles complaint form submission with prepared statements
 */

session_start();
include 'db.php';

// Set content type for JSON response
header('Content-Type: application/json');

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and sanitize form data
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

// Handle image upload
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
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP']);
        exit;
    }
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Image size exceeds 5MB limit']);
        exit;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'complaint_' . time() . '_' . uniqid() . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $imagePath = $targetPath; // Store relative path
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


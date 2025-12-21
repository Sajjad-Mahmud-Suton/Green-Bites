<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    DATABASE MIGRATION: COMPLAINT STATUS                   ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  Adds user_id and status columns to complaints table                      ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

require_once __DIR__ . '/../db.php';

echo "<h2>Adding Complaint Status Feature</h2>";

// Add user_id column if not exists
$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM complaints LIKE 'user_id'");
if (mysqli_num_rows($checkColumn) == 0) {
    $sql = "ALTER TABLE complaints ADD COLUMN user_id INT NULL AFTER id";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;'>✓ Added 'user_id' column</p>";
    } else {
        echo "<p style='color:red;'>✗ Failed to add 'user_id': " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:blue;'>• 'user_id' column already exists</p>";
}

// Add status column if not exists (pending, seen, in_progress, resolved, closed)
$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM complaints LIKE 'status'");
if (mysqli_num_rows($checkColumn) == 0) {
    $sql = "ALTER TABLE complaints ADD COLUMN status ENUM('pending', 'seen', 'in_progress', 'resolved', 'closed') DEFAULT 'pending' AFTER is_seen";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;'>✓ Added 'status' column</p>";
    } else {
        echo "<p style='color:red;'>✗ Failed to add 'status': " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:blue;'>• 'status' column already exists</p>";
}

// Add admin_response column if not exists
$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM complaints LIKE 'admin_response'");
if (mysqli_num_rows($checkColumn) == 0) {
    $sql = "ALTER TABLE complaints ADD COLUMN admin_response TEXT NULL AFTER status";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;'>✓ Added 'admin_response' column</p>";
    } else {
        echo "<p style='color:red;'>✗ Failed to add 'admin_response': " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:blue;'>• 'admin_response' column already exists</p>";
}

// Add responded_at column if not exists
$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM complaints LIKE 'responded_at'");
if (mysqli_num_rows($checkColumn) == 0) {
    $sql = "ALTER TABLE complaints ADD COLUMN responded_at DATETIME NULL AFTER admin_response";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;'>✓ Added 'responded_at' column</p>";
    } else {
        echo "<p style='color:red;'>✗ Failed to add 'responded_at': " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:blue;'>• 'responded_at' column already exists</p>";
}

// Update existing complaints: set status based on is_seen
$sql = "UPDATE complaints SET status = 'seen' WHERE is_seen = 1 AND status = 'pending'";
if (mysqli_query($conn, $sql)) {
    $affected = mysqli_affected_rows($conn);
    echo "<p style='color:green;'>✓ Updated $affected existing seen complaints to 'seen' status</p>";
}

// Add index for user_id for faster queries
$checkIndex = mysqli_query($conn, "SHOW INDEX FROM complaints WHERE Key_name = 'idx_user_id'");
if (mysqli_num_rows($checkIndex) == 0) {
    $sql = "ALTER TABLE complaints ADD INDEX idx_user_id (user_id)";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;'>✓ Added index on 'user_id'</p>";
    }
}

echo "<br><h3 style='color:green;'>✓ Migration Complete!</h3>";
echo "<p>Now users can see their complaint history and status.</p>";
echo "<p><a href='../my_complaints.php'>Go to My Complaints</a></p>";
?>

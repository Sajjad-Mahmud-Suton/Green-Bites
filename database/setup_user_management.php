<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  GREEN BITES - User Management & Manual Order Setup                        ║
 * ║  This script adds necessary columns for:                                   ║
 * ║  - User status management (active/paused/suspended)                       ║
 * ║  - Manual order placement by admin                                         ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  GREEN BITES - User Management & Manual Order Setup           ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Connect to database
require_once __DIR__ . '/../db.php';

if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error() . "\n");
}

echo "✓ Database connected successfully\n\n";

$errors = [];
$success = [];

// ═══════════════════════════════════════════════════════════════════════════
// SECTION 1: ADD USER STATUS COLUMNS
// ═══════════════════════════════════════════════════════════════════════════

echo "=== Adding User Status Columns ===\n";

// Check if status column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'status'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE `users` ADD COLUMN `status` ENUM('active', 'paused', 'suspended') NOT NULL DEFAULT 'active' AFTER `phone`";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added 'status' column to users table\n";
        $success[] = "Added status column";
    } else {
        echo "✗ Failed to add status column: " . mysqli_error($conn) . "\n";
        $errors[] = "status column: " . mysqli_error($conn);
    }
} else {
    echo "→ 'status' column already exists\n";
}

// Check if added_by column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'added_by'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE `users` ADD COLUMN `added_by` INT(11) DEFAULT NULL AFTER `status`";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added 'added_by' column to users table\n";
        $success[] = "Added added_by column";
    } else {
        echo "✗ Failed to add added_by column: " . mysqli_error($conn) . "\n";
        $errors[] = "added_by column: " . mysqli_error($conn);
    }
} else {
    echo "→ 'added_by' column already exists\n";
}

// Check if status_changed_by column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'status_changed_by'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE `users` ADD COLUMN `status_changed_by` INT(11) DEFAULT NULL AFTER `added_by`";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added 'status_changed_by' column to users table\n";
        $success[] = "Added status_changed_by column";
    } else {
        echo "✗ Failed to add status_changed_by column: " . mysqli_error($conn) . "\n";
        $errors[] = "status_changed_by column: " . mysqli_error($conn);
    }
} else {
    echo "→ 'status_changed_by' column already exists\n";
}

// Check if status_changed_at column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'status_changed_at'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE `users` ADD COLUMN `status_changed_at` TIMESTAMP NULL DEFAULT NULL AFTER `status_changed_by`";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added 'status_changed_at' column to users table\n";
        $success[] = "Added status_changed_at column";
    } else {
        echo "✗ Failed to add status_changed_at column: " . mysqli_error($conn) . "\n";
        $errors[] = "status_changed_at column: " . mysqli_error($conn);
    }
} else {
    echo "→ 'status_changed_at' column already exists\n";
}

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// SECTION 2: ADD MANUAL ORDER COLUMNS
// ═══════════════════════════════════════════════════════════════════════════

echo "=== Adding Manual Order Columns ===\n";

// Check if is_manual_order column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'is_manual_order'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE `orders` ADD COLUMN `is_manual_order` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added 'is_manual_order' column to orders table\n";
        $success[] = "Added is_manual_order column";
    } else {
        echo "✗ Failed to add is_manual_order column: " . mysqli_error($conn) . "\n";
        $errors[] = "is_manual_order column: " . mysqli_error($conn);
    }
} else {
    echo "→ 'is_manual_order' column already exists\n";
}

// Check if manual_order_by column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'manual_order_by'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE `orders` ADD COLUMN `manual_order_by` INT(11) DEFAULT NULL AFTER `is_manual_order`";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added 'manual_order_by' column to orders table\n";
        $success[] = "Added manual_order_by column";
    } else {
        echo "✗ Failed to add manual_order_by column: " . mysqli_error($conn) . "\n";
        $errors[] = "manual_order_by column: " . mysqli_error($conn);
    }
} else {
    echo "→ 'manual_order_by' column already exists\n";
}

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// SECTION 3: CREATE INDEXES
// ═══════════════════════════════════════════════════════════════════════════

echo "=== Creating Indexes ===\n";

// Index for user status - check if exists first
$result = mysqli_query($conn, "SHOW INDEX FROM users WHERE Key_name = 'idx_user_status'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE `users` ADD INDEX `idx_user_status` (`status`)";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added index 'idx_user_status'\n";
        $success[] = "Added user status index";
    } else {
        echo "✗ Failed to add user status index: " . mysqli_error($conn) . "\n";
        $errors[] = "user status index: " . mysqli_error($conn);
    }
} else {
    echo "→ Index 'idx_user_status' already exists\n";
}

// Index for manual orders - check if exists first
$result = mysqli_query($conn, "SHOW INDEX FROM orders WHERE Key_name = 'idx_manual_orders'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE `orders` ADD INDEX `idx_manual_orders` (`is_manual_order`)";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added index 'idx_manual_orders'\n";
        $success[] = "Added manual orders index";
    } else {
        echo "✗ Failed to add manual orders index: " . mysqli_error($conn) . "\n";
        $errors[] = "manual orders index: " . mysqli_error($conn);
    }
} else {
    echo "→ Index 'idx_manual_orders' already exists\n";
}

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// SECTION 4: UPDATE EXISTING USERS
// ═══════════════════════════════════════════════════════════════════════════

echo "=== Updating Existing Users ===\n";

$updateResult = mysqli_query($conn, "UPDATE `users` SET `status` = 'active' WHERE `status` = '' OR `status` IS NULL");
$affectedRows = mysqli_affected_rows($conn);
if ($affectedRows > 0) {
    echo "✓ Set {$affectedRows} user(s) to 'active' status\n";
    $success[] = "Updated {$affectedRows} users to active";
} else {
    echo "→ All users already have a valid status\n";
}

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// SUMMARY
// ═══════════════════════════════════════════════════════════════════════════

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                        SETUP SUMMARY                          ║\n";
echo "╠═══════════════════════════════════════════════════════════════╣\n";

if (empty($errors)) {
    echo "║  ✓ All operations completed successfully!                     ║\n";
    echo "║                                                               ║\n";
    echo "║  Changes made:                                                ║\n";
    foreach ($success as $s) {
        $padded = str_pad("  - " . $s, 61);
        echo "║{$padded}║\n";
    }
} else {
    echo "║  ⚠ Setup completed with " . count($errors) . " error(s)                          ║\n";
    echo "║                                                               ║\n";
    echo "║  Errors:                                                      ║\n";
    foreach ($errors as $e) {
        $padded = str_pad("  - " . substr($e, 0, 57), 61);
        echo "║{$padded}║\n";
    }
}

echo "╚═══════════════════════════════════════════════════════════════╝\n";

mysqli_close($conn);
?>

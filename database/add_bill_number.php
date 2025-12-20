<?php
/**
 * Add bill_number column to orders table
 * Run this file once in browser: http://localhost/Green-Bites/database/add_bill_number.php
 */

require_once __DIR__ . '/../db.php';

echo "<h2>Adding Bill Number to Orders Table</h2>";

// Step 1: Add bill_number column
$sql1 = "ALTER TABLE orders ADD COLUMN bill_number VARCHAR(20) AFTER id";
if (mysqli_query($conn, $sql1)) {
    echo "<p style='color:green;'>✅ bill_number column added successfully!</p>";
} else {
    if (strpos(mysqli_error($conn), 'Duplicate column') !== false) {
        echo "<p style='color:orange;'>⚠️ bill_number column already exists.</p>";
    } else {
        echo "<p style='color:red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
}

// Step 2: Generate bill numbers for existing orders
$result = mysqli_query($conn, "SELECT id FROM orders WHERE bill_number IS NULL OR bill_number = ''");
$updated = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $orderId = $row['id'];
    // Format: GB-YYYYMMDD-XXXX (e.g., GB-20251220-0001)
    $billNumber = 'GB-' . date('Ymd') . '-' . str_pad($orderId, 4, '0', STR_PAD_LEFT);
    
    $stmt = mysqli_prepare($conn, "UPDATE orders SET bill_number = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $billNumber, $orderId);
    if (mysqli_stmt_execute($stmt)) {
        $updated++;
    }
    mysqli_stmt_close($stmt);
}

echo "<p style='color:green;'>✅ Updated $updated existing orders with bill numbers.</p>";

// Step 3: Add unique index
$sql3 = "ALTER TABLE orders ADD UNIQUE INDEX idx_bill_number (bill_number)";
if (mysqli_query($conn, $sql3)) {
    echo "<p style='color:green;'>✅ Unique index added!</p>";
} else {
    if (strpos(mysqli_error($conn), 'Duplicate') !== false) {
        echo "<p style='color:orange;'>⚠️ Index already exists.</p>";
    } else {
        echo "<p style='color:red;'>❌ Index error: " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>✅ Done! Bill number system is ready.</h3>";
echo "<p><a href='../my_orders.php'>Go to My Orders</a></p>";

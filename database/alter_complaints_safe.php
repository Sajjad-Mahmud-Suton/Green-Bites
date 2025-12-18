<?php

include '../db.php';

// Function to check if column exists
function columnExists($conn, $table, $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return mysqli_num_rows($result) > 0;
}

// Function to get column name variations
function getComplaintColumnName($conn, $table) {
    $possibleNames = ['complain', 'complaint', 'details', 'text', 'description', 'message'];
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
    
    while ($row = mysqli_fetch_assoc($result)) {
        if (in_array(strtolower($row['Field']), $possibleNames)) {
            return $row['Field'];
        }
    }
    return null;
}

echo "Starting complaints table alteration...\n<br>";

// Check if table exists, if not create it
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'complaints'");
if (mysqli_num_rows($tableCheck) == 0) {
    echo "Creating complaints table...\n<br>";
    $createTable = "CREATE TABLE complaints (
        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        image_path VARCHAR(255) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $createTable)) {
        echo "✓ Table created successfully!\n<br>";
    } else {
        echo "✗ Error creating table: " . mysqli_error($conn) . "\n<br>";
        exit;
    }
} else {
    echo "Table exists. Altering structure...\n<br>";
    
    // Add id column if missing
    if (!columnExists($conn, 'complaints', 'id')) {
        echo "Adding id column...\n<br>";
        mysqli_query($conn, "ALTER TABLE complaints ADD COLUMN id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT FIRST");
    } else {
        // Make sure id is auto_increment
        mysqli_query($conn, "ALTER TABLE complaints MODIFY COLUMN id INT UNSIGNED AUTO_INCREMENT");
        echo "✓ id column verified\n<br>";
    }
    
    // Add name column if missing
    if (!columnExists($conn, 'complaints', 'name')) {
        echo "Adding name column...\n<br>";
        mysqli_query($conn, "ALTER TABLE complaints ADD COLUMN name VARCHAR(100) NOT NULL AFTER id");
        echo "✓ name column added\n<br>";
    } else {
        echo "✓ name column exists\n<br>";
    }
    
    // Add email column if missing
    if (!columnExists($conn, 'complaints', 'email')) {
        echo "Adding email column...\n<br>";
        mysqli_query($conn, "ALTER TABLE complaints ADD COLUMN email VARCHAR(150) NOT NULL AFTER name");
        echo "✓ email column added\n<br>";
    } else {
        echo "✓ email column exists\n<br>";
    }
    
    // Handle message column - rename old columns or add new
    $oldColumn = getComplaintColumnName($conn, 'complaints');
    
    if ($oldColumn && $oldColumn !== 'message') {
        echo "Renaming column '$oldColumn' to 'message'...\n<br>";
        mysqli_query($conn, "ALTER TABLE complaints CHANGE COLUMN `$oldColumn` message TEXT NOT NULL");
        echo "✓ Column renamed to message\n<br>";
    } elseif (!columnExists($conn, 'complaints', 'message')) {
        echo "Adding message column...\n<br>";
        mysqli_query($conn, "ALTER TABLE complaints ADD COLUMN message TEXT NOT NULL AFTER email");
        echo "✓ message column added\n<br>";
    } else {
        echo "✓ message column exists\n<br>";
    }
    
    // Add image_path column if missing
    if (!columnExists($conn, 'complaints', 'image_path')) {
        echo "Adding image_path column...\n<br>";
        mysqli_query($conn, "ALTER TABLE complaints ADD COLUMN image_path VARCHAR(255) NULL AFTER message");
        echo "✓ image_path column added\n<br>";
    } else {
        echo "✓ image_path column exists\n<br>";
    }
    
    // Add created_at column if missing
    if (!columnExists($conn, 'complaints', 'created_at')) {
        echo "Adding created_at column...\n<br>";
        mysqli_query($conn, "ALTER TABLE complaints ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER image_path");
        echo "✓ created_at column added\n<br>";
    } else {
        echo "✓ created_at column exists\n<br>";
    }
}

// Verify final structure
echo "\n<br>Final table structure:\n<br>";
echo "<pre>";
$result = mysqli_query($conn, "DESCRIBE complaints");
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . "\n";
}
echo "</pre>";

echo "\n<br>✓ Table alteration complete!\n<br>";

mysqli_close($conn);
?>


<?php
require_once 'db.php';

// Create carousel_slides table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS carousel_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) DEFAULT 0,
    image_url VARCHAR(500) NOT NULL,
    btn_text VARCHAR(100) DEFAULT 'Order Now',
    btn_link VARCHAR(255) DEFAULT '#dealsSection',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $createTable)) {
    echo "Table created successfully<br>";
} else {
    echo "Table already exists or error: " . mysqli_error($conn) . "<br>";
}

// Check if table is empty
$check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM carousel_slides");
$count = mysqli_fetch_assoc($check)['cnt'];

if ($count == 0) {
    // Insert sample slides
    $inserts = [
        "INSERT INTO carousel_slides (title, description, price, image_url, sort_order) VALUES ('Biriyani Special', 'Rich chicken biriyani with fresh salad & raita', 120, 'images/biriyani.jpg', 1)",
        "INSERT INTO carousel_slides (title, description, price, image_url, sort_order) VALUES ('Burger Combo', 'Crispy chicken burger with fries & cold drink', 90, 'images/burger.jpg', 2)",
        "INSERT INTO carousel_slides (title, description, price, image_url, sort_order) VALUES ('Fried Rice Special', 'Egg fried rice with mixed vegetables & curry', 80, 'images/friedrice.jpg', 3)"
    ];
    
    foreach ($inserts as $sql) {
        if (mysqli_query($conn, $sql)) {
            echo "Slide inserted<br>";
        } else {
            echo "Error: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "Slides already exist: $count slides<br>";
}

echo "<br>Done! <a href='index.php'>Go to homepage</a>";
?>

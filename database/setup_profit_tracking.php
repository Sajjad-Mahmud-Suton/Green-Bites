<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    PROFIT TRACKING SETUP SCRIPT                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  This script:                                                             ║
 * ║    1. Adds buying_price column to menu_items if not exists                ║
 * ║    2. Creates profits table                                               ║
 * ║    3. Backfills profits for existing delivered orders                     ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

require_once __DIR__ . '/../db.php';

echo "<h1>Profit Tracking Setup</h1>";
echo "<pre>";

// ═══════════════════════════════════════════════════════════════════════════
// STEP 1: Add buying_price column to menu_items
// ═══════════════════════════════════════════════════════════════════════════

echo "Step 1: Adding buying_price column to menu_items...\n";

// Check if column exists
$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM menu_items LIKE 'buying_price'");
if (mysqli_num_rows($checkColumn) == 0) {
    $sql = "ALTER TABLE `menu_items` ADD COLUMN `buying_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `price`";
    if (mysqli_query($conn, $sql)) {
        echo "✓ buying_price column added successfully!\n";
        
        // Update existing items with 80% of selling price
        $updateSql = "UPDATE `menu_items` SET `buying_price` = ROUND(`price` * 0.8, 2) WHERE `buying_price` = 0";
        if (mysqli_query($conn, $updateSql)) {
            $affected = mysqli_affected_rows($conn);
            echo "✓ Updated {$affected} menu items with default buying price (80% of selling price)\n";
        }
    } else {
        echo "✗ Error adding column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "→ buying_price column already exists\n";
}

// ═══════════════════════════════════════════════════════════════════════════
// STEP 2: Create profits table
// ═══════════════════════════════════════════════════════════════════════════

echo "\nStep 2: Creating profits table...\n";

$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'profits'");
if (mysqli_num_rows($checkTable) == 0) {
    $sql = "CREATE TABLE IF NOT EXISTS `profits` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` INT(11) NOT NULL,
        `product_id` INT(11) NOT NULL,
        `product_name` VARCHAR(100) NOT NULL,
        `quantity` INT(11) NOT NULL DEFAULT 1,
        `selling_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `buying_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `profit_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `revenue` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `investment` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `calculated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_order_id` (`order_id`),
        KEY `idx_product_id` (`product_id`),
        KEY `idx_calculated_at` (`calculated_at`),
        KEY `idx_order_product` (`order_id`, `product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (mysqli_query($conn, $sql)) {
        echo "✓ profits table created successfully!\n";
    } else {
        echo "✗ Error creating table: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "→ profits table already exists\n";
}

// ═══════════════════════════════════════════════════════════════════════════
// STEP 3: Create profit_summary table
// ═══════════════════════════════════════════════════════════════════════════

echo "\nStep 3: Creating profit_summary table...\n";

$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'profit_summary'");
if (mysqli_num_rows($checkTable) == 0) {
    $sql = "CREATE TABLE IF NOT EXISTS `profit_summary` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `period_type` ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL,
        `period_start` DATE NOT NULL,
        `period_end` DATE NOT NULL,
        `total_revenue` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `total_investment` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `total_profit` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `total_orders` INT(11) NOT NULL DEFAULT 0,
        `total_items_sold` INT(11) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_period_unique` (`period_type`, `period_start`),
        KEY `idx_period_type` (`period_type`),
        KEY `idx_period_start` (`period_start`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (mysqli_query($conn, $sql)) {
        echo "✓ profit_summary table created successfully!\n";
    } else {
        echo "✗ Error creating table: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "→ profit_summary table already exists\n";
}

// ═══════════════════════════════════════════════════════════════════════════
// STEP 4: Backfill profits for existing delivered orders
// ═══════════════════════════════════════════════════════════════════════════

echo "\nStep 4: Backfilling profits for existing delivered orders...\n";

// Get all delivered orders that don't have profit entries yet
$sql = "SELECT o.id, o.items, o.order_date 
        FROM orders o 
        WHERE o.status = 'Delivered' 
        AND NOT EXISTS (SELECT 1 FROM profits p WHERE p.order_id = o.id)";
$result = mysqli_query($conn, $sql);

$totalOrders = 0;
$totalItems = 0;
$totalProfit = 0;

if ($result && mysqli_num_rows($result) > 0) {
    while ($order = mysqli_fetch_assoc($result)) {
        $items = json_decode($order['items'], true);
        if (!is_array($items)) continue;
        
        $totalOrders++;
        
        foreach ($items as $item) {
            $itemId = intval($item['id'] ?? 0);
            $itemName = $item['title'] ?? $item['name'] ?? 'Unknown Item';
            $quantity = intval($item['quantity'] ?? 1);
            $sellingPrice = floatval($item['price'] ?? 0);
            
            // Get buying price from menu_items, or estimate at 80% of selling
            $buyingPrice = 0;
            if ($itemId > 0) {
                $menuResult = mysqli_query($conn, "SELECT buying_price FROM menu_items WHERE id = {$itemId}");
                if ($menuResult && $menuRow = mysqli_fetch_assoc($menuResult)) {
                    $buyingPrice = floatval($menuRow['buying_price']);
                }
            }
            
            // If no buying price set, assume 80% of selling price
            if ($buyingPrice <= 0) {
                $buyingPrice = round($sellingPrice * 0.8, 2);
            }
            
            $revenue = $sellingPrice * $quantity;
            $investment = $buyingPrice * $quantity;
            $profitAmount = $revenue - $investment;
            
            // Insert profit record
            $insertSql = "INSERT INTO profits (order_id, product_id, product_name, quantity, selling_price, buying_price, profit_amount, revenue, investment, calculated_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertSql);
            mysqli_stmt_bind_param($stmt, 'iisiddddds', 
                $order['id'], 
                $itemId, 
                $itemName, 
                $quantity, 
                $sellingPrice, 
                $buyingPrice, 
                $profitAmount,
                $revenue,
                $investment,
                $order['order_date']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $totalItems++;
                $totalProfit += $profitAmount;
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    echo "✓ Backfilled {$totalOrders} orders with {$totalItems} items\n";
    echo "✓ Total profit calculated: ৳" . number_format($totalProfit, 2) . "\n";
} else {
    echo "→ No delivered orders to backfill or all already processed\n";
}

echo "\n</pre>";
echo "<h2 style='color: green;'>✓ Profit tracking setup complete!</h2>";
echo "<p><a href='../admin/index.php'>Go to Admin Dashboard</a></p>";

mysqli_close($conn);
?>

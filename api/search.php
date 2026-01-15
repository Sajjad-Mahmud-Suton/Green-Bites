<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  GREEN BITES - Live Search API                                            ║
 * ║  Endpoint for searching menu items with autocomplete                      ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * 
 * ACCEPTS: GET
 *   - q: Search query (minimum 2 characters)
 *   - limit: Maximum results to return (default: 10, max: 20)
 * 
 * RETURNS: JSON array of matching menu items
 */

header('Content-Type: application/json');

// Allow only GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../db.php';

$query = trim($_GET['q'] ?? '');
$limit = min(intval($_GET['limit'] ?? 10), 20);

// Minimum 2 characters required
if (strlen($query) < 2) {
    echo json_encode([
        'success' => true,
        'results' => [],
        'message' => 'Query too short'
    ]);
    exit;
}

// Prepare search - case insensitive partial matching
$searchTerm = '%' . $query . '%';

$sql = "SELECT 
            m.id,
            m.title,
            m.price,
            m.discount_percent,
            m.image_url,
            m.quantity,
            m.is_available,
            c.name as category_name,
            c.id as category_id
        FROM menu_items m
        LEFT JOIN categories c ON m.category_id = c.id
        WHERE (m.title LIKE ? OR c.name LIKE ?)
          AND m.is_available = 1
        ORDER BY 
            CASE 
                WHEN m.title LIKE ? THEN 1  -- Exact prefix match first
                WHEN m.title LIKE ? THEN 2  -- Contains query
                ELSE 3
            END,
            m.title ASC
        LIMIT ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$prefixTerm = $query . '%';
mysqli_stmt_bind_param($stmt, 'ssssi', $searchTerm, $searchTerm, $prefixTerm, $searchTerm, $limit);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$results = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Calculate final price with discount
    $originalPrice = floatval($row['price']);
    $discount = intval($row['discount_percent'] ?? 0);
    $finalPrice = $discount > 0 ? $originalPrice - ($originalPrice * $discount / 100) : $originalPrice;
    
    $results[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'price' => $finalPrice,
        'original_price' => $originalPrice,
        'discount_percent' => $discount,
        'image_url' => $row['image_url'] ?: 'images/default-food.png',
        'category' => $row['category_name'] ?? 'Uncategorized',
        'category_id' => (int)$row['category_id'],
        'in_stock' => ($row['quantity'] > 0),
        'quantity' => (int)$row['quantity']
    ];
}

mysqli_stmt_close($stmt);

echo json_encode([
    'success' => true,
    'results' => $results,
    'count' => count($results),
    'query' => $query
]);

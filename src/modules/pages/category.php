<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                      GREEN BITES - CATEGORY PAGE                          ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../config/bootstrap.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get category ID
$categoryId = intval($_GET['id'] ?? 0);

if ($categoryId <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch category info
$catStmt = mysqli_prepare($conn, "SELECT id, name, icon, description FROM categories WHERE id = ?");
mysqli_stmt_bind_param($catStmt, 'i', $categoryId);
mysqli_stmt_execute($catStmt);
$catResult = mysqli_stmt_get_result($catStmt);
$category = mysqli_fetch_assoc($catResult);
mysqli_stmt_close($catStmt);

if (!$category) {
    header('Location: index.php');
    exit;
}

// Fetch menu items for this category
$itemsStmt = mysqli_prepare($conn, "SELECT id, title, price, image_url, description, is_available FROM menu_items WHERE category_id = ? ORDER BY title ASC");
mysqli_stmt_bind_param($itemsStmt, 'i', $categoryId);
mysqli_stmt_execute($itemsStmt);
$itemsResult = mysqli_stmt_get_result($itemsStmt);
$menuItems = [];
while ($item = mysqli_fetch_assoc($itemsResult)) {
    $menuItems[] = $item;
}
mysqli_stmt_close($itemsStmt);

$categoryName = htmlspecialchars($category['name']);
$categoryIcon = $category['icon'] ?: 'tag';
$categoryDesc = htmlspecialchars($category['description'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $categoryName; ?> - Green Bites</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?>

<!-- Category Hero Section -->
<section class="py-5 mt-5" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
    <div class="container text-center text-white py-4">
        <i class="bi bi-<?php echo $categoryIcon; ?> display-1 mb-3"></i>
        <h1 class="display-4 fw-bold"><?php echo $categoryName; ?></h1>
        <?php if ($categoryDesc): ?>
        <p class="lead"><?php echo $categoryDesc; ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Menu Items Section -->
<section class="py-5">
    <div class="container">
        <?php if (empty($menuItems)): ?>
        <div class="text-center py-5">
            <i class="bi bi-emoji-frown display-1 text-muted"></i>
            <h3 class="mt-3 text-muted">No items available in this category</h3>
            <a href="index.php" class="btn btn-success mt-3">
                <i class="bi bi-house me-2"></i>Back to Home
            </a>
        </div>
        <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php foreach ($menuItems as $item): 
                $imgUrl = $item['image_url'] ?: 'images/placeholder.jpg';
                $isAvailable = $item['is_available'] ?? 1;
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm menu-card <?php echo !$isAvailable ? 'opacity-50' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>" style="height: 180px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                        <?php if ($item['description']): ?>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="badge bg-success fs-6">৳<?php echo number_format($item['price'], 0); ?></span>
                            <?php if ($isAvailable): ?>
                            <button class="btn btn-outline-success btn-sm order-btn" 
                                    data-item-id="<?php echo $item['id']; ?>"
                                    data-item-title="<?php echo htmlspecialchars($item['title']); ?>"
                                    data-item-price="<?php echo $item['price']; ?>"
                                    data-item-image="<?php echo htmlspecialchars($imgUrl); ?>">
                                <i class="bi bi-cart-plus me-1"></i>Add
                            </button>
                            <?php else: ?>
                            <span class="badge bg-secondary">Unavailable</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>

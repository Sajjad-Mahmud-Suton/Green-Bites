<?php
session_start();
require_once 'db.php';

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
$itemsStmt = mysqli_prepare($conn, "SELECT id, title, price, discount_percent, image_url, description, is_available, quantity FROM menu_items WHERE category_id = ? ORDER BY title ASC");
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
                $quantity = intval($item['quantity'] ?? 0);
                $isStockout = ($quantity == 0);
                $isLowStock = ($quantity > 0 && $quantity <= 5);
                $originalPrice = floatval($item['price']);
                $discountPercent = intval($item['discount_percent'] ?? 0);
                $finalPrice = $discountPercent > 0 ? $originalPrice - ($originalPrice * $discountPercent / 100) : $originalPrice;
                $hasDiscount = $discountPercent > 0;
                
                // Generate description if empty
                $description = $item['description'] ?? '';
                $displayDescription = $description;
                if (empty($displayDescription)) {
                  $descriptions = [
                    "A delicious and freshly prepared " . strtolower($item['title']) . " made with premium ingredients.",
                    "Experience the authentic taste of our " . strtolower($item['title']) . ". Made with love and care.",
                    "Enjoy our signature " . strtolower($item['title']) . " - crafted for an unforgettable taste.",
                    "Our " . strtolower($item['title']) . " is a customer favorite! Prepared fresh daily."
                  ];
                  $displayDescription = $descriptions[array_rand($descriptions)];
                }
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm menu-card <?php echo $isStockout ? 'stockout-card' : (!$isAvailable ? 'opacity-50' : ''); ?>">
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="card-img-top <?php echo $isStockout ? 'stockout-image' : ''; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="height: 180px; object-fit: cover;">
                        <?php if ($hasDiscount): ?>
                            <div class="discount-badge">
                                <i class="bi bi-lightning-fill"></i><?php echo $discountPercent; ?>% OFF
                            </div>
                        <?php endif; ?>
                        <?php if ($isStockout): ?>
                            <div class="stock-badge stockout-badge">
                                <i class="bi bi-x-circle-fill me-1"></i>Stockout
                            </div>
                        <?php elseif ($isLowStock): ?>
                            <div class="stock-badge low-stock-badge">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Only <?php echo $quantity; ?> left!
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                        <?php if ($item['description']): ?>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="price-info-row">
                              <?php if ($hasDiscount): ?>
                              <div class="price-container">
                                  <span class="badge bg-success fs-6">৳<?php echo number_format($finalPrice, 0); ?></span>
                                  <span class="original-price text-muted text-decoration-line-through ms-1">৳<?php echo number_format($originalPrice, 0); ?></span>
                              </div>
                              <?php else: ?>
                              <span class="badge bg-success fs-6">৳<?php echo number_format($originalPrice, 0); ?></span>
                              <?php endif; ?>
                              <!-- Info Button -->
                              <button class="product-info-btn-sm" 
                                      onclick="showProductInfo('<?php echo addslashes($item['title']); ?>', '<?php echo $imgUrl; ?>', '<?php echo addslashes($displayDescription); ?>', <?php echo $finalPrice; ?>, <?php echo $originalPrice; ?>, <?php echo $discountPercent; ?>, <?php echo $quantity; ?>)"
                                      title="View Details">
                                <i class="bi bi-info-lg"></i>
                              </button>
                            </div>
                            <?php if ($isStockout): ?>
                            <button class="btn btn-secondary btn-sm disabled order-btn" disabled
                                    data-item-id="<?php echo $item['id']; ?>"
                                    data-item-title="<?php echo htmlspecialchars($item['title']); ?>"
                                    data-item-price="<?php echo $finalPrice; ?>"
                                    data-item-original-price="<?php echo $originalPrice; ?>"
                                    data-item-discount="<?php echo $discountPercent; ?>"
                                    data-item-image="<?php echo htmlspecialchars($imgUrl); ?>">
                                <i class="bi bi-x-circle me-1"></i>Out of Stock
                            </button>
                            <?php elseif ($isAvailable): ?>
                            <button class="btn btn-outline-success btn-sm order-btn" 
                                    data-item-id="<?php echo $item['id']; ?>"
                                    data-item-title="<?php echo htmlspecialchars($item['title']); ?>"
                                    data-item-price="<?php echo $finalPrice; ?>"
                                    data-item-original-price="<?php echo $originalPrice; ?>"
                                    data-item-discount="<?php echo $discountPercent; ?>"
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

<!-- Product Info Modal -->
<div class="modal fade" id="productInfoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content product-info-modal-content">
      <button type="button" class="btn-close product-info-close" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="product-info-wrapper">
        <div class="product-info-image-section">
          <div class="product-info-image-container">
            <img id="productInfoImage" src="" alt="Product Image">
            <div class="product-info-badges">
              <span id="productInfoDiscount" class="product-info-discount-badge d-none"></span>
              <span id="productInfoStock" class="product-info-stock-badge"></span>
            </div>
          </div>
        </div>
        <div class="product-info-details">
          <div class="product-info-category">
            <i class="bi bi-tag-fill"></i> Green Bites Special
          </div>
          <h2 id="productInfoTitle" class="product-info-title"></h2>
          <p id="productInfoDescription" class="product-info-description"></p>
          
          <div class="product-info-price-section">
            <div class="product-info-price-row">
              <span id="productInfoPrice" class="product-info-current-price"></span>
              <span id="productInfoOriginalPrice" class="product-info-original-price d-none"></span>
            </div>
            <div id="productInfoSavings" class="product-info-savings d-none">
              <i class="bi bi-piggy-bank-fill"></i> You save <span id="productInfoSaveAmount"></span>
            </div>
          </div>
          
          <div class="product-info-features">
            <div class="product-info-feature">
              <i class="bi bi-clock-fill"></i>
              <span>Fresh & Ready</span>
            </div>
            <div class="product-info-feature">
              <i class="bi bi-star-fill"></i>
              <span>Top Rated</span>
            </div>
            <div class="product-info-feature">
              <i class="bi bi-heart-fill"></i>
              <span>Customer Favorite</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.price-info-row{display:flex;align-items:center;gap:8px}
.product-info-btn-sm{width:24px;height:24px;min-width:24px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#15803d);border:none;color:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s ease;font-size:.75rem;padding:0;box-shadow:0 2px 8px rgba(22,163,74,.3)}
.product-info-btn-sm:hover{transform:scale(1.1);box-shadow:0 4px 12px rgba(22,163,74,.5);background:linear-gradient(135deg,#22c55e,#16a34a)}
.product-info-btn-sm i{font-size:.7rem;font-weight:700}
.product-info-modal-content{border:none;border-radius:24px;overflow:hidden;background:linear-gradient(135deg,#f0fdf4,#fff);box-shadow:0 25px 80px rgba(0,0,0,.2)}
.product-info-close{position:absolute;top:20px;right:20px;z-index:100;background:white;border-radius:50%;padding:12px;opacity:1;box-shadow:0 4px 15px rgba(0,0,0,.1);transition:all .3s ease}
.product-info-close:hover{transform:rotate(90deg);background:#fee2e2}
.product-info-wrapper{display:flex;flex-wrap:wrap}
.product-info-image-section{flex:0 0 50%;max-width:50%;padding:30px;background:linear-gradient(135deg,#dcfce7,#bbf7d0);display:flex;align-items:center;justify-content:center}
.product-info-image-container{position:relative;width:100%;max-width:350px}
.product-info-image-container img{width:100%;height:320px;object-fit:cover;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);transition:transform .5s ease}
.product-info-image-container:hover img{transform:scale(1.05)}
.product-info-badges{position:absolute;top:15px;left:15px;display:flex;flex-direction:column;gap:8px}
.product-info-discount-badge{background:linear-gradient(135deg,#ef4444,#dc2626);color:white;padding:6px 14px;border-radius:50px;font-weight:700;font-size:.85rem;box-shadow:0 4px 15px rgba(239,68,68,.4)}
.product-info-stock-badge{background:linear-gradient(135deg,#16a34a,#15803d);color:white;padding:6px 14px;border-radius:50px;font-weight:600;font-size:.8rem;box-shadow:0 4px 15px rgba(22,163,74,.4)}
.product-info-stock-badge.low-stock{background:linear-gradient(135deg,#f59e0b,#d97706)}
.product-info-stock-badge.out-of-stock{background:linear-gradient(135deg,#ef4444,#dc2626)}
.product-info-details{flex:0 0 50%;max-width:50%;padding:40px;display:flex;flex-direction:column;justify-content:center}
.product-info-category{display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#16a34a;padding:6px 16px;border-radius:50px;font-weight:600;font-size:.85rem;margin-bottom:15px;width:fit-content}
.product-info-title{font-size:2rem;font-weight:800;color:#1a1a2e;margin-bottom:15px;line-height:1.2}
.product-info-description{color:#64748b;font-size:1rem;line-height:1.7;margin-bottom:25px}
.product-info-price-section{background:white;padding:20px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.05);margin-bottom:25px}
.product-info-price-row{display:flex;align-items:center;gap:15px}
.product-info-current-price{font-size:2.2rem;font-weight:800;color:#16a34a}
.product-info-original-price{font-size:1.3rem;color:#9ca3af;text-decoration:line-through}
.product-info-savings{margin-top:10px;padding:8px 16px;background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e;border-radius:10px;font-weight:600;font-size:.9rem;display:inline-flex;align-items:center;gap:8px;width:fit-content}
.product-info-features{display:flex;gap:15px;flex-wrap:wrap}
.product-info-feature{display:flex;align-items:center;gap:6px;background:#f8fafc;padding:10px 16px;border-radius:12px;font-size:.85rem;color:#475569;font-weight:500}
.product-info-feature i{color:#16a34a}
@media(max-width:768px){.product-info-wrapper{flex-direction:column}.product-info-image-section,.product-info-details{flex:0 0 100%;max-width:100%}.product-info-image-section{padding:20px}.product-info-image-container img{height:220px}.product-info-details{padding:25px}.product-info-title{font-size:1.5rem}.product-info-current-price{font-size:1.8rem}.product-info-features{gap:10px}.product-info-feature{padding:8px 12px;font-size:.8rem}}
</style>

<script>
function showProductInfo(title, image, description, finalPrice, originalPrice, discount, quantity) {
  document.getElementById('productInfoTitle').textContent = title;
  document.getElementById('productInfoImage').src = image;
  document.getElementById('productInfoDescription').textContent = description;
  document.getElementById('productInfoPrice').textContent = '৳' + Math.round(finalPrice).toLocaleString();
  
  const discountBadge = document.getElementById('productInfoDiscount');
  const originalPriceEl = document.getElementById('productInfoOriginalPrice');
  const savingsEl = document.getElementById('productInfoSavings');
  const saveAmountEl = document.getElementById('productInfoSaveAmount');
  
  if (discount > 0) {
    discountBadge.textContent = discount + '% OFF';
    discountBadge.classList.remove('d-none');
    originalPriceEl.textContent = '৳' + Math.round(originalPrice).toLocaleString();
    originalPriceEl.classList.remove('d-none');
    saveAmountEl.textContent = '৳' + Math.round(originalPrice - finalPrice).toLocaleString();
    savingsEl.classList.remove('d-none');
  } else {
    discountBadge.classList.add('d-none');
    originalPriceEl.classList.add('d-none');
    savingsEl.classList.add('d-none');
  }
  
  const stockBadge = document.getElementById('productInfoStock');
  stockBadge.classList.remove('low-stock', 'out-of-stock');
  
  if (quantity === 0) {
    stockBadge.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>Out of Stock';
    stockBadge.classList.add('out-of-stock');
  } else if (quantity <= 5) {
    stockBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>Only ' + quantity + ' left!';
    stockBadge.classList.add('low-stock');
  } else {
    stockBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>In Stock';
  }
  
  new bootstrap.Modal(document.getElementById('productInfoModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>

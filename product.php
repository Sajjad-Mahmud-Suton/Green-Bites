<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  GREEN BITES - Single Product View                                       ║
 * ║  Displays a single menu item when clicked from search                    ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

require_once __DIR__ . '/db.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// If search query is provided, find matching products
$products = [];
$singleProduct = null;

if ($productId > 0) {
    // Fetch single product by ID
    $stmt = mysqli_prepare($conn, "SELECT m.*, c.name as category_name, c.id as category_id 
                                    FROM menu_items m 
                                    LEFT JOIN categories c ON m.category_id = c.id 
                                    WHERE m.id = ? AND m.is_available = 1");
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $singleProduct = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($singleProduct) {
        $products[] = $singleProduct;
    }
} elseif (!empty($searchQuery)) {
    // Search for products with case-insensitive partial matching
    $searchTerm = '%' . $searchQuery . '%';
    $stmt = mysqli_prepare($conn, "SELECT m.*, c.name as category_name, c.id as category_id 
                                    FROM menu_items m 
                                    LEFT JOIN categories c ON m.category_id = c.id 
                                    WHERE (m.title LIKE ? OR c.name LIKE ? OR m.description LIKE ?) 
                                    AND m.is_available = 1
                                    ORDER BY 
                                        CASE 
                                            WHEN m.title LIKE ? THEN 1
                                            WHEN m.title LIKE ? THEN 2
                                            ELSE 3
                                        END,
                                        m.title ASC
                                    LIMIT 20");
    $prefixTerm = $searchQuery . '%';
    mysqli_stmt_bind_param($stmt, 'sssss', $searchTerm, $searchTerm, $searchTerm, $prefixTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Get page title
$pageTitle = "Search Results";
if ($singleProduct) {
    $pageTitle = htmlspecialchars($singleProduct['title']);
} elseif (!empty($searchQuery)) {
    $pageTitle = "Search: " . htmlspecialchars($searchQuery);
}

// Fetch related products (same category, excluding current product)
$relatedProducts = [];
if ($singleProduct) {
    $stmt = mysqli_prepare($conn, "SELECT m.*, c.name as category_name 
                                    FROM menu_items m 
                                    LEFT JOIN categories c ON m.category_id = c.id 
                                    WHERE m.category_id = ? AND m.id != ? AND m.is_available = 1 
                                    ORDER BY RAND() 
                                    LIMIT 4");
    mysqli_stmt_bind_param($stmt, 'ii', $singleProduct['category_id'], $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $relatedProducts[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?> - Green Bites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .product-hero {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      padding: 2rem 0;
      margin-bottom: 2rem;
    }
    .product-image {
      width: 100%;
      max-width: 400px;
      height: 350px;
      object-fit: cover;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    .product-details {
      padding: 2rem 0;
    }
    .product-title {
      font-size: 2.5rem;
      font-weight: 800;
      color: #1a1a2e;
      margin-bottom: 0.5rem;
    }
    .product-category {
      color: #16a34a;
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 1rem;
    }
    .product-price {
      font-size: 2rem;
      font-weight: 800;
      color: #16a34a;
    }
    .product-original-price {
      font-size: 1.3rem;
      color: #9ca3af;
      text-decoration: line-through;
      margin-left: 0.5rem;
    }
    .discount-badge {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
      padding: 0.4rem 1rem;
      border-radius: 50px;
      font-weight: 700;
      font-size: 0.9rem;
      margin-left: 1rem;
    }
    .add-to-cart-btn {
      background: linear-gradient(135deg, #16a34a, #15803d);
      border: none;
      padding: 1rem 2rem;
      font-size: 1.1rem;
      font-weight: 700;
      border-radius: 12px;
      transition: all 0.3s ease;
    }
    .add-to-cart-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(22, 163, 74, 0.4);
    }
    .stock-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.9rem;
    }
    .stock-badge.in-stock {
      background: #dcfce7;
      color: #16a34a;
    }
    .stock-badge.low-stock {
      background: #fef3c7;
      color: #d97706;
    }
    .stock-badge.out-of-stock {
      background: #fee2e2;
      color: #dc2626;
    }
    .search-result-card {
      border: none;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      height: 100%;
    }
    .search-result-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    }
    .search-result-card img {
      height: 180px;
      object-fit: cover;
    }
    .related-section {
      background: #f8fafc;
      padding: 3rem 0;
      margin-top: 3rem;
    }
    .quantity-control {
      display: inline-flex;
      align-items: center;
      gap: 0;
      background: #f3f4f6;
      border-radius: 12px;
      padding: 0.3rem;
    }
    .quantity-control button {
      width: 40px;
      height: 40px;
      border: none;
      background: white;
      border-radius: 10px;
      font-size: 1.2rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .quantity-control button:hover {
      background: #16a34a;
      color: white;
    }
    .quantity-control input {
      width: 60px;
      text-align: center;
      border: none;
      background: transparent;
      font-size: 1.2rem;
      font-weight: 700;
    }
    .no-results {
      text-align: center;
      padding: 4rem 2rem;
    }
    .no-results i {
      font-size: 5rem;
      color: #d1d5db;
      margin-bottom: 1rem;
    }
    .breadcrumb-custom {
      background: transparent;
      padding: 0;
    }
    .breadcrumb-custom a {
      color: #16a34a;
      text-decoration: none;
    }
    .breadcrumb-custom a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>
  
  <main>
    <?php if (empty($products)): ?>
      <!-- No Results -->
      <div class="container py-5">
        <div class="no-results">
          <i class="bi bi-search"></i>
          <h3>No products found</h3>
          <p class="text-muted">We couldn't find any products matching your search.</p>
          <a href="index.php" class="btn btn-success btn-lg mt-3">
            <i class="bi bi-house me-2"></i>Back to Home
          </a>
        </div>
      </div>
      
    <?php elseif ($singleProduct): ?>
      <!-- Single Product View -->
      <section class="product-hero">
        <div class="container">
          <!-- Breadcrumb -->
          <nav class="breadcrumb-custom mb-4">
            <a href="index.php"><i class="bi bi-house me-1"></i>Home</a>
            <span class="mx-2">/</span>
            <a href="category.php?id=<?php echo $singleProduct['category_id']; ?>"><?php echo htmlspecialchars($singleProduct['category_name']); ?></a>
            <span class="mx-2">/</span>
            <span class="text-muted"><?php echo htmlspecialchars($singleProduct['title']); ?></span>
          </nav>
          
          <div class="row align-items-center">
            <div class="col-lg-5 text-center mb-4 mb-lg-0">
              <img src="<?php echo htmlspecialchars($singleProduct['image_url'] ?: 'images/default-food.png'); ?>" 
                   alt="<?php echo htmlspecialchars($singleProduct['title']); ?>"
                   class="product-image"
                   onerror="this.src='images/default-food.png'">
            </div>
            <div class="col-lg-7">
              <div class="product-details">
                <span class="product-category">
                  <i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($singleProduct['category_name']); ?>
                </span>
                <h1 class="product-title"><?php echo htmlspecialchars($singleProduct['title']); ?></h1>
                
                <?php if (!empty($singleProduct['description'])): ?>
                  <p class="text-muted fs-5 mb-4"><?php echo htmlspecialchars($singleProduct['description']); ?></p>
                <?php endif; ?>
                
                <?php
                $originalPrice = floatval($singleProduct['price']);
                $discount = intval($singleProduct['discount_percent'] ?? 0);
                $finalPrice = $discount > 0 ? $originalPrice - ($originalPrice * $discount / 100) : $originalPrice;
                $quantity = intval($singleProduct['quantity'] ?? 0);
                ?>
                
                <div class="mb-4">
                  <span class="product-price">৳<?php echo number_format($finalPrice, 0); ?></span>
                  <?php if ($discount > 0): ?>
                    <span class="product-original-price">৳<?php echo number_format($originalPrice, 0); ?></span>
                    <span class="discount-badge"><?php echo $discount; ?>% OFF</span>
                  <?php endif; ?>
                </div>
                
                <div class="mb-4">
                  <?php if ($quantity > 10): ?>
                    <span class="stock-badge in-stock"><i class="bi bi-check-circle-fill"></i>In Stock</span>
                  <?php elseif ($quantity > 0): ?>
                    <span class="stock-badge low-stock"><i class="bi bi-exclamation-circle-fill"></i>Only <?php echo $quantity; ?> left!</span>
                  <?php else: ?>
                    <span class="stock-badge out-of-stock"><i class="bi bi-x-circle-fill"></i>Out of Stock</span>
                  <?php endif; ?>
                </div>
                
                <?php if ($quantity > 0): ?>
                  <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="quantity-control">
                      <button type="button" onclick="decreaseQty()">−</button>
                      <input type="number" id="productQty" value="1" min="1" max="<?php echo $quantity; ?>" readonly>
                      <button type="button" onclick="increaseQty(<?php echo $quantity; ?>)">+</button>
                    </div>
                    <button class="btn btn-success add-to-cart-btn" onclick="addToCartFromProduct(<?php echo $singleProduct['id']; ?>, '<?php echo addslashes($singleProduct['title']); ?>', <?php echo $finalPrice; ?>)">
                      <i class="bi bi-cart-plus me-2"></i>Add to Cart
                    </button>
                  </div>
                <?php else: ?>
                  <button class="btn btn-secondary btn-lg" disabled>
                    <i class="bi bi-x-circle me-2"></i>Out of Stock
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </section>
      
      <?php if (!empty($relatedProducts)): ?>
        <!-- Related Products -->
        <section class="related-section">
          <div class="container">
            <h3 class="mb-4"><i class="bi bi-grid me-2 text-success"></i>You May Also Like</h3>
            <div class="row g-4">
              <?php foreach ($relatedProducts as $related): 
                $relOriginal = floatval($related['price']);
                $relDiscount = intval($related['discount_percent'] ?? 0);
                $relFinal = $relDiscount > 0 ? $relOriginal - ($relOriginal * $relDiscount / 100) : $relOriginal;
              ?>
                <div class="col-6 col-md-3">
                  <a href="product.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                    <div class="search-result-card">
                      <img src="<?php echo htmlspecialchars($related['image_url'] ?: 'images/default-food.png'); ?>" 
                           class="w-100" alt="<?php echo htmlspecialchars($related['title']); ?>"
                           onerror="this.src='images/default-food.png'">
                      <div class="p-3">
                        <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($related['title']); ?></h6>
                        <div class="text-success fw-bold">৳<?php echo number_format($relFinal, 0); ?></div>
                      </div>
                    </div>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>
      
    <?php else: ?>
      <!-- Search Results Grid -->
      <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2><i class="bi bi-search me-2 text-success"></i>Search Results</h2>
            <p class="text-muted mb-0">
              Found <strong><?php echo count($products); ?></strong> products matching 
              "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"
            </p>
          </div>
          <a href="index.php" class="btn btn-outline-success">
            <i class="bi bi-house me-1"></i>Back Home
          </a>
        </div>
        
        <div class="row g-4">
          <?php foreach ($products as $product):
            $pOriginal = floatval($product['price']);
            $pDiscount = intval($product['discount_percent'] ?? 0);
            $pFinal = $pDiscount > 0 ? $pOriginal - ($pOriginal * $pDiscount / 100) : $pOriginal;
            $pQty = intval($product['quantity'] ?? 0);
          ?>
            <div class="col-6 col-md-4 col-lg-3">
              <div class="search-result-card">
                <a href="product.php?id=<?php echo $product['id']; ?>">
                  <div class="position-relative">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'images/default-food.png'); ?>" 
                         class="w-100" alt="<?php echo htmlspecialchars($product['title']); ?>"
                         onerror="this.src='images/default-food.png'">
                    <?php if ($pDiscount > 0): ?>
                      <span class="position-absolute top-0 end-0 m-2 badge bg-danger"><?php echo $pDiscount; ?>% OFF</span>
                    <?php endif; ?>
                    <?php if ($pQty <= 0): ?>
                      <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                           style="background:rgba(0,0,0,0.5)">
                        <span class="badge bg-danger fs-6">Out of Stock</span>
                      </div>
                    <?php endif; ?>
                  </div>
                </a>
                <div class="p-3">
                  <span class="badge bg-success-subtle text-success mb-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
                  <h6 class="mb-2"><?php echo htmlspecialchars($product['title']); ?></h6>
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <span class="text-success fw-bold fs-5">৳<?php echo number_format($pFinal, 0); ?></span>
                      <?php if ($pDiscount > 0): ?>
                        <small class="text-muted text-decoration-line-through ms-1">৳<?php echo number_format($pOriginal, 0); ?></small>
                      <?php endif; ?>
                    </div>
                    <?php if ($pQty > 0): ?>
                      <button class="btn btn-success btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                        <i class="bi bi-cart-plus"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </main>
  
  <?php include 'includes/footer.php'; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/cart.js"></script>
  <script>
    function increaseQty(max) {
      const input = document.getElementById('productQty');
      let val = parseInt(input.value) || 1;
      if (val < max) {
        input.value = val + 1;
      }
    }
    
    function decreaseQty() {
      const input = document.getElementById('productQty');
      let val = parseInt(input.value) || 1;
      if (val > 1) {
        input.value = val - 1;
      }
    }
    
    function addToCartFromProduct(id, title, price) {
      const qty = parseInt(document.getElementById('productQty').value) || 1;
      
      // Use the cart.js addToCart function with quantity
      if (typeof window.cart !== 'undefined') {
        for (let i = 0; i < qty; i++) {
          addToCart(id);
        }
        
        // Show success toast
        showToast(`Added ${qty}x ${title} to cart!`, 'success');
      } else {
        // Fallback - add directly
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existingIndex = cart.findIndex(item => item.id === id);
        
        if (existingIndex !== -1) {
          cart[existingIndex].quantity += qty;
        } else {
          cart.push({ id: id, quantity: qty });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        showToast(`Added ${qty}x ${title} to cart!`, 'success');
      }
    }
    
    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `position-fixed bottom-0 end-0 m-4 p-3 bg-${type} text-white rounded-3 shadow-lg`;
      toast.style.zIndex = '9999';
      toast.innerHTML = `<i class="bi bi-check-circle me-2"></i>${message}`;
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.5s';
        setTimeout(() => toast.remove(), 500);
      }, 2500);
    }
  </script>
</body>
</html>

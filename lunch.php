<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lunch - Green Bites</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>

<!-- LUNCH SECTION -->
<section id="lunchSection" class="py-3">
  <div class="container">
    <!-- Page Header -->
    <div class="menu-page-header text-center mb-4">
      <h2 class="menu-page-title"><i class="bi bi-egg-fried me-2"></i>Lunch</h2>
      <div class="heading-underline mx-auto"></div>
      <p class="text-muted mt-2">Hearty and delicious lunch options</p>
    </div>
    
    <!-- MENU CARDS - Dynamically loaded from database -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4 mb-4">
      <?php
      // Include database connection
      include 'db.php';
      
      // Fetch lunch items (category_id = 3 for Lunch)
      $sql = "SELECT * FROM menu_items WHERE category_id = 3 AND is_available = 1 ORDER BY title";
      $result = mysqli_query($conn, $sql);
      
      if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $title = htmlspecialchars($row['title']);
          $price = number_format($row['price'], 0);
          $image = htmlspecialchars($row['image_url']);
          $description = htmlspecialchars($row['description'] ?? '');
          $quantity = intval($row['quantity'] ?? 0);
          $isStockout = ($quantity == 0);
          $isLowStock = ($quantity > 0 && $quantity <= 5);
          ?>
          <div class="col">
            <div class="card shadow menu-card border-success h-100 <?php echo $isStockout ? 'stockout-card' : ''; ?>">
              <div class="position-relative">
                <img src="<?php echo $image; ?>" class="card-img-top <?php echo $isStockout ? 'stockout-image' : ''; ?>" style="height:140px;object-fit:cover;" alt="<?php echo $title; ?>">
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
                <div class="card-title-price">
                  <h5 class="card-title"><?php echo $title; ?></h5>
                  <span class="badge bg-success price-badge">৳<?php echo $price; ?></span>
                </div>
                <?php if ($isStockout): ?>
                  <button class="btn btn-secondary w-100 disabled" disabled>
                    <i class="bi bi-x-circle me-1"></i>Out of Stock
                  </button>
                <?php else: ?>
                  <button class="btn btn-success w-100 order-btn" data-item-id="<?php echo $row['id']; ?>" data-item-title="<?php echo $title; ?>" data-item-price="<?php echo $price; ?>" data-item-image="<?php echo $image; ?>">Order</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php
        }
      } else {
        echo '<div class="col-12"><div class="alert alert-info text-center">No lunch items available at the moment. Please check back later!</div></div>';
      }
      
      mysqli_close($conn);
      ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>

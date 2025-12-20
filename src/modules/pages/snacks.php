<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                       GREEN BITES - SNACKS PAGE                           ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Snacks - Green Bites</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/svg+xml" href="<?php echo IMAGES_URL; ?>/logo-icon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo CSS_URL; ?>/style.css">
</head>
<body class="bg-light">
<?php include COMPONENTS_PATH . '/header.php'; ?>

<section id="snacksSection" class="py-3">
  <div class="container">
    <!-- Page Header -->
    <div class="menu-page-header text-center mb-4">
      <h2 class="menu-page-title"><i class="bi bi-cookie me-2"></i>Snacks</h2>
      <div class="heading-underline mx-auto"></div>
      <p class="text-muted mt-2">Quick bites and tasty treats</p>
    </div>
    
    <!-- MENU CARDS - Dynamically loaded from database -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4 mb-4">
      <?php
      // Fetch snacks items (category_id = 4)
      $sql = "SELECT * FROM menu_items WHERE category_id = 4 AND is_available = 1 ORDER BY title";
      $result = mysqli_query($conn, $sql);
      
      if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $title = htmlspecialchars($row['title']);
          $price = number_format($row['price'], 0);
          $image = htmlspecialchars($row['image_url']);
          $description = htmlspecialchars($row['description'] ?? '');
          ?>
          <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="<?php echo $image; ?>" class="card-img-top" style="height:140px;object-fit:cover;" alt="<?php echo $title; ?>">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title"><?php echo $title; ?></h5>
                  <span class="badge bg-success price-badge">৳<?php echo $price; ?></span>
                </div>
                <button class="btn btn-success w-100 order-btn" data-item-id="<?php echo $row['id']; ?>" data-item-title="<?php echo $title; ?>" data-item-price="<?php echo $price; ?>" data-item-image="<?php echo $image; ?>">Order</button>
              </div>
            </div>
          </div>
          <?php
        }
      } else {
        echo '<div class="col-12"><div class="alert alert-info text-center">No snacks available at the moment. Please check back later!</div></div>';
      }
      
      mysqli_close($conn);
      ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>

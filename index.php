<?php

require_once __DIR__ . '/config/security.php';
initSecureSession();

require_once 'db.php';

// Generate CSRF Token using security function
$csrf_token = generateCSRFToken();

// Get user info from session (if logged in)
$userName  = '';
$userEmail = '';
if (isset($_SESSION['user_id'])) {
  $userName  = htmlspecialchars($_SESSION['full_name'] ?? '');
  $userEmail = htmlspecialchars($_SESSION['email'] ?? '');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Green Bites - Student</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="images/logo-icon.svg">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
      
  <!-- HOME SECTION - Modern Hero Carousel -->
  <section id="homeSection" class="hero-carousel-section">
    <div class="container-fluid px-0">
      <!-- Modern Carousel -->
      <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel" data-bs-interval="5000">
        
        <!-- Carousel Indicators -->
        <div class="carousel-indicators custom-indicators">
          <?php
          $slideResult = mysqli_query($conn, "SELECT * FROM carousel_slides WHERE is_active = 1 ORDER BY sort_order ASC");
          $slideCount = mysqli_num_rows($slideResult);
          $slideIndex = 0;
          mysqli_data_seek($slideResult, 0);
          while ($slideIndex < $slideCount) {
            $activeClass = $slideIndex === 0 ? 'active' : '';
            echo '<button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="'.$slideIndex.'" class="'.$activeClass.'"></button>';
            $slideIndex++;
          }
          mysqli_data_seek($slideResult, 0);
          ?>
        </div>
        
        <!-- Carousel Items -->
        <div class="carousel-inner">
          <?php
          $isFirst = true;
          while ($slide = mysqli_fetch_assoc($slideResult)):
            $activeClass = $isFirst ? 'active' : '';
            $isFirst = false;
            
            // Get linked menu item details if available
            $menuItem = null;
            $menuDiscount = 0;
            $menuFinalPrice = 0;
            if (!empty($slide['menu_item_id'])) {
              $menuQuery = mysqli_query($conn, "SELECT id, title, price, discount_percent, image_url FROM menu_items WHERE id = " . intval($slide['menu_item_id']));
              $menuItem = mysqli_fetch_assoc($menuQuery);
              if ($menuItem) {
                $menuDiscount = intval($menuItem['discount_percent'] ?? 0);
                $menuOriginalPrice = floatval($menuItem['price']);
                $menuFinalPrice = $menuDiscount > 0 ? $menuOriginalPrice - ($menuOriginalPrice * $menuDiscount / 100) : $menuOriginalPrice;
              }
            }
          ?>
          <div class="carousel-item <?php echo $activeClass; ?>">
            <!-- Background Image with Overlay -->
            <div class="carousel-bg" style="background-image: url('<?php echo htmlspecialchars($slide['image_url']); ?>');">
              <div class="carousel-overlay"></div>
            </div>
            
            <!-- Content -->
            <div class="carousel-content-wrapper">
              <div class="container">
                <div class="row align-items-center min-vh-50">
                  <div class="col-lg-6">
                    <div class="carousel-content">
                      <span class="carousel-badge">
                        <i class="bi bi-fire me-1"></i> <?php echo $menuDiscount > 0 ? $menuDiscount . '% OFF!' : "Today's Special"; ?>
                      </span>
                      <h1 class="carousel-title"><?php echo htmlspecialchars($slide['title']); ?></h1>
                      <p class="carousel-desc"><?php echo htmlspecialchars($slide['description']); ?></p>
                      
                      <div class="carousel-price-tag">
                        <?php if ($menuItem && $menuDiscount > 0): ?>
                        <span class="price-label">Now Only</span>
                        <span class="price-amount">৳<?php echo number_format($menuFinalPrice, 0); ?></span>
                        <span class="price-original">৳<?php echo number_format($menuOriginalPrice, 0); ?></span>
                        <?php else: ?>
                        <span class="price-label">Only</span>
                        <span class="price-amount">৳<?php echo number_format($slide['price'], 0); ?></span>
                        <?php endif; ?>
                      </div>
                      
                      <?php if ($menuItem): ?>
                      <button type="button" class="btn carousel-cta order-btn" 
                              data-item-id="<?php echo $menuItem['id']; ?>" 
                              data-item-title="<?php echo htmlspecialchars($menuItem['title']); ?>" 
                              data-item-price="<?php echo $menuFinalPrice; ?>"
                              data-item-original-price="<?php echo $menuOriginalPrice; ?>"
                              data-item-discount="<?php echo $menuDiscount; ?>"
                              data-item-image="<?php echo htmlspecialchars($menuItem['image_url'] ?? $slide['image_url']); ?>">
                        <i class="bi bi-cart-plus me-2"></i><?php echo htmlspecialchars($slide['btn_text'] ?? 'Order Now'); ?>
                      </button>
                      <?php else: ?>
                      <a href="#menuSection" class="btn carousel-cta" onclick="scrollToMenu(event)">
                        <i class="bi bi-bag me-2"></i>View Menu
                      </a>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="col-lg-6 d-none d-lg-block">
                    <div class="carousel-image-wrapper">
                      <div class="carousel-floating-image">
                        <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" alt="<?php echo htmlspecialchars($slide['title']); ?>">
                      </div>
                      <div class="floating-elements">
                        <div class="float-badge badge-1">
                          <i class="bi bi-star-fill"></i> 4.9
                        </div>
                        <div class="float-badge badge-2">
                          <i class="bi bi-clock"></i> 15 min
                        </div>
                        <div class="float-badge badge-3">
                          <i class="bi bi-fire"></i> Hot
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
        
        <!-- Navigation Arrows -->
        <button class="carousel-control-prev custom-nav" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
          <div class="nav-icon-wrapper">
            <i class="bi bi-chevron-left"></i>
          </div>
        </button>
        <button class="carousel-control-next custom-nav" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
          <div class="nav-icon-wrapper">
            <i class="bi bi-chevron-right"></i>
          </div>
        </button>
      </div>
    </div>
  </section>

  <!-- FOOD MENU SECTION -->
  <section id="menuSection" class="menu-section py-5">
    <div class="container">
      <div class="text-center mb-4">
        <span class="section-badge"><i class="bi bi-grid-fill me-2"></i>Our Menu</span>
        <h2 class="section-title-modern">Delicious <span class="gradient-text">Food Items</span></h2>
      </div>
      
      <!-- FOOD CARDS -->
      <div id="dealsSection" class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4 mb-4">
        <?php
        // Include database connection
        include 'db.php';
        
        // Fetch all food items from every category (no category filter)
        $sql = "SELECT * FROM menu_items WHERE is_available = 1 ORDER BY title";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            $title = htmlspecialchars($row['title']);
            $originalPrice = floatval($row['price']);
            $discountPercent = intval($row['discount_percent'] ?? 0);
            $finalPrice = $discountPercent > 0 ? $originalPrice - ($originalPrice * $discountPercent / 100) : $originalPrice;
            $hasDiscount = $discountPercent > 0;
            $image = htmlspecialchars($row['image_url']);
            $description = htmlspecialchars($row['description'] ?? '');
            $quantity = intval($row['quantity'] ?? 0);
            $isStockout = ($quantity == 0);
            $isLowStock = ($quantity > 0 && $quantity <= 5);
            
            // Generate description if empty
            $displayDescription = $description;
            if (empty($displayDescription)) {
              $descriptions = [
                "A delicious and freshly prepared " . strtolower($title) . " made with premium ingredients. Perfect for satisfying your cravings!",
                "Experience the authentic taste of our " . strtolower($title) . ". Made with love and care for the best dining experience.",
                "Enjoy our signature " . strtolower($title) . " - crafted with the finest ingredients for an unforgettable taste.",
                "Our " . strtolower($title) . " is a customer favorite! Prepared fresh daily with quality ingredients."
              ];
              $displayDescription = $descriptions[array_rand($descriptions)];
            }
            ?>
            <div class="col">
              <div class="card shadow menu-card border-success h-100 <?php echo $isStockout ? 'stockout-card' : ''; ?>">
                <div class="position-relative">
                  <img src="<?php echo $image; ?>" class="card-img-top <?php echo $isStockout ? 'stockout-image' : ''; ?>" style="height:140px;object-fit:cover;" alt="<?php echo $title; ?>">
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
                  <div class="card-title-price">
                    <h5 class="card-title"><?php echo $title; ?></h5>
                    <div class="price-info-row">
                      <?php if ($hasDiscount): ?>
                      <div class="price-display">
                        <span class="badge bg-success price-badge">৳<?php echo number_format($finalPrice, 0); ?></span>
                        <small class="original-price">৳<?php echo number_format($originalPrice, 0); ?></small>
                      </div>
                      <?php else: ?>
                      <span class="badge bg-success price-badge">৳<?php echo number_format($originalPrice, 0); ?></span>
                      <?php endif; ?>
                      <!-- Info Button -->
                      <button class="product-info-btn-sm" 
                              onclick="event.stopPropagation(); showProductInfo('<?php echo addslashes($title); ?>', '<?php echo $image; ?>', '<?php echo addslashes($displayDescription); ?>', <?php echo $finalPrice; ?>, <?php echo $originalPrice; ?>, <?php echo $discountPercent; ?>, <?php echo $quantity; ?>)"
                              title="View Details">
                        <i class="bi bi-info-lg"></i>
                      </button>
                    </div>
                  </div>
                  <?php if ($isStockout): ?>
                    <button class="btn btn-secondary w-100 disabled order-btn" disabled data-item-id="<?php echo $row['id']; ?>" data-item-title="<?php echo $title; ?>" data-item-price="<?php echo $finalPrice; ?>" data-item-original-price="<?php echo $originalPrice; ?>" data-item-discount="<?php echo $discountPercent; ?>" data-item-image="<?php echo $image; ?>">
                      <i class="bi bi-x-circle me-1"></i>Out of Stock
                    </button>
                  <?php else: ?>
                    <button class="btn btn-success w-100 order-btn" data-item-id="<?php echo $row['id']; ?>" data-item-title="<?php echo $title; ?>" data-item-price="<?php echo $finalPrice; ?>" data-item-original-price="<?php echo $originalPrice; ?>" data-item-discount="<?php echo $discountPercent; ?>" data-item-image="<?php echo $image; ?>">Order</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php
          }
        } else {
          echo '<div class="col-12"><div class="alert alert-info text-center">No items available at the moment. Please check back later!</div></div>';
        }
        
        mysqli_close($conn);
        ?>
      </div>
    </div>
  </section>

  <!-- ABOUT US SECTION - Modern Design -->
  <section id="aboutusSection" class="about-section-modern py-5">
    <div class="container">
      <!-- Section Header -->
      <div class="text-center mb-5">
        <span class="section-badge">🌿 Who We Are</span>
        <h2 class="section-title-modern">About <span class="gradient-text">Green Bites</span></h2>
        <p class="section-subtitle">Revolutionizing campus dining experience with technology and taste</p>
      </div>
      
      <div class="row align-items-center g-5">
        <!-- Left Content -->
        <div class="col-lg-6">
          <div class="about-content-modern">
            <div class="about-badge-row mb-4">
              <span class="mini-badge"><i class="bi bi-patch-check-fill"></i> Trusted by 500+ Students</span>
              <span class="mini-badge"><i class="bi bi-star-fill"></i> 4.9 Rating</span>
            </div>
            
            <h3 class="about-subtitle">Your Campus Food Companion! 🍽️</h3>
            
            <p class="about-text">
              <strong>Green Bites</strong> is not just a canteen—it's a <span class="highlight-tag">digital dining revolution</span> designed specifically for university students and teachers. We believe that <span class="highlight-tag">great food fuels great minds!</span>
            </p>
            
            <p class="about-text">
              Say goodbye to long queues and confusion. With Green Bites, experience <span class="highlight-tag">instant ordering</span>, real-time menu updates, and delicious Bangladeshi flavors—all at your fingertips.
            </p>
            
            <div class="about-stats-row">
              <div class="stat-item">
                <div class="stat-number">32+</div>
                <div class="stat-label">Menu Items</div>
              </div>
              <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Happy Users</div>
              </div>
              <div class="stat-item">
                <div class="stat-number">99%</div>
                <div class="stat-label">Satisfaction</div>
              </div>
            </div>
            
            <a href="#teamSection" class="btn btn-gradient-modern mt-4">
              <i class="bi bi-people-fill me-2"></i>Meet Our Team
            </a>
          </div>
        </div>
        
        <!-- Right Features Grid -->
        <div class="col-lg-6">
          <div class="features-grid-modern">
            <div class="feature-card-modern" data-aos="fade-up">
              <div class="feature-icon-modern">
                <i class="bi bi-lightning-charge-fill"></i>
              </div>
              <div class="feature-content">
                <h5>Lightning Fast Orders</h5>
                <p>Order your favorite food in under 30 seconds with our intuitive interface</p>
              </div>
              <div class="feature-glow"></div>
            </div>
            
            <div class="feature-card-modern" data-aos="fade-up" data-aos-delay="100">
              <div class="feature-icon-modern purple">
                <i class="bi bi-clock-history"></i>
              </div>
              <div class="feature-content">
                <h5>Real-Time Tracking</h5>
                <p>Track your order status live from kitchen to your table</p>
              </div>
              <div class="feature-glow purple"></div>
            </div>
            
            <div class="feature-card-modern" data-aos="fade-up" data-aos-delay="200">
              <div class="feature-icon-modern orange">
                <i class="bi bi-shield-lock-fill"></i>
              </div>
              <div class="feature-content">
                <h5>Secure Payments</h5>
                <p>Multiple payment options - bKash, Nagad, Cards & Cash</p>
              </div>
              <div class="feature-glow orange"></div>
            </div>
            
            <div class="feature-card-modern" data-aos="fade-up" data-aos-delay="300">
              <div class="feature-icon-modern cyan">
                <i class="bi bi-piggy-bank-fill"></i>
              </div>
              <div class="feature-content">
                <h5>Budget Friendly</h5>
                <p>Student-friendly prices without compromising quality</p>
              </div>
              <div class="feature-glow cyan"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Background Elements -->
    <div class="about-bg-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>
  </section>

  <!-- MEET THE DEVELOPERS SECTION - Modern Design -->
  <section id="teamSection" class="team-section-modern py-5">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-badge dark">👨‍💻 The Creators</span>
        <h2 class="section-title-modern">Meet the <span class="gradient-text">Developers</span></h2>
        <p class="section-subtitle">The passionate minds who brought Green Bites to life</p>
      </div>
      
      <div class="row justify-content-center g-4">
        <!-- Developer 1 - Sajjad -->
        <div class="col-md-6 col-lg-5">
          <div class="developer-card">
            <div class="developer-card-bg"></div>
            <div class="developer-content">
              <div class="developer-avatar-wrapper">
                <div class="developer-avatar">
                  <img src="sajjad.jpg" alt="Sajjad Mahmud Suton">
                </div>
                <div class="avatar-ring"></div>
                <div class="status-badge online">
                  <i class="bi bi-code-slash"></i>
                </div>
              </div>
              
              <h4 class="developer-name">Md. Sajjad Mahmud Suton</h4>
              <div class="developer-role">
                <span class="role-tag primary">Full-Stack Developer</span>
              </div>
              
              <p class="developer-bio">
                Passionate about building beautiful web applications. Specialized in UI/UX design, 
                frontend development with Bootstrap, and backend integration with PHP & MySQL.
              </p>
              
              <div class="developer-skills">
                <span class="skill-tag">PHP</span>
                <span class="skill-tag">MySQL</span>
                <span class="skill-tag">Bootstrap</span>
                <span class="skill-tag">JavaScript</span>
              </div>
              
              <div class="developer-social">
                <a href="mailto:sajjadmahmudsuton@gmail.com" class="social-btn-modern email" title="Email">
                  <i class="bi bi-envelope-fill"></i>
                </a>
                <a href="https://github.com/Sajjad-Mahmud-Suton" class="social-btn-modern github" title="GitHub" target="_blank">
                  <i class="bi bi-github"></i>
                </a>
                <a href="https://www.facebook.com/sajjadmahmudsuton.suton" class="social-btn-modern facebook" title="Facebook" target="_blank">
                  <i class="bi bi-facebook"></i>
                </a>
                <a href="https://wa.me/8801968161494" class="social-btn-modern whatsapp" title="WhatsApp" target="_blank">
                  <i class="bi bi-whatsapp"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Developer 2 - Esha -->
        <div class="col-md-6 col-lg-5">
          <div class="developer-card">
            <div class="developer-card-bg alt"></div>
            <div class="developer-content">
              <div class="developer-avatar-wrapper">
                <div class="developer-avatar alt">
                  <img src="https://ui-avatars.com/api/?name=Esha+Akter&background=a855f7&color=fff&size=150&bold=true" alt="Esha Akter">
                </div>
                <div class="avatar-ring alt"></div>
                <div class="status-badge online alt">
                  <i class="bi bi-palette-fill"></i>
                </div>
              </div>
              
              <h4 class="developer-name">Esha Akter</h4>
              <div class="developer-role">
                <span class="role-tag secondary">Frontend Developer</span>
              </div>
              
              <p class="developer-bio">
                Creative frontend developer with an eye for detail. Expert in responsive design, 
                UI components, and creating seamless user experiences across all devices.
              </p>
              
              <div class="developer-skills">
                <span class="skill-tag alt">HTML5</span>
                <span class="skill-tag alt">CSS3</span>
                <span class="skill-tag alt">Bootstrap</span>
                <span class="skill-tag alt">UI/UX</span>
              </div>
              
              <div class="developer-social">
                <a href="mailto:mstehsa981@gmail.com" class="social-btn-modern email" title="Email">
                  <i class="bi bi-envelope-fill"></i>
                </a>
                <a href="https://github.com/Esha-Akter" class="social-btn-modern github" title="GitHub" target="_blank">
                  <i class="bi bi-github"></i>
                </a>
                <a href="#" class="social-btn-modern facebook" title="Facebook">
                  <i class="bi bi-facebook"></i>
                </a>
                <a href="#" class="social-btn-modern whatsapp" title="WhatsApp">
                  <i class="bi bi-whatsapp"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Project Info -->
      <div class="project-info-card mt-5">
        <div class="row align-items-center">
          <div class="col-md-8">
            <div class="project-info-content">
              <h5><i class="bi bi-mortarboard-fill me-2"></i>Academic Project</h5>
              <p>Green Bites was developed as a university project to demonstrate modern web development skills and solve real campus dining challenges.</p>
            </div>
          </div>
          <div class="col-md-4 text-center text-md-end">
            <a href="https://github.com/Sajjad-Mahmud-Suton/Green-Bites" target="_blank" class="btn btn-outline-light">
              <i class="bi bi-github me-2"></i>View on GitHub
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- COMPLAINTS SECTION -->
  <section id="complaintsSection" class="mb-5">
    <div class="container">
      <h3 class="mb-3 text-danger">Complaint</h3>
      <!-- Complaints Disabled Alert -->
      <div id="complaintsDisabledAlert" class="alert alert-warning d-none" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <span id="complaintsDisabledMessage">Complaint submission is currently closed. Please try again later.</span>
      </div>
      <form id="complaintForm" action="submit_complaint.php" method="POST" enctype="multipart/form-data" class="card p-3 shadow-sm">
        <div class="mb-3">
          <label class="form-label">Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="complaintName" name="name" value="<?php echo $userName; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control" id="complaintEmail" name="email" value="<?php echo $userEmail; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Order ID (optional)</label>
          <input type="text" class="form-control" id="complaintOrderId" name="order_id">
        </div>
        <div class="mb-3">
          <label class="form-label">Complaint Message <span class="text-danger">*</span></label>
          <textarea class="form-control" id="complaintText" name="message" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Add Image (optional)</label>
          <input type="file" class="form-control" id="complaintImage" name="image" accept="image/*">
        </div>
        <button type="submit" class="btn btn-danger w-100" id="complaintSubmitBtn">Submit</button>
        <small id="complaintMsg" class="text-success ms-2"></small>
      </form>
    </div>
  </section>

  <!-- Complaint success popup modal -->
  <div id="complaintSuccessModal" class="complaint-modal-overlay d-none">
    <div class="complaint-modal-box">
      <div class="complaint-modal-icon">✓</div>
      <div class="complaint-modal-title">Submitted Successfully!</div>
      <button type="button" id="complaintModalClose" class="btn btn-success complaint-modal-close-btn">
        Close
      </button>
    </div>
  </div>

  <!-- Complaint Form Handler Script -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const complaintForm = document.getElementById('complaintForm');
    const complaintSuccessModal = document.getElementById('complaintSuccessModal');
    const complaintModalClose = document.getElementById('complaintModalClose');
    const complaintMsg = document.getElementById('complaintMsg');
    const complaintSubmitBtn = document.getElementById('complaintSubmitBtn');
    const complaintsDisabledAlert = document.getElementById('complaintsDisabledAlert');
    const complaintsDisabledMessage = document.getElementById('complaintsDisabledMessage');
    
    // Check complaint status on page load
    async function checkComplaintStatus() {
      try {
        const response = await fetch('api/check_complaint_status.php');
        const data = await response.json();
        if (data.success && !data.enabled) {
          // Disable complaint form
          complaintsDisabledAlert.classList.remove('d-none');
          complaintsDisabledMessage.textContent = data.message;
          complaintSubmitBtn.disabled = true;
          complaintSubmitBtn.textContent = 'Submissions Closed';
          complaintSubmitBtn.classList.remove('btn-danger');
          complaintSubmitBtn.classList.add('btn-secondary');
        }
      } catch (error) {
        console.error('Error checking complaint status:', error);
      }
    }
    checkComplaintStatus();

    if (complaintForm) {
      complaintForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (complaintMsg) {
          complaintMsg.textContent = '';
          complaintMsg.className = 'text-success ms-2';
        }

        const name = document.getElementById('complaintName')?.value.trim() || '';
        const email = document.getElementById('complaintEmail')?.value.trim() || '';
        const message = document.getElementById('complaintText')?.value.trim() || '';

        if (!name || !email || !message) {
          if (complaintMsg) {
            complaintMsg.textContent = 'Please fill in all required fields.';
            complaintMsg.className = 'text-danger ms-2';
          }
          return;
        }

        const formData = new FormData(complaintForm);
        const submitBtn = complaintForm.querySelector('button[type="submit"]');
        
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        }

        try {
          const response = await fetch('submit_complaint.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.json();

          if (result.success) {
            complaintForm.reset();
            if (complaintMsg) complaintMsg.textContent = '';
            if (complaintSuccessModal) {
              complaintSuccessModal.classList.remove('d-none');
            }
          } else {
            if (complaintMsg) {
              complaintMsg.textContent = result.message || 'Error submitting complaint.';
              complaintMsg.className = 'text-danger ms-2';
            }
          }
        } catch (err) {
          console.error(err);
          if (complaintMsg) {
            complaintMsg.textContent = 'Error submitting complaint. Please try again.';
            complaintMsg.className = 'text-danger ms-2';
          }
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit';
          }
        }
      });
    }

    if (complaintModalClose && complaintSuccessModal) {
      complaintModalClose.addEventListener('click', function() {
        complaintSuccessModal.classList.add('d-none');
      });
    }
  });
  </script>
 
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
/* Price Info Row with Info Button */
.price-info-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Small Info Button for Price Row */
.product-info-btn-sm {
  width: 24px;
  height: 24px;
  min-width: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, #16a34a, #15803d);
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.75rem;
  padding: 0;
  box-shadow: 0 2px 8px rgba(22, 163, 74, 0.3);
}

.product-info-btn-sm:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.5);
  background: linear-gradient(135deg, #22c55e, #16a34a);
}

.product-info-btn-sm i {
  font-size: 0.7rem;
  font-weight: 700;
}

/* Legacy - keep for category pages */
.product-info-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: linear-gradient(135deg, #16a34a, #15803d);
  border: 2px solid white;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.8rem;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
  z-index: 10;
}

.product-info-btn:hover {
  transform: scale(1.15);
  box-shadow: 0 6px 20px rgba(22, 163, 74, 0.6);
  background: linear-gradient(135deg, #22c55e, #16a34a);
}

.product-info-btn i {
  font-size: 0.8rem;
}

/* Product Info Modal */
.product-info-modal-content {
  border: none;
  border-radius: 24px;
  overflow: hidden;
  background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
}

.product-info-close {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 100;
  background: white;
  border-radius: 50%;
  padding: 12px;
  opacity: 1;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.product-info-close:hover {
  transform: rotate(90deg);
  background: #fee2e2;
}

.product-info-wrapper {
  display: flex;
  flex-wrap: wrap;
}

.product-info-image-section {
  flex: 0 0 50%;
  max-width: 50%;
  padding: 30px;
  background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-info-image-container {
  position: relative;
  width: 100%;
  max-width: 350px;
}

.product-info-image-container img {
  width: 100%;
  height: 320px;
  object-fit: cover;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  transition: transform 0.5s ease;
}

.product-info-image-container:hover img {
  transform: scale(1.05);
}

.product-info-badges {
  position: absolute;
  top: 15px;
  left: 15px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.product-info-discount-badge {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  padding: 6px 14px;
  border-radius: 50px;
  font-weight: 700;
  font-size: 0.85rem;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.product-info-stock-badge {
  background: linear-gradient(135deg, #16a34a, #15803d);
  color: white;
  padding: 6px 14px;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.8rem;
  box-shadow: 0 4px 15px rgba(22, 163, 74, 0.4);
}

.product-info-stock-badge.low-stock {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.product-info-stock-badge.out-of-stock {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.product-info-details {
  flex: 0 0 50%;
  max-width: 50%;
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.product-info-category {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #dcfce7, #bbf7d0);
  color: #16a34a;
  padding: 6px 16px;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.85rem;
  margin-bottom: 15px;
  width: fit-content;
}

.product-info-title {
  font-size: 2rem;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 15px;
  line-height: 1.2;
}

.product-info-description {
  color: #64748b;
  font-size: 1rem;
  line-height: 1.7;
  margin-bottom: 25px;
}

.product-info-price-section {
  background: white;
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  margin-bottom: 25px;
}

.product-info-price-row {
  display: flex;
  align-items: center;
  gap: 15px;
}

.product-info-current-price {
  font-size: 2.2rem;
  font-weight: 800;
  color: #16a34a;
}

.product-info-original-price {
  font-size: 1.3rem;
  color: #9ca3af;
  text-decoration: line-through;
}

.product-info-savings {
  margin-top: 10px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  color: #92400e;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  width: fit-content;
}

.product-info-features {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.product-info-feature {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #f8fafc;
  padding: 10px 16px;
  border-radius: 12px;
  font-size: 0.85rem;
  color: #475569;
  font-weight: 500;
}

.product-info-feature i {
  color: #16a34a;
}

/* Responsive */
@media (max-width: 768px) {
  .product-info-wrapper {
    flex-direction: column;
  }
  
  .product-info-image-section,
  .product-info-details {
    flex: 0 0 100%;
    max-width: 100%;
  }
  
  .product-info-image-section {
    padding: 20px;
  }
  
  .product-info-image-container img {
    height: 220px;
  }
  
  .product-info-details {
    padding: 25px;
  }
  
  .product-info-title {
    font-size: 1.5rem;
  }
  
  .product-info-current-price {
    font-size: 1.8rem;
  }
  
  .product-info-features {
    gap: 10px;
  }
  
  .product-info-feature {
    padding: 8px 12px;
    font-size: 0.8rem;
  }
}
</style>

<script>
// Product Info Modal Function
function showProductInfo(title, image, description, finalPrice, originalPrice, discount, quantity) {
  // Set modal content
  document.getElementById('productInfoTitle').textContent = title;
  document.getElementById('productInfoImage').src = image;
  document.getElementById('productInfoDescription').textContent = description;
  document.getElementById('productInfoPrice').textContent = '৳' + Math.round(finalPrice).toLocaleString();
  
  // Handle discount
  const discountBadge = document.getElementById('productInfoDiscount');
  const originalPriceEl = document.getElementById('productInfoOriginalPrice');
  const savingsEl = document.getElementById('productInfoSavings');
  const saveAmountEl = document.getElementById('productInfoSaveAmount');
  
  if (discount > 0) {
    discountBadge.textContent = discount + '% OFF';
    discountBadge.classList.remove('d-none');
    originalPriceEl.textContent = '৳' + Math.round(originalPrice).toLocaleString();
    originalPriceEl.classList.remove('d-none');
    
    const savings = originalPrice - finalPrice;
    saveAmountEl.textContent = '৳' + Math.round(savings).toLocaleString();
    savingsEl.classList.remove('d-none');
  } else {
    discountBadge.classList.add('d-none');
    originalPriceEl.classList.add('d-none');
    savingsEl.classList.add('d-none');
  }
  
  // Handle stock status
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
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('productInfoModal'));
  modal.show();
}
</script>
  
<?php include 'includes/footer.php'; ?>
</body>
</html>

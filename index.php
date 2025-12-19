<?php

session_start();
require_once 'db.php';


if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get user info from new auth session structure
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
            if (!empty($slide['menu_item_id'])) {
              $menuQuery = mysqli_query($conn, "SELECT id, title, price, image_url FROM menu_items WHERE id = " . intval($slide['menu_item_id']));
              $menuItem = mysqli_fetch_assoc($menuQuery);
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
                        <i class="bi bi-fire me-1"></i> Today's Special
                      </span>
                      <h1 class="carousel-title"><?php echo htmlspecialchars($slide['title']); ?></h1>
                      <p class="carousel-desc"><?php echo htmlspecialchars($slide['description']); ?></p>
                      
                      <div class="carousel-price-tag">
                        <span class="price-label">Only</span>
                        <span class="price-amount">৳<?php echo number_format($slide['price'], 0); ?></span>
                      </div>
                      
                      <?php if ($menuItem): ?>
                      <button type="button" class="btn carousel-cta order-btn" 
                              data-item-id="<?php echo $menuItem['id']; ?>" 
                              data-item-title="<?php echo htmlspecialchars($menuItem['title']); ?>" 
                              data-item-price="<?php echo number_format($menuItem['price'], 0); ?>"
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
                  <button class="btn btn-success w-100 order-btn" data-item-id="<?php echo $row['id']; ?>" data-item-title="<?php echo $title; ?>" data-item-price="<?php echo $price; ?>">Order</button>
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
        <button type="submit" class="btn btn-danger w-100">Submit</button>
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
 
  
<?php include 'includes/footer.php'; ?>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Green Bites - Student</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>
      
  <!-- HOME SECTION -->
  <section id="homeSection" class="py-3">
    <div class="container">
      <!-- SLIDER FOR DAILY SPECIALS -->
      <div id="foodSlider" class="carousel slide shadow rounded mb-4" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="images/biriyani.jpg" class="d-block w-100" style="height:320px;object-fit:cover;">
            <div class="carousel-caption bg-dark bg-opacity-50 rounded">
              <h5>Biriyani Special</h5>
              <p>Rich chicken biriyani, fresh salad</p>
              <span class="badge bg-warning fs-5">৳120</span>
            </div>
          </div>
          <div class="carousel-item">
            <img src="images/burger.jpg" class="d-block w-100" style="height:320px;object-fit:cover;">
            <div class="carousel-caption bg-dark bg-opacity-50 rounded">
              <h5>Burger Combo</h5>
              <p>Crispy chicken burger & fries</p>
              <span class="badge bg-warning fs-5">৳90</span>
            </div>
          </div>
          <div class="carousel-item">
            <img src="images/friedrice.jpg" class="d-block w-100" style="height:320px;object-fit:cover;">
            <div class="carousel-caption bg-dark bg-opacity-50 rounded">
              <h5>Fried Rice Special</h5>
              <p>Egg fried rice with veg curry</p>
              <span class="badge bg-warning fs-5">৳80</span>
            </div>
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#foodSlider" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#foodSlider" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
      <!-- FOOD CARDS -->
      <div id="dealsSection" class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4 mb-4">
        <!-- Card Example—add more as needed -->
        <div class="col">
          <div class="card shadow menu-card border-success h-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/e/e6/Panta_Ilish.jpg" class="card-img-top" style="height:140px;object-fit:cover;">
            <div class="card-body">
              <div class="card-title-price">
                <h5 class="card-title">Steamed Rice</h5>
                <span class="badge bg-success price-badge">৳50</span>
              </div>
              <button class="btn btn-success w-100 order-btn">Order</button>
            </div>
          </div>
          
        </div>
        <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="https://www.allrecipes.com/thmb/249U3lsxHXdSPJdrTITzK_saOjE=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/6067251-bengali-chicken-curry-with-potatoes-Linda-Chor-4x3-1-94006a09cdec49ceaa26c1044f50359a.jpg" class="card-img-top" style="height:140px;object-fit:cover;">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title">Chicken Curry</h5>
                  <span class="badge bg-success price-badge">৳150</span>
                </div>
                <button class="btn btn-success w-100 order-btn">Order</button>
              </div>
            </div>
            
          </div>
          <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="https://www.bongcravings.com/wp-content/uploads/2017/01/IMG_3555.jpg" class="card-img-top" style="height:140px;object-fit:cover;">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title">Mutton Korma</h5>
                  <span class="badge bg-success price-badge">৳250</span>
                </div>
                <button class="btn btn-success w-100 order-btn">Order</button>
              </div>
            </div>
            
          </div>
          <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="https://cookishcreation.com/wp-content/uploads/2021/05/Beef-Tehari-Cookish-Creation.jpg" class="card-img-top" style="height:140px;object-fit:cover;">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title">Beef Tehari</h5>
                  <span class="badge bg-success price-badge">৳200</span>
                </div>
                <button class="btn btn-success w-100 order-btn">Order</button>
              </div>
            </div>
            
          </div>
          <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="https://www.licious.in/blog/wp-content/uploads/2022/08/shutterstock_1810759399.jpg" class="card-img-top" style="height:140px;object-fit:cover;">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title">Hilsa Fish Curry</h5>
                  <span class="badge bg-success price-badge">৳300</span>
                </div>
                <button class="btn btn-success w-100 order-btn">Order</button>
              </div>
            </div>
            
          </div>
          <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="https://png.pngtree.com/png-clipart/20240830/original/pngtree-a-delicious-chicken-biryani-png-image_15893631.png" class="card-img-top" style="height:140px;object-fit:cover;">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title">Chicken Biryani</h5>
                  <span class="badge bg-success price-badge">৳200</span>
                </div>
                <button class="btn btn-success w-100 order-btn">Order</button>
              </div>
            </div>
            
          </div>
          <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="https://i0.wp.com/veganbangla.com/wp-content/uploads/2020/05/fullsizeoutput_79b.jpeg?fit=1200%2C794&ssl=1" class="card-img-top" style="height:140px;object-fit:cover;">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title">Lentil Dal</h5>
                  <span class="badge bg-success price-badge">৳80</span>
                </div>
                <button class="btn btn-success w-100 order-btn">Order</button>
              </div>
            </div>
            
          </div>
          <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="https://i0.wp.com/www.spiceandcolour.com/wp-content/uploads/2020/06/sabji-1.jpg?fit=1140%2C760&ssl=1" class="card-img-top" style="height:140px;object-fit:cover;">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title">Vegetable Curry</h5>
                  <span class="badge bg-success price-badge">৳100</span>
                </div>
                <button class="btn btn-success w-100 order-btn">Order</button>
              </div>
            </div>
            
          </div>
          <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="https://www.chainbaker.com/wp-content/uploads/2021/05/IMG_1918.jpg" class="card-img-top" style="height:140px;object-fit:cover;">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title">Bakarkhani</h5>
                  <span class="badge bg-success price-badge">৳50</span>
                </div>
                <button class="btn btn-success w-100 order-btn">Order</button>
              </div>
            </div>
            
          </div>
          <div class="col">
            <div class="card shadow menu-card border-success h-100">
              <img src="https://i.ytimg.com/vi/D2AgyOh0zJg/hq720.jpg?sqp=-oaymwEhCK4FEIIDSFryq4qpAxMIARUAAAAAGAElAADIQj0AgKJD&rs=AOn4CLCPbdGrghQeSTTZpm43XmAlRCh4Ag" class="card-img-top" style="height:140px;object-fit:cover;">
              <div class="card-body">
                <div class="card-title-price">
                  <h5 class="card-title">Beef Haleem</h5>
                  <span class="badge bg-success price-badge">৳150</span>
                </div>
                <button class="btn btn-success w-100 order-btn">Order</button>
              </div>
            </div>
            
          </div>

        <!-- More cards ... -->
      </div>
    </div>
  </section>

  <!-- ABOUT US SECTION -->
  <section id="aboutusSection" class="about-section py-5">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="about-heading">About <span class="highlight-text">Green Bites</span></h2>
        <div class="heading-underline mx-auto"></div>
      </div>
      
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <div class="about-content">
            <p class="lead">
              <span class="highlight-word">Green Bites</span> is a <span class="highlight-word">modern canteen management system</span> 
              designed specifically for university students and teachers. We believe that great food 
              fuels great minds!
            </p>
            <p>
              We created Green Bites to <span class="highlight-word">revolutionize</span> the campus dining experience. 
              No more long queues, no more confusion—just <span class="highlight-word">quick ordering</span>, 
              <span class="highlight-word">easy payments</span>, and delicious food delivered to your table. 
              Our platform helps reduce food waste and makes the entire canteen experience smoother for everyone.
            </p>
            <p>
              At Green Bites, we're committed to <span class="highlight-word">freshness</span>, 
              <span class="highlight-word">hygiene</span>, and authentic <span class="highlight-word">Bangladeshi flavors</span>. 
              Combined with our user-friendly technology, we're bringing a new era of campus dining 
              right to your fingertips.
            </p>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="features-grid">
            <div class="feature-card">
              <div class="feature-icon">🚀</div>
              <h5>Instant Ordering</h5>
              <p>Order food in seconds with our simple interface</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">📋</div>
              <h5>Live Menu Updates</h5>
              <p>Real-time menu with availability status</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">🔒</div>
              <h5>Secure Payments</h5>
              <p>Multiple payment options including bKash & cards</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">💰</div>
              <h5>Student-Friendly Pricing</h5>
              <p>Affordable meals for every budget</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- MEET THE TEAM SECTION -->
  <section id="teamSection" class="team-section py-5">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="team-heading">Meet the <span class="highlight-text">Developers</span></h2>
        <div class="heading-underline mx-auto"></div>
        <p class="text-muted mt-3">The talented minds behind Green Bites</p>
      </div>
      
      <div class="row justify-content-center g-4">
        <!-- Team Member 1 -->
        <div class="col-md-6 col-lg-5">
          <div class="team-card">
            <div class="team-card-inner">
              <div class="team-avatar">
                <img src="sajjad.jpg" alt="Developer 1">
              </div>
              <div class="team-info">
                <h4 class="team-name">Md. Sajjad Mahmud Suton</h4>
                <span class="team-role">Full-Stack Developer</span>
                <p class="team-desc">
                  Worked on UI/UX design, frontend development with Bootstrap, 
                  and Firebase integration for real-time data management.
                </p>
                <div class="team-links">
                  <a href="mailto:sajjadmahmudsuton@gmail.com" class="team-link" title="Email">
                    <i class="bi bi-envelope-fill"></i>
                  </a>
                  <a href="https://github.com/Sajjad-Mahmud-Suton" class="team-link" title="GitHub">
                    <i class="bi bi-github"></i>
                  </a>
                  <a href="https://www.facebook.com/sajjadmahmudsuton.suton" class="team-link" title="Facebook">
                    <i class="bi bi-facebook"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Team Member 2 -->
        <div class="col-md-6 col-lg-5">
          <div class="team-card">
            <div class="team-card-inner">
              <div class="team-avatar">
                <img src="https://ui-avatars.com/api/?name=Esha+Akter&background=1f9ebd&color=fff&size=150" alt="Developer 2">
              </div>
              <div class="team-info">
                <h4 class="team-name">Esha Akter</h4>
                <span class="team-role">Frontend Developer</span>
                <p class="team-desc">
                  Contributed to responsive design, UI components, 
                  and ensuring a seamless user experience across devices.
                </p>
                <div class="team-links">
                  <a href="mailto:mstehsa981@gmail.com" class="team-link" title="Email">
                    <i class="bi bi-envelope-fill"></i>
                  </a>
                  <a href="https://github.com/Esha-Akter" class="team-link" title="GitHub">
                    <i class="bi bi-github"></i>
                  </a>
                  <a href="#" class="team-link" title="Facebook">
                    <i class="bi bi-facebook"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- COMPLAINTS SECTION -->
  <section id="complaintsSection" class="mb-5">
    <div class="container">
      <h3 class="mb-3 text-danger">Complaint</h3>
      <form id="complaintForm" class="card p-3 shadow-sm">
        <div class="mb-3">
          <label class="form-label">Order ID (optional)</label>
          <input type="text" class="form-control" id="complaintOrderId">
        </div>
        <div class="mb-3">
          <label class="form-label">Complaint</label>
          <textarea class="form-control" id="complaintText" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Add Image (optional)</label>
          <input type="file" class="form-control" id="complaintImage" accept="image/*">
        </div>
        <button type="submit" class="btn btn-danger w-100">Submit</button>
        <small id="complaintMsg" class="text-success ms-2"></small>
      </form>
    </div>
  </section>

  
<?php include 'includes/footer.php'; ?>
</body>
</html>
